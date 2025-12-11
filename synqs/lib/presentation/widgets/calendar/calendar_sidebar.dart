import 'package:flutter/material.dart';
import '../../../data/models/calendar.dart';
import '../../../data/models/google_account.dart';

class CalendarSidebar extends StatelessWidget {
  final List<Calendar> calendars;
  final Set<int> visibleCalendars;
  final ValueChanged<int> onCalendarToggle;
  final ValueChanged<int>? onSyncAccount;

  const CalendarSidebar({
    super.key,
    required this.calendars,
    required this.visibleCalendars,
    required this.onCalendarToggle,
    this.onSyncAccount,
  });

  @override
  Widget build(BuildContext context) {
    // Group calendars by account
    final Map<GoogleAccount?, List<Calendar>> calendarsByAccount = {};

    for (final calendar in calendars) {
      final account = calendar.googleAccount;
      calendarsByAccount.putIfAbsent(account, () => []).add(calendar);
    }

    return Container(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Row(
            children: [
              Icon(
                Icons.calendar_view_month,
                color: Theme.of(context).colorScheme.primary,
              ),
              const SizedBox(width: 8),
              Text(
                'Calendars',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: Theme.of(context).colorScheme.primary,
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Calendars list
          Expanded(
            child: ListView(
              children: [
                // Show/Hide all controls
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    TextButton.icon(
                      onPressed: () {
                        for (final calendar in calendars) {
                          if (!visibleCalendars.contains(calendar.id)) {
                            onCalendarToggle(calendar.id);
                          }
                        }
                      },
                      icon: const Icon(Icons.visibility, size: 16),
                      label: const Text('Show All'),
                      style: TextButton.styleFrom(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      ),
                    ),
                    TextButton.icon(
                      onPressed: () {
                        for (final calendar in calendars) {
                          if (visibleCalendars.contains(calendar.id)) {
                            onCalendarToggle(calendar.id);
                          }
                        }
                      },
                      icon: const Icon(Icons.visibility_off, size: 16),
                      label: const Text('Hide All'),
                      style: TextButton.styleFrom(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 8),
                const Divider(),
                const SizedBox(height: 8),

                // Calendars grouped by account
                ...calendarsByAccount.entries.map((entry) {
                  final account = entry.key;
                  final accountCalendars = entry.value;

                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Account header
                      if (account != null) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          child: Row(
                            children: [
                              CircleAvatar(
                                radius: 12,
                                backgroundImage: account.picture != null
                                    ? NetworkImage(account.picture!)
                                    : null,
                                child: account.picture == null
                                    ? Text(
                                        account.email[0].toUpperCase(),
                                        style: const TextStyle(fontSize: 12),
                                      )
                                    : null,
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      account.name ?? account.email,
                                      style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                        fontWeight: FontWeight.w600,
                                      ),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                    Text(
                                      account.email,
                                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                        color: Theme.of(context).colorScheme.outline,
                                      ),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ],
                                ),
                              ),
                              if (onSyncAccount != null)
                                IconButton(
                                  onPressed: () => onSyncAccount!(account.id),
                                  icon: const Icon(Icons.sync, size: 18),
                                  tooltip: 'Sync calendars',
                                  padding: EdgeInsets.zero,
                                  constraints: const BoxConstraints(
                                    minWidth: 32,
                                    minHeight: 32,
                                  ),
                                ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 4),
                      ],

                      // Account calendars
                      ...accountCalendars.map((calendar) => _buildCalendarTile(
                        context,
                        calendar,
                        visibleCalendars.contains(calendar.id),
                        () => onCalendarToggle(calendar.id),
                      )),

                      if (account != null) const SizedBox(height: 16),
                    ],
                  );
                }),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCalendarTile(
    BuildContext context,
    Calendar calendar,
    bool isVisible,
    VoidCallback onToggle,
  ) {
    final calendarColor = _getCalendarColor(calendar, context);

    return InkWell(
      onTap: onToggle,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
        margin: const EdgeInsets.only(bottom: 4),
        child: Row(
          children: [
            // Color indicator and checkbox
            Stack(
              alignment: Alignment.center,
              children: [
                Container(
                  width: 20,
                  height: 20,
                  decoration: BoxDecoration(
                    color: isVisible ? calendarColor : Colors.transparent,
                    border: Border.all(
                      color: calendarColor,
                      width: 2,
                    ),
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
                if (isVisible)
                  Icon(
                    Icons.check,
                    size: 14,
                    color: _getContrastColor(calendarColor),
                  ),
              ],
            ),

            const SizedBox(width: 12),

            // Calendar name
            Expanded(
              child: Text(
                calendar.name,
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  fontWeight: isVisible ? FontWeight.w500 : FontWeight.normal,
                  color: isVisible
                      ? Theme.of(context).colorScheme.onSurface
                      : Theme.of(context).colorScheme.outline,
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ),

            // Primary indicator
            if (calendar.isPrimary)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.primary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: Theme.of(context).colorScheme.primary.withOpacity(0.3),
                  ),
                ),
                child: Text(
                  'Primary',
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Theme.of(context).colorScheme.primary,
                    fontWeight: FontWeight.w500,
                    fontSize: 10,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Color _getCalendarColor(Calendar calendar, BuildContext context) {
    if (calendar.color.isNotEmpty) {
      try {
        return Color(int.parse(calendar.color.replaceFirst('#', '0xFF')));
      } catch (e) {
        // Fall back to default color
      }
    }
    return Theme.of(context).colorScheme.primary;
  }

  Color _getContrastColor(Color backgroundColor) {
    // Calculate luminance to determine if we should use black or white text
    final luminance = backgroundColor.computeLuminance();
    return luminance > 0.5 ? Colors.black : Colors.white;
  }
}