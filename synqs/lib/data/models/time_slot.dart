import 'package:json_annotation/json_annotation.dart';

part 'time_slot.g.dart';

@JsonSerializable()
class TimeSlot {
  @JsonKey(name: 'start_time')
  final String startTime;
  @JsonKey(name: 'end_time')
  final String endTime;
  @JsonKey(name: 'start_datetime')
  final DateTime startDateTime;
  @JsonKey(name: 'end_datetime')
  final DateTime endDateTime;
  @JsonKey(name: 'is_available')
  final bool isAvailable;

  const TimeSlot({
    required this.startTime,
    required this.endTime,
    required this.startDateTime,
    required this.endDateTime,
    this.isAvailable = true,
  });

  factory TimeSlot.fromJson(Map<String, dynamic> json) =>
      _$TimeSlotFromJson(json);

  Map<String, dynamic> toJson() => _$TimeSlotToJson(this);

  TimeSlot copyWith({
    String? startTime,
    String? endTime,
    DateTime? startDateTime,
    DateTime? endDateTime,
    bool? isAvailable,
  }) {
    return TimeSlot(
      startTime: startTime ?? this.startTime,
      endTime: endTime ?? this.endTime,
      startDateTime: startDateTime ?? this.startDateTime,
      endDateTime: endDateTime ?? this.endDateTime,
      isAvailable: isAvailable ?? this.isAvailable,
    );
  }

  Duration get duration => endDateTime.difference(startDateTime);

  String get displayTime => '$startTime - $endTime';

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is TimeSlot &&
           other.startDateTime == startDateTime &&
           other.endDateTime == endDateTime;
  }

  @override
  int get hashCode => startDateTime.hashCode ^ endDateTime.hashCode;

  @override
  String toString() {
    return 'TimeSlot(startTime: $startTime, endTime: $endTime, available: $isAvailable)';
  }
}