import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../auth/login_page.dart';
import '../main/main_page.dart';
import '../../providers/auth_provider.dart';

class SplashPage extends ConsumerStatefulWidget {
  const SplashPage({super.key});

  @override
  ConsumerState<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends ConsumerState<SplashPage> {
  @override
  void initState() {
    super.initState();
    _initializeApp();
  }

  Future<void> _initializeApp() async {
    // Add initialization logic here
    await Future.delayed(const Duration(seconds: 2));

    if (mounted) {
      _navigateToNextScreen();
    }
  }

  void _navigateToNextScreen() {
    final authState = ref.read(authProvider);

    authState.when(
      data: (user) {
        if (user != null) {
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(builder: (_) => const MainPage()),
          );
        } else {
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(builder: (_) => const LoginPage()),
          );
        }
      },
      loading: () {
        // Stay on splash screen
      },
      error: (_, __) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const LoginPage()),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Theme.of(context).colorScheme.primary,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // App Logo/Icon
            Container(
              width: 120,
              height: 120,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 20,
                    offset: const Offset(0, 10),
                  ),
                ],
              ),
              child: const Icon(
                Icons.calendar_today_rounded,
                size: 60,
                color: Color(0xFF3B82F6),
              ),
            ),
            const SizedBox(height: 32),
            // App Name
            Text(
              'Synqs',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.bold,
                fontSize: 48,
              ),
            ),
            const SizedBox(height: 8),
            // Tagline
            Text(
              'Calendar & Community Management',
              style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                color: Colors.white.withOpacity(0.9),
                fontSize: 16,
              ),
            ),
            const SizedBox(height: 48),
            // Loading indicator
            const CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
            ),
          ],
        ),
      ),
    );
  }
}