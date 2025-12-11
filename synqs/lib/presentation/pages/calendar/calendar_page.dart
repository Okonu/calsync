import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:table_calendar/table_calendar.dart';
import 'package:intl/intl.dart';
import '../../providers/calendar_provider.dart';
import '../../widgets/calendar/calendar_event_tile.dart';
import '../../widgets/calendar/calendar_controls.dart';
import '../../widgets/calendar/calendar_sidebar.dart';
import '../../../data/models/calendar_event.dart';

class CalendarPage extends ConsumerStatefulWidget {
  const CalendarPage({super.key});

  @override
  ConsumerState<CalendarPage> createState() => _CalendarPageState();
}

class _CalendarPageState extends ConsumerState<CalendarPage> {
  late DateTime _focusedDay;
  DateTime? _selectedDay;
  CalendarFormat _calendarFormat = CalendarFormat.month;

  @override
  void initState() {
    super.initState();
    _focusedDay = DateTime.now();
    _selectedDay = DateTime.now();
  }

  @override
  Widget build(BuildContext context) {
    final selectedDate = ref.watch(selectedDateProvider);
    final calendarViewMode = ref.watch(calendarViewModeProvider);
    final visibleCalendars = ref.watch(visibleCalendarsProvider);

    // Calculate date range for events based on view mode
    final (startDate, endDate) = _getDateRange(calendarViewMode, selectedDate);

    final eventsAsync = ref.watch(calendarEventsProvider(
      start: startDate,
      end: endDate,
      calendarIds: visibleCalendars.isEmpty ? null : visibleCalendars.toList(),
    ));

    final calendarsAsync = ref.watch(calendarListProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Calendar'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              ref.read(calendarEventsProvider(
                start: startDate,
                end: endDate,
                calendarIds: visibleCalendars.isEmpty ? null : visibleCalendars.toList(),
              ).notifier).refresh();
              ref.read(calendarListProvider.notifier).refresh();
            },
          ),
          IconButton(
            icon: const Icon(Icons.today),
            onPressed: () {
              ref.read(selectedDateProvider.notifier).setToday();
              setState(() {
                _focusedDay = DateTime.now();
                _selectedDay = DateTime.now();
              });
            },
          ),
          PopupMenuButton<CalendarView>(
            icon: const Icon(Icons.view_module),
            onSelected: (CalendarView view) {
              ref.read(calendarViewModeProvider.notifier).setViewMode(view);
              setState(() {
                _calendarFormat = _getCalendarFormat(view);
              });
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: CalendarView.month,
                child: Text('Month View'),
              ),
              const PopupMenuItem(
                value: CalendarView.week,
                child: Text('Week View'),
              ),
              const PopupMenuItem(
                value: CalendarView.day,
                child: Text('Day View'),
              ),
              const PopupMenuItem(
                value: CalendarView.agenda,
                child: Text('Agenda View'),
              ),
            ],
          ),
        ],
      ),
      body: Row(
        children: [
          // Calendar sidebar for desktop/tablet
          if (MediaQuery.of(context).size.width > 768)
            Container(
              width: 300,
              decoration: BoxDecoration(
                border: Border(
                  right: BorderSide(
                    color: Theme.of(context).dividerColor,
                    width: 1,
                  ),
                ),
              ),
              child: calendarsAsync.when(
                data: (calendars) => CalendarSidebar(
                  calendars: calendars,
                  visibleCalendars: visibleCalendars,
                  onCalendarToggle: (calendarId) {
                    ref.read(visibleCalendarsProvider.notifier).toggleCalendar(calendarId);
                  },
                  onSyncAccount: (accountId) async {
                    try {
                      await ref.read(googleAccountListProvider.notifier).syncAccount(accountId);
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Calendar synced successfully')),
                        );
                      }
                    } catch (e) {
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(content: Text('Sync failed: $e')),
                        );
                      }
                    }
                  },
                ),
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (error, _) => Center(child: Text('Error: $error')),
              ),
            ),

          // Main calendar content
          Expanded(
            child: Column(
              children: [
                // Calendar controls
                CalendarControls(
                  selectedDate: selectedDate,
                  onDateChanged: (date) {
                    ref.read(selectedDateProvider.notifier).setDate(date);
                    setState(() {
                      _focusedDay = date;
                      _selectedDay = date;
                    });
                  },
                  onPreviousMonth: () {
                    setState(() {
                      _focusedDay = DateTime(_focusedDay.year, _focusedDay.month - 1);
                    });
                  },
                  onNextMonth: () {
                    setState(() {
                      _focusedDay = DateTime(_focusedDay.year, _focusedDay.month + 1);
                    });
                  },
                ),

                // Calendar widget
                if (calendarViewMode != CalendarView.agenda) ...[
                  Container(
                    decoration: BoxDecoration(
                      border: Border(
                        bottom: BorderSide(
                          color: Theme.of(context).dividerColor,
                          width: 1,
                        ),
                      ),
                    ),
                    child: eventsAsync.when(
                      data: (events) => TableCalendar<CalendarEvent>(
                        firstDay: DateTime.utc(2020, 1, 1),
                        lastDay: DateTime.utc(2030, 12, 31),
                        focusedDay: _focusedDay,
                        selectedDayPredicate: (day) => isSameDay(_selectedDay, day),
                        calendarFormat: _calendarFormat,
                        eventLoader: (day) => _getEventsForDay(events, day),
                        startingDayOfWeek: StartingDayOfWeek.monday,
                        calendarStyle: CalendarStyle(
                          outsideDaysVisible: false,
                          weekendTextStyle: TextStyle(
                            color: Theme.of(context).colorScheme.error,
                          ),
                          markerDecoration: BoxDecoration(
                            color: Theme.of(context).colorScheme.primary,
                            shape: BoxShape.circle,
                          ),
                          markersMaxCount: 3,
                          markersAlignment: Alignment.bottomCenter,
                        ),
                        headerStyle: const HeaderStyle(
                          formatButtonVisible: false,
                          titleCentered: true,
                        ),
                        onDaySelected: (selectedDay, focusedDay) {
                          setState(() {
                            _selectedDay = selectedDay;
                            _focusedDay = focusedDay;
                          });
                          ref.read(selectedDateProvider.notifier).setDate(selectedDay);
                        },
                        onFormatChanged: (format) {
                          setState(() {
                            _calendarFormat = format;
                          });
                        },
                        onPageChanged: (focusedDay) {
                          setState(() {
                            _focusedDay = focusedDay;
                          });
                        },
                        calendarBuilders: CalendarBuilders(
                          markerBuilder: (context, date, events) {
                            if (events.isEmpty) return null;
                            return _buildEventMarkers(events);
                          },
                        ),
                      ),
                      loading: () => const SizedBox(
                        height: 300,
                        child: Center(child: CircularProgressIndicator()),
                      ),
                      error: (error, _) => SizedBox(
                        height: 300,
                        child: Center(child: Text('Error loading calendar: $error')),
                      ),
                    ),
                  ),
                ],

                // Events list
                Expanded(
                  child: eventsAsync.when(
                    data: (events) {
                      List<CalendarEvent> displayEvents;

                      if (calendarViewMode == CalendarView.agenda) {
                        // Show all events in agenda view
                        displayEvents = events
                          ..sort((a, b) => a.startsAt.compareTo(b.startsAt));
                      } else {
                        // Show events for selected day
                        displayEvents = _getEventsForDay(events, _selectedDay ?? DateTime.now());
                      }

                      if (displayEvents.isEmpty) {
                        return Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.event_busy,
                                size: 64,
                                color: Theme.of(context).colorScheme.outline,
                              ),
                              const SizedBox(height: 16),
                              Text(
                                calendarViewMode == CalendarView.agenda
                                    ? 'No events found'
                                    : 'No events for ${DateFormat.yMMMd().format(_selectedDay ?? DateTime.now())}',
                                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  color: Theme.of(context).colorScheme.outline,
                                ),
                              ),
                            ],
                          ),
                        );
                      }

                      return ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: displayEvents.length,
                        itemBuilder: (context, index) {
                          final event = displayEvents[index];
                          return CalendarEventTile(
                            event: event,
                            onTap: () => _showEventDetails(context, event),
                          );
                        },
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
                            'Error loading events',
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
                            onPressed: () => ref.refresh(calendarEventsProvider(
                              start: startDate,
                              end: endDate,
                              calendarIds: visibleCalendars.isEmpty ? null : visibleCalendars.toList(),
                            )),
                            child: const Text('Retry'),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () => _showCreateEventDialog(context),
        child: const Icon(Icons.add),
      ),
    );
  }

  (DateTime, DateTime) _getDateRange(CalendarView viewMode, DateTime selectedDate) {
    switch (viewMode) {
      case CalendarView.day:
        return (
          DateTime(selectedDate.year, selectedDate.month, selectedDate.day),
          DateTime(selectedDate.year, selectedDate.month, selectedDate.day, 23, 59, 59),
        );
      case CalendarView.week:
        final startOfWeek = selectedDate.subtract(Duration(days: selectedDate.weekday - 1));
        return (
          DateTime(startOfWeek.year, startOfWeek.month, startOfWeek.day),
          DateTime(startOfWeek.year, startOfWeek.month, startOfWeek.day + 6, 23, 59, 59),
        );
      case CalendarView.month:
        return (
          DateTime(selectedDate.year, selectedDate.month, 1),
          DateTime(selectedDate.year, selectedDate.month + 1, 0, 23, 59, 59),
        );
      case CalendarView.agenda:
        return (
          DateTime.now().subtract(const Duration(days: 30)),
          DateTime.now().add(const Duration(days: 90)),
        );
    }
  }

  CalendarFormat _getCalendarFormat(CalendarView viewMode) {
    switch (viewMode) {
      case CalendarView.month:
        return CalendarFormat.month;
      case CalendarView.week:
        return CalendarFormat.twoWeeks;
      default:
        return CalendarFormat.month;
    }
  }

  List<CalendarEvent> _getEventsForDay(List<CalendarEvent> events, DateTime day) {
    return events.where((event) => isSameDay(event.startsAt, day)).toList()
      ..sort((a, b) => a.startsAt.compareTo(b.startsAt));
  }

  Widget _buildEventMarkers(List<CalendarEvent> events) {
    final colors = events.take(3).map((e) => _getEventColor(e)).toList();

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: colors.map((color) => Container(
        width: 6,
        height: 6,
        margin: const EdgeInsets.symmetric(horizontal: 1),
        decoration: BoxDecoration(
          color: color,
          shape: BoxShape.circle,
        ),
      )).toList(),
    );
  }

  Color _getEventColor(CalendarEvent event) {
    if (event.color != null) {
      try {
        return Color(int.parse(event.color!.replaceFirst('#', '0xFF')));
      } catch (e) {
        // Fall back to calendar color or default
      }
    }

    if (event.calendar?.color != null) {
      try {
        return Color(int.parse(event.calendar!.color.replaceFirst('#', '0xFF')));
      } catch (e) {
        // Fall back to default
      }
    }

    return Theme.of(context).colorScheme.primary;
  }

  void _showEventDetails(BuildContext context, CalendarEvent event) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(event.title),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (event.description?.isNotEmpty == true) ...[
              Text(event.description!),
              const SizedBox(height: 8),
            ],
            Text('📅 ${DateFormat.yMMMEd().add_jm().format(event.startsAt)}'),
            if (!event.allDay)
              Text('⏰ ${DateFormat.jm().format(event.startsAt)} - ${DateFormat.jm().format(event.endsAt)}'),
            if (event.location?.isNotEmpty == true) ...[
              const SizedBox(height: 4),
              Text('📍 ${event.location}'),
            ],
            if (event.calendar != null) ...[
              const SizedBox(height: 4),
              Text('📚 ${event.calendar!.name}'),
            ],
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  void _showCreateEventDialog(BuildContext context) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Event creation feature coming soon'),
      ),
    );
  }
}