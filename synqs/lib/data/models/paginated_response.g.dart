// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'paginated_response.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

PaginatedResponse<T> _$PaginatedResponseFromJson<T>(
  Map<String, dynamic> json,
  T Function(Object? json) fromJsonT,
) =>
    PaginatedResponse<T>(
      data: (json['data'] as List<dynamic>).map(fromJsonT).toList(),
      currentPage: (json['current_page'] as num).toInt(),
      firstPageUrl: json['first_page_url'] as String?,
      from: (json['from'] as num).toInt(),
      lastPage: (json['last_page'] as num).toInt(),
      lastPageUrl: json['last_page_url'] as String?,
      links: (json['links'] as List<dynamic>)
          .map((e) => PageLink.fromJson(e as Map<String, dynamic>))
          .toList(),
      nextPageUrl: json['next_page_url'] as String?,
      path: json['path'] as String,
      perPage: (json['per_page'] as num).toInt(),
      prevPageUrl: json['prev_page_url'] as String?,
      to: (json['to'] as num).toInt(),
      total: (json['total'] as num).toInt(),
    );

Map<String, dynamic> _$PaginatedResponseToJson<T>(
  PaginatedResponse<T> instance,
  Object? Function(T value) toJsonT,
) =>
    <String, dynamic>{
      'data': instance.data.map(toJsonT).toList(),
      'current_page': instance.currentPage,
      'first_page_url': instance.firstPageUrl,
      'from': instance.from,
      'last_page': instance.lastPage,
      'last_page_url': instance.lastPageUrl,
      'links': instance.links,
      'next_page_url': instance.nextPageUrl,
      'path': instance.path,
      'per_page': instance.perPage,
      'prev_page_url': instance.prevPageUrl,
      'to': instance.to,
      'total': instance.total,
    };

PageLink _$PageLinkFromJson(Map<String, dynamic> json) => PageLink(
      url: json['url'] as String?,
      label: json['label'] as String,
      active: json['active'] as bool? ?? false,
    );

Map<String, dynamic> _$PageLinkToJson(PageLink instance) => <String, dynamic>{
      'url': instance.url,
      'label': instance.label,
      'active': instance.active,
    };
