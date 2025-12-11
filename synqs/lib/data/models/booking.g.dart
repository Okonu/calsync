// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'booking.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Booking _$BookingFromJson(Map<String, dynamic> json) => Booking(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String,
      email: json['email'] as String,
      startsAt: DateTime.parse(json['starts_at'] as String),
      endsAt: DateTime.parse(json['ends_at'] as String),
      notes: json['notes'] as String?,
      status: json['status'] as String? ?? 'confirmed',
      uid: json['uid'] as String,
      googleEventId: json['google_event_id'] as String?,
      meetingLink: json['meeting_link'] as String?,
      bookingPageId: (json['booking_page_id'] as num).toInt(),
      calendarId: (json['calendar_id'] as num?)?.toInt(),
      bookingPage: json['booking_page'] == null
          ? null
          : BookingPage.fromJson(json['booking_page'] as Map<String, dynamic>),
      calendar: json['calendar'] == null
          ? null
          : Calendar.fromJson(json['calendar'] as Map<String, dynamic>),
      createdAt: json['created_at'] == null
          ? null
          : DateTime.parse(json['created_at'] as String),
      updatedAt: json['updated_at'] == null
          ? null
          : DateTime.parse(json['updated_at'] as String),
    );

Map<String, dynamic> _$BookingToJson(Booking instance) => <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'email': instance.email,
      'starts_at': instance.startsAt.toIso8601String(),
      'ends_at': instance.endsAt.toIso8601String(),
      'notes': instance.notes,
      'status': instance.status,
      'uid': instance.uid,
      'google_event_id': instance.googleEventId,
      'meeting_link': instance.meetingLink,
      'booking_page_id': instance.bookingPageId,
      'calendar_id': instance.calendarId,
      'booking_page': instance.bookingPage,
      'calendar': instance.calendar,
      'created_at': instance.createdAt?.toIso8601String(),
      'updated_at': instance.updatedAt?.toIso8601String(),
    };
