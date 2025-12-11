import 'package:json_annotation/json_annotation.dart';
import 'booking_page.dart';
import 'calendar.dart';

part 'booking.g.dart';

@JsonSerializable()
class Booking {
  final int id;
  final String name;
  final String email;
  @JsonKey(name: 'starts_at')
  final DateTime startsAt;
  @JsonKey(name: 'ends_at')
  final DateTime endsAt;
  final String? notes;
  final String status;
  final String uid;
  @JsonKey(name: 'google_event_id')
  final String? googleEventId;
  @JsonKey(name: 'meeting_link')
  final String? meetingLink;
  @JsonKey(name: 'booking_page_id')
  final int bookingPageId;
  @JsonKey(name: 'calendar_id')
  final int? calendarId;
  @JsonKey(name: 'booking_page')
  final BookingPage? bookingPage;
  final Calendar? calendar;
  @JsonKey(name: 'created_at')
  final DateTime? createdAt;
  @JsonKey(name: 'updated_at')
  final DateTime? updatedAt;

  const Booking({
    required this.id,
    required this.name,
    required this.email,
    required this.startsAt,
    required this.endsAt,
    this.notes,
    this.status = 'confirmed',
    required this.uid,
    this.googleEventId,
    this.meetingLink,
    required this.bookingPageId,
    this.calendarId,
    this.bookingPage,
    this.calendar,
    this.createdAt,
    this.updatedAt,
  });

  factory Booking.fromJson(Map<String, dynamic> json) =>
      _$BookingFromJson(json);

  Map<String, dynamic> toJson() => _$BookingToJson(this);

  Booking copyWith({
    int? id,
    String? name,
    String? email,
    DateTime? startsAt,
    DateTime? endsAt,
    String? notes,
    String? status,
    String? uid,
    String? googleEventId,
    String? meetingLink,
    int? bookingPageId,
    int? calendarId,
    BookingPage? bookingPage,
    Calendar? calendar,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Booking(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      startsAt: startsAt ?? this.startsAt,
      endsAt: endsAt ?? this.endsAt,
      notes: notes ?? this.notes,
      status: status ?? this.status,
      uid: uid ?? this.uid,
      googleEventId: googleEventId ?? this.googleEventId,
      meetingLink: meetingLink ?? this.meetingLink,
      bookingPageId: bookingPageId ?? this.bookingPageId,
      calendarId: calendarId ?? this.calendarId,
      bookingPage: bookingPage ?? this.bookingPage,
      calendar: calendar ?? this.calendar,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  bool get isCancelled => status == 'cancelled';
  bool get isConfirmed => status == 'confirmed';
  bool get isPending => status == 'pending';

  Duration get duration => endsAt.difference(startsAt);

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is Booking && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;

  @override
  String toString() {
    return 'Booking(id: $id, name: $name, startsAt: $startsAt, status: $status)';
  }
}