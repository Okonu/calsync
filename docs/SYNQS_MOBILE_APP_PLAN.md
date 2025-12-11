# Synqs - CalSync Mobile App Development Plan

## Overview

Synqs is the Flutter mobile application for CalSync, providing comprehensive calendar management, booking, and community features across iOS and Android platforms.

## Technology Stack

### Framework
- **Flutter** (Latest stable version 3.19+)
- **Dart** programming language

### State Management
- **Riverpod** for state management
- **Freezed** for immutable data classes
- **Json Annotation** for JSON serialization

### UI Framework
- **Material Design 3** components
- **Custom theme** matching CalSync branding
- **Responsive design** for various screen sizes

### Network & API
- **Dio** for HTTP requests
- **Retrofit** for API client generation
- **Pretty Dio Logger** for debugging

### Storage & Caching
- **Hive** for local storage
- **Secure Storage** for sensitive data (tokens)
- **Cached Network Image** for image caching

### Authentication
- **Google Sign-In** package
- **Flutter Secure Storage** for token management
- **Biometric authentication** support

### Calendar Integration
- **Device Calendar** plugin
- **Table Calendar** for custom calendar views
- **Timezone** package for date/time handling

### Push Notifications
- **Firebase Cloud Messaging** for push notifications
- **Flutter Local Notifications** for local notifications

### Development Tools
- **Build Runner** for code generation
- **Flutter Launcher Icons** for app icons
- **Flutter Native Splash** for splash screens

## Project Structure

```
synqs/
├── lib/
│   ├── main.dart                 # App entry point
│   ├── app.dart                  # App configuration
│   ├── core/                     # Core utilities
│   │   ├── constants/           # App constants
│   │   ├── errors/              # Error handling
│   │   ├── network/             # Network configuration
│   │   ├── storage/             # Storage services
│   │   ├── theme/               # App theming
│   │   └── utils/               # Utility functions
│   ├── data/                    # Data layer
│   │   ├── datasources/         # API data sources
│   │   ├── models/              # Data models
│   │   ├── repositories/        # Repository implementations
│   │   └── services/            # External services
│   ├── domain/                  # Domain layer
│   │   ├── entities/            # Business entities
│   │   ├── repositories/        # Repository interfaces
│   │   └── usecases/            # Business logic
│   ├── presentation/            # Presentation layer
│   │   ├── pages/               # App pages/screens
│   │   │   ├── auth/           # Authentication screens
│   │   │   ├── calendar/       # Calendar screens
│   │   │   ├── bookings/       # Booking screens
│   │   │   ├── communities/    # Community screens
│   │   │   └── profile/        # Profile screens
│   │   ├── widgets/             # Reusable widgets
│   │   └── providers/           # Riverpod providers
│   └── generated/               # Generated code
├── assets/                      # App assets
│   ├── icons/                  # App icons
│   ├── images/                 # Images
│   └── fonts/                  # Custom fonts
├── test/                       # Unit tests
├── integration_test/           # Integration tests
└── android/ios/               # Platform-specific code
```

## Core Features Implementation

### 1. Authentication Setup

#### pubspec.yaml dependencies
```yaml
dependencies:
  flutter:
    sdk: flutter

  # State Management
  flutter_riverpod: ^2.4.9
  riverpod_annotation: ^2.3.3

  # Network
  dio: ^5.4.0
  retrofit: ^4.0.3
  json_annotation: ^4.8.1

  # Storage
  hive: ^2.2.3
  hive_flutter: ^1.1.0
  flutter_secure_storage: ^9.0.0

  # UI
  google_fonts: ^6.1.0
  cached_network_image: ^3.3.0

  # Authentication
  google_sign_in: ^6.2.1
  local_auth: ^2.1.7

  # Calendar
  table_calendar: ^3.0.9
  device_calendar: ^4.3.2

  # Utilities
  intl: ^0.19.0
  timezone: ^0.9.2

dev_dependencies:
  flutter_test:
    sdk: flutter

  # Code Generation
  build_runner: ^2.4.7
  riverpod_generator: ^2.3.9
  retrofit_generator: ^8.0.4
  json_serializable: ^6.7.1
  hive_generator: ^2.0.1

  # Development Tools
  flutter_launcher_icons: ^0.13.1
  flutter_native_splash: ^2.3.8
  very_good_analysis: ^5.1.0
```

### 2. API Client Setup

