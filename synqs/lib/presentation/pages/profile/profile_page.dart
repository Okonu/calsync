import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../providers/auth_provider.dart';
import '../auth/login_page.dart';

class ProfilePage extends ConsumerWidget {
  const ProfilePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Profile'),
        actions: [
          IconButton(
            icon: const Icon(Icons.settings),
            onPressed: () {
              // TODO: Navigate to settings
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Settings coming soon')),
              );
            },
          ),
        ],
      ),
      body: authState.when(
        data: (user) {
          if (user == null) {
            return const Center(
              child: Text('No user data available'),
            );
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                // Profile Header
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 30,
                          backgroundColor: Theme.of(context).colorScheme.primary,
                          child: Text(
                            user.name.isNotEmpty ? user.name[0].toUpperCase() : '?',
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                user.name,
                                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                user.email,
                                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                  color: Theme.of(context).colorScheme.onSurface.withOpacity(0.7),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),

                // Menu Items
                _buildMenuSection(
                  context,
                  'Account',
                  [
                    _buildMenuItem(
                      context,
                      Icons.person_outline,
                      'Edit Profile',
                      () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Edit profile coming soon')),
                        );
                      },
                    ),
                    _buildMenuItem(
                      context,
                      Icons.notifications_outlined,
                      'Notifications',
                      () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Notification settings coming soon')),
                        );
                      },
                    ),
                    _buildMenuItem(
                      context,
                      Icons.security_outlined,
                      'Privacy & Security',
                      () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Privacy settings coming soon')),
                        );
                      },
                    ),
                  ],
                ),

                _buildMenuSection(
                  context,
                  'Calendar',
                  [
                    _buildMenuItem(
                      context,
                      Icons.calendar_today_outlined,
                      'Connected Calendars',
                      () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Calendar settings coming soon')),
                        );
                      },
                    ),
                    _buildMenuItem(
                      context,
                      Icons.sync_outlined,
                      'Sync Settings',
                      () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Sync settings coming soon')),
                        );
                      },
                    ),
                  ],
                ),

                _buildMenuSection(
                  context,
                  'Support',
                  [
                    _buildMenuItem(
                      context,
                      Icons.help_outline,
                      'Help & Support',
                      () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Help center coming soon')),
                        );
                      },
                    ),
                    _buildMenuItem(
                      context,
                      Icons.info_outline,
                      'About',
                      () {
                        showAboutDialog(
                          context: context,
                          applicationName: 'Synqs',
                          applicationVersion: '1.0.0',
                          applicationIcon: const Icon(Icons.calendar_today),
                        );
                      },
                    ),
                  ],
                ),

                const SizedBox(height: 24),

                // Logout Button
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () => _showLogoutDialog(context, ref),
                    icon: const Icon(Icons.logout),
                    label: const Text('Sign Out'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Theme.of(context).colorScheme.error,
                      foregroundColor: Colors.white,
                    ),
                  ),
                ),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stackTrace) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 64, color: Colors.grey),
              const SizedBox(height: 16),
              Text('Error loading profile: $error'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.refresh(authProvider),
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMenuSection(BuildContext context, String title, List<Widget> items) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(left: 16, bottom: 8),
          child: Text(
            title,
            style: Theme.of(context).textTheme.titleSmall?.copyWith(
              fontWeight: FontWeight.bold,
              color: Theme.of(context).colorScheme.primary,
            ),
          ),
        ),
        Card(
          child: Column(children: items),
        ),
        const SizedBox(height: 16),
      ],
    );
  }

  Widget _buildMenuItem(
    BuildContext context,
    IconData icon,
    String title,
    VoidCallback onTap,
  ) {
    return ListTile(
      leading: Icon(icon),
      title: Text(title),
      trailing: const Icon(Icons.chevron_right),
      onTap: onTap,
    );
  }

  void _showLogoutDialog(BuildContext context, WidgetRef ref) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Sign Out'),
          content: const Text('Are you sure you want to sign out?'),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('Cancel'),
            ),
            ElevatedButton(
              onPressed: () async {
                Navigator.of(context).pop();
                await ref.read(authProvider.notifier).logout();
                if (context.mounted) {
                  Navigator.of(context).pushAndRemoveUntil(
                    MaterialPageRoute(builder: (_) => const LoginPage()),
                    (route) => false,
                  );
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: Theme.of(context).colorScheme.error,
                foregroundColor: Colors.white,
              ),
              child: const Text('Sign Out'),
            ),
          ],
        );
      },
    );
  }
}