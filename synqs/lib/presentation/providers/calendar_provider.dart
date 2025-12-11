import 'package:riverpod_annotation/riverpod_annotation.dart';
import '../../data/datasources/api_client.dart';
import '../../data/models/calendar_event.dart';
import '../../data/models/calendar.dart';
import '../../data/models/google_account.dart';
import '../../core/network/dio_client.dart';

part 'calendar_provider.g.dart';

@riverpod
ApiClient calendarApiClient(CalendarApiClientRef ref) {
  return ApiClient(DioClient.instance);
}

@riverpod
class CalendarEvents extends _$CalendarEvents {
  @override
  Future<List<CalendarEvent>> build({
    required DateTime start,
    required DateTime end,
    List<int>? calendarIds,
  }) async {
    final apiClient = ref.read(calendarApiClientProvider);
    return await apiClient.getEvents(
      start.toIso8601String(),
      end.toIso8601String(),
      calendarIds,
    );
  }

  Future<void> refresh({
    DateTime? newStart,
    DateTime? newEnd,
    List<int>? newCalendarIds,
  }) async {
    state = const AsyncLoading();
    try {
      final apiClient = ref.read(calendarApiClientProvider);
      final events = await apiClient.getEvents(
        (newStart ?? start).toIso8601String(),
        (newEnd ?? end).toIso8601String(),
        newCalendarIds ?? calendarIds,
      );
      state = AsyncData(events);
    } catch (e, stackTrace) {
      state = AsyncError(e, stackTrace);
    }
  }

  DateTime get start => DateTime.now().subtract(const Duration(days: 30));
  DateTime get end => DateTime.now().add(const Duration(days: 90));
  List<int>? get calendarIds => null;
}

@riverpod
class CalendarList extends _$CalendarList {
  @override
  Future<List<Calendar>> build() async {
    final apiClient = ref.read(calendarApiClientProvider);
    return await apiClient.getCalendars();
  }

  Future<void> updateCalendarVisibility(int calendarId, bool isVisible) async {
    try {
      final apiClient = ref.read(calendarApiClientProvider);
      await apiClient.updateCalendarVisibility(
        calendarId,
        {'is_visible': isVisible},
      );
      // Refresh the calendar list
      ref.invalidateSelf();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> updateCalendarColor(int calendarId, String color) async {
    try {
      final apiClient = ref.read(calendarApiClientProvider);
      await apiClient.updateCalendarColor(
        calendarId,
        {'color': color},
      );
      // Refresh the calendar list
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
class GoogleAccountList extends _$GoogleAccountList {
  @override
  Future<List<GoogleAccount>> build() async {
    final apiClient = ref.read(calendarApiClientProvider);
    return await apiClient.getGoogleAccounts();
  }

  Future<void> syncAccount(int accountId) async {
    try {
      final apiClient = ref.read(calendarApiClientProvider);
      await apiClient.syncGoogleAccount(accountId);

      // Refresh both accounts and calendars
      ref.invalidateSelf();
      ref.invalidate(calendarListProvider);
    } catch (e) {
      rethrow;
    }
  }

  Future<void> updateAccountColor(int accountId, String color) async {
    try {
      final apiClient = ref.read(calendarApiClientProvider);
      await apiClient.updateAccountColor(
        accountId,
        {'color': color},
      );
      ref.invalidateSelf();
    } catch (e) {
      rethrow;
    }
  }

  Future<void> updateAccountStatus(int accountId, bool isActive) async {
    try {
      final apiClient = ref.read(calendarApiClientProvider);
      await apiClient.updateAccountStatus(
        accountId,
        {'is_active': isActive},
      );

      // Refresh both accounts and calendars
      ref.invalidateSelf();
      ref.invalidate(calendarListProvider);
    } catch (e) {
      rethrow;
    }
  }

  Future<void> deleteAccount(int accountId) async {
    try {
      final apiClient = ref.read(calendarApiClientProvider);
      await apiClient.deleteGoogleAccount(accountId);

      // Refresh both accounts and calendars
      ref.invalidateSelf();
      ref.invalidate(calendarListProvider);
    } catch (e) {
      rethrow;
    }
  }

  Future<void> refresh() async {
    ref.invalidateSelf();
  }
}

@riverpod
class EventDetails extends _$EventDetails {
  @override
  Future<CalendarEvent?> build(int eventId) async {
    try {
      final apiClient = ref.read(calendarApiClientProvider);
      return await apiClient.getEventDetails(eventId);
    } catch (e) {
      return null;
    }
  }
}

// Provider for selected date in calendar
@riverpod
class SelectedDate extends _$SelectedDate {
  @override
  DateTime build() {
    return DateTime.now();
  }

  void setDate(DateTime date) {
    state = date;
  }

  void setToday() {
    state = DateTime.now();
  }

  void nextDay() {
    state = state.add(const Duration(days: 1));
  }

  void previousDay() {
    state = state.subtract(const Duration(days: 1));
  }
}

// Provider for calendar view mode
@riverpod
class CalendarViewMode extends _$CalendarViewMode {
  @override
  CalendarView build() {
    return CalendarView.month;
  }

  void setViewMode(CalendarView mode) {
    state = mode;
  }
}

enum CalendarView {
  month,
  week,
  day,
  agenda,
}

// Provider for visible calendars filter
@riverpod
class VisibleCalendars extends _$VisibleCalendars {
  @override
  Set<int> build() {
    return {};
  }

  void toggleCalendar(int calendarId) {
    if (state.contains(calendarId)) {
      state = {...state}..remove(calendarId);
    } else {
      state = {...state, calendarId};
    }
  }

  void setVisibleCalendars(Set<int> calendarIds) {
    state = calendarIds;
  }

  void showAllCalendars() {
    state = {};
  }

  void hideAllCalendars(List<Calendar> calendars) {
    state = calendars.map((c) => c.id).toSet();
  }
}