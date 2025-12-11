import 'package:json_annotation/json_annotation.dart';

part 'login_request.g.dart';

@JsonSerializable()
class LoginRequest {
  final String? email;
  final String? password;
  @JsonKey(name: 'google_token')
  final String? googleToken;
  @JsonKey(name: 'device_name')
  final String deviceName;

  const LoginRequest({
    this.email,
    this.password,
    this.googleToken,
    required this.deviceName,
  });

  factory LoginRequest.fromJson(Map<String, dynamic> json) =>
      _$LoginRequestFromJson(json);

  Map<String, dynamic> toJson() => _$LoginRequestToJson(this);

  LoginRequest copyWith({
    String? email,
    String? password,
    String? googleToken,
    String? deviceName,
  }) {
    return LoginRequest(
      email: email ?? this.email,
      password: password ?? this.password,
      googleToken: googleToken ?? this.googleToken,
      deviceName: deviceName ?? this.deviceName,
    );
  }
}