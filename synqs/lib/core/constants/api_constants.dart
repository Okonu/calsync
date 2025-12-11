class ApiConstants {
  // Base URL - Update this to match your Laravel Valet domain
  static const String baseUrl = 'http://calsync.test/api/';

  // Alternative for local development with Laravel serve
  // static const String baseUrl = 'http://127.0.0.1:8000/api/';

  // Production URL (when deployed)
  // static const String baseUrl = 'https://your-domain.com/api/';

  // API Endpoints
  static const String login = 'auth/login';
  static const String logout = 'auth/logout';
  static const String me = 'auth/me';
  static const String refresh = 'auth/refresh';

  // Calendar endpoints
  static const String events = 'calendar/events';
  static const String calendars = 'calendar/calendars';
  static const String accounts = 'calendar/accounts';

  // Booking endpoints
  static const String bookings = 'bookings';
  static const String bookingSettings = 'bookings/settings';

  // Community endpoints
  static const String communities = 'communities';
  static const String publicCommunities = 'public/communities';

  // Public booking endpoints
  static const String publicBooking = 'public/booking';

  // Headers
  static const String authHeader = 'Authorization';
  static const String contentType = 'Content-Type';
  static const String accept = 'Accept';
  static const String applicationJson = 'application/json';

  // Storage keys
  static const String tokenKey = 'auth_token';
  static const String userKey = 'current_user';
  static const String refreshTokenKey = 'refresh_token';
}