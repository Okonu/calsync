import 'package:dio/dio.dart';
import 'package:retrofit/retrofit.dart';
import '../models/auth_response.dart';
import '../models/login_request.dart';
import '../models/user.dart';
import '../models/calendar_event.dart';
import '../models/calendar.dart';
import '../models/google_account.dart';
import '../models/booking.dart';
import '../models/booking_page.dart';
import '../models/time_slot.dart';
import '../models/community.dart';
import '../models/community_event.dart';
import '../models/paginated_response.dart';

part 'api_client.g.dart';

@RestApi()
abstract class ApiClient {
  factory ApiClient(Dio dio, {String baseUrl}) = _ApiClient;

  // Authentication endpoints
  @POST('/auth/login')
  Future<AuthResponse> login(@Body() LoginRequest request);

  @POST('/auth/logout')
  Future<void> logout();

  @GET('/auth/me')
  Future<User> getCurrentUser();

  @POST('/auth/refresh')
  Future<AuthResponse> refreshToken(@Body() Map<String, dynamic> request);

  // User endpoints
  @GET('/user')
  Future<User> getUserProfile();

  @PUT('/user')
  Future<User> updateUserProfile(@Body() Map<String, dynamic> request);

  @GET('/user/stats')
  Future<Map<String, dynamic>> getUserStats();

  // Calendar endpoints
  @GET('/calendar')
  Future<List<GoogleAccount>> getCalendarAccounts();

  @GET('/calendar/events')
  Future<List<CalendarEvent>> getEvents(
    @Query('start') String start,
    @Query('end') String end,
    @Query('calendar_ids[]') List<int>? calendarIds,
  );

  @GET('/calendar/events/{id}')
  Future<CalendarEvent> getEventDetails(@Path('id') int id);

  @GET('/calendar/calendars')
  Future<List<Calendar>> getCalendars();

  @PUT('/calendar/calendars/{id}')
  Future<Calendar> updateCalendar(
    @Path('id') int id,
    @Body() Map<String, dynamic> request,
  );

  @PATCH('/calendar/calendars/{id}/visibility')
  Future<Calendar> updateCalendarVisibility(
    @Path('id') int id,
    @Body() Map<String, bool> request,
  );

  @PATCH('/calendar/calendars/{id}/color')
  Future<Calendar> updateCalendarColor(
    @Path('id') int id,
    @Body() Map<String, String> request,
  );

  @GET('/calendar/accounts')
  Future<List<GoogleAccount>> getGoogleAccounts();

  // Google account management
  @POST('/google/accounts/{id}/sync')
  Future<GoogleAccount> syncGoogleAccount(@Path('id') int id);

  @PATCH('/google/accounts/{id}/color')
  Future<GoogleAccount> updateAccountColor(
    @Path('id') int id,
    @Body() Map<String, String> request,
  );

  @PATCH('/google/accounts/{id}/status')
  Future<GoogleAccount> updateAccountStatus(
    @Path('id') int id,
    @Body() Map<String, bool> request,
  );

  @DELETE('/google/accounts/{id}')
  Future<void> deleteGoogleAccount(@Path('id') int id);

  // Booking endpoints
  @GET('/bookings')
  Future<PaginatedResponse<Booking>> getBookings(@Queries() Map<String, dynamic> queries);

  @GET('/bookings/{id}')
  Future<Booking> getBookingDetails(@Path('id') int id);

  @POST('/bookings/{id}/cancel')
  Future<Booking> cancelBooking(@Path('id') int id);

  @PUT('/bookings/{id}/reschedule')
  Future<Booking> rescheduleBooking(@Path('id') int id, @Body() Map<String, dynamic> request);

  @POST('/bookings')
  Future<Booking> createBooking(@Body() Map<String, dynamic> request);

  // Booking Pages endpoints
  @GET('/booking-pages')
  Future<List<BookingPage>> getBookingPages();

  @POST('/booking-pages')
  Future<BookingPage> createBookingPage(@Body() Map<String, dynamic> request);

  @GET('/booking-pages/{id}')
  Future<BookingPage> getBookingPageDetails(@Path('id') int id);

  @PUT('/booking-pages/{id}')
  Future<BookingPage> updateBookingPage(@Path('id') int id, @Body() Map<String, dynamic> request);

  @DELETE('/booking-pages/{id}')
  Future<void> deleteBookingPage(@Path('id') int id);

  @GET('/booking-pages/{id}/time-slots')
  Future<List<String>> getAvailableTimeSlots(@Path('id') int id, @Queries() Map<String, dynamic> queries);

  // Public booking endpoints
  @GET('/public/booking/{slug}')
  Future<BookingPage> getPublicBookingPage(@Path('slug') String slug);

  @GET('/public/booking/{slug}/slots')
  Future<List<TimeSlot>> getAvailableSlots(
    @Path('slug') String slug,
    @Query('date') String date,
  );

  @POST('/public/booking/{slug}')
  Future<Booking> createPublicBooking(
    @Path('slug') String slug,
    @Body() Map<String, dynamic> request,
  );

  // Community endpoints
  @GET('/communities')
  Future<PaginatedResponse<Community>> getCommunities(@Query('page') int page);

  @POST('/communities')
  Future<Community> createCommunity(@Body() Map<String, dynamic> request);

  @GET('/communities/{id}')
  Future<Community> getCommunity(@Path('id') int id);

  @PUT('/communities/{id}')
  Future<Community> updateCommunity(
    @Path('id') int id,
    @Body() Map<String, dynamic> request,
  );

  @DELETE('/communities/{id}')
  Future<void> deleteCommunity(@Path('id') int id);

  @GET('/communities/{id}/stats')
  Future<Map<String, dynamic>> getCommunityStats(@Path('id') int id);

  @GET('/communities/{id}/calendar')
  Future<List<CalendarEvent>> getCommunityCalendar(
    @Path('id') int id,
    @Query('start') String start,
    @Query('end') String end,
  );

  // Community events
  @GET('/communities/{id}/events')
  Future<PaginatedResponse<CommunityEvent>> getCommunityEvents(
    @Path('id') int id,
    @Query('page') int page,
  );

  @POST('/communities/{id}/events')
  Future<CommunityEvent> createCommunityEvent(
    @Path('id') int id,
    @Body() Map<String, dynamic> request,
  );

  @GET('/communities/{communityId}/events/{eventId}')
  Future<CommunityEvent> getCommunityEvent(
    @Path('communityId') int communityId,
    @Path('eventId') int eventId,
  );

  @PUT('/communities/{communityId}/events/{eventId}')
  Future<CommunityEvent> updateCommunityEvent(
    @Path('communityId') int communityId,
    @Path('eventId') int eventId,
    @Body() Map<String, dynamic> request,
  );

  @DELETE('/communities/{communityId}/events/{eventId}')
  Future<void> deleteCommunityEvent(
    @Path('communityId') int communityId,
    @Path('eventId') int eventId,
  );

  // Public community endpoints
  @GET('/public/communities')
  Future<PaginatedResponse<Community>> getPublicCommunities(@Query('page') int page);

  @GET('/public/communities/{slug}')
  Future<Community> getPublicCommunity(@Path('slug') String slug);

  @GET('/public/communities/{slug}/events')
  Future<PaginatedResponse<CommunityEvent>> getPublicCommunityEvents(
    @Path('slug') String slug,
    @Query('page') int page,
  );

  @GET('/public/communities/{communitySlug}/events/{eventSlug}')
  Future<CommunityEvent> getPublicCommunityEvent(
    @Path('communitySlug') String communitySlug,
    @Path('eventSlug') String eventSlug,
  );
}