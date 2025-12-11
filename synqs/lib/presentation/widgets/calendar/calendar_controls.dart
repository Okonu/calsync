import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class CalendarControls extends StatelessWidget {
  final DateTime selectedDate;
  final ValueChanged<DateTime> onDateChanged;
  final VoidCallback onPreviousMonth;
  final VoidCallback onNextMonth;

  const CalendarControls({
    super.key,
    required this.selectedDate,
    required this.onDateChanged,
    required this.onPreviousMonth,
    required this.onNextMonth,
  });

  @override
  Widget build(BuildContext context) {
    final monthFormat = DateFormat.yMMMM();

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        border: Border(
          bottom: BorderSide(
            color: Theme.of(context).dividerColor,
            width: 1,
          ),
        ),
      ),
      child: Row(
        children: [
          // Previous month button
          IconButton(
            onPressed: onPreviousMonth,
            icon: const Icon(Icons.chevron_left),
            tooltip: 'Previous month',
          ),

          const SizedBox(width: 8),

          // Current month/year display
          Expanded(
            child: InkWell(
              onTap: () => _showDatePicker(context),
              borderRadius: BorderRadius.circular(8),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
                child: Text(
                  monthFormat.format(selectedDate),
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
                  textAlign: TextAlign.center,
                ),
              ),
            ),
          ),

          const SizedBox(width: 8),

          // Next month button
          IconButton(
            onPressed: onNextMonth,
            icon: const Icon(Icons.chevron_right),
            tooltip: 'Next month',
          ),
        ],
      ),
    );
  }

  Future<void> _showDatePicker(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime(2030),
      helpText: 'Select month',
    );

    if (picked != null && picked != selectedDate) {
      onDateChanged(picked);
    }
  }
}