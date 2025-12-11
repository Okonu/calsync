// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'time_slot.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

TimeSlot _$TimeSlotFromJson(Map<String, dynamic> json) => TimeSlot(
      startTime: json['start_time'] as String,
      endTime: json['end_time'] as String,
      startDateTime: DateTime.parse(json['start_datetime'] as String),
      endDateTime: DateTime.parse(json['end_datetime'] as String),
      isAvailable: json['is_available'] as bool? ?? true,
    );

Map<String, dynamic> _$TimeSlotToJson(TimeSlot instance) => <String, dynamic>{
      'start_time': instance.startTime,
      'end_time': instance.endTime,
      'start_datetime': instance.startDateTime.toIso8601String(),
      'end_datetime': instance.endDateTime.toIso8601String(),
      'is_available': instance.isAvailable,
    };
