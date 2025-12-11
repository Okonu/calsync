import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../../data/models/calendar_event.dart';

class CalendarEventTile extends StatelessWidget {
  final CalendarEvent event;
  final VoidCallback? onTap;
  final bool showDate;
  final bool isCompact;

  const CalendarEventTile({
    super.key,
    required this.event,
    this.onTap,
    this.showDate = false,
    this.isCompact = false,
  });

  @override
  Widget build(BuildContext context) {
    final eventColor = _getEventColor(context);
    final timeFormat = DateFormat.jm();
    final dateFormat = DateFormat.MMMd();

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
                color: eventColor,
                width: 4,
              ),
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Event title and time
              Row(
                children: [
                  Expanded(
                    child: Text(
                      event.title,
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                      maxLines: isCompact ? 1 : 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  if (!isCompact) ...[
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: eventColor.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: eventColor.withOpacity(0.3),
                        ),
                      ),
                      child: Text(
                        event.allDay
                            ? 'All Day'
                            : '${timeFormat.format(event.startsAt)} - ${timeFormat.format(event.endsAt)}',
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: eventColor,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  ],
                ],
              ),

              if (!isCompact) ...[
                // Date (if showDate is true)
                if (showDate) ...[
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(
                        Icons.calendar_today,
                        size: 16,
                        color: Theme.of(context).colorScheme.outline,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        dateFormat.format(event.startsAt),
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Theme.of(context).colorScheme.outline,
                        ),
                      ),
                    ],
                  ),
                ],

                // Description
                if (event.description?.isNotEmpty == true) ...[
                  const SizedBox(height: 8),
                  Text(
                    event.description!,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: Theme.of(context).colorScheme.onSurface.withOpacity(0.8),
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],

                // Location
                if (event.location?.isNotEmpty == true) ...[
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(
                        Icons.location_on,
                        size: 16,
                        color: Theme.of(context).colorScheme.outline,
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          event.location!,
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: Theme.of(context).colorScheme.outline,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ],

                // Calendar info
                if (event.calendar != null) ...[
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Container(
                        width: 12,
                        height: 12,
                        decoration: BoxDecoration(
                          color: eventColor,
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          event.calendar!.name,
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: Theme.of(context).colorScheme.outline,
                            fontWeight: FontWeight.w500,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ],
              ] else ...[
                // Compact mode - show time only
                const SizedBox(height: 4),
                Text(
                  event.allDay
                      ? 'All Day'
                      : '${timeFormat.format(event.startsAt)} - ${timeFormat.format(event.endsAt)}',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Theme.of(context).colorScheme.outline,
                  ),
                ),
              ],

              // Status indicator
              if (event.status != 'confirmed') ...[
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 6,
                    vertical: 2,
                  ),
                  decoration: BoxDecoration(
                    color: _getStatusColor(context, event.status).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: _getStatusColor(context, event.status).withOpacity(0.3),
                    ),
                  ),
                  child: Text(
                    event.status.toUpperCase(),
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: _getStatusColor(context, event.status),
                      fontWeight: FontWeight.w600,
                      fontSize: 10,
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Color _getEventColor(BuildContext context) {
    // Try event-specific color first
    if (event.color != null) {
      try {
        return Color(int.parse(event.color!.replaceFirst('#', '0xFF')));
      } catch (e) {
        // Fall back to calendar color
      }
    }

    // Try calendar color
    if (event.calendar?.color != null) {
      try {
        return Color(int.parse(event.calendar!.color.replaceFirst('#', '0xFF')));
      } catch (e) {
        // Fall back to theme color
      }
    }

    // Default to theme primary color
    return Theme.of(context).colorScheme.primary;
  }

  Color _getStatusColor(BuildContext context, String status) {
    switch (status.toLowerCase()) {
      case 'confirmed':
        return Theme.of(context).colorScheme.primary;
      case 'tentative':
        return Colors.orange;
      case 'cancelled':
        return Theme.of(context).colorScheme.error;
      default:
        return Theme.of(context).colorScheme.outline;
    }
  }
}