import 'package:json_annotation/json_annotation.dart';
import 'user.dart';
import 'calendar.dart';

part 'booking_page.g.dart';

@JsonSerializable()
class BookingPage {
  final int id;
  final String title;
  final String? description;
  final int duration;
  final String slug;
  @JsonKey(name: 'is_active')
  final bool isActive;
  @JsonKey(name: 'available_days')
  final List<int> availableDays;
  @JsonKey(name: 'start_time')
  final String startTime;
  @JsonKey(name: 'end_time')
  final String endTime;
  @JsonKey(name: 'buffer_time')
  final int? bufferTime;
  @JsonKey(name: 'max_days_in_advance')
  final int? maxDaysInAdvance;
  @JsonKey(name: 'user_id')
  final int userId;
  @JsonKey(name: 'calendar_id')
  final int? calendarId;
  final User? user;
  final Calendar? calendar;
  @JsonKey(name: 'created_at')
  final DateTime? createdAt;
  @JsonKey(name: 'updated_at')
  final DateTime? updatedAt;

  const BookingPage({
    required this.id,
    required this.title,
    this.description,
    required this.duration,
    required this.slug,
    this.isActive = true,
    this.availableDays = const [1, 2, 3, 4, 5], // Monday to Friday
    this.startTime = '09:00',
    this.endTime = '17:00',
    this.bufferTime,
    this.maxDaysInAdvance,
    required this.userId,
    this.calendarId,
    this.user,
    this.calendar,
    this.createdAt,
    this.updatedAt,
  });

  factory BookingPage.fromJson(Map<String, dynamic> json) =>
      _$BookingPageFromJson(json);

  Map<String, dynamic> toJson() => _$BookingPageToJson(this);

  BookingPage copyWith({
    int? id,
    String? title,
    String? description,
    int? duration,
    String? slug,
    bool? isActive,
    List<int>? availableDays,
    String? startTime,
    String? endTime,
    int? bufferTime,
    int? maxDaysInAdvance,
    int? userId,
    int? calendarId,
    User? user,
    Calendar? calendar,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return BookingPage(
      id: id ?? this.id,
      title: title ?? this.title,
      description: description ?? this.description,
      duration: duration ?? this.duration,
      slug: slug ?? this.slug,
      isActive: isActive ?? this.isActive,
      availableDays: availableDays ?? this.availableDays,
      startTime: startTime ?? this.startTime,
      endTime: endTime ?? this.endTime,
      bufferTime: bufferTime ?? this.bufferTime,
      maxDaysInAdvance: maxDaysInAdvance ?? this.maxDaysInAdvance,
      userId: userId ?? this.userId,
      calendarId: calendarId ?? this.calendarId,
      user: user ?? this.user,
      calendar: calendar ?? this.calendar,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  String get publicUrl => '/book/$slug';

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is BookingPage && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;

  @override
  String toString() {
    return 'BookingPage(id: $id, title: $title, slug: $slug)';
  }
}