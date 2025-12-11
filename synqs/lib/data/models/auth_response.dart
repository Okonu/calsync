import 'package:json_annotation/json_annotation.dart';
import 'user.dart';

part 'auth_response.g.dart';

@JsonSerializable()
class AuthResponse {
  final User user;
  final String token;
  @JsonKey(name: 'token_type')
  final String tokenType;
  @JsonKey(name: 'refresh_token')
  final String? refreshToken;

  const AuthResponse({
    required this.user,
    required this.token,
    this.tokenType = 'Bearer',
    this.refreshToken,
  });

  factory AuthResponse.fromJson(Map<String, dynamic> json) =>
      _$AuthResponseFromJson(json);

  Map<String, dynamic> toJson() => _$AuthResponseToJson(this);
}