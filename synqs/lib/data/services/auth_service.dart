import 'dart:io';
import 'package:device_info_plus/device_info_plus.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:package_info_plus/package_info_plus.dart';
import '../datasources/api_client.dart';
import '../models/auth_response.dart';
import '../models/login_request.dart';
import '../models/user.dart';
import '../../core/constants/api_constants.dart';
import '../../core/network/dio_client.dart';
import '../../core/storage/hive_config.dart';

class AuthService {
  static const FlutterSecureStorage _secureStorage = FlutterSecureStorage();

  final GoogleSignIn _googleSignIn = GoogleSignIn(
    scopes: [
      'email',
      'profile',
      'https://www.googleapis.com/auth/calendar',
      'https://www.googleapis.com/auth/calendar.events',
      'https://www.googleapis.com/auth/calendar.readonly',
      'https://www.googleapis.com/auth/calendar.events.readonly',
    ],
  );

  late final ApiClient _apiClient;

  AuthService() {
    _apiClient = ApiClient(DioClient.instance);
  }

  /// Get current authenticated user
  Future<User?> getCurrentUser() async {
    try {
      final token = await _secureStorage.read(key: ApiConstants.tokenKey);
      if (token == null) return null;

      // Try to get user from local storage first
      final userBox = HiveConfig.getUserBox();
      final userData = userBox.get(ApiConstants.userKey);

      if (userData != null && userData is Map) {
        try {
          return User.fromJson(Map<String, dynamic>.from(userData));
        } catch (e) {
          // If local data is corrupted, fetch from API
        }
      }

      // Fetch fresh user data from API
      final user = await _apiClient.getCurrentUser();
      await _storeUserData(user);
      return user;
    } catch (e) {
      // If API call fails but we have a token, try to use cached data
      final token = await _secureStorage.read(key: ApiConstants.tokenKey);
      if (token != null) {
        final userBox = HiveConfig.getUserBox();
        final userData = userBox.get(ApiConstants.userKey);
        if (userData != null && userData is Map) {
          try {
            return User.fromJson(Map<String, dynamic>.from(userData));
          } catch (e) {
            // Cached data is corrupted, logout
            await logout();
          }
        }
      }
      return null;
    }
  }

  /// Login with email and password or Google token
  Future<User> login({
    String? email,
    String? password,
    String? googleToken,
  }) async {
    try {
      String deviceName = await _getDeviceName();

      AuthResponse authResponse;

      if (googleToken != null || (email == null && password == null)) {
        // Google authentication
        final googleUser = await _signInWithGoogle();
        final googleAuth = await googleUser.authentication;

        authResponse = await _apiClient.login(LoginRequest(
          googleToken: googleAuth.accessToken,
          deviceName: deviceName,
        ));
      } else if (email != null && password != null) {
        // Email/password authentication
        authResponse = await _apiClient.login(LoginRequest(
          email: email,
          password: password,
          deviceName: deviceName,
        ));
      } else {
        throw Exception('Invalid login parameters');
      }

      // Store authentication data
      await _secureStorage.write(key: ApiConstants.tokenKey, value: authResponse.token);
      if (authResponse.refreshToken != null) {
        await _secureStorage.write(key: ApiConstants.refreshTokenKey, value: authResponse.refreshToken!);
      }
      await _storeUserData(authResponse.user);

      return authResponse.user;
    } catch (e) {
      // Clean up any partial authentication state
      await _clearAuthData();
      throw Exception('Login failed: ${e.toString()}');
    }
  }

  /// Google Sign-In helper
  Future<GoogleSignInAccount> _signInWithGoogle() async {
    try {
      // Sign out first to ensure account selection
      await _googleSignIn.signOut();

      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();
      if (googleUser == null) {
        throw Exception('Google sign in was cancelled');
      }

      return googleUser;
    } catch (e) {
      throw Exception('Google sign in failed: ${e.toString()}');
    }
  }

  /// Logout user
  Future<void> logout() async {
    try {
      // Try to call logout API (best effort)
      try {
        await _apiClient.logout();
      } catch (e) {
        // API logout failed, but continue with local cleanup
      }

      // Sign out from Google if signed in
      if (await _googleSignIn.isSignedIn()) {
        await _googleSignIn.signOut();
      }

      // Clear all stored authentication data
      await _clearAuthData();
    } catch (e) {
      // Ensure cleanup happens even if there are errors
      await _clearAuthData();
      throw Exception('Logout failed: ${e.toString()}');
    }
  }

  /// Get stored auth token
  Future<String?> getAuthToken() async {
    return await _secureStorage.read(key: ApiConstants.tokenKey);
  }

  /// Check if user is authenticated
  Future<bool> isAuthenticated() async {
    final token = await getAuthToken();
    return token != null && token.isNotEmpty;
  }

  /// Refresh authentication token
  Future<bool> refreshToken() async {
    try {
      final refreshToken = await _secureStorage.read(key: ApiConstants.refreshTokenKey);
      if (refreshToken == null) return false;

      final deviceName = await _getDeviceName();
      final authResponse = await _apiClient.refreshToken({
        'refresh_token': refreshToken,
        'device_name': deviceName,
      });

      // Update stored tokens
      await _secureStorage.write(key: ApiConstants.tokenKey, value: authResponse.token);
      if (authResponse.refreshToken != null) {
        await _secureStorage.write(key: ApiConstants.refreshTokenKey, value: authResponse.refreshToken!);
      }

      // Update user data if provided
      await _storeUserData(authResponse.user);

      return true;
    } catch (e) {
      // Refresh failed, clear auth data
      await _clearAuthData();
      return false;
    }
  }

  /// Store user data locally
  Future<void> _storeUserData(User user) async {
    final userBox = HiveConfig.getUserBox();
    await userBox.put(ApiConstants.userKey, user.toJson());
  }

  /// Clear all authentication data
  Future<void> _clearAuthData() async {
    await _secureStorage.delete(key: ApiConstants.tokenKey);
    await _secureStorage.delete(key: ApiConstants.refreshTokenKey);

    final userBox = HiveConfig.getUserBox();
    await userBox.delete(ApiConstants.userKey);
  }

  /// Get device name for token identification
  Future<String> _getDeviceName() async {
    try {
      final deviceInfo = DeviceInfoPlugin();
      final packageInfo = await PackageInfo.fromPlatform();

      if (Platform.isAndroid) {
        final androidInfo = await deviceInfo.androidInfo;
        return '${packageInfo.appName} on ${androidInfo.brand} ${androidInfo.model}';
      } else if (Platform.isIOS) {
        final iosInfo = await deviceInfo.iosInfo;
        return '${packageInfo.appName} on ${iosInfo.name}';
      } else {
        return '${packageInfo.appName} Mobile';
      }
    } catch (e) {
      return 'Synqs Mobile App';
    }
  }
}