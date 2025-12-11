import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../../data/models/booking.dart';

class BookingTile extends StatelessWidget {
  final Booking booking;
  final VoidCallback? onTap;
  final VoidCallback? onCancel;
  final VoidCallback? onReschedule;
  final bool showDate;
  final bool isCompact;

  const BookingTile({
    super.key,
    required this.booking,
    this.onTap,
    this.onCancel,
    this.onReschedule,
    this.showDate = true,
    this.isCompact = false,
  });

  @override
  Widget build(BuildContext context) {
    final statusColor = _getStatusColor(context, booking.status);
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
                color: statusColor,
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
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          booking.name,
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                            fontWeight: FontWeight.w600,
                          ),
                          maxLines: isCompact ? 1 : 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          booking.email,
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: Theme.of(context).colorScheme.outline,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),

                  // Status badge
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: statusColor.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: statusColor.withOpacity(0.3),
                      ),
                    ),
                    child: Text(
                      booking.status.toUpperCase(),
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: statusColor,
                        fontWeight: FontWeight.w600,
                        fontSize: 10,
                      ),
                    ),
                  ),
                ],
              ),

              if (!isCompact) ...[
                const SizedBox(height: 12),

                // Booking page title
                if (booking.bookingPage != null) ...[
                  Row(
                    children: [
                      Icon(
                        Icons.event_note,
                        size: 16,
                        color: Theme.of(context).colorScheme.outline,
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          booking.bookingPage!.title,
                          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            fontWeight: FontWeight.w500,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                ],

                // Date and time
                Row(
                  children: [
                    Icon(
                      Icons.access_time,
                      size: 16,
                      color: Theme.of(context).colorScheme.outline,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      '${timeFormat.format(booking.startsAt)} - ${timeFormat.format(booking.endsAt)}',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Theme.of(context).colorScheme.outline,
                      ),
                    ),
                    if (showDate) ...[
                      const SizedBox(width: 12),
                      Icon(
                        Icons.calendar_today,
                        size: 16,
                        color: Theme.of(context).colorScheme.outline,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        dateFormat.format(booking.startsAt),
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Theme.of(context).colorScheme.outline,
                        ),
                      ),
                    ],
                  ],
                ),

                // Duration
                const SizedBox(height: 4),
                Row(
                  children: [
                    Icon(
                      Icons.schedule,
                      size: 16,
                      color: Theme.of(context).colorScheme.outline,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      '${booking.duration.inMinutes} minutes',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Theme.of(context).colorScheme.outline,
                      ),
                    ),
                  ],
                ),

                // Meeting link
                if (booking.meetingLink != null) ...[
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(
                        Icons.video_call,
                        size: 16,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          'Meeting link available',
                          style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: Theme.of(context).colorScheme.primary,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],

                // Notes
                if (booking.notes?.isNotEmpty == true) ...[
                  const SizedBox(height: 8),
                  Text(
                    booking.notes!,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      color: Theme.of(context).colorScheme.onSurface.withOpacity(0.8),
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],

                // Action buttons for confirmed bookings
                if (booking.isConfirmed && (onCancel != null || onReschedule != null)) ...[
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      if (onReschedule != null) ...[
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: onReschedule,
                            icon: const Icon(Icons.schedule, size: 16),
                            label: const Text('Reschedule'),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 8),
                            ),
                          ),
                        ),
                        if (onCancel != null) const SizedBox(width: 8),
                      ],
                      if (onCancel != null)
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: onCancel,
                            icon: const Icon(Icons.cancel, size: 16),
                            label: const Text('Cancel'),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: Theme.of(context).colorScheme.error,
                              side: BorderSide(color: Theme.of(context).colorScheme.error),
                              padding: const EdgeInsets.symmetric(vertical: 8),
                            ),
                          ),
                        ),
                    ],
                  ),
                ],
              ] else ...[
                // Compact mode - show time and status only
                const SizedBox(height: 4),
                Row(
                  children: [
                    Text(
                      '${timeFormat.format(booking.startsAt)} - ${timeFormat.format(booking.endsAt)}',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Theme.of(context).colorScheme.outline,
                      ),
                    ),
                    if (showDate) ...[
                      const SizedBox(width: 8),
                      Text(
                        dateFormat.format(booking.startsAt),
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: Theme.of(context).colorScheme.outline,
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Color _getStatusColor(BuildContext context, String status) {
    switch (status.toLowerCase()) {
      case 'confirmed':
        return Colors.green;
      case 'pending':
        return Colors.orange;
      case 'cancelled':
        return Theme.of(context).colorScheme.error;
      default:
        return Theme.of(context).colorScheme.outline;
    }
  }
}