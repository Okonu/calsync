import 'package:json_annotation/json_annotation.dart';
import 'user.dart';
import 'google_account.dart';
import 'calendar.dart';

part 'community.g.dart';

@JsonSerializable()
class Community {
  final int id;
  final String name;
  final String slug;
  final String? description;
  final String? logo;
  final String? website;
  @JsonKey(name: 'contact_email')
  final String? contactEmail;
  @JsonKey(name: 'calendar_email')
  final String? calendarEmail;
  @JsonKey(name: 'destination_calendar_id')
  final int? destinationCalendarId;
  @JsonKey(name: 'google_account_id')
  final int? googleAccountId;
  @JsonKey(name: 'social_links')
  final Map<String, dynamic>? socialLinks;
  final String? timezone;
  final String color;
  @JsonKey(name: 'is_public')
  final bool isPublic;
  @JsonKey(name: 'is_active')
  final bool isActive;
  @JsonKey(name: 'user_id')
  final int userId;
  final User? user;
  @JsonKey(name: 'google_account')
  final GoogleAccount? googleAccount;
  @JsonKey(name: 'destination_calendar')
  final Calendar? destinationCalendar;
  @JsonKey(name: 'events_count')
  final int? eventsCount;
  @JsonKey(name: 'calls_for_speakers_count')
  final int? callsForSpeakersCount;
  @JsonKey(name: 'logo_url')
  final String? logoUrl;
  @JsonKey(name: 'public_url')
  final String? publicUrl;
  @JsonKey(name: 'created_at')
  final DateTime? createdAt;
  @JsonKey(name: 'updated_at')
  final DateTime? updatedAt;

  const Community({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    this.logo,
    this.website,
    this.contactEmail,
    this.calendarEmail,
    this.destinationCalendarId,
    this.googleAccountId,
    this.socialLinks,
    this.timezone,
    this.color = '#3B82F6',
    this.isPublic = true,
    this.isActive = true,
    required this.userId,
    this.user,
    this.googleAccount,
    this.destinationCalendar,
    this.eventsCount,
    this.callsForSpeakersCount,
    this.logoUrl,
    this.publicUrl,
    this.createdAt,
    this.updatedAt,
  });

  factory Community.fromJson(Map<String, dynamic> json) =>
      _$CommunityFromJson(json);

  Map<String, dynamic> toJson() => _$CommunityToJson(this);

  Community copyWith({
    int? id,
    String? name,
    String? slug,
    String? description,
    String? logo,
    String? website,
    String? contactEmail,
    String? calendarEmail,
    int? destinationCalendarId,
    int? googleAccountId,
    Map<String, dynamic>? socialLinks,
    String? timezone,
    String? color,
    bool? isPublic,
    bool? isActive,
    int? userId,
    User? user,
    GoogleAccount? googleAccount,
    Calendar? destinationCalendar,
    int? eventsCount,
    int? callsForSpeakersCount,
    String? logoUrl,
    String? publicUrl,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Community(
      id: id ?? this.id,
      name: name ?? this.name,
      slug: slug ?? this.slug,
      description: description ?? this.description,
      logo: logo ?? this.logo,
      website: website ?? this.website,
      contactEmail: contactEmail ?? this.contactEmail,
      calendarEmail: calendarEmail ?? this.calendarEmail,
      destinationCalendarId: destinationCalendarId ?? this.destinationCalendarId,
      googleAccountId: googleAccountId ?? this.googleAccountId,
      socialLinks: socialLinks ?? this.socialLinks,
      timezone: timezone ?? this.timezone,
      color: color ?? this.color,
      isPublic: isPublic ?? this.isPublic,
      isActive: isActive ?? this.isActive,
      userId: userId ?? this.userId,
      user: user ?? this.user,
      googleAccount: googleAccount ?? this.googleAccount,
      destinationCalendar: destinationCalendar ?? this.destinationCalendar,
      eventsCount: eventsCount ?? this.eventsCount,
      callsForSpeakersCount: callsForSpeakersCount ?? this.callsForSpeakersCount,
      logoUrl: logoUrl ?? this.logoUrl,
      publicUrl: publicUrl ?? this.publicUrl,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  bool get hasCalendarIntegration => calendarEmail != null || googleAccountId != null;

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is Community && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;

  @override
  String toString() {
    return 'Community(id: $id, name: $name, slug: $slug)';
  }
}