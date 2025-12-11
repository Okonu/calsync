// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'google_account.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

GoogleAccount _$GoogleAccountFromJson(Map<String, dynamic> json) =>
    GoogleAccount(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String,
      email: json['email'] as String,
      avatar: json['avatar'] as String?,
      color: json['color'] as String,
      isPrimary: json['is_primary'] as bool? ?? false,
      isActive: json['is_active'] as bool? ?? true,
      userId: (json['user_id'] as num).toInt(),
      tokenExpiresAt: json['token_expires_at'] == null
          ? null
          : DateTime.parse(json['token_expires_at'] as String),
      calendars: (json['calendars'] as List<dynamic>?)
          ?.map((e) => Calendar.fromJson(e as Map<String, dynamic>))
          .toList(),
      calendarsCount: (json['calendars_count'] as num?)?.toInt(),
      createdAt: json['created_at'] == null
          ? null
          : DateTime.parse(json['created_at'] as String),
      updatedAt: json['updated_at'] == null
          ? null
          : DateTime.parse(json['updated_at'] as String),
    );

Map<String, dynamic> _$GoogleAccountToJson(GoogleAccount instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'email': instance.email,
      'avatar': instance.avatar,
      'color': instance.color,
      'is_primary': instance.isPrimary,
      'is_active': instance.isActive,
      'user_id': instance.userId,
      'token_expires_at': instance.tokenExpiresAt?.toIso8601String(),
      'calendars': instance.calendars,
      'calendars_count': instance.calendarsCount,
      'created_at': instance.createdAt?.toIso8601String(),
      'updated_at': instance.updatedAt?.toIso8601String(),
    };