```dart
// lib/data/datasources/api_client.dart
import 'package:dio/dio.dart';
import 'package:retrofit/retrofit.dart';
import 'package:json_annotation/json_annotation.dart';

part 'api_client.g.dart';

@RestApi(baseUrl: "https://your-domain.com/api/")
abstract class ApiClient {
  factory ApiClient(Dio dio, {String baseUrl}) = _ApiClient;

  // Authentication
  @POST("/auth/login")
  Future<AuthResponse> login(@Body() LoginRequest request);

  @POST("/auth/logout")
  Future<void> logout();

  @GET("/auth/me")
  Future<User> getCurrentUser();

  // Calendar
  @GET("/calendar/events")
  Future<List<CalendarEvent>> getEvents(
    @Query("start") String start,
    @Query("end") String end,
    @Query("calendar_ids[]") List<int>? calendarIds,
  );

  @GET("/calendar/calendars")
  Future<List<Calendar>> getCalendars();

  @PUT("/calendar/calendars/{id}")
  Future<Calendar> updateCalendar(
    @Path("id") int id,
    @Body() UpdateCalendarRequest request,
  );

  // Bookings
  @GET("/bookings")
  Future<PaginatedResponse<Booking>> getBookings(@Query("page") int page);

  @POST("/bookings/{id}/cancel")
  Future<Booking> cancelBooking(@Path("id") int id);

  @GET("/public/booking/{slug}")
  Future<BookingPage> getPublicBookingPage(@Path("slug") String slug);

  @GET("/public/booking/{slug}/slots")
  Future<List<TimeSlot>> getAvailableSlots(
    @Path("slug") String slug,
    @Query("date") String date,
  );

  @POST("/public/booking/{slug}")
  Future<Booking> createBooking(
    @Path("slug") String slug,
    @Body() CreateBookingRequest request,
  );

  // Communities
  @GET("/communities")
  Future<PaginatedResponse<Community>> getCommunities(@Query("page") int page);

  @GET("/communities/{id}")
  Future<Community> getCommunity(@Path("id") int id);

  @POST("/communities")
  Future<Community> createCommunity(@Body() CreateCommunityRequest request);

  @GET("/communities/{id}/events")
  Future<PaginatedResponse<CommunityEvent>> getCommunityEvents(
    @Path("id") int id,
    @Query("page") int page,
  );

  // Public endpoints
  @GET("/public/communities")
  Future<PaginatedResponse<Community>> getPublicCommunities(@Query("page") int page);

  @GET("/public/communities/{slug}")
  Future<Community> getPublicCommunity(@Path("slug") String slug);
}
```

### 3. State Management with Riverpod

```dart
// lib/presentation/providers/auth_provider.dart
import 'package:riverpod_annotation/riverpod_annotation.dart';
import 'package:synqs/data/models/user.dart';
import 'package:synqs/data/services/auth_service.dart';

part 'auth_provider.g.dart';

@riverpod
class AuthNotifier extends _$AuthNotifier {
  @override
  Future<User?> build() async {
    final authService = ref.read(authServiceProvider);
    return await authService.getCurrentUser();
  }

  Future<void> login({String? email, String? password, String? googleToken}) async {
    state = const AsyncLoading();

    try {
      final authService = ref.read(authServiceProvider);
      final user = await authService.login(
        email: email,
        password: password,
        googleToken: googleToken,
      );
      state = AsyncData(user);
    } catch (e) {
      state = AsyncError(e, StackTrace.current);
    }
  }

  Future<void> logout() async {
    final authService = ref.read(authServiceProvider);
    await authService.logout();
    state = const AsyncData(null);
  }
}

@riverpod
AuthService authService(AuthServiceRef ref) {
  return AuthService(ref.read(apiClientProvider));
}
```

### 4. Calendar Widget

