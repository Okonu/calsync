import 'package:riverpod_annotation/riverpod_annotation.dart';
import '../../data/models/user.dart';
import '../../data/services/auth_service.dart';

part 'auth_provider.g.dart';

@riverpod
class Auth extends _$Auth {
  @override
  Future<User?> build() async {
    final authService = ref.read(authServiceProvider);
    return await authService.getCurrentUser();
  }

  Future<void> login({
    String? email,
    String? password,
    String? googleToken,
  }) async {
    state = const AsyncLoading();

    try {
      final authService = ref.read(authServiceProvider);
      final user = await authService.login(
        email: email,
        password: password,
        googleToken: googleToken,
      );
      state = AsyncData(user);
    } catch (e, stackTrace) {
      state = AsyncError(e, stackTrace);
    }
  }

  Future<void> logout() async {
    try {
      final authService = ref.read(authServiceProvider);
      await authService.logout();
      state = const AsyncData(null);
    } catch (e, stackTrace) {
      state = AsyncError(e, stackTrace);
    }
  }

  Future<void> refreshUser() async {
    try {
      final authService = ref.read(authServiceProvider);
      final user = await authService.getCurrentUser();
      state = AsyncData(user);
    } catch (e, stackTrace) {
      state = AsyncError(e, stackTrace);
    }
  }
}

@riverpod
AuthService authService(AuthServiceRef ref) {
  return AuthService();
}