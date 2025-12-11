import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../providers/community_provider.dart';
import '../../widgets/community/community_tile.dart';
import '../../widgets/community/community_form.dart';
import '../../../data/models/community.dart';

class CommunitiesPage extends ConsumerStatefulWidget {
  const CommunitiesPage({super.key});

  @override
  ConsumerState<CommunitiesPage> createState() => _CommunitiesPageState();
}

class _CommunitiesPageState extends ConsumerState<CommunitiesPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Communities'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              ref.read(communityListProvider().notifier).refresh();
              ref.read(publicCommunityListProvider().notifier).refresh();
            },
          ),
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () => _showSearchDialog(context),
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'My Communities'),
            Tab(text: 'Discover'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildMyCommunities(),
          _buildDiscoverCommunities(),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showCreateCommunityDialog(context),
        child: const Icon(Icons.add),
      ),
    );
  }

  Widget _buildMyCommunities() {
    final communitiesAsync = ref.watch(communityListProvider());

    return communitiesAsync.when(
      data: (paginatedCommunities) {
        final communities = paginatedCommunities.data;

        if (communities.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.group_add,
                  size: 64,
                  color: Theme.of(context).colorScheme.outline,
                ),
                const SizedBox(height: 16),
                Text(
                  'No communities yet',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    color: Theme.of(context).colorScheme.outline,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Create your first community to get started',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: Theme.of(context).colorScheme.outline,
                  ),
                ),
                const SizedBox(height: 24),
                ElevatedButton.icon(
                  onPressed: () => _showCreateCommunityDialog(context),
                  icon: const Icon(Icons.add),
                  label: const Text('Create Community'),
                ),
              ],
            ),
          );
        }

        return RefreshIndicator(
          onRefresh: () async {
            await ref.read(communityListProvider().notifier).refresh();
          },
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: communities.length,
            itemBuilder: (context, index) {
              final community = communities[index];
              return CommunityTile(
                community: community,
                showActions: true,
                onTap: () => _showCommunityDetails(context, community),
                onEdit: () => _showEditCommunityDialog(context, community),
                onDelete: () => _showDeleteConfirmation(context, community),
              );
            },
          ),
        );
      },
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.error_outline,
              size: 64,
              color: Theme.of(context).colorScheme.error,
            ),
            const SizedBox(height: 16),
            Text(
              'Error loading communities',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            Text(
              error.toString(),
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Theme.of(context).colorScheme.outline,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () => ref.refresh(communityListProvider()),
              child: const Text('Retry'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDiscoverCommunities() {
    final publicCommunitiesAsync = ref.watch(publicCommunityListProvider());

    return publicCommunitiesAsync.when(
      data: (paginatedCommunities) {
        final communities = paginatedCommunities.data;

        if (communities.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  Icons.explore,
                  size: 64,
                  color: Theme.of(context).colorScheme.outline,
                ),
                const SizedBox(height: 16),
                Text(
                  'No public communities found',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    color: Theme.of(context).colorScheme.outline,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'Check back later for new communities',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: Theme.of(context).colorScheme.outline,
                  ),
                ),
              ],
            ),
          );
        }

        return RefreshIndicator(
          onRefresh: () async {
            await ref.read(publicCommunityListProvider().notifier).refresh();
          },
          child: ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: communities.length,
            itemBuilder: (context, index) {
              final community = communities[index];
              return CommunityTile(
                community: community,
                onTap: () => _showPublicCommunityDetails(context, community),
              );
            },
          ),
        );
      },
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.error_outline,
              size: 64,
              color: Theme.of(context).colorScheme.error,
            ),
            const SizedBox(height: 16),
            Text(
              'Error loading public communities',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            Text(
              error.toString(),
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Theme.of(context).colorScheme.outline,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () => ref.refresh(publicCommunityListProvider()),
              child: const Text('Retry'),
            ),
          ],
        ),
      ),
    );
  }

  void _showCreateCommunityDialog(BuildContext context) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => CommunityForm(
          onSuccess: () {
            Navigator.of(context).pop();
            ref.read(communityListProvider().notifier).refresh();
          },
          onCancel: () => Navigator.of(context).pop(),
        ),
      ),
    );
  }

  void _showEditCommunityDialog(BuildContext context, Community community) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => CommunityForm(
          community: community,
          onSuccess: () {
            Navigator.of(context).pop();
            ref.read(communityListProvider().notifier).refresh();
          },
          onCancel: () => Navigator.of(context).pop(),
        ),
      ),
    );
  }

  void _showCommunityDetails(BuildContext context, Community community) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.9,
        minChildSize: 0.5,
        builder: (context, scrollController) => Container(
          padding: const EdgeInsets.all(20),
          child: ListView(
            controller: scrollController,
            children: [
              // Handle bar
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.outline,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Community header
              Row(
                children: [
                  Container(
                    width: 60,
                    height: 60,
                    decoration: BoxDecoration(
                      color: _getCommunityColor(community).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: _getCommunityColor(community).withOpacity(0.3),
                      ),
                    ),
                    child: community.logoUrl != null
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: Image.network(
                              community.logoUrl!,
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) => Center(
                                child: Text(
                                  community.name[0].toUpperCase(),
                                  style: TextStyle(
                                    color: _getCommunityColor(community),
                                    fontWeight: FontWeight.bold,
                                    fontSize: 24,
                                  ),
                                ),
                              ),
                            ),
                          )
                        : Center(
                            child: Text(
                              community.name[0].toUpperCase(),
                              style: TextStyle(
                                color: _getCommunityColor(community),
                                fontWeight: FontWeight.bold,
                                fontSize: 24,
                              ),
                            ),
                          ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          community.name,
                          style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        Text(
                          '@${community.slug}',
                          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: Theme.of(context).colorScheme.outline,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),

              if (community.description?.isNotEmpty == true) ...[
                const SizedBox(height: 16),
                Text(
                  community.description!,
                  style: Theme.of(context).textTheme.bodyLarge,
                ),
              ],

              const SizedBox(height: 20),

              // Quick actions
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.of(context).pop();
                        _showEditCommunityDialog(context, community);
                      },
                      icon: const Icon(Icons.edit),
                      label: const Text('Edit'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () {
                        // TODO: Navigate to community events
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Community events coming soon')),
                        );
                      },
                      icon: const Icon(Icons.event),
                      label: const Text('Events'),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  void _showPublicCommunityDetails(BuildContext context, Community community) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.6,
        maxChildSize: 0.9,
        minChildSize: 0.4,
        builder: (context, scrollController) => Container(
          padding: const EdgeInsets.all(20),
          child: ListView(
            controller: scrollController,
            children: [
              // Handle bar
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.outline,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Community info
              Text(
                community.name,
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  fontWeight: FontWeight.bold,
                ),
              ),
              Text(
                '@${community.slug}',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: Theme.of(context).colorScheme.outline,
                ),
              ),

              if (community.description?.isNotEmpty == true) ...[
                const SizedBox(height: 16),
                Text(
                  community.description!,
                  style: Theme.of(context).textTheme.bodyLarge,
                ),
              ],

              if (community.website != null) ...[
                const SizedBox(height: 16),
                Row(
                  children: [
                    const Icon(Icons.language, size: 16),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        community.website!,
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Theme.of(context).colorScheme.primary,
                          decoration: TextDecoration.underline,
                        ),
                      ),
                    ),
                  ],
                ),
              ],

              const SizedBox(height: 20),

              ElevatedButton.icon(
                onPressed: () {
                  // TODO: Navigate to public community events
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Public community events coming soon')),
                  );
                },
                icon: const Icon(Icons.event),
                label: const Text('View Events'),
              ),

              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  void _showDeleteConfirmation(BuildContext context, Community community) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Community'),
        content: Text(
          'Are you sure you want to delete "${community.name}"? This action cannot be undone.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.of(context).pop();
              try {
                await ref.read(communityListProvider().notifier).deleteCommunity(community.id);
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Community deleted successfully')),
                  );
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Failed to delete community: $e')),
                  );
                }
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Theme.of(context).colorScheme.error,
              foregroundColor: Colors.white,
            ),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }

  void _showSearchDialog(BuildContext context) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Search feature coming soon')),
    );
  }

  Color _getCommunityColor(Community community) {
    try {
      return Color(int.parse(community.color.replaceFirst('#', '0xFF')));
    } catch (e) {
      return const Color(0xFF3B82F6);
    }
  }
}