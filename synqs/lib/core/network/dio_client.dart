import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:pretty_dio_logger/pretty_dio_logger.dart';
import '../constants/api_constants.dart';

class DioClient {
  static Dio? _dio;

  static Dio get instance {
    _dio ??= _createDio();
    return _dio!;
  }

  static Dio _createDio() {
    final dio = Dio(
      BaseOptions(
        baseUrl: ApiConstants.baseUrl,
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
        sendTimeout: const Duration(seconds: 30),
        headers: {
          ApiConstants.contentType: ApiConstants.applicationJson,
          ApiConstants.accept: ApiConstants.applicationJson,
        },
      ),
    );

    // Add interceptors
    dio.interceptors.add(_AuthInterceptor());
    dio.interceptors.add(_ErrorInterceptor());

    // Add pretty logger for debug builds
    dio.interceptors.add(
      PrettyDioLogger(
        requestHeader: true,
        requestBody: true,
        responseBody: true,
        responseHeader: false,
        error: true,
        compact: true,
        maxWidth: 90,
      ),
    );

    return dio;
  }

  static void updateBaseUrl(String newBaseUrl) {
    _dio?.options.baseUrl = newBaseUrl;
  }

  static void clearInstance() {
    _dio?.close();
    _dio = null;
  }
}

class _AuthInterceptor extends Interceptor {
  static const FlutterSecureStorage _secureStorage = FlutterSecureStorage();

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) async {
    // Add auth token to requests
    final token = await _secureStorage.read(key: ApiConstants.tokenKey);
    if (token != null && token.isNotEmpty) {
      options.headers[ApiConstants.authHeader] = 'Bearer $token';
    }

    handler.next(options);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    // Handle 401 unauthorized errors
    if (err.response?.statusCode == 401) {
      // Try to refresh token
      final refreshed = await _refreshToken();
      if (refreshed) {
        // Retry the original request
        try {
          final response = await DioClient.instance.fetch(err.requestOptions);
          handler.resolve(response);
          return;
        } catch (e) {
          // If retry fails, proceed with original error
        }
      } else {
        // Clear auth data if refresh fails
        await _clearAuthData();
      }
    }

    handler.next(err);
  }

  Future<bool> _refreshToken() async {
    try {
      final refreshToken = await _secureStorage.read(key: ApiConstants.refreshTokenKey);
      if (refreshToken == null) return false;

      final response = await DioClient.instance.post(
        ApiConstants.refresh,
        data: {'refresh_token': refreshToken},
      );

      if (response.statusCode == 200) {
        final data = response.data;
        await _secureStorage.write(key: ApiConstants.tokenKey, value: data['token']);
        if (data['refresh_token'] != null) {
          await _secureStorage.write(key: ApiConstants.refreshTokenKey, value: data['refresh_token']);
        }
        return true;
      }
    } catch (e) {
      // Refresh failed
    }
    return false;
  }

  Future<void> _clearAuthData() async {
    await _secureStorage.delete(key: ApiConstants.tokenKey);
    await _secureStorage.delete(key: ApiConstants.refreshTokenKey);
    await _secureStorage.delete(key: ApiConstants.userKey);
  }
}

class _ErrorInterceptor extends Interceptor {
  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    // Transform DioException to more user-friendly errors
    String message = 'An error occurred';

    switch (err.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        message = 'Connection timeout. Please check your internet connection.';
        break;
      case DioExceptionType.badResponse:
        final statusCode = err.response?.statusCode;
        switch (statusCode) {
          case 400:
            message = 'Bad request. Please check your input.';
            break;
          case 401:
            message = 'Unauthorized. Please login again.';
            break;
          case 403:
            message = 'Access forbidden. You don\'t have permission.';
            break;
          case 404:
            message = 'Resource not found.';
            break;
          case 422:
            // Validation errors
            final errors = err.response?.data?['errors'];
            if (errors is Map) {
              final firstError = errors.values.first;
              if (firstError is List && firstError.isNotEmpty) {
                message = firstError.first.toString();
              }
            } else {
              message = err.response?.data?['message'] ?? 'Validation failed.';
            }
            break;
          case 500:
            message = 'Server error. Please try again later.';
            break;
          default:
            message = 'HTTP Error: $statusCode';
        }
        break;
      case DioExceptionType.connectionError:
        message = 'No internet connection. Please check your network.';
        break;
      case DioExceptionType.cancel:
        message = 'Request was cancelled.';
        break;
      default:
        message = 'An unexpected error occurred.';
    }

    // Create a new exception with user-friendly message
    final newError = DioException(
      requestOptions: err.requestOptions,
      response: err.response,
      type: err.type,
      error: message,
      message: message,
    );

    handler.next(newError);
  }
}