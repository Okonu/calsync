import 'package:json_annotation/json_annotation.dart';
import 'google_account.dart';

part 'calendar.g.dart';

@JsonSerializable()
class Calendar {
  final int id;
  final String name;
  final String color;
  @JsonKey(name: 'is_primary')
  final bool isPrimary;
  @JsonKey(name: 'is_visible')
  final bool isVisible;
  @JsonKey(name: 'google_id')
  final String googleId;
  @JsonKey(name: 'google_account_id')
  final int googleAccountId;
  @JsonKey(name: 'google_account')
  final GoogleAccount? googleAccount;
  @JsonKey(name: 'created_at')
  final DateTime? createdAt;
  @JsonKey(name: 'updated_at')
  final DateTime? updatedAt;

  const Calendar({
    required this.id,
    required this.name,
    required this.color,
    this.isPrimary = false,
    this.isVisible = true,
    required this.googleId,
    required this.googleAccountId,
    this.googleAccount,
    this.createdAt,
    this.updatedAt,
  });

  factory Calendar.fromJson(Map<String, dynamic> json) =>
      _$CalendarFromJson(json);

  Map<String, dynamic> toJson() => _$CalendarToJson(this);

  Calendar copyWith({
    int? id,
    String? name,
    String? color,
    bool? isPrimary,
    bool? isVisible,
    String? googleId,
    int? googleAccountId,
    GoogleAccount? googleAccount,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Calendar(
      id: id ?? this.id,
      name: name ?? this.name,
      color: color ?? this.color,
      isPrimary: isPrimary ?? this.isPrimary,
      isVisible: isVisible ?? this.isVisible,
      googleId: googleId ?? this.googleId,
      googleAccountId: googleAccountId ?? this.googleAccountId,
      googleAccount: googleAccount ?? this.googleAccount,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is Calendar && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;

  @override
  String toString() {
    return 'Calendar(id: $id, name: $name, color: $color)';
  }
}