// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'booking_provider.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

String _$bookingApiClientHash() => r'6ccb40418073186af84427d28e550f87cc7d60e8';

/// See also [bookingApiClient].
@ProviderFor(bookingApiClient)
final bookingApiClientProvider = AutoDisposeProvider<ApiClient>.internal(
  bookingApiClient,
  name: r'bookingApiClientProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$bookingApiClientHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef BookingApiClientRef = AutoDisposeProviderRef<ApiClient>;
String _$bookingListHash() => r'e3f74e232d9da3d026b88cb530a660745a5a2c73';

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

abstract class _$BookingList
    extends BuildlessAutoDisposeAsyncNotifier<PaginatedResponse<Booking>> {
  late final int page;
  late final int perPage;
  late final String? status;
  late final DateTime? startDate;
  late final DateTime? endDate;

  FutureOr<PaginatedResponse<Booking>> build({
    int page = 1,
    int perPage = 20,
    String? status,
    DateTime? startDate,
    DateTime? endDate,
  });
}

/// See also [BookingList].
@ProviderFor(BookingList)
const bookingListProvider = BookingListFamily();

/// See also [BookingList].
class BookingListFamily extends Family<AsyncValue<PaginatedResponse<Booking>>> {
  /// See also [BookingList].
  const BookingListFamily();

  /// See also [BookingList].
  BookingListProvider call({
    int page = 1,
    int perPage = 20,
    String? status,
    DateTime? startDate,
    DateTime? endDate,
  }) {
    return BookingListProvider(
      page: page,
      perPage: perPage,
      status: status,
      startDate: startDate,
      endDate: endDate,
    );
  }

  @override
  BookingListProvider getProviderOverride(
    covariant BookingListProvider provider,
  ) {
    return call(
      page: provider.page,
      perPage: provider.perPage,
      status: provider.status,
      startDate: provider.startDate,
      endDate: provider.endDate,
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
  String? get name => r'bookingListProvider';
}

/// See also [BookingList].
class BookingListProvider extends AutoDisposeAsyncNotifierProviderImpl<
    BookingList, PaginatedResponse<Booking>> {
  /// See also [BookingList].
  BookingListProvider({
    int page = 1,
    int perPage = 20,
    String? status,
    DateTime? startDate,
    DateTime? endDate,
  }) : this._internal(
          () => BookingList()
            ..page = page
            ..perPage = perPage
            ..status = status
            ..startDate = startDate
            ..endDate = endDate,
          from: bookingListProvider,
          name: r'bookingListProvider',
          debugGetCreateSourceHash:
              const bool.fromEnvironment('dart.vm.product')
                  ? null
                  : _$bookingListHash,
          dependencies: BookingListFamily._dependencies,
          allTransitiveDependencies:
              BookingListFamily._allTransitiveDependencies,
          page: page,
          perPage: perPage,
          status: status,
          startDate: startDate,
          endDate: endDate,
        );

  BookingListProvider._internal(
    super._createNotifier, {
    required super.name,
    required super.dependencies,
    required super.allTransitiveDependencies,
    required super.debugGetCreateSourceHash,
    required super.from,
    required this.page,
    required this.perPage,
    required this.status,
    required this.startDate,
    required this.endDate,
  }) : super.internal();

  final int page;
  final int perPage;
  final String? status;
  final DateTime? startDate;
  final DateTime? endDate;

  @override
  FutureOr<PaginatedResponse<Booking>> runNotifierBuild(
    covariant BookingList notifier,
  ) {
    return notifier.build(
      page: page,
      perPage: perPage,
      status: status,
      startDate: startDate,
      endDate: endDate,
    );
  }

  @override
  Override overrideWith(BookingList Function() create) {
    return ProviderOverride(
      origin: this,
      override: BookingListProvider._internal(
        () => create()
          ..page = page
          ..perPage = perPage
          ..status = status
          ..startDate = startDate
          ..endDate = endDate,
        from: from,
        name: null,
        dependencies: null,
        allTransitiveDependencies: null,
        debugGetCreateSourceHash: null,
        page: page,
        perPage: perPage,
        status: status,
        startDate: startDate,
        endDate: endDate,
      ),
    );
  }

  @override
  AutoDisposeAsyncNotifierProviderElement<BookingList,
      PaginatedResponse<Booking>> createElement() {
    return _BookingListProviderElement(this);
  }

  @override
  bool operator ==(Object other) {
    return other is BookingListProvider &&
        other.page == page &&
        other.perPage == perPage &&
        other.status == status &&
        other.startDate == startDate &&
        other.endDate == endDate;
  }

  @override
  int get hashCode {
    var hash = _SystemHash.combine(0, runtimeType.hashCode);
    hash = _SystemHash.combine(hash, page.hashCode);
    hash = _SystemHash.combine(hash, perPage.hashCode);
    hash = _SystemHash.combine(hash, status.hashCode);
    hash = _SystemHash.combine(hash, startDate.hashCode);
    hash = _SystemHash.combine(hash, endDate.hashCode);

    return _SystemHash.finish(hash);
  }
}

mixin BookingListRef
    on AutoDisposeAsyncNotifierProviderRef<PaginatedResponse<Booking>> {
  /// The parameter `page` of this provider.
  int get page;

  /// The parameter `perPage` of this provider.
  int get perPage;

  /// The parameter `status` of this provider.
  String? get status;

  /// The parameter `startDate` of this provider.
  DateTime? get startDate;

  /// The parameter `endDate` of this provider.
  DateTime? get endDate;
}

class _BookingListProviderElement
    extends AutoDisposeAsyncNotifierProviderElement<BookingList,
        PaginatedResponse<Booking>> with BookingListRef {
  _BookingListProviderElement(super.provider);

  @override
  int get page => (origin as BookingListProvider).page;
  @override
  int get perPage => (origin as BookingListProvider).perPage;
  @override
  String? get status => (origin as BookingListProvider).status;
  @override
  DateTime? get startDate => (origin as BookingListProvider).startDate;
  @override
  DateTime? get endDate => (origin as BookingListProvider).endDate;
}

String _$bookingPageListHash() => r'6d849a39e10ccb8d62ed8d396f03627f99c2d3d7';

/// See also [BookingPageList].
@ProviderFor(BookingPageList)
final bookingPageListProvider = AutoDisposeAsyncNotifierProvider<
    BookingPageList, List<BookingPage>>.internal(
  BookingPageList.new,
  name: r'bookingPageListProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$bookingPageListHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef _$BookingPageList = AutoDisposeAsyncNotifier<List<BookingPage>>;
String _$availableTimeSlotsHash() =>
    r'31591a0464fc6bd7bdff91bbe6843d42038366df';

abstract class _$AvailableTimeSlots
    extends BuildlessAutoDisposeAsyncNotifier<List<DateTime>> {
  late final int bookingPageId;
  late final DateTime date;

  FutureOr<List<DateTime>> build({
    required int bookingPageId,
    required DateTime date,
  });
}

/// See also [AvailableTimeSlots].
@ProviderFor(AvailableTimeSlots)
const availableTimeSlotsProvider = AvailableTimeSlotsFamily();

/// See also [AvailableTimeSlots].
class AvailableTimeSlotsFamily extends Family<AsyncValue<List<DateTime>>> {
  /// See also [AvailableTimeSlots].
  const AvailableTimeSlotsFamily();

  /// See also [AvailableTimeSlots].
  AvailableTimeSlotsProvider call({
    required int bookingPageId,
    required DateTime date,
  }) {
    return AvailableTimeSlotsProvider(
      bookingPageId: bookingPageId,
      date: date,
    );
  }

  @override
  AvailableTimeSlotsProvider getProviderOverride(
    covariant AvailableTimeSlotsProvider provider,
  ) {
    return call(
      bookingPageId: provider.bookingPageId,
      date: provider.date,
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
  String? get name => r'availableTimeSlotsProvider';
}

/// See also [AvailableTimeSlots].
class AvailableTimeSlotsProvider extends AutoDisposeAsyncNotifierProviderImpl<
    AvailableTimeSlots, List<DateTime>> {
  /// See also [AvailableTimeSlots].
  AvailableTimeSlotsProvider({
    required int bookingPageId,
    required DateTime date,
  }) : this._internal(
          () => AvailableTimeSlots()
            ..bookingPageId = bookingPageId
            ..date = date,
          from: availableTimeSlotsProvider,
          name: r'availableTimeSlotsProvider',
          debugGetCreateSourceHash:
              const bool.fromEnvironment('dart.vm.product')
                  ? null
                  : _$availableTimeSlotsHash,
          dependencies: AvailableTimeSlotsFamily._dependencies,
          allTransitiveDependencies:
              AvailableTimeSlotsFamily._allTransitiveDependencies,
          bookingPageId: bookingPageId,
          date: date,
        );

  AvailableTimeSlotsProvider._internal(
    super._createNotifier, {
    required super.name,
    required super.dependencies,
    required super.allTransitiveDependencies,
    required super.debugGetCreateSourceHash,
    required super.from,
    required this.bookingPageId,
    required this.date,
  }) : super.internal();

  final int bookingPageId;
  final DateTime date;

  @override
  FutureOr<List<DateTime>> runNotifierBuild(
    covariant AvailableTimeSlots notifier,
  ) {
    return notifier.build(
      bookingPageId: bookingPageId,
      date: date,
    );
  }

  @override
  Override overrideWith(AvailableTimeSlots Function() create) {
    return ProviderOverride(
      origin: this,
      override: AvailableTimeSlotsProvider._internal(
        () => create()
          ..bookingPageId = bookingPageId
          ..date = date,
        from: from,
        name: null,
        dependencies: null,
        allTransitiveDependencies: null,
        debugGetCreateSourceHash: null,
        bookingPageId: bookingPageId,
        date: date,
      ),
    );
  }

  @override
  AutoDisposeAsyncNotifierProviderElement<AvailableTimeSlots, List<DateTime>>
      createElement() {
    return _AvailableTimeSlotsProviderElement(this);
  }

  @override
  bool operator ==(Object other) {
    return other is AvailableTimeSlotsProvider &&
        other.bookingPageId == bookingPageId &&
        other.date == date;
  }

  @override
  int get hashCode {
    var hash = _SystemHash.combine(0, runtimeType.hashCode);
    hash = _SystemHash.combine(hash, bookingPageId.hashCode);
    hash = _SystemHash.combine(hash, date.hashCode);

    return _SystemHash.finish(hash);
  }
}

mixin AvailableTimeSlotsRef
    on AutoDisposeAsyncNotifierProviderRef<List<DateTime>> {
  /// The parameter `bookingPageId` of this provider.
  int get bookingPageId;

  /// The parameter `date` of this provider.
  DateTime get date;
}

class _AvailableTimeSlotsProviderElement
    extends AutoDisposeAsyncNotifierProviderElement<AvailableTimeSlots,
        List<DateTime>> with AvailableTimeSlotsRef {
  _AvailableTimeSlotsProviderElement(super.provider);

  @override
  int get bookingPageId => (origin as AvailableTimeSlotsProvider).bookingPageId;
  @override
  DateTime get date => (origin as AvailableTimeSlotsProvider).date;
}

String _$bookingDetailsHash() => r'c379c487c1e01f6026cb9a7d5a96df07ff5cea70';

abstract class _$BookingDetails
    extends BuildlessAutoDisposeAsyncNotifier<Booking?> {
  late final int bookingId;

  FutureOr<Booking?> build(
    int bookingId,
  );
}

/// See also [BookingDetails].
@ProviderFor(BookingDetails)
const bookingDetailsProvider = BookingDetailsFamily();

/// See also [BookingDetails].
class BookingDetailsFamily extends Family<AsyncValue<Booking?>> {
  /// See also [BookingDetails].
  const BookingDetailsFamily();

  /// See also [BookingDetails].
  BookingDetailsProvider call(
    int bookingId,
  ) {
    return BookingDetailsProvider(
      bookingId,
    );
  }

  @override
  BookingDetailsProvider getProviderOverride(
    covariant BookingDetailsProvider provider,
  ) {
    return call(
      provider.bookingId,
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
  String? get name => r'bookingDetailsProvider';
}

/// See also [BookingDetails].
class BookingDetailsProvider
    extends AutoDisposeAsyncNotifierProviderImpl<BookingDetails, Booking?> {
  /// See also [BookingDetails].
  BookingDetailsProvider(
    int bookingId,
  ) : this._internal(
          () => BookingDetails()..bookingId = bookingId,
          from: bookingDetailsProvider,
          name: r'bookingDetailsProvider',
          debugGetCreateSourceHash:
              const bool.fromEnvironment('dart.vm.product')
                  ? null
                  : _$bookingDetailsHash,
          dependencies: BookingDetailsFamily._dependencies,
          allTransitiveDependencies:
              BookingDetailsFamily._allTransitiveDependencies,
          bookingId: bookingId,
        );

  BookingDetailsProvider._internal(
    super._createNotifier, {
    required super.name,
    required super.dependencies,
    required super.allTransitiveDependencies,
    required super.debugGetCreateSourceHash,
    required super.from,
    required this.bookingId,
  }) : super.internal();

  final int bookingId;

  @override
  FutureOr<Booking?> runNotifierBuild(
    covariant BookingDetails notifier,
  ) {
    return notifier.build(
      bookingId,
    );
  }

  @override
  Override overrideWith(BookingDetails Function() create) {
    return ProviderOverride(
      origin: this,
      override: BookingDetailsProvider._internal(
        () => create()..bookingId = bookingId,
        from: from,
        name: null,
        dependencies: null,
        allTransitiveDependencies: null,
        debugGetCreateSourceHash: null,
        bookingId: bookingId,
      ),
    );
  }

  @override
  AutoDisposeAsyncNotifierProviderElement<BookingDetails, Booking?>
      createElement() {
    return _BookingDetailsProviderElement(this);
  }

  @override
  bool operator ==(Object other) {
    return other is BookingDetailsProvider && other.bookingId == bookingId;
  }

  @override
  int get hashCode {
    var hash = _SystemHash.combine(0, runtimeType.hashCode);
    hash = _SystemHash.combine(hash, bookingId.hashCode);

    return _SystemHash.finish(hash);
  }
}

mixin BookingDetailsRef on AutoDisposeAsyncNotifierProviderRef<Booking?> {
  /// The parameter `bookingId` of this provider.
  int get bookingId;
}

class _BookingDetailsProviderElement
    extends AutoDisposeAsyncNotifierProviderElement<BookingDetails, Booking?>
    with BookingDetailsRef {
  _BookingDetailsProviderElement(super.provider);

  @override
  int get bookingId => (origin as BookingDetailsProvider).bookingId;
}

String _$bookingCreationHash() => r'533ac7a880861c728091a2f88364e575de9acda5';

/// See also [BookingCreation].
@ProviderFor(BookingCreation)
final bookingCreationProvider =
    AutoDisposeNotifierProvider<BookingCreation, AsyncValue<Booking?>>.internal(
  BookingCreation.new,
  name: r'bookingCreationProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$bookingCreationHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef _$BookingCreation = AutoDisposeNotifier<AsyncValue<Booking?>>;
String _$selectedTimeSlotHash() => r'ed31898e694f38e12da96409b2f68a3a305df12c';

/// See also [SelectedTimeSlot].
@ProviderFor(SelectedTimeSlot)
final selectedTimeSlotProvider =
    AutoDisposeNotifierProvider<SelectedTimeSlot, DateTime?>.internal(
  SelectedTimeSlot.new,
  name: r'selectedTimeSlotProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$selectedTimeSlotHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef _$SelectedTimeSlot = AutoDisposeNotifier<DateTime?>;
String _$selectedBookingPageHash() =>
    r'7a35b58f3c4b24b7a676bf76f04ab9951e097c5d';

/// See also [SelectedBookingPage].
@ProviderFor(SelectedBookingPage)
final selectedBookingPageProvider =
    AutoDisposeNotifierProvider<SelectedBookingPage, BookingPage?>.internal(
  SelectedBookingPage.new,
  name: r'selectedBookingPageProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$selectedBookingPageHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef _$SelectedBookingPage = AutoDisposeNotifier<BookingPage?>;
// ignore_for_file: type=lint
// ignore_for_file: subtype_of_sealed_class, invalid_use_of_internal_member, invalid_use_of_visible_for_testing_member
