// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'calendar.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Calendar _$CalendarFromJson(Map<String, dynamic> json) => Calendar(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String,
      color: json['color'] as String,
      isPrimary: json['is_primary'] as bool? ?? false,
      isVisible: json['is_visible'] as bool? ?? true,
      googleId: json['google_id'] as String,
      googleAccountId: (json['google_account_id'] as num).toInt(),
      googleAccount: json['google_account'] == null
          ? null
          : GoogleAccount.fromJson(
              json['google_account'] as Map<String, dynamic>),
      createdAt: json['created_at'] == null
          ? null
          : DateTime.parse(json['created_at'] as String),
      updatedAt: json['updated_at'] == null
          ? null
          : DateTime.parse(json['updated_at'] as String),
    );

Map<String, dynamic> _$CalendarToJson(Calendar instance) => <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'color': instance.color,
      'is_primary': instance.isPrimary,
      'is_visible': instance.isVisible,
      'google_id': instance.googleId,
      'google_account_id': instance.googleAccountId,
      'google_account': instance.googleAccount,
      'created_at': instance.createdAt?.toIso8601String(),
      'updated_at': instance.updatedAt?.toIso8601String(),
    };
