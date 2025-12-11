// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'community_provider.dart';

// **************************************************************************
// RiverpodGenerator
// **************************************************************************

String _$communityApiClientHash() =>
    r'9f8fd94b45d67ae59be19ea731960771c7218f0d';

/// See also [communityApiClient].
@ProviderFor(communityApiClient)
final communityApiClientProvider = AutoDisposeProvider<ApiClient>.internal(
  communityApiClient,
  name: r'communityApiClientProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$communityApiClientHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef CommunityApiClientRef = AutoDisposeProviderRef<ApiClient>;
String _$communityListHash() => r'0a397fd8e350baebb15be742f10529bc5f4d9133';

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

abstract class _$CommunityList
    extends BuildlessAutoDisposeAsyncNotifier<PaginatedResponse<Community>> {
  late final int page;
  late final int perPage;

  FutureOr<PaginatedResponse<Community>> build({
    int page = 1,
    int perPage = 20,
  });
}

/// See also [CommunityList].
@ProviderFor(CommunityList)
const communityListProvider = CommunityListFamily();

/// See also [CommunityList].
class CommunityListFamily
    extends Family<AsyncValue<PaginatedResponse<Community>>> {
  /// See also [CommunityList].
  const CommunityListFamily();

  /// See also [CommunityList].
  CommunityListProvider call({
    int page = 1,
    int perPage = 20,
  }) {
    return CommunityListProvider(
      page: page,
      perPage: perPage,
    );
  }

  @override
  CommunityListProvider getProviderOverride(
    covariant CommunityListProvider provider,
  ) {
    return call(
      page: provider.page,
      perPage: provider.perPage,
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
  String? get name => r'communityListProvider';
}

/// See also [CommunityList].
class CommunityListProvider extends AutoDisposeAsyncNotifierProviderImpl<
    CommunityList, PaginatedResponse<Community>> {
  /// See also [CommunityList].
  CommunityListProvider({
    int page = 1,
    int perPage = 20,
  }) : this._internal(
          () => CommunityList()
            ..page = page
            ..perPage = perPage,
          from: communityListProvider,
          name: r'communityListProvider',
          debugGetCreateSourceHash:
              const bool.fromEnvironment('dart.vm.product')
                  ? null
                  : _$communityListHash,
          dependencies: CommunityListFamily._dependencies,
          allTransitiveDependencies:
              CommunityListFamily._allTransitiveDependencies,
          page: page,
          perPage: perPage,
        );

  CommunityListProvider._internal(
    super._createNotifier, {
    required super.name,
    required super.dependencies,
    required super.allTransitiveDependencies,
    required super.debugGetCreateSourceHash,
    required super.from,
    required this.page,
    required this.perPage,
  }) : super.internal();

  final int page;
  final int perPage;

  @override
  FutureOr<PaginatedResponse<Community>> runNotifierBuild(
    covariant CommunityList notifier,
  ) {
    return notifier.build(
      page: page,
      perPage: perPage,
    );
  }

  @override
  Override overrideWith(CommunityList Function() create) {
    return ProviderOverride(
      origin: this,
      override: CommunityListProvider._internal(
        () => create()
          ..page = page
          ..perPage = perPage,
        from: from,
        name: null,
        dependencies: null,
        allTransitiveDependencies: null,
        debugGetCreateSourceHash: null,
        page: page,
        perPage: perPage,
      ),
    );
  }

  @override
  AutoDisposeAsyncNotifierProviderElement<CommunityList,
      PaginatedResponse<Community>> createElement() {
    return _CommunityListProviderElement(this);
  }

  @override
  bool operator ==(Object other) {
    return other is CommunityListProvider &&
        other.page == page &&
        other.perPage == perPage;
  }

  @override
  int get hashCode {
    var hash = _SystemHash.combine(0, runtimeType.hashCode);
    hash = _SystemHash.combine(hash, page.hashCode);
    hash = _SystemHash.combine(hash, perPage.hashCode);

    return _SystemHash.finish(hash);
  }
}

mixin CommunityListRef
    on AutoDisposeAsyncNotifierProviderRef<PaginatedResponse<Community>> {
  /// The parameter `page` of this provider.
  int get page;

  /// The parameter `perPage` of this provider.
  int get perPage;
}

class _CommunityListProviderElement
    extends AutoDisposeAsyncNotifierProviderElement<CommunityList,
        PaginatedResponse<Community>> with CommunityListRef {
  _CommunityListProviderElement(super.provider);

  @override
  int get page => (origin as CommunityListProvider).page;
  @override
  int get perPage => (origin as CommunityListProvider).perPage;
}

String _$communityDetailsHash() => r'2865c04d3fe7a1baa28a089b2f2d0dc74fcb0195';

abstract class _$CommunityDetails
    extends BuildlessAutoDisposeAsyncNotifier<Community?> {
  late final int communityId;

  FutureOr<Community?> build(
    int communityId,
  );
}

/// See also [CommunityDetails].
@ProviderFor(CommunityDetails)
const communityDetailsProvider = CommunityDetailsFamily();

/// See also [CommunityDetails].
class CommunityDetailsFamily extends Family<AsyncValue<Community?>> {
  /// See also [CommunityDetails].
  const CommunityDetailsFamily();

  /// See also [CommunityDetails].
  CommunityDetailsProvider call(
    int communityId,
  ) {
    return CommunityDetailsProvider(
      communityId,
    );
  }

  @override
  CommunityDetailsProvider getProviderOverride(
    covariant CommunityDetailsProvider provider,
  ) {
    return call(
      provider.communityId,
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
  String? get name => r'communityDetailsProvider';
}

/// See also [CommunityDetails].
class CommunityDetailsProvider
    extends AutoDisposeAsyncNotifierProviderImpl<CommunityDetails, Community?> {
  /// See also [CommunityDetails].
  CommunityDetailsProvider(
    int communityId,
  ) : this._internal(
          () => CommunityDetails()..communityId = communityId,
          from: communityDetailsProvider,
          name: r'communityDetailsProvider',
          debugGetCreateSourceHash:
              const bool.fromEnvironment('dart.vm.product')
                  ? null
                  : _$communityDetailsHash,
          dependencies: CommunityDetailsFamily._dependencies,
          allTransitiveDependencies:
              CommunityDetailsFamily._allTransitiveDependencies,
          communityId: communityId,
        );

  CommunityDetailsProvider._internal(
    super._createNotifier, {
    required super.name,
    required super.dependencies,
    required super.allTransitiveDependencies,
    required super.debugGetCreateSourceHash,
    required super.from,
    required this.communityId,
  }) : super.internal();

  final int communityId;

  @override
  FutureOr<Community?> runNotifierBuild(
    covariant CommunityDetails notifier,
  ) {
    return notifier.build(
      communityId,
    );
  }

  @override
  Override overrideWith(CommunityDetails Function() create) {
    return ProviderOverride(
      origin: this,
      override: CommunityDetailsProvider._internal(
        () => create()..communityId = communityId,
        from: from,
        name: null,
        dependencies: null,
        allTransitiveDependencies: null,
        debugGetCreateSourceHash: null,
        communityId: communityId,
      ),
    );
  }

  @override
  AutoDisposeAsyncNotifierProviderElement<CommunityDetails, Community?>
      createElement() {
    return _CommunityDetailsProviderElement(this);
  }

  @override
  bool operator ==(Object other) {
    return other is CommunityDetailsProvider &&
        other.communityId == communityId;
  }

  @override
  int get hashCode {
    var hash = _SystemHash.combine(0, runtimeType.hashCode);
    hash = _SystemHash.combine(hash, communityId.hashCode);

    return _SystemHash.finish(hash);
  }
}

mixin CommunityDetailsRef on AutoDisposeAsyncNotifierProviderRef<Community?> {
  /// The parameter `communityId` of this provider.
  int get communityId;
}

class _CommunityDetailsProviderElement
    extends AutoDisposeAsyncNotifierProviderElement<CommunityDetails,
        Community?> with CommunityDetailsRef {
  _CommunityDetailsProviderElement(super.provider);

  @override
  int get communityId => (origin as CommunityDetailsProvider).communityId;
}

String _$communityEventsHash() => r'1222dda8fae349b37b8daca7a15494235aa9706a';

abstract class _$CommunityEvents extends BuildlessAutoDisposeAsyncNotifier<
    PaginatedResponse<CommunityEvent>> {
  late final int communityId;
  late final int page;
  late final int perPage;

  FutureOr<PaginatedResponse<CommunityEvent>> build({
    required int communityId,
    int page = 1,
    int perPage = 20,
  });
}

/// See also [CommunityEvents].
@ProviderFor(CommunityEvents)
const communityEventsProvider = CommunityEventsFamily();

/// See also [CommunityEvents].
class CommunityEventsFamily
    extends Family<AsyncValue<PaginatedResponse<CommunityEvent>>> {
  /// See also [CommunityEvents].
  const CommunityEventsFamily();

  /// See also [CommunityEvents].
  CommunityEventsProvider call({
    required int communityId,
    int page = 1,
    int perPage = 20,
  }) {
    return CommunityEventsProvider(
      communityId: communityId,
      page: page,
      perPage: perPage,
    );
  }

  @override
  CommunityEventsProvider getProviderOverride(
    covariant CommunityEventsProvider provider,
  ) {
    return call(
      communityId: provider.communityId,
      page: provider.page,
      perPage: provider.perPage,
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
  String? get name => r'communityEventsProvider';
}

/// See also [CommunityEvents].
class CommunityEventsProvider extends AutoDisposeAsyncNotifierProviderImpl<
    CommunityEvents, PaginatedResponse<CommunityEvent>> {
  /// See also [CommunityEvents].
  CommunityEventsProvider({
    required int communityId,
    int page = 1,
    int perPage = 20,
  }) : this._internal(
          () => CommunityEvents()
            ..communityId = communityId
            ..page = page
            ..perPage = perPage,
          from: communityEventsProvider,
          name: r'communityEventsProvider',
          debugGetCreateSourceHash:
              const bool.fromEnvironment('dart.vm.product')
                  ? null
                  : _$communityEventsHash,
          dependencies: CommunityEventsFamily._dependencies,
          allTransitiveDependencies:
              CommunityEventsFamily._allTransitiveDependencies,
          communityId: communityId,
          page: page,
          perPage: perPage,
        );

  CommunityEventsProvider._internal(
    super._createNotifier, {
    required super.name,
    required super.dependencies,
    required super.allTransitiveDependencies,
    required super.debugGetCreateSourceHash,
    required super.from,
    required this.communityId,
    required this.page,
    required this.perPage,
  }) : super.internal();

  final int communityId;
  final int page;
  final int perPage;

  @override
  FutureOr<PaginatedResponse<CommunityEvent>> runNotifierBuild(
    covariant CommunityEvents notifier,
  ) {
    return notifier.build(
      communityId: communityId,
      page: page,
      perPage: perPage,
    );
  }

  @override
  Override overrideWith(CommunityEvents Function() create) {
    return ProviderOverride(
      origin: this,
      override: CommunityEventsProvider._internal(
        () => create()
          ..communityId = communityId
          ..page = page
          ..perPage = perPage,
        from: from,
        name: null,
        dependencies: null,
        allTransitiveDependencies: null,
        debugGetCreateSourceHash: null,
        communityId: communityId,
        page: page,
        perPage: perPage,
      ),
    );
  }

  @override
  AutoDisposeAsyncNotifierProviderElement<CommunityEvents,
      PaginatedResponse<CommunityEvent>> createElement() {
    return _CommunityEventsProviderElement(this);
  }

  @override
  bool operator ==(Object other) {
    return other is CommunityEventsProvider &&
        other.communityId == communityId &&
        other.page == page &&
        other.perPage == perPage;
  }

  @override
  int get hashCode {
    var hash = _SystemHash.combine(0, runtimeType.hashCode);
    hash = _SystemHash.combine(hash, communityId.hashCode);
    hash = _SystemHash.combine(hash, page.hashCode);
    hash = _SystemHash.combine(hash, perPage.hashCode);

    return _SystemHash.finish(hash);
  }
}

mixin CommunityEventsRef
    on AutoDisposeAsyncNotifierProviderRef<PaginatedResponse<CommunityEvent>> {
  /// The parameter `communityId` of this provider.
  int get communityId;

  /// The parameter `page` of this provider.
  int get page;

  /// The parameter `perPage` of this provider.
  int get perPage;
}

class _CommunityEventsProviderElement
    extends AutoDisposeAsyncNotifierProviderElement<CommunityEvents,
        PaginatedResponse<CommunityEvent>> with CommunityEventsRef {
  _CommunityEventsProviderElement(super.provider);

  @override
  int get communityId => (origin as CommunityEventsProvider).communityId;
  @override
  int get page => (origin as CommunityEventsProvider).page;
  @override
  int get perPage => (origin as CommunityEventsProvider).perPage;
}

String _$communityStatsHash() => r'89ef2c9fc4c9e70d77b69b4b0208df40ad9a7d5f';

abstract class _$CommunityStats
    extends BuildlessAutoDisposeAsyncNotifier<Map<String, dynamic>?> {
  late final int communityId;

  FutureOr<Map<String, dynamic>?> build(
    int communityId,
  );
}

/// See also [CommunityStats].
@ProviderFor(CommunityStats)
const communityStatsProvider = CommunityStatsFamily();

/// See also [CommunityStats].
class CommunityStatsFamily extends Family<AsyncValue<Map<String, dynamic>?>> {
  /// See also [CommunityStats].
  const CommunityStatsFamily();

  /// See also [CommunityStats].
  CommunityStatsProvider call(
    int communityId,
  ) {
    return CommunityStatsProvider(
      communityId,
    );
  }

  @override
  CommunityStatsProvider getProviderOverride(
    covariant CommunityStatsProvider provider,
  ) {
    return call(
      provider.communityId,
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
  String? get name => r'communityStatsProvider';
}

/// See also [CommunityStats].
class CommunityStatsProvider extends AutoDisposeAsyncNotifierProviderImpl<
    CommunityStats, Map<String, dynamic>?> {
  /// See also [CommunityStats].
  CommunityStatsProvider(
    int communityId,
  ) : this._internal(
          () => CommunityStats()..communityId = communityId,
          from: communityStatsProvider,
          name: r'communityStatsProvider',
          debugGetCreateSourceHash:
              const bool.fromEnvironment('dart.vm.product')
                  ? null
                  : _$communityStatsHash,
          dependencies: CommunityStatsFamily._dependencies,
          allTransitiveDependencies:
              CommunityStatsFamily._allTransitiveDependencies,
          communityId: communityId,
        );

  CommunityStatsProvider._internal(
    super._createNotifier, {
    required super.name,
    required super.dependencies,
    required super.allTransitiveDependencies,
    required super.debugGetCreateSourceHash,
    required super.from,
    required this.communityId,
  }) : super.internal();

  final int communityId;

  @override
  FutureOr<Map<String, dynamic>?> runNotifierBuild(
    covariant CommunityStats notifier,
  ) {
    return notifier.build(
      communityId,
    );
  }

  @override
  Override overrideWith(CommunityStats Function() create) {
    return ProviderOverride(
      origin: this,
      override: CommunityStatsProvider._internal(
        () => create()..communityId = communityId,
        from: from,
        name: null,
        dependencies: null,
        allTransitiveDependencies: null,
        debugGetCreateSourceHash: null,
        communityId: communityId,
      ),
    );
  }

  @override
  AutoDisposeAsyncNotifierProviderElement<CommunityStats, Map<String, dynamic>?>
      createElement() {
    return _CommunityStatsProviderElement(this);
  }

  @override
  bool operator ==(Object other) {
    return other is CommunityStatsProvider && other.communityId == communityId;
  }

  @override
  int get hashCode {
    var hash = _SystemHash.combine(0, runtimeType.hashCode);
    hash = _SystemHash.combine(hash, communityId.hashCode);

    return _SystemHash.finish(hash);
  }
}

mixin CommunityStatsRef
    on AutoDisposeAsyncNotifierProviderRef<Map<String, dynamic>?> {
  /// The parameter `communityId` of this provider.
  int get communityId;
}

class _CommunityStatsProviderElement
    extends AutoDisposeAsyncNotifierProviderElement<CommunityStats,
        Map<String, dynamic>?> with CommunityStatsRef {
  _CommunityStatsProviderElement(super.provider);

  @override
  int get communityId => (origin as CommunityStatsProvider).communityId;
}

String _$publicCommunityListHash() =>
    r'699fd7be6585cad43b3b2e73a492150c4aaaa564';

abstract class _$PublicCommunityList
    extends BuildlessAutoDisposeAsyncNotifier<PaginatedResponse<Community>> {
  late final int page;
  late final int perPage;

  FutureOr<PaginatedResponse<Community>> build({
    int page = 1,
    int perPage = 20,
  });
}

/// See also [PublicCommunityList].
@ProviderFor(PublicCommunityList)
const publicCommunityListProvider = PublicCommunityListFamily();

/// See also [PublicCommunityList].
class PublicCommunityListFamily
    extends Family<AsyncValue<PaginatedResponse<Community>>> {
  /// See also [PublicCommunityList].
  const PublicCommunityListFamily();

  /// See also [PublicCommunityList].
  PublicCommunityListProvider call({
    int page = 1,
    int perPage = 20,
  }) {
    return PublicCommunityListProvider(
      page: page,
      perPage: perPage,
    );
  }

  @override
  PublicCommunityListProvider getProviderOverride(
    covariant PublicCommunityListProvider provider,
  ) {
    return call(
      page: provider.page,
      perPage: provider.perPage,
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
  String? get name => r'publicCommunityListProvider';
}

/// See also [PublicCommunityList].
class PublicCommunityListProvider extends AutoDisposeAsyncNotifierProviderImpl<
    PublicCommunityList, PaginatedResponse<Community>> {
  /// See also [PublicCommunityList].
  PublicCommunityListProvider({
    int page = 1,
    int perPage = 20,
  }) : this._internal(
          () => PublicCommunityList()
            ..page = page
            ..perPage = perPage,
          from: publicCommunityListProvider,
          name: r'publicCommunityListProvider',
          debugGetCreateSourceHash:
              const bool.fromEnvironment('dart.vm.product')
                  ? null
                  : _$publicCommunityListHash,
          dependencies: PublicCommunityListFamily._dependencies,
          allTransitiveDependencies:
              PublicCommunityListFamily._allTransitiveDependencies,
          page: page,
          perPage: perPage,
        );

  PublicCommunityListProvider._internal(
    super._createNotifier, {
    required super.name,
    required super.dependencies,
    required super.allTransitiveDependencies,
    required super.debugGetCreateSourceHash,
    required super.from,
    required this.page,
    required this.perPage,
  }) : super.internal();

  final int page;
  final int perPage;

  @override
  FutureOr<PaginatedResponse<Community>> runNotifierBuild(
    covariant PublicCommunityList notifier,
  ) {
    return notifier.build(
      page: page,
      perPage: perPage,
    );
  }

  @override
  Override overrideWith(PublicCommunityList Function() create) {
    return ProviderOverride(
      origin: this,
      override: PublicCommunityListProvider._internal(
        () => create()
          ..page = page
          ..perPage = perPage,
        from: from,
        name: null,
        dependencies: null,
        allTransitiveDependencies: null,
        debugGetCreateSourceHash: null,
        page: page,
        perPage: perPage,
      ),
    );
  }

  @override
  AutoDisposeAsyncNotifierProviderElement<PublicCommunityList,
      PaginatedResponse<Community>> createElement() {
    return _PublicCommunityListProviderElement(this);
  }

  @override
  bool operator ==(Object other) {
    return other is PublicCommunityListProvider &&
        other.page == page &&
        other.perPage == perPage;
  }

  @override
  int get hashCode {
    var hash = _SystemHash.combine(0, runtimeType.hashCode);
    hash = _SystemHash.combine(hash, page.hashCode);
    hash = _SystemHash.combine(hash, perPage.hashCode);

    return _SystemHash.finish(hash);
  }
}

mixin PublicCommunityListRef
    on AutoDisposeAsyncNotifierProviderRef<PaginatedResponse<Community>> {
  /// The parameter `page` of this provider.
  int get page;

  /// The parameter `perPage` of this provider.
  int get perPage;
}

class _PublicCommunityListProviderElement
    extends AutoDisposeAsyncNotifierProviderElement<PublicCommunityList,
        PaginatedResponse<Community>> with PublicCommunityListRef {
  _PublicCommunityListProviderElement(super.provider);

  @override
  int get page => (origin as PublicCommunityListProvider).page;
  @override
  int get perPage => (origin as PublicCommunityListProvider).perPage;
}

String _$publicCommunityDetailsHash() =>
    r'845b01821f46dee7c458d0d93517d614c951f27f';

abstract class _$PublicCommunityDetails
    extends BuildlessAutoDisposeAsyncNotifier<Community?> {
  late final String slug;

  FutureOr<Community?> build(
    String slug,
  );
}

/// See also [PublicCommunityDetails].
@ProviderFor(PublicCommunityDetails)
const publicCommunityDetailsProvider = PublicCommunityDetailsFamily();

/// See also [PublicCommunityDetails].
class PublicCommunityDetailsFamily extends Family<AsyncValue<Community?>> {
  /// See also [PublicCommunityDetails].
  const PublicCommunityDetailsFamily();

  /// See also [PublicCommunityDetails].
  PublicCommunityDetailsProvider call(
    String slug,
  ) {
    return PublicCommunityDetailsProvider(
      slug,
    );
  }

  @override
  PublicCommunityDetailsProvider getProviderOverride(
    covariant PublicCommunityDetailsProvider provider,
  ) {
    return call(
      provider.slug,
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
  String? get name => r'publicCommunityDetailsProvider';
}

/// See also [PublicCommunityDetails].
class PublicCommunityDetailsProvider
    extends AutoDisposeAsyncNotifierProviderImpl<PublicCommunityDetails,
        Community?> {
  /// See also [PublicCommunityDetails].
  PublicCommunityDetailsProvider(
    String slug,
  ) : this._internal(
          () => PublicCommunityDetails()..slug = slug,
          from: publicCommunityDetailsProvider,
          name: r'publicCommunityDetailsProvider',
          debugGetCreateSourceHash:
              const bool.fromEnvironment('dart.vm.product')
                  ? null
                  : _$publicCommunityDetailsHash,
          dependencies: PublicCommunityDetailsFamily._dependencies,
          allTransitiveDependencies:
              PublicCommunityDetailsFamily._allTransitiveDependencies,
          slug: slug,
        );

  PublicCommunityDetailsProvider._internal(
    super._createNotifier, {
    required super.name,
    required super.dependencies,
    required super.allTransitiveDependencies,
    required super.debugGetCreateSourceHash,
    required super.from,
    required this.slug,
  }) : super.internal();

  final String slug;

  @override
  FutureOr<Community?> runNotifierBuild(
    covariant PublicCommunityDetails notifier,
  ) {
    return notifier.build(
      slug,
    );
  }

  @override
  Override overrideWith(PublicCommunityDetails Function() create) {
    return ProviderOverride(
      origin: this,
      override: PublicCommunityDetailsProvider._internal(
        () => create()..slug = slug,
        from: from,
        name: null,
        dependencies: null,
        allTransitiveDependencies: null,
        debugGetCreateSourceHash: null,
        slug: slug,
      ),
    );
  }

  @override
  AutoDisposeAsyncNotifierProviderElement<PublicCommunityDetails, Community?>
      createElement() {
    return _PublicCommunityDetailsProviderElement(this);
  }

  @override
  bool operator ==(Object other) {
    return other is PublicCommunityDetailsProvider && other.slug == slug;
  }

  @override
  int get hashCode {
    var hash = _SystemHash.combine(0, runtimeType.hashCode);
    hash = _SystemHash.combine(hash, slug.hashCode);

    return _SystemHash.finish(hash);
  }
}

mixin PublicCommunityDetailsRef
    on AutoDisposeAsyncNotifierProviderRef<Community?> {
  /// The parameter `slug` of this provider.
  String get slug;
}

class _PublicCommunityDetailsProviderElement
    extends AutoDisposeAsyncNotifierProviderElement<PublicCommunityDetails,
        Community?> with PublicCommunityDetailsRef {
  _PublicCommunityDetailsProviderElement(super.provider);

  @override
  String get slug => (origin as PublicCommunityDetailsProvider).slug;
}

String _$publicCommunityEventsHash() =>
    r'5ce0e3ae2fe3602d89a2d5fe908b8d4b918f3fd7';

abstract class _$PublicCommunityEvents
    extends BuildlessAutoDisposeAsyncNotifier<
        PaginatedResponse<CommunityEvent>> {
  late final String communitySlug;
  late final int page;
  late final int perPage;

  FutureOr<PaginatedResponse<CommunityEvent>> build({
    required String communitySlug,
    int page = 1,
    int perPage = 20,
  });
}

/// See also [PublicCommunityEvents].
@ProviderFor(PublicCommunityEvents)
const publicCommunityEventsProvider = PublicCommunityEventsFamily();

/// See also [PublicCommunityEvents].
class PublicCommunityEventsFamily
    extends Family<AsyncValue<PaginatedResponse<CommunityEvent>>> {
  /// See also [PublicCommunityEvents].
  const PublicCommunityEventsFamily();

  /// See also [PublicCommunityEvents].
  PublicCommunityEventsProvider call({
    required String communitySlug,
    int page = 1,
    int perPage = 20,
  }) {
    return PublicCommunityEventsProvider(
      communitySlug: communitySlug,
      page: page,
      perPage: perPage,
    );
  }

  @override
  PublicCommunityEventsProvider getProviderOverride(
    covariant PublicCommunityEventsProvider provider,
  ) {
    return call(
      communitySlug: provider.communitySlug,
      page: provider.page,
      perPage: provider.perPage,
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
  String? get name => r'publicCommunityEventsProvider';
}

/// See also [PublicCommunityEvents].
class PublicCommunityEventsProvider
    extends AutoDisposeAsyncNotifierProviderImpl<PublicCommunityEvents,
        PaginatedResponse<CommunityEvent>> {
  /// See also [PublicCommunityEvents].
  PublicCommunityEventsProvider({
    required String communitySlug,
    int page = 1,
    int perPage = 20,
  }) : this._internal(
          () => PublicCommunityEvents()
            ..communitySlug = communitySlug
            ..page = page
            ..perPage = perPage,
          from: publicCommunityEventsProvider,
          name: r'publicCommunityEventsProvider',
          debugGetCreateSourceHash:
              const bool.fromEnvironment('dart.vm.product')
                  ? null
                  : _$publicCommunityEventsHash,
          dependencies: PublicCommunityEventsFamily._dependencies,
          allTransitiveDependencies:
              PublicCommunityEventsFamily._allTransitiveDependencies,
          communitySlug: communitySlug,
          page: page,
          perPage: perPage,
        );

  PublicCommunityEventsProvider._internal(
    super._createNotifier, {
    required super.name,
    required super.dependencies,
    required super.allTransitiveDependencies,
    required super.debugGetCreateSourceHash,
    required super.from,
    required this.communitySlug,
    required this.page,
    required this.perPage,
  }) : super.internal();

  final String communitySlug;
  final int page;
  final int perPage;

  @override
  FutureOr<PaginatedResponse<CommunityEvent>> runNotifierBuild(
    covariant PublicCommunityEvents notifier,
  ) {
    return notifier.build(
      communitySlug: communitySlug,
      page: page,
      perPage: perPage,
    );
  }

  @override
  Override overrideWith(PublicCommunityEvents Function() create) {
    return ProviderOverride(
      origin: this,
      override: PublicCommunityEventsProvider._internal(
        () => create()
          ..communitySlug = communitySlug
          ..page = page
          ..perPage = perPage,
        from: from,
        name: null,
        dependencies: null,
        allTransitiveDependencies: null,
        debugGetCreateSourceHash: null,
        communitySlug: communitySlug,
        page: page,
        perPage: perPage,
      ),
    );
  }

  @override
  AutoDisposeAsyncNotifierProviderElement<PublicCommunityEvents,
      PaginatedResponse<CommunityEvent>> createElement() {
    return _PublicCommunityEventsProviderElement(this);
  }

  @override
  bool operator ==(Object other) {
    return other is PublicCommunityEventsProvider &&
        other.communitySlug == communitySlug &&
        other.page == page &&
        other.perPage == perPage;
  }

  @override
  int get hashCode {
    var hash = _SystemHash.combine(0, runtimeType.hashCode);
    hash = _SystemHash.combine(hash, communitySlug.hashCode);
    hash = _SystemHash.combine(hash, page.hashCode);
    hash = _SystemHash.combine(hash, perPage.hashCode);

    return _SystemHash.finish(hash);
  }
}

mixin PublicCommunityEventsRef
    on AutoDisposeAsyncNotifierProviderRef<PaginatedResponse<CommunityEvent>> {
  /// The parameter `communitySlug` of this provider.
  String get communitySlug;

  /// The parameter `page` of this provider.
  int get page;

  /// The parameter `perPage` of this provider.
  int get perPage;
}

class _PublicCommunityEventsProviderElement
    extends AutoDisposeAsyncNotifierProviderElement<PublicCommunityEvents,
        PaginatedResponse<CommunityEvent>> with PublicCommunityEventsRef {
  _PublicCommunityEventsProviderElement(super.provider);

  @override
  String get communitySlug =>
      (origin as PublicCommunityEventsProvider).communitySlug;
  @override
  int get page => (origin as PublicCommunityEventsProvider).page;
  @override
  int get perPage => (origin as PublicCommunityEventsProvider).perPage;
}

String _$communityCreationHash() => r'37995e7ab8eaa034d16a6efabb5d768a33ca3d91';

/// See also [CommunityCreation].
@ProviderFor(CommunityCreation)
final communityCreationProvider = AutoDisposeNotifierProvider<CommunityCreation,
    AsyncValue<Community?>>.internal(
  CommunityCreation.new,
  name: r'communityCreationProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$communityCreationHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef _$CommunityCreation = AutoDisposeNotifier<AsyncValue<Community?>>;
String _$selectedCommunityHash() => r'77523e518eb2d9c086ab508778559d295aaa5840';

/// See also [SelectedCommunity].
@ProviderFor(SelectedCommunity)
final selectedCommunityProvider =
    AutoDisposeNotifierProvider<SelectedCommunity, Community?>.internal(
  SelectedCommunity.new,
  name: r'selectedCommunityProvider',
  debugGetCreateSourceHash: const bool.fromEnvironment('dart.vm.product')
      ? null
      : _$selectedCommunityHash,
  dependencies: null,
  allTransitiveDependencies: null,
);

typedef _$SelectedCommunity = AutoDisposeNotifier<Community?>;
// ignore_for_file: type=lint
// ignore_for_file: subtype_of_sealed_class, invalid_use_of_internal_member, invalid_use_of_visible_for_testing_member
