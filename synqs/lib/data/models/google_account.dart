import 'package:json_annotation/json_annotation.dart';
import 'calendar.dart';

part 'google_account.g.dart';

@JsonSerializable()
class GoogleAccount {
  final int id;
  final String name;
  final String email;
  final String? avatar;
  final String color;
  @JsonKey(name: 'is_primary')
  final bool isPrimary;
  @JsonKey(name: 'is_active')
  final bool isActive;
  @JsonKey(name: 'user_id')
  final int userId;
  @JsonKey(name: 'token_expires_at')
  final DateTime? tokenExpiresAt;
  final List<Calendar>? calendars;
  @JsonKey(name: 'calendars_count')
  final int? calendarsCount;
  @JsonKey(name: 'created_at')
  final DateTime? createdAt;
  @JsonKey(name: 'updated_at')
  final DateTime? updatedAt;

  const GoogleAccount({
    required this.id,
    required this.name,
    required this.email,
    this.avatar,
    required this.color,
    this.isPrimary = false,
    this.isActive = true,
    required this.userId,
    this.tokenExpiresAt,
    this.calendars,
    this.calendarsCount,
    this.createdAt,
    this.updatedAt,
  });

  factory GoogleAccount.fromJson(Map<String, dynamic> json) =>
      _$GoogleAccountFromJson(json);

  Map<String, dynamic> toJson() => _$GoogleAccountToJson(this);

  // Backward compatibility getter
  String? get picture => avatar;

  GoogleAccount copyWith({
    int? id,
    String? name,
    String? email,
    String? avatar,
    String? color,
    bool? isPrimary,
    bool? isActive,
    int? userId,
    DateTime? tokenExpiresAt,
    List<Calendar>? calendars,
    int? calendarsCount,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return GoogleAccount(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      avatar: avatar ?? this.avatar,
      color: color ?? this.color,
      isPrimary: isPrimary ?? this.isPrimary,
      isActive: isActive ?? this.isActive,
      userId: userId ?? this.userId,
      tokenExpiresAt: tokenExpiresAt ?? this.tokenExpiresAt,
      calendars: calendars ?? this.calendars,
      calendarsCount: calendarsCount ?? this.calendarsCount,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is GoogleAccount && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;

  @override
  String toString() {
    return 'GoogleAccount(id: $id, name: $name, email: $email)';
  }
}