import 'package:riverpod_annotation/riverpod_annotation.dart';
import '../../data/datasources/api_client.dart';
import '../../data/models/community.dart';
import '../../data/models/community_event.dart';
import '../../data/models/paginated_response.dart';
import '../../core/network/dio_client.dart';

part 'community_provider.g.dart';

@riverpod
ApiClient communityApiClient(CommunityApiClientRef ref) {
  return ApiClient(DioClient.instance);
}

@riverpod
class CommunityList extends _$CommunityList {
  @override
  Future<PaginatedResponse<Community>> build({
    int page = 1,
    int perPage = 20,
  }) async {
    final apiClient = ref.read(communityApiClientProvider);
    return await apiClient.getCommunities(page);
  }

  Future<Community> createCommunity(Map<String, dynamic> data) async {
    try {
      final apiClient = ref.read(communityApiClientProvider);
      final community = await apiClient.createCommunity(data);

      // Refresh the community list
      ref.invalidateSelf();

      return community;
    } catch (e) {
      rethrow;
    }
  }

  Future<Community> updateCommunity(int id, Map<String, dynamic> data) async {
    try {
      final apiClient = ref.read(communityApiClientProvider);
      final community = await apiClient.updateCommunity(id, data);

      // Refresh the community list
      ref.invalidateSelf();

      return community;
    } catch (e) {
      rethrow;
    }
  }

  Future<void> deleteCommunity(int id) async {
    try {
      final apiClient = ref.read(communityApiClientProvider);
      await apiClient.deleteCommunity(id);

      // Refresh the community list
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
class CommunityDetails extends _$CommunityDetails {
  @override
  Future<Community?> build(int communityId) async {
    try {
      final apiClient = ref.read(communityApiClientProvider);
      return await apiClient.getCommunity(communityId);
    } catch (e) {
      return null;
    }
  }

  Future<void> refresh() async {
    ref.invalidateSelf();
  }
}

@riverpod
class CommunityEvents extends _$CommunityEvents {
  @override
  Future<PaginatedResponse<CommunityEvent>> build({
    required int communityId,
    int page = 1,
    int perPage = 20,
  }) async {
    final apiClient = ref.read(communityApiClientProvider);
    return await apiClient.getCommunityEvents(communityId, page);
  }

  Future<CommunityEvent> createEvent(int communityId, Map<String, dynamic> data) async {
    try {
      final apiClient = ref.read(communityApiClientProvider);
      final event = await apiClient.createCommunityEvent(communityId, data);

      // Refresh the events list
      ref.invalidateSelf();

      return event;
    } catch (e) {
      rethrow;
    }
  }

  Future<CommunityEvent> updateEvent(
    int communityId,
    int eventId,
    Map<String, dynamic> data,
  ) async {
    try {
      final apiClient = ref.read(communityApiClientProvider);
      final event = await apiClient.updateCommunityEvent(communityId, eventId, data);

      // Refresh the events list
      ref.invalidateSelf();

      return event;
    } catch (e) {
      rethrow;
    }
  }

  Future<void> deleteEvent(int communityId, int eventId) async {
    try {
      final apiClient = ref.read(communityApiClientProvider);
      await apiClient.deleteCommunityEvent(communityId, eventId);

      // Refresh the events list
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
class CommunityStats extends _$CommunityStats {
  @override
  Future<Map<String, dynamic>?> build(int communityId) async {
    try {
      final apiClient = ref.read(communityApiClientProvider);
      return await apiClient.getCommunityStats(communityId);
    } catch (e) {
      return null;
    }
  }
}

@riverpod
class PublicCommunityList extends _$PublicCommunityList {
  @override
  Future<PaginatedResponse<Community>> build({
    int page = 1,
    int perPage = 20,
  }) async {
    final apiClient = ref.read(communityApiClientProvider);
    return await apiClient.getPublicCommunities(page);
  }

  Future<void> refresh() async {
    ref.invalidateSelf();
  }
}

@riverpod
class PublicCommunityDetails extends _$PublicCommunityDetails {
  @override
  Future<Community?> build(String slug) async {
    try {
      final apiClient = ref.read(communityApiClientProvider);
      return await apiClient.getPublicCommunity(slug);
    } catch (e) {
      return null;
    }
  }
}

@riverpod
class PublicCommunityEvents extends _$PublicCommunityEvents {
  @override
  Future<PaginatedResponse<CommunityEvent>> build({
    required String communitySlug,
    int page = 1,
    int perPage = 20,
  }) async {
    final apiClient = ref.read(communityApiClientProvider);
    return await apiClient.getPublicCommunityEvents(communitySlug, page);
  }
}

// Provider for community creation state
@riverpod
class CommunityCreation extends _$CommunityCreation {
  @override
  AsyncValue<Community?> build() {
    return const AsyncValue.data(null);
  }

  Future<Community> createCommunity({
    required String name,
    required String slug,
    String? description,
    String? website,
    String? contactEmail,
    Map<String, dynamic>? socialLinks,
    String? timezone,
    String color = '#3B82F6',
    bool isPublic = true,
  }) async {
    state = const AsyncValue.loading();

    try {
      final apiClient = ref.read(communityApiClientProvider);
      final community = await apiClient.createCommunity({
        'name': name,
        'slug': slug,
        if (description != null) 'description': description,
        if (website != null) 'website': website,
        if (contactEmail != null) 'contact_email': contactEmail,
        if (socialLinks != null) 'social_links': socialLinks,
        if (timezone != null) 'timezone': timezone,
        'color': color,
        'is_public': isPublic,
      });

      state = AsyncValue.data(community);

      // Refresh community list
      ref.invalidate(communityListProvider);

      return community;
    } catch (e, stackTrace) {
      state = AsyncValue.error(e, stackTrace);
      rethrow;
    }
  }

  void reset() {
    state = const AsyncValue.data(null);
  }
}

// Provider for selected community
@riverpod
class SelectedCommunity extends _$SelectedCommunity {
  @override
  Community? build() {
    return null;
  }

  void selectCommunity(Community community) {
    state = community;
  }

  void clearSelection() {
    state = null;
  }
}