```dart
// lib/presentation/widgets/calendar/calendar_widget.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:table_calendar/table_calendar.dart';
import 'package:synqs/presentation/providers/calendar_provider.dart';

class CalendarWidget extends ConsumerStatefulWidget {
  const CalendarWidget({super.key});

  @override
  ConsumerState<CalendarWidget> createState() => _CalendarWidgetState();
}

class _CalendarWidgetState extends ConsumerState<CalendarWidget> {
  DateTime _focusedDay = DateTime.now();
  DateTime? _selectedDay;

  @override
  Widget build(BuildContext context) {
    final eventsAsync = ref.watch(calendarEventsProvider(
      start: DateTime(_focusedDay.year, _focusedDay.month, 1),
      end: DateTime(_focusedDay.year, _focusedDay.month + 1, 0),
    ));

    return Column(
      children: [
        TableCalendar<CalendarEvent>(
          firstDay: DateTime.utc(2020, 1, 1),
          lastDay: DateTime.utc(2030, 12, 31),
          focusedDay: _focusedDay,
          selectedDayPredicate: (day) => isSameDay(_selectedDay, day),
          eventLoader: (day) {
            return eventsAsync.when(
              data: (events) => events
                  .where((event) => isSameDay(event.startDateTime, day))
                  .toList(),
              loading: () => [],
              error: (_, __) => [],
            );
          },
          calendarStyle: CalendarStyle(
            outsideDaysVisible: false,
            markerDecoration: BoxDecoration(
              color: Theme.of(context).primaryColor,
              shape: BoxShape.circle,
            ),
          ),
          onDaySelected: (selectedDay, focusedDay) {
            setState(() {
              _selectedDay = selectedDay;
              _focusedDay = focusedDay;
            });
          },
          onPageChanged: (focusedDay) {
            setState(() {
              _focusedDay = focusedDay;
            });
          },
        ),
        const SizedBox(height: 16),
        Expanded(
          child: eventsAsync.when(
            data: (events) {
              final dayEvents = events
                  .where((event) => isSameDay(event.startDateTime, _selectedDay ?? _focusedDay))
                  .toList();

              if (dayEvents.isEmpty) {
                return const Center(
                  child: Text('No events for this day'),
                );
              }

              return ListView.builder(
                itemCount: dayEvents.length,
                itemBuilder: (context, index) {
                  final event = dayEvents[index];
                  return EventTile(event: event);
                },
              );
            },
            loading: () => const Center(child: CircularProgressIndicator()),
            error: (error, _) => Center(
              child: Text('Error loading events: $error'),
            ),
          ),
        ),
      ],
    );
  }
}
```

### 5. Main App Structure

```dart
// lib/main.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'package:synqs/app.dart';
import 'package:synqs/core/storage/hive_config.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Hive
  await Hive.initFlutter();
  await HiveConfig.init();

  runApp(
    const ProviderScope(
      child: SynqsApp(),
    ),
  );
}
```

```dart
// lib/app.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:synqs/core/theme/app_theme.dart';
import 'package:synqs/presentation/pages/splash/splash_page.dart';

class SynqsApp extends ConsumerWidget {
  const SynqsApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return MaterialApp(
      title: 'Synqs',
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: ThemeMode.system,
      home: const SplashPage(),
    );
  }
}
```

## Development Phases

### Phase 1: Project Setup & Authentication (Week 1-2)
- [ ] Flutter project initialization
- [ ] Dependency setup and configuration
- [ ] App theming and branding
- [ ] Google Sign-In integration
- [ ] API client setup with Dio/Retrofit
- [ ] Secure storage implementation

### Phase 2: Core Navigation & Calendar (Week 3-4)
- [ ] Bottom navigation setup
- [ ] Calendar view implementation
- [ ] Event listing and details
- [ ] Calendar management features
- [ ] Google Calendar sync

### Phase 3: Booking System (Week 5-6)
- [ ] Booking page discovery and display
- [ ] Available time slots selection
- [ ] Booking creation flow
- [ ] User bookings management
- [ ] QR code generation for booking links

### Phase 4: Community Features (Week 7-8)
- [ ] Community listing and search
- [ ] Community detail views
- [ ] Event management for communities
- [ ] Speaker management
- [ ] Call for Speakers functionality

### Phase 5: Advanced Features (Week 9-10)
- [ ] Push notifications setup
- [ ] Offline support with local caching
- [ ] Biometric authentication
- [ ] Dark mode support
- [ ] App settings and preferences

### Phase 6: Testing & Deployment (Week 11-12)
- [ ] Unit and widget testing
- [ ] Integration testing
- [ ] Performance optimization
- [ ] App store preparation
- [ ] Beta testing and feedback

## Key Flutter Packages

```yaml
# Core packages
flutter_riverpod: ^2.4.9      # State management
go_router: ^12.1.3            # Navigation
dio: ^5.4.0                   # HTTP client
hive: ^2.2.3                  # Local storage

# UI packages
google_fonts: ^6.1.0          # Typography
cached_network_image: ^3.3.0  # Image caching
shimmer: ^3.0.0               # Loading animations
lottie: ^2.7.0                # Animations

# Platform features
google_sign_in: ^6.2.1        # Google authentication
local_auth: ^2.1.7            # Biometric auth
device_calendar: ^4.3.2       # Calendar integration
url_launcher: ^6.2.2          # External links

# Development
build_runner: ^2.4.7          # Code generation
flutter_launcher_icons: ^0.13.1  # App icons
flutter_native_splash: ^2.3.8    # Splash screen
```

## Design Principles

1. **Material Design 3**: Follow latest Material Design guidelines
2. **Responsive Design**: Adapt to different screen sizes
3. **Accessibility**: Full accessibility support
4. **Performance**: 60fps animations and smooth scrolling
5. **Offline-First**: Core features work offline

This comprehensive plan provides a roadmap for developing Synqs as a full-featured Flutter mobile app that seamlessly integrates with the CalSync platform.