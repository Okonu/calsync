import 'package:json_annotation/json_annotation.dart';
import 'community.dart';

part 'community_event.g.dart';

@JsonSerializable()
class CommunityEvent {
  final int id;
  final String title;
  final String slug;
  final String? description;
  final String type;
  @JsonKey(name: 'starts_at')
  final DateTime startsAt;
  @JsonKey(name: 'ends_at')
  final DateTime endsAt;
  final String? location;
  @JsonKey(name: 'meeting_link')
  final String? meetingLink;
  @JsonKey(name: 'meeting_platform')
  final String? meetingPlatform;
  @JsonKey(name: 'google_calendar_event_id')
  final String? googleCalendarEventId;
  @JsonKey(name: 'google_calendar_id')
  final String? googleCalendarId;
  @JsonKey(name: 'is_online')
  final bool isOnline;
  @JsonKey(name: 'is_recurring')
  final bool isRecurring;
  @JsonKey(name: 'recurrence_settings')
  final Map<String, dynamic>? recurrenceSettings;
  @JsonKey(name: 'max_attendees')
  final int? maxAttendees;
  @JsonKey(name: 'requires_approval')
  final bool requiresApproval;
  final String status;
  @JsonKey(name: 'is_public')
  final bool isPublic;
  @JsonKey(name: 'speaker_requirements')
  final String? speakerRequirements;
  @JsonKey(name: 'custom_fields')
  final Map<String, dynamic>? customFields;
  @JsonKey(name: 'community_id')
  final int communityId;
  @JsonKey(name: 'call_for_speakers_id')
  final int? callForSpeakersId;
  final Community? community;
  @JsonKey(name: 'sessions_count')
  final int? sessionsCount;
  @JsonKey(name: 'speakers_count')
  final int? speakersCount;
  @JsonKey(name: 'total_speakers')
  final int? totalSpeakers;
  @JsonKey(name: 'available_sessions')
  final int? availableSessions;
  @JsonKey(name: 'meeting_platform_name')
  final String? meetingPlatformName;
  @JsonKey(name: 'public_url')
  final String? publicUrl;
  @JsonKey(name: 'created_at')
  final DateTime? createdAt;
  @JsonKey(name: 'updated_at')
  final DateTime? updatedAt;

  const CommunityEvent({
    required this.id,
    required this.title,
    required this.slug,
    this.description,
    required this.type,
    required this.startsAt,
    required this.endsAt,
    this.location,
    this.meetingLink,
    this.meetingPlatform,
    this.googleCalendarEventId,
    this.googleCalendarId,
    this.isOnline = false,
    this.isRecurring = false,
    this.recurrenceSettings,
    this.maxAttendees,
    this.requiresApproval = false,
    this.status = 'draft',
    this.isPublic = true,
    this.speakerRequirements,
    this.customFields,
    required this.communityId,
    this.callForSpeakersId,
    this.community,
    this.sessionsCount,
    this.speakersCount,
    this.totalSpeakers,
    this.availableSessions,
    this.meetingPlatformName,
    this.publicUrl,
    this.createdAt,
    this.updatedAt,
  });

  factory CommunityEvent.fromJson(Map<String, dynamic> json) =>
      _$CommunityEventFromJson(json);

  Map<String, dynamic> toJson() => _$CommunityEventToJson(this);

  CommunityEvent copyWith({
    int? id,
    String? title,
    String? slug,
    String? description,
    String? type,
    DateTime? startsAt,
    DateTime? endsAt,
    String? location,
    String? meetingLink,
    String? meetingPlatform,
    String? googleCalendarEventId,
    String? googleCalendarId,
    bool? isOnline,
    bool? isRecurring,
    Map<String, dynamic>? recurrenceSettings,
    int? maxAttendees,
    bool? requiresApproval,
    String? status,
    bool? isPublic,
    String? speakerRequirements,
    Map<String, dynamic>? customFields,
    int? communityId,
    int? callForSpeakersId,
    Community? community,
    int? sessionsCount,
    int? speakersCount,
    int? totalSpeakers,
    int? availableSessions,
    String? meetingPlatformName,
    String? publicUrl,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return CommunityEvent(
      id: id ?? this.id,
      title: title ?? this.title,
      slug: slug ?? this.slug,
      description: description ?? this.description,
      type: type ?? this.type,
      startsAt: startsAt ?? this.startsAt,
      endsAt: endsAt ?? this.endsAt,
      location: location ?? this.location,
      meetingLink: meetingLink ?? this.meetingLink,
      meetingPlatform: meetingPlatform ?? this.meetingPlatform,
      googleCalendarEventId: googleCalendarEventId ?? this.googleCalendarEventId,
      googleCalendarId: googleCalendarId ?? this.googleCalendarId,
      isOnline: isOnline ?? this.isOnline,
      isRecurring: isRecurring ?? this.isRecurring,
      recurrenceSettings: recurrenceSettings ?? this.recurrenceSettings,
      maxAttendees: maxAttendees ?? this.maxAttendees,
      requiresApproval: requiresApproval ?? this.requiresApproval,
      status: status ?? this.status,
      isPublic: isPublic ?? this.isPublic,
      speakerRequirements: speakerRequirements ?? this.speakerRequirements,
      customFields: customFields ?? this.customFields,
      communityId: communityId ?? this.communityId,
      callForSpeakersId: callForSpeakersId ?? this.callForSpeakersId,
      community: community ?? this.community,
      sessionsCount: sessionsCount ?? this.sessionsCount,
      speakersCount: speakersCount ?? this.speakersCount,
      totalSpeakers: totalSpeakers ?? this.totalSpeakers,
      availableSessions: availableSessions ?? this.availableSessions,
      meetingPlatformName: meetingPlatformName ?? this.meetingPlatformName,
      publicUrl: publicUrl ?? this.publicUrl,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  bool get isDraft => status == 'draft';
  bool get isPublished => status == 'published';
  bool get isCancelled => status == 'cancelled';

  bool get usesGoogleMeet => meetingPlatform == 'google_meet';
  bool get hasGoogleCalendarEvent => googleCalendarEventId?.isNotEmpty == true;
  bool get requiresMeetingLink => isOnline && meetingPlatform != null && meetingPlatform != 'google_meet';

  Duration get duration => endsAt.difference(startsAt);

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is CommunityEvent && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;

  @override
  String toString() {
    return 'CommunityEvent(id: $id, title: $title, type: $type, status: $status)';
  }
}