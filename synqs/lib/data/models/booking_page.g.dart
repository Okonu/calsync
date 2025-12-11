// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'booking_page.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

BookingPage _$BookingPageFromJson(Map<String, dynamic> json) => BookingPage(
      id: (json['id'] as num).toInt(),
      title: json['title'] as String,
      description: json['description'] as String?,
      duration: (json['duration'] as num).toInt(),
      slug: json['slug'] as String,
      isActive: json['is_active'] as bool? ?? true,
      availableDays: (json['available_days'] as List<dynamic>?)
              ?.map((e) => (e as num).toInt())
              .toList() ??
          const [1, 2, 3, 4, 5],
      startTime: json['start_time'] as String? ?? '09:00',
      endTime: json['end_time'] as String? ?? '17:00',
      bufferTime: (json['buffer_time'] as num?)?.toInt(),
      maxDaysInAdvance: (json['max_days_in_advance'] as num?)?.toInt(),
      userId: (json['user_id'] as num).toInt(),
      calendarId: (json['calendar_id'] as num?)?.toInt(),
      user: json['user'] == null
          ? null
          : User.fromJson(json['user'] as Map<String, dynamic>),
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

Map<String, dynamic> _$BookingPageToJson(BookingPage instance) =>
    <String, dynamic>{
      'id': instance.id,
      'title': instance.title,
      'description': instance.description,
      'duration': instance.duration,
      'slug': instance.slug,
      'is_active': instance.isActive,
      'available_days': instance.availableDays,
      'start_time': instance.startTime,
      'end_time': instance.endTime,
      'buffer_time': instance.bufferTime,
      'max_days_in_advance': instance.maxDaysInAdvance,
      'user_id': instance.userId,
      'calendar_id': instance.calendarId,
      'user': instance.user,
      'calendar': instance.calendar,
      'created_at': instance.createdAt?.toIso8601String(),
      'updated_at': instance.updatedAt?.toIso8601String(),
    };
