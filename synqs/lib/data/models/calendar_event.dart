import 'package:json_annotation/json_annotation.dart';
import 'calendar.dart';

part 'calendar_event.g.dart';

@JsonSerializable()
class CalendarEvent {
  final int id;
  final String title;
  final String? description;
  final String? location;
  @JsonKey(name: 'starts_at')
  final DateTime startsAt;
  @JsonKey(name: 'ends_at')
  final DateTime endsAt;
  @JsonKey(name: 'all_day')
  final bool allDay;
  final String status;
  final String? color;
  @JsonKey(name: 'google_id')
  final String? googleId;
  final List<dynamic>? attendees;
  final List<dynamic>? recurrence;
  @JsonKey(name: 'calendar_id')
  final int calendarId;
  final Calendar? calendar;

  const CalendarEvent({
    required this.id,
    required this.title,
    this.description,
    this.location,
    required this.startsAt,
    required this.endsAt,
    this.allDay = false,
    this.status = 'confirmed',
    this.color,
    this.googleId,
    this.attendees,
    this.recurrence,
    required this.calendarId,
    this.calendar,
  });

  factory CalendarEvent.fromJson(Map<String, dynamic> json) =>
      _$CalendarEventFromJson(json);

  Map<String, dynamic> toJson() => _$CalendarEventToJson(this);

  CalendarEvent copyWith({
    int? id,
    String? title,
    String? description,
    String? location,
    DateTime? startsAt,
    DateTime? endsAt,
    bool? allDay,
    String? status,
    String? color,
    String? googleId,
    List<dynamic>? attendees,
    List<dynamic>? recurrence,
    int? calendarId,
    Calendar? calendar,
  }) {
    return CalendarEvent(
      id: id ?? this.id,
      title: title ?? this.title,
      description: description ?? this.description,
      location: location ?? this.location,
      startsAt: startsAt ?? this.startsAt,
      endsAt: endsAt ?? this.endsAt,
      allDay: allDay ?? this.allDay,
      status: status ?? this.status,
      color: color ?? this.color,
      googleId: googleId ?? this.googleId,
      attendees: attendees ?? this.attendees,
      recurrence: recurrence ?? this.recurrence,
      calendarId: calendarId ?? this.calendarId,
      calendar: calendar ?? this.calendar,
    );
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is CalendarEvent && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;

  @override
  String toString() {
    return 'CalendarEvent(id: $id, title: $title, startsAt: $startsAt)';
  }
}