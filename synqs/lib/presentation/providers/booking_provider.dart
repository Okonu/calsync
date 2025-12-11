import 'package:riverpod_annotation/riverpod_annotation.dart';
import '../../data/datasources/api_client.dart';
import '../../data/models/booking.dart';
import '../../data/models/booking_page.dart';
import '../../data/models/paginated_response.dart';
import '../../core/network/dio_client.dart';

part 'booking_provider.g.dart';

@riverpod
ApiClient bookingApiClient(BookingApiClientRef ref) {
  return ApiClient(DioClient.instance);
}

@riverpod
class BookingList extends _$BookingList {
  @override
  Future<PaginatedResponse<Booking>> build({
    int page = 1,
    int perPage = 20,
    String? status,
    DateTime? startDate,
    DateTime? endDate,
  }) async {
    final apiClient = ref.read(bookingApiClientProvider);
    return await apiClient.getBookings({
      'page': page.toString(),
      'per_page': perPage.toString(),
      if (status != null) 'status': status,
      if (startDate != null) 'start_date': startDate.toIso8601String(),
      if (endDate != null) 'end_date': endDate.toIso8601String(),
    });
  }

  Future<void> cancelBooking(int bookingId) async {
    try {
      final apiClient = ref.read(bookingApiClientProvider);
      await apiClient.cancelBooking(bookingId);

      // Refresh the booking list
      ref.invalidateSelf();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> rescheduleBooking(int bookingId, DateTime newStartTime, DateTime newEndTime) async {
    try {
      final apiClient = ref.read(bookingApiClientProvider);
      await apiClient.rescheduleBooking(bookingId, {
        'starts_at': newStartTime.toIso8601String(),
        'ends_at': newEndTime.toIso8601String(),
      });

      // Refresh the booking list
      ref.invalidateSelf();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> refresh() async {
    ref.invalidateSelf();
  }
}

@riverpod
class BookingPageList extends _$BookingPageList {
  @override
  Future<List<BookingPage>> build() async {
    final apiClient = ref.read(bookingApiClientProvider);
    return await apiClient.getBookingPages();
  }

  Future<BookingPage> createBookingPage(Map<String, dynamic> data) async {
    try {
      final apiClient = ref.read(bookingApiClientProvider);
      final bookingPage = await apiClient.createBookingPage(data);

      // Refresh the booking page list
      ref.invalidateSelf();

      return bookingPage;
    } catch (e) {
      rethrow;
    }
  }

  Future<BookingPage> updateBookingPage(int id, Map<String, dynamic> data) async {
    try {
      final apiClient = ref.read(bookingApiClientProvider);
      final bookingPage = await apiClient.updateBookingPage(id, data);

      // Refresh the booking page list
      ref.invalidateSelf();

      return bookingPage;
    } catch (e) {
      rethrow;
    }
  }

  Future<void> deleteBookingPage(int id) async {
    try {
      final apiClient = ref.read(bookingApiClientProvider);
      await apiClient.deleteBookingPage(id);

      // Refresh the booking page list
      ref.invalidateSelf();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> refresh() async {
    ref.invalidateSelf();
  }
}

@riverpod
class AvailableTimeSlots extends _$AvailableTimeSlots {
  @override
  Future<List<DateTime>> build({
    required int bookingPageId,
    required DateTime date,
  }) async {
    final apiClient = ref.read(bookingApiClientProvider);
    final slots = await apiClient.getAvailableTimeSlots(bookingPageId, {
      'date': date.toIso8601String().split('T')[0], // YYYY-MM-DD format
    });

    return slots.map((slot) => DateTime.parse(slot)).toList();
  }

  Future<void> refresh() async {
    ref.invalidateSelf();
  }
}

@riverpod
class BookingDetails extends _$BookingDetails {
  @override
  Future<Booking?> build(int bookingId) async {
    try {
      final apiClient = ref.read(bookingApiClientProvider);
      return await apiClient.getBookingDetails(bookingId);
    } catch (e) {
      return null;
    }
  }
}

// Provider for creating new bookings
@riverpod
class BookingCreation extends _$BookingCreation {
  @override
  AsyncValue<Booking?> build() {
    return const AsyncValue.data(null);
  }

  Future<Booking> createBooking({
    required int bookingPageId,
    required String name,
    required String email,
    required DateTime startsAt,
    required DateTime endsAt,
    String? notes,
  }) async {
    state = const AsyncValue.loading();

    try {
      final apiClient = ref.read(bookingApiClientProvider);
      final booking = await apiClient.createBooking({
        'booking_page_id': bookingPageId,
        'name': name,
        'email': email,
        'starts_at': startsAt.toIso8601String(),
        'ends_at': endsAt.toIso8601String(),
        if (notes != null) 'notes': notes,
      });

      state = AsyncValue.data(booking);

      // Refresh related providers
      ref.invalidate(bookingListProvider);
      ref.invalidate(availableTimeSlotsProvider);

      return booking;
    } catch (e, stackTrace) {
      state = AsyncValue.error(e, stackTrace);
      rethrow;
    }
  }

  void reset() {
    state = const AsyncValue.data(null);
  }
}

// Provider for selected time slot during booking process
@riverpod
class SelectedTimeSlot extends _$SelectedTimeSlot {
  @override
  DateTime? build() {
    return null;
  }

  void selectTimeSlot(DateTime timeSlot) {
    state = timeSlot;
  }

  void clearSelection() {
    state = null;
  }
}

// Provider for selected booking page during booking process
@riverpod
class SelectedBookingPage extends _$SelectedBookingPage {
  @override
  BookingPage? build() {
    return null;
  }

  void selectBookingPage(BookingPage bookingPage) {
    state = bookingPage;
  }

  void clearSelection() {
    state = null;
  }
}