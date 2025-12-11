// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'login_request.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

LoginRequest _$LoginRequestFromJson(Map<String, dynamic> json) => LoginRequest(
      email: json['email'] as String?,
      password: json['password'] as String?,
      googleToken: json['google_token'] as String?,
      deviceName: json['device_name'] as String,
    );

Map<String, dynamic> _$LoginRequestToJson(LoginRequest instance) =>
    <String, dynamic>{
      'email': instance.email,
      'password': instance.password,
      'google_token': instance.googleToken,
      'device_name': instance.deviceName,
    };
