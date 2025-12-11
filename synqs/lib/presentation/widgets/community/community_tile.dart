import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../../data/models/community.dart';

class CommunityTile extends StatelessWidget {
  final Community community;
  final VoidCallback? onTap;
  final VoidCallback? onEdit;
  final VoidCallback? onDelete;
  final bool showActions;
  final bool isCompact;

  const CommunityTile({
    super.key,
    required this.community,
    this.onTap,
    this.onEdit,
    this.onDelete,
    this.showActions = false,
    this.isCompact = false,
  });

  @override
  Widget build(BuildContext context) {
    final communityColor = _getCommunityColor();

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: EdgeInsets.all(isCompact ? 12 : 16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            border: Border(
              left: BorderSide(
                color: communityColor,
                width: 4,
              ),
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header row
              Row(
                children: [
                  // Logo or color indicator
                  Container(
                    width: isCompact ? 40 : 50,
                    height: isCompact ? 40 : 50,
                    decoration: BoxDecoration(
                      color: communityColor.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: communityColor.withOpacity(0.3),
                      ),
                    ),
                    child: community.logoUrl != null
                        ? ClipRRect(
                            borderRadius: BorderRadius.circular(8),
                            child: Image.network(
                              community.logoUrl!,
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) => _buildFallbackLogo(communityColor),
                            ),
                          )
                        : _buildFallbackLogo(communityColor),
                  ),

                  const SizedBox(width: 12),

                  // Community info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                community.name,
                                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.w600,
                                ),
                                maxLines: isCompact ? 1 : 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            // Status indicators
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                if (community.isPublic)
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 6,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: Colors.green.withOpacity(0.1),
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(
                                        color: Colors.green.withOpacity(0.3),
                                      ),
                                    ),
                                    child: Text(
                                      'PUBLIC',
                                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                        color: Colors.green,
                                        fontWeight: FontWeight.w600,
                                        fontSize: 9,
                                      ),
                                    ),
                                  ),
                                if (!community.isActive) ...[
                                  const SizedBox(width: 4),
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 6,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: Colors.grey.withOpacity(0.1),
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(
                                        color: Colors.grey.withOpacity(0.3),
                                      ),
                                    ),
                                    child: Text(
                                      'INACTIVE',
                                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                        color: Colors.grey,
                                        fontWeight: FontWeight.w600,
                                        fontSize: 9,
                                      ),
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ],
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '@${community.slug}',
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: Theme.of(context).colorScheme.outline,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Actions menu
                  if (showActions)
                    PopupMenuButton<String>(
                      onSelected: (value) {
                        switch (value) {
                          case 'edit':
                            onEdit?.call();
                            break;
                          case 'delete':
                            onDelete?.call();
                            break;
                        }
                      },
                      itemBuilder: (context) => [
                        const PopupMenuItem(
                          value: 'edit',
                          child: Row(
                            children: [
                              Icon(Icons.edit, size: 16),
                              SizedBox(width: 8),
                              Text('Edit'),
                            ],
                          ),
                        ),
                        const PopupMenuItem(
                          value: 'delete',
                          child: Row(
                            children: [
                              Icon(Icons.delete, size: 16, color: Colors.red),
                              SizedBox(width: 8),
                              Text('Delete', style: TextStyle(color: Colors.red)),
                            ],
                          ),
                        ),
                      ],
                    ),
                ],
              ),

              if (!isCompact) ...[
                const SizedBox(height: 12),

                // Description
                if (community.description?.isNotEmpty == true) ...[
                  Text(
                    community.description!,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: Theme.of(context).colorScheme.onSurface.withOpacity(0.8),
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 8),
                ],

                // Stats row
                Row(
                  children: [
                    if (community.eventsCount != null) ...[
                      Icon(
                        Icons.event,
                        size: 16,
                        color: Theme.of(context).colorScheme.outline,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        '${community.eventsCount} events',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Theme.of(context).colorScheme.outline,
                        ),
                      ),
                    ],
                    if (community.callsForSpeakersCount != null && community.eventsCount != null)
                      const SizedBox(width: 12),
                    if (community.callsForSpeakersCount != null) ...[
                      Icon(
                        Icons.campaign,
                        size: 16,
                        color: Theme.of(context).colorScheme.outline,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        '${community.callsForSpeakersCount} CFS',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Theme.of(context).colorScheme.outline,
                        ),
                      ),
                    ],
                    const Spacer(),
                    if (community.createdAt != null)
                      Text(
                        'Created ${DateFormat.yMMMd().format(community.createdAt!)}',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Theme.of(context).colorScheme.outline,
                        ),
                      ),
                  ],
                ),

                // Website and contact info
                if (community.website != null || community.contactEmail != null) ...[
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      if (community.website != null) ...[
                        Icon(
                          Icons.language,
                          size: 16,
                          color: Theme.of(context).colorScheme.outline,
                        ),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            community.website!,
                            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                              color: Theme.of(context).colorScheme.primary,
                              decoration: TextDecoration.underline,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                      if (community.contactEmail != null) ...[
                        if (community.website != null) const SizedBox(width: 12),
                        Icon(
                          Icons.email,
                          size: 16,
                          color: Theme.of(context).colorScheme.outline,
                        ),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            community.contactEmail!,
                            style: Theme.of(context).textTheme.bodySmall?.copyWith(
                              color: Theme.of(context).colorScheme.outline,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ],
                  ),
                ],

                // Calendar integration indicator
                if (community.hasCalendarIntegration) ...[
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(
                        Icons.sync,
                        size: 16,
                        color: Colors.green,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        'Calendar sync enabled',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Colors.green,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildFallbackLogo(Color color) {
    return Center(
      child: Text(
        community.name.isNotEmpty ? community.name[0].toUpperCase() : 'C',
        style: TextStyle(
          color: color,
          fontWeight: FontWeight.bold,
          fontSize: isCompact ? 16 : 20,
        ),
      ),
    );
  }

  Color _getCommunityColor() {
    try {
      return Color(int.parse(community.color.replaceFirst('#', '0xFF')));
    } catch (e) {
      return const Color(0xFF3B82F6); // Default blue color
    }
  }
}