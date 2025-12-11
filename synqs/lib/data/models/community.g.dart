// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'community.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Community _$CommunityFromJson(Map<String, dynamic> json) => Community(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String,
      slug: json['slug'] as String,
      description: json['description'] as String?,
      logo: json['logo'] as String?,
      website: json['website'] as String?,
      contactEmail: json['contact_email'] as String?,
      calendarEmail: json['calendar_email'] as String?,
      destinationCalendarId: (json['destination_calendar_id'] as num?)?.toInt(),
      googleAccountId: (json['google_account_id'] as num?)?.toInt(),
      socialLinks: json['social_links'] as Map<String, dynamic>?,
      timezone: json['timezone'] as String?,
      color: json['color'] as String? ?? '#3B82F6',
      isPublic: json['is_public'] as bool? ?? true,
      isActive: json['is_active'] as bool? ?? true,
      userId: (json['user_id'] as num).toInt(),
      user: json['user'] == null
          ? null
          : User.fromJson(json['user'] as Map<String, dynamic>),
      googleAccount: json['google_account'] == null
          ? null
          : GoogleAccount.fromJson(
              json['google_account'] as Map<String, dynamic>),
      destinationCalendar: json['destination_calendar'] == null
          ? null
          : Calendar.fromJson(
              json['destination_calendar'] as Map<String, dynamic>),
      eventsCount: (json['events_count'] as num?)?.toInt(),
      callsForSpeakersCount:
          (json['calls_for_speakers_count'] as num?)?.toInt(),
      logoUrl: json['logo_url'] as String?,
      publicUrl: json['public_url'] as String?,
      createdAt: json['created_at'] == null
          ? null
          : DateTime.parse(json['created_at'] as String),
      updatedAt: json['updated_at'] == null
          ? null
          : DateTime.parse(json['updated_at'] as String),
    );

Map<String, dynamic> _$CommunityToJson(Community instance) => <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'slug': instance.slug,
      'description': instance.description,
      'logo': instance.logo,
      'website': instance.website,
      'contact_email': instance.contactEmail,
      'calendar_email': instance.calendarEmail,
      'destination_calendar_id': instance.destinationCalendarId,
      'google_account_id': instance.googleAccountId,
      'social_links': instance.socialLinks,
      'timezone': instance.timezone,
      'color': instance.color,
      'is_public': instance.isPublic,
      'is_active': instance.isActive,
      'user_id': instance.userId,
      'user': instance.user,
      'google_account': instance.googleAccount,
      'destination_calendar': instance.destinationCalendar,
      'events_count': instance.eventsCount,
      'calls_for_speakers_count': instance.callsForSpeakersCount,
      'logo_url': instance.logoUrl,
      'public_url': instance.publicUrl,
      'created_at': instance.createdAt?.toIso8601String(),
      'updated_at': instance.updatedAt?.toIso8601String(),
    };
