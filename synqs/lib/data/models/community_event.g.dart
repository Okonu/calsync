// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'community_event.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

CommunityEvent _$CommunityEventFromJson(Map<String, dynamic> json) =>
    CommunityEvent(
      id: (json['id'] as num).toInt(),
      title: json['title'] as String,
      slug: json['slug'] as String,
      description: json['description'] as String?,
      type: json['type'] as String,
      startsAt: DateTime.parse(json['starts_at'] as String),
      endsAt: DateTime.parse(json['ends_at'] as String),
      location: json['location'] as String?,
      meetingLink: json['meeting_link'] as String?,
      meetingPlatform: json['meeting_platform'] as String?,
      googleCalendarEventId: json['google_calendar_event_id'] as String?,
      googleCalendarId: json['google_calendar_id'] as String?,
      isOnline: json['is_online'] as bool? ?? false,
      isRecurring: json['is_recurring'] as bool? ?? false,
      recurrenceSettings: json['recurrence_settings'] as Map<String, dynamic>?,
      maxAttendees: (json['max_attendees'] as num?)?.toInt(),
      requiresApproval: json['requires_approval'] as bool? ?? false,
      status: json['status'] as String? ?? 'draft',
      isPublic: json['is_public'] as bool? ?? true,
      speakerRequirements: json['speaker_requirements'] as String?,
      customFields: json['custom_fields'] as Map<String, dynamic>?,
      communityId: (json['community_id'] as num).toInt(),
      callForSpeakersId: (json['call_for_speakers_id'] as num?)?.toInt(),
      community: json['community'] == null
          ? null
          : Community.fromJson(json['community'] as Map<String, dynamic>),
      sessionsCount: (json['sessions_count'] as num?)?.toInt(),
      speakersCount: (json['speakers_count'] as num?)?.toInt(),
      totalSpeakers: (json['total_speakers'] as num?)?.toInt(),
      availableSessions: (json['available_sessions'] as num?)?.toInt(),
      meetingPlatformName: json['meeting_platform_name'] as String?,
      publicUrl: json['public_url'] as String?,
      createdAt: json['created_at'] == null
          ? null
          : DateTime.parse(json['created_at'] as String),
      updatedAt: json['updated_at'] == null
          ? null
          : DateTime.parse(json['updated_at'] as String),
    );

Map<String, dynamic> _$CommunityEventToJson(CommunityEvent instance) =>
    <String, dynamic>{
      'id': instance.id,
      'title': instance.title,
      'slug': instance.slug,
      'description': instance.description,
      'type': instance.type,
      'starts_at': instance.startsAt.toIso8601String(),
      'ends_at': instance.endsAt.toIso8601String(),
      'location': instance.location,
      'meeting_link': instance.meetingLink,
      'meeting_platform': instance.meetingPlatform,
      'google_calendar_event_id': instance.googleCalendarEventId,
      'google_calendar_id': instance.googleCalendarId,
      'is_online': instance.isOnline,
      'is_recurring': instance.isRecurring,
      'recurrence_settings': instance.recurrenceSettings,
      'max_attendees': instance.maxAttendees,
      'requires_approval': instance.requiresApproval,
      'status': instance.status,
      'is_public': instance.isPublic,
      'speaker_requirements': instance.speakerRequirements,
      'custom_fields': instance.customFields,
      'community_id': instance.communityId,
      'call_for_speakers_id': instance.callForSpeakersId,
      'community': instance.community,
      'sessions_count': instance.sessionsCount,
      'speakers_count': instance.speakersCount,
      'total_speakers': instance.totalSpeakers,
      'available_sessions': instance.availableSessions,
      'meeting_platform_name': instance.meetingPlatformName,
      'public_url': instance.publicUrl,
      'created_at': instance.createdAt?.toIso8601String(),
      'updated_at': instance.updatedAt?.toIso8601String(),
    };
