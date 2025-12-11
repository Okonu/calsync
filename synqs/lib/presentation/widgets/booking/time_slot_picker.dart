import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class TimeSlotPicker extends StatelessWidget {
  final List<DateTime> availableSlots;
  final DateTime? selectedSlot;
  final ValueChanged<DateTime> onSlotSelected;
  final bool isLoading;
  final String? errorMessage;
  final VoidCallback? onRetry;

  const TimeSlotPicker({
    super.key,
    required this.availableSlots,
    this.selectedSlot,
    required this.onSlotSelected,
    this.isLoading = false,
    this.errorMessage,
    this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(32),
          child: CircularProgressIndicator(),
        ),
      );
    }

    if (errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.error_outline,
                size: 48,
                color: Theme.of(context).colorScheme.error,
              ),
              const SizedBox(height: 16),
              Text(
                'Failed to load time slots',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 8),
              Text(
                errorMessage!,
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: Theme.of(context).colorScheme.outline,
                ),
                textAlign: TextAlign.center,
              ),
              if (onRetry != null) ...[
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: onRetry,
                  child: const Text('Retry'),
                ),
              ],
            ],
          ),
        ),
      );
    }

    if (availableSlots.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.event_busy,
                size: 64,
                color: Theme.of(context).colorScheme.outline,
              ),
              const SizedBox(height: 16),
              Text(
                'No time slots available',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                  color: Theme.of(context).colorScheme.outline,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Please select a different date',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  color: Theme.of(context).colorScheme.outline,
                ),
              ),
            ],
          ),
        ),
      );
    }

    // Group slots by hour for better organization
    final groupedSlots = _groupSlotsByHour(availableSlots);
    final timeFormat = DateFormat.jm();

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Available Times',
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 12),
          Expanded(
            child: ListView.builder(
              itemCount: groupedSlots.length,
              itemBuilder: (context, index) {
                final hourGroup = groupedSlots[index];
                final hourLabel = hourGroup.keys.first;
                final slots = hourGroup.values.first;

                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (index > 0) const SizedBox(height: 16),
                    Padding(
                      padding: const EdgeInsets.only(left: 4, bottom: 8),
                      child: Text(
                        hourLabel,
                        style: Theme.of(context).textTheme.labelLarge?.copyWith(
                          color: Theme.of(context).colorScheme.outline,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: slots.map((slot) {
                        final isSelected = selectedSlot == slot;

                        return FilterChip(
                          selected: isSelected,
                          onSelected: (_) => onSlotSelected(slot),
                          label: Text(
                            timeFormat.format(slot),
                            style: TextStyle(
                              fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                            ),
                          ),
                          selectedColor: Theme.of(context).colorScheme.primary.withOpacity(0.2),
                          checkmarkColor: Theme.of(context).colorScheme.primary,
                          side: BorderSide(
                            color: isSelected
                                ? Theme.of(context).colorScheme.primary
                                : Theme.of(context).colorScheme.outline.withOpacity(0.3),
                          ),
                        );
                      }).toList(),
                    ),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  List<Map<String, List<DateTime>>> _groupSlotsByHour(List<DateTime> slots) {
    final Map<String, List<DateTime>> grouped = {};

    for (final slot in slots) {
      final hourKey = DateFormat.j().format(slot); // e.g., "9 AM", "2 PM"
      grouped.putIfAbsent(hourKey, () => []).add(slot);
    }

    // Convert to list of maps for easier ListView building
    return grouped.entries.map((entry) => {entry.key: entry.value}).toList();
  }
}