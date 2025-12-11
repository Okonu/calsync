// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'calendar_event.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

CalendarEvent _$CalendarEventFromJson(Map<String, dynamic> json) =>
    CalendarEvent(
      id: (json['id'] as num).toInt(),
      title: json['title'] as String,
      description: json['description'] as String?,
      location: json['location'] as String?,
      startsAt: DateTime.parse(json['starts_at'] as String),
      endsAt: DateTime.parse(json['ends_at'] as String),
      allDay: json['all_day'] as bool? ?? false,
      status: json['status'] as String? ?? 'confirmed',
      color: json['color'] as String?,
      googleId: json['google_id'] as String?,
      attendees: json['attendees'] as List<dynamic>?,
      recurrence: json['recurrence'] as List<dynamic>?,
      calendarId: (json['calendar_id'] as num).toInt(),
      calendar: json['calendar'] == null
          ? null
          : Calendar.fromJson(json['calendar'] as Map<String, dynamic>),
    );

Map<String, dynamic> _$CalendarEventToJson(CalendarEvent instance) =>
    <String, dynamic>{
      'id': instance.id,
      'title': instance.title,
      'description': instance.description,
      'location': instance.location,
      'starts_at': instance.startsAt.toIso8601String(),
      'ends_at': instance.endsAt.toIso8601String(),
      'all_day': instance.allDay,
      'status': instance.status,
      'color': instance.color,
      'google_id': instance.googleId,
      'attendees': instance.attendees,
      'recurrence': instance.recurrence,
      'calendar_id': instance.calendarId,
      'calendar': instance.calendar,
    };
