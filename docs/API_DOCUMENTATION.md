# CalSync Mobile API Documentation

## Overview

The CalSync Mobile API provides comprehensive endpoints for managing calendars, bookings, communities, and events. All API endpoints are prefixed with `/api/` and require authentication via Laravel Sanctum tokens.

## Authentication

### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password", // Optional if using Google auth
    "google_token": "google_oauth_token", // Optional
    "device_name": "iPhone 15"
}
```

### Response
```json
{
    "user": {...},
    "token": "api_token_here",
    "token_type": "Bearer"
}
```

### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

### Get Current User
```http
GET /api/auth/me
Authorization: Bearer {token}
```

## User Management

### Get User Profile
```http
GET /api/user
Authorization: Bearer {token}
```

### Update Profile
```http
PUT /api/user
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com"
}
```

### Get User Stats
```http
GET /api/user/stats
Authorization: Bearer {token}
```

## Calendar & Events

### Get Calendar Overview
```http
GET /api/calendar
Authorization: Bearer {token}
```

### Get Events
```http
GET /api/calendar/events?start=2024-01-01&end=2024-01-31&calendar_ids[]=1&calendar_ids[]=2
Authorization: Bearer {token}
```

### Get Event Details
```http
GET /api/calendar/events/{id}
Authorization: Bearer {token}
```

### Get Calendars
```http
GET /api/calendar/calendars
Authorization: Bearer {token}
```

### Update Calendar
```http
PUT /api/calendar/calendars/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "is_visible": true,
    "color": "#FF5722"
}
```

### Get Google Accounts
```http
GET /api/calendar/accounts
Authorization: Bearer {token}
```

### Sync Google Account
```http
POST /api/google/accounts/{id}/sync
Authorization: Bearer {token}
```

## Bookings

### Get User Bookings
```http
GET /api/bookings
Authorization: Bearer {token}
```

### Get Booking Details
```http
GET /api/bookings/{id}
Authorization: Bearer {token}
```

### Cancel Booking
```http
POST /api/bookings/{id}/cancel
Authorization: Bearer {token}
```

### Get Booking Settings
```http
GET /api/bookings/settings
Authorization: Bearer {token}
```

### Update Booking Settings
```http
PUT /api/bookings/settings
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "30 min meeting",
    "description": "Quick chat",
    "duration": 30,
    "calendar_id": 1,
    "available_days": [1,2,3,4,5],
    "start_time": "09:00",
    "end_time": "17:00"
}
```

## Public Booking Endpoints

### Get Public Booking Page
```http
GET /api/public/booking/{slug}
```

### Get Available Slots
```http
GET /api/public/booking/{slug}/slots?date=2024-01-15
```

### Create Booking
```http
POST /api/public/booking/{slug}
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "start_datetime": "2024-01-15T10:00:00Z",
    "notes": "Looking forward to our meeting"
}
```

## Communities

### Get User Communities
```http
GET /api/communities
Authorization: Bearer {token}
```

### Create Community
```http
POST /api/communities
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Tech Meetup",
    "description": "Monthly tech discussions",
    "timezone": "UTC",
    "is_public": true,
    "color": "#3B82F6"
}
```

### Get Community Details
```http
GET /api/communities/{community}
Authorization: Bearer {token}
```

### Update Community
```http
PUT /api/communities/{community}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Community Name",
    "description": "Updated description"
}
```

### Get Community Stats
```http
GET /api/communities/{community}/stats
Authorization: Bearer {token}
```

### Get Community Calendar
```http
GET /api/communities/{community}/calendar?start=2024-01-01&end=2024-01-31
Authorization: Bearer {token}
```

## Community Events

### Get Community Events
```http
GET /api/communities/{community}/events
Authorization: Bearer {token}
```

### Create Event
```http
POST /api/communities/{community}/events
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Tech Talk",
    "description": "Latest in AI",
    "type": "webinar",
    "starts_at": "2024-02-15T19:00:00Z",
    "ends_at": "2024-02-15T20:00:00Z",
    "is_online": true,
    "meeting_platform": "google_meet",
    "is_public": true
}
```

### Get Event Details
```http
GET /api/communities/{community}/events/{event}
Authorization: Bearer {token}
```

### Add Speaker to Event
```http
POST /api/communities/{community}/events/{event}/speakers
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "bio": "AI researcher",
    "topic_title": "Future of AI"
}
```

### Get Event Sessions
```http
GET /api/communities/{community}/events/{event}/sessions
Authorization: Bearer {token}
```

### Create Event Session
```http
POST /api/communities/{community}/events/{event}/sessions
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Morning Session",
    "starts_at": "2024-02-15T09:00:00Z",
    "ends_at": "2024-02-15T12:00:00Z",
    "max_speakers": 3
}
```

## Call for Speakers

### Get CFS List
```http
GET /api/communities/{community}/cfs
Authorization: Bearer {token}
```

### Create CFS
```http
POST /api/communities/{community}/cfs
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "Tech Conference 2024",
    "description": "Looking for speakers",
    "deadline": "2024-01-31T23:59:59Z"
}
```

### Get CFS Applications
```http
GET /api/communities/{community}/cfs/{cfs}/applications
Authorization: Bearer {token}
```

### Approve Application
```http
POST /api/communities/{community}/cfs/{cfs}/applications/{application}/approve
Authorization: Bearer {token}
```

## Public Community Endpoints

### Get Public Communities
```http
GET /api/public/communities
```

### Get Public Community Details
```http
GET /api/public/communities/{slug}
```

### Get Public Community Events
```http
GET /api/public/communities/{slug}/events
```

### Get Public Event Details
```http
GET /api/public/communities/{slug}/events/{eventSlug}
```

### Get Public CFS List
```http
GET /api/public/communities/{slug}/cfs
```

## Rate Limiting

- Authenticated requests: 120 requests per minute
- Public endpoints: 60 requests per minute
- Rate limit headers are included in all responses:
  - `X-RateLimit-Limit`: Maximum requests allowed
  - `X-RateLimit-Remaining`: Remaining requests in current window

## Error Handling

All API endpoints return consistent error responses:

```json
{
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

Common HTTP status codes:
- `200`: Success
- `201`: Created
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Error
- `429`: Too Many Requests
- `500`: Server Error

## Pagination

List endpoints support pagination with the following parameters:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20, max: 100)

Paginated responses include:
```json
{
    "data": [...],
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
}
```