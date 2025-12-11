// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'calendar_provider.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

String _$calendarApiClientHash() => r'38dd480aacf66b5019ddfebb899458dc15495a11';

/// See also [calendarApiClient].
@ProviderFor(calendarApiClient)
final calendarApiClientProvider = AutoDisposeProvider<ApiClient>.internal(
  calendarApiClient,
  name: r'calendarApiClientProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$calendarApiClientHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef CalendarApiClientRef = AutoDisposeProviderRef<ApiClient>;
String _$calendarEventsHash() => r'f578f2926a0cebb9f0d3e50306107d423f2633d6';

/// Copied from Dart SDK
class _SystemHash {
  _SystemHash._();

  static int combine(int hash, int value) {
    // ignore: parameter_assignments
    hash = 0x1fffffff & (hash + value);
    // ignore: parameter_assignments
    hash = 0x1fffffff & (hash + ((0x0007ffff & hash) << 10));
    return hash ^ (hash >> 6);
  }

  static int finish(int hash) {
    // ignore: parameter_assignments
    hash = 0x1fffffff & (hash + ((0x03ffffff & hash) << 3));
    // ignore: parameter_assignments
    hash = hash ^ (hash >> 11);
    return 0x1fffffff & (hash + ((0x00003fff & hash) << 15));
  }
}

abstract class _$CalendarEvents
    extends BuildlessAutoDisposeAsyncNotifier<List<CalendarEvent>> {
  late final DateTime start;
  late final DateTime end;
  late final List<int>? calendarIds;

  FutureOr<List<CalendarEvent>> build({
    required DateTime start,
    required DateTime end,
    List<int>? calendarIds,
  });
}

/// See also [CalendarEvents].
@ProviderFor(CalendarEvents)
const calendarEventsProvider = CalendarEventsFamily();

/// See also [CalendarEvents].
class CalendarEventsFamily extends Family<AsyncValue<List<CalendarEvent>>> {
  /// See also [CalendarEvents].
  const CalendarEventsFamily();

  /// See also [CalendarEvents].
  CalendarEventsProvider call({
    required DateTime start,
    required DateTime end,
    List<int>? calendarIds,
  }) {
    return CalendarEventsProvider(
      start: start,
      end: end,
      calendarIds: calendarIds,
    );
  }

  @override
  CalendarEventsProvider getProviderOverride(
    covariant CalendarEventsProvider provider,
  ) {
    return call(
      start: provider.start,
      end: provider.end,
      calendarIds: provider.calendarIds,
    );
  }

  static const Iterable<ProviderOrFamily>? _dependencies = null;

  @override
  Iterable<ProviderOrFamily>? get dependencies => _dependencies;

  static const Iterable<ProviderOrFamily>? _allTransitiveDependencies = null;

  @override
  Iterable<ProviderOrFamily>? get allTransitiveDependencies =>
      _allTransitiveDependencies;

  @override
  String? get name => r'calendarEventsProvider';
}

/// See also [CalendarEvents].
class CalendarEventsProvider extends AutoDisposeAsyncNotifierProviderImpl<
    CalendarEvents, List<CalendarEvent>> {
  /// See also [CalendarEvents].
  CalendarEventsProvider({
    required DateTime start,
    required DateTime end,
    List<int>? calendarIds,
  }) : this._internal(
          () => CalendarEvents()
            ..start = start
            ..end = end
            ..calendarIds = calendarIds,
          from: calendarEventsProvider,
          name: r'calendarEventsProvider',
          debugGetCreateSourceHash:
              const bool.fromEnvironment('dart.vm.product')
                  ? null
                  : _$calendarEventsHash,
          dependencies: CalendarEventsFamily._dependencies,
          allTransitiveDependencies:
              CalendarEventsFamily._allTransitiveDependencies,
          start: start,
          end: end,
          calendarIds: calendarIds,
        );

  CalendarEventsProvider._internal(
    super._createNotifier, {
    required super.name,
    required super.dependencies,
    required super.allTransitiveDependencies,
    required super.debugGetCreateSourceHash,
    required super.from,
    required this.start,
    required this.end,
    required this.calendarIds,
  }) : super.internal();

  final DateTime start;
  final DateTime end;
  final List<int>? calendarIds;

  @override
  FutureOr<List<CalendarEvent>> runNotifierBuild(
    covariant CalendarEvents notifier,
  ) {
    return notifier.build(
      start: start,
      end: end,
      calendarIds: calendarIds,
    );
  }

  @override
  Override overrideWith(CalendarEvents Function() create) {
    return ProviderOverride(
      origin: this,
      override: CalendarEventsProvider._internal(
        () => create()
          ..start = start
          ..end = end
          ..calendarIds = calendarIds,
        from: from,
        name: null,
        dependencies: null,
        allTransitiveDependencies: null,
        debugGetCreateSourceHash: null,
        start: start,
        end: end,
        calendarIds: calendarIds,
      ),
    );
  }

  @override
  AutoDisposeAsyncNotifierProviderElement<CalendarEvents, List<CalendarEvent>>
      createElement() {
    return _CalendarEventsProviderElement(this);
  }

  @override
  bool operator ==(Object other) {
    return other is CalendarEventsProvider &&
        other.start == start &&
        other.end == end &&
        other.calendarIds == calendarIds;
  }

  @override
  int get hashCode {
    var hash = _SystemHash.combine(0, runtimeType.hashCode);
    hash = _SystemHash.combine(hash, start.hashCode);
    hash = _SystemHash.combine(hash, end.hashCode);
    hash = _SystemHash.combine(hash, calendarIds.hashCode);

    return _SystemHash.finish(hash);
  }
}

mixin CalendarEventsRef
    on AutoDisposeAsyncNotifierProviderRef<List<CalendarEvent>> {
  /// The parameter `start` of this provider.
  DateTime get start;

  /// The parameter `end` of this provider.
  DateTime get end;

  /// The parameter `calendarIds` of this provider.
  List<int>? get calendarIds;
}

class _CalendarEventsProviderElement
    extends AutoDisposeAsyncNotifierProviderElement<CalendarEvents,
        List<CalendarEvent>> with CalendarEventsRef {
  _CalendarEventsProviderElement(super.provider);

  @override
  DateTime get start => (origin as CalendarEventsProvider).start;
  @override
  DateTime get end => (origin as CalendarEventsProvider).end;
  @override
  List<int>? get calendarIds => (origin as CalendarEventsProvider).calendarIds;
}

String _$calendarListHash() => r'd0547d9d8288f2ac96d076c880bc40952a3e3fb2';

/// See also [CalendarList].
@ProviderFor(CalendarList)
final calendarListProvider =
    AutoDisposeAsyncNotifierProvider<CalendarList, List<Calendar>>.internal(
  CalendarList.new,
  name: r'calendarListProvider',
  debugGetCreateSourceHash:
      const bool.fromEnvironment('dart.vm.product') ? null : _$calendarListHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef _$CalendarList = AutoDisposeAsyncNotifier<List<Calendar>>;
String _$googleAccountListHash() => r'8ca72a0fe498390251b69d7e4b33f5395d64be87';

/// See also [GoogleAccountList].
@ProviderFor(GoogleAccountList)
final googleAccountListProvider = AutoDisposeAsyncNotifierProvider<
    GoogleAccountList, List<GoogleAccount>>.internal(
  GoogleAccountList.new,
  name: r'googleAccountListProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$googleAccountListHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef _$GoogleAccountList = AutoDisposeAsyncNotifier<List<GoogleAccount>>;
String _$eventDetailsHash() => r'3bc9e1119919c41604cdec16d03bc15342c8fc85';

abstract class _$EventDetails
    extends BuildlessAutoDisposeAsyncNotifier<CalendarEvent?> {
  late final int eventId;

  FutureOr<CalendarEvent?> build(
    int eventId,
  );
}

/// See also [EventDetails].
@ProviderFor(EventDetails)
const eventDetailsProvider = EventDetailsFamily();

/// See also [EventDetails].
class EventDetailsFamily extends Family<AsyncValue<CalendarEvent?>> {
  /// See also [EventDetails].
  const EventDetailsFamily();

  /// See also [EventDetails].
  EventDetailsProvider call(
    int eventId,
  ) {
    return EventDetailsProvider(
      eventId,
    );
  }

  @override
  EventDetailsProvider getProviderOverride(
    covariant EventDetailsProvider provider,
  ) {
    return call(
      provider.eventId,
    );
  }

  static const Iterable<ProviderOrFamily>? _dependencies = null;

  @override
  Iterable<ProviderOrFamily>? get dependencies => _dependencies;

  static const Iterable<ProviderOrFamily>? _allTransitiveDependencies = null;

  @override
  Iterable<ProviderOrFamily>? get allTransitiveDependencies =>
      _allTransitiveDependencies;

  @override
  String? get name => r'eventDetailsProvider';
}

/// See also [EventDetails].
class EventDetailsProvider
    extends AutoDisposeAsyncNotifierProviderImpl<EventDetails, CalendarEvent?> {
  /// See also [EventDetails].
  EventDetailsProvider(
    int eventId,
  ) : this._internal(
          () => EventDetails()..eventId = eventId,
          from: eventDetailsProvider,
          name: r'eventDetailsProvider',
          debugGetCreateSourceHash:
              const bool.fromEnvironment('dart.vm.product')
                  ? null
                  : _$eventDetailsHash,
          dependencies: EventDetailsFamily._dependencies,
          allTransitiveDependencies:
              EventDetailsFamily._allTransitiveDependencies,
          eventId: eventId,
        );

  EventDetailsProvider._internal(
    super._createNotifier, {
    required super.name,
    required super.dependencies,
    required super.allTransitiveDependencies,
    required super.debugGetCreateSourceHash,
    required super.from,
    required this.eventId,
  }) : super.internal();

  final int eventId;

  @override
  FutureOr<CalendarEvent?> runNotifierBuild(
    covariant EventDetails notifier,
  ) {
    return notifier.build(
      eventId,
    );
  }

  @override
  Override overrideWith(EventDetails Function() create) {
    return ProviderOverride(
      origin: this,
      override: EventDetailsProvider._internal(
        () => create()..eventId = eventId,
        from: from,
        name: null,
        dependencies: null,
        allTransitiveDependencies: null,
        debugGetCreateSourceHash: null,
        eventId: eventId,
      ),
    );
  }

  @override
  AutoDisposeAsyncNotifierProviderElement<EventDetails, CalendarEvent?>
      createElement() {
    return _EventDetailsProviderElement(this);
  }

  @override
  bool operator ==(Object other) {
    return other is EventDetailsProvider && other.eventId == eventId;
  }

  @override
  int get hashCode {
    var hash = _SystemHash.combine(0, runtimeType.hashCode);
    hash = _SystemHash.combine(hash, eventId.hashCode);

    return _SystemHash.finish(hash);
  }
}

mixin EventDetailsRef on AutoDisposeAsyncNotifierProviderRef<CalendarEvent?> {
  /// The parameter `eventId` of this provider.
  int get eventId;
}

class _EventDetailsProviderElement
    extends AutoDisposeAsyncNotifierProviderElement<EventDetails,
        CalendarEvent?> with EventDetailsRef {
  _EventDetailsProviderElement(super.provider);

  @override
  int get eventId => (origin as EventDetailsProvider).eventId;
}

String _$selectedDateHash() => r'f88dc44765b7c2a6086f64b67b70a526e657b76a';

/// See also [SelectedDate].
@ProviderFor(SelectedDate)
final selectedDateProvider =
    AutoDisposeNotifierProvider<SelectedDate, DateTime>.internal(
  SelectedDate.new,
  name: r'selectedDateProvider',
  debugGetCreateSourceHash:
      const bool.fromEnvironment('dart.vm.product') ? null : _$selectedDateHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef _$SelectedDate = AutoDisposeNotifier<DateTime>;
String _$calendarViewModeHash() => r'6985e1be36515ecc78a39b67fd285415e3534c46';

/// See also [CalendarViewMode].
@ProviderFor(CalendarViewMode)
final calendarViewModeProvider =
    AutoDisposeNotifierProvider<CalendarViewMode, CalendarView>.internal(
  CalendarViewMode.new,
  name: r'calendarViewModeProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$calendarViewModeHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef _$CalendarViewMode = AutoDisposeNotifier<CalendarView>;
String _$visibleCalendarsHash() => r'e3deb840fb509e74b2110145b17093faee416285';

/// See also [VisibleCalendars].
@ProviderFor(VisibleCalendars)
final visibleCalendarsProvider =
    AutoDisposeNotifierProvider<VisibleCalendars, Set<int>>.internal(
  VisibleCalendars.new,
  name: r'visibleCalendarsProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$visibleCalendarsHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef _$VisibleCalendars = AutoDisposeNotifier<Set<int>>;
// ignore_for_file: type=lint
// ignore_for_file: subtype_of_sealed_class, invalid_use_of_internal_member, invalid_use_of_visible_for_testing_member
