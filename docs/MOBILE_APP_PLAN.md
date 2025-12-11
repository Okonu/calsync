# CalSync Mobile App Development Plan

## Overview

This document outlines the development approach for the CalSync mobile application using React Native with Expo, providing cross-platform support for iOS and Android.

## Technology Stack

### Frontend Framework
- **React Native** with **Expo** (SDK 51+)
- **TypeScript** for type safety
- **Expo Router** for navigation

### State Management
- **Redux Toolkit** with **RTK Query** for API calls and caching
- **React Hook Form** for form management

### UI Framework
- **NativeBase** or **Tamagui** for consistent UI components
- **React Native Paper** (Material Design)
- **Expo Vector Icons** for icons

### Authentication
- **Expo AuthSession** for OAuth flows
- **SecureStore** for token storage
- **Biometric authentication** support

### Calendar Integration
- **Expo Calendar** for device calendar access
- **react-native-calendars** for custom calendar views
- **Date-fns** for date manipulation

### Push Notifications
- **Expo Notifications** for push notifications
- **Firebase** for advanced notification features

### Development Tools
- **Expo CLI** for development workflow
- **EAS Build** for app builds
- **EAS Submit** for app store deployment

## Project Structure

```
calsync-mobile/
├── app/                    # Expo Router app directory
│   ├── (auth)/            # Authentication screens
│   ├── (tabs)/            # Main tab navigation
│   ├── communities/       # Community-related screens
│   ├── events/           # Event management screens
│   └── settings/         # Settings screens
├── components/            # Reusable components
│   ├── calendar/         # Calendar-specific components
│   ├── forms/            # Form components
│   └── ui/               # Basic UI components
├── hooks/                # Custom React hooks
├── services/             # API services
│   ├── api.ts           # RTK Query API definitions
│   └── auth.ts          # Authentication service
├── store/                # Redux store configuration
├── types/                # TypeScript type definitions
├── utils/                # Utility functions
└── constants/            # App constants
```

## Core Features Implementation

### 1. Authentication Flow

#### Google OAuth Integration
```typescript
// services/auth.ts
import * as AuthSession from 'expo-auth-session';
import * as SecureStore from 'expo-secure-store';

export class AuthService {
  private static readonly API_BASE = 'https://your-domain.com/api';

  static async loginWithGoogle(): Promise<AuthResult> {
    const redirectUri = AuthSession.makeRedirectUri({
      scheme: 'calsync',
      path: 'auth/callback'
    });

    const authRequest = new AuthSession.AuthRequest({
      clientId: 'your-google-client-id',
      scopes: ['openid', 'profile', 'email'],
      redirectUri,
      responseType: AuthSession.ResponseType.Code,
    });

    const result = await authRequest.promptAsync({
      authorizationEndpoint: 'https://accounts.google.com/o/oauth2/v2/auth',
    });

    if (result.type === 'success') {
      return this.exchangeCodeForToken(result.params.code);
    }

    throw new Error('Authentication failed');
  }

  private static async exchangeCodeForToken(code: string): Promise<AuthResult> {
    const response = await fetch(`${this.API_BASE}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        google_code: code,
        device_name: 'Mobile App'
      })
    });

    const data = await response.json();

    if (response.ok) {
      await SecureStore.setItemAsync('auth_token', data.token);
      return data;
    }

    throw new Error(data.message);
  }
}
```

### 2. API Integration with RTK Query

```typescript
// services/api.ts
import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react';
import * as SecureStore from 'expo-secure-store';

const baseQuery = fetchBaseQuery({
  baseUrl: 'https://your-domain.com/api/',
  prepareHeaders: async (headers) => {
    const token = await SecureStore.getItemAsync('auth_token');
    if (token) {
      headers.set('authorization', `Bearer ${token}`);
    }
    return headers;
  },
});

export const api = createApi({
  reducerPath: 'api',
  baseQuery,
  tagTypes: ['User', 'Calendar', 'Event', 'Booking', 'Community'],
  endpoints: (builder) => ({
    // User endpoints
    getUser: builder.query<User, void>({
      query: () => 'user',
      providesTags: ['User'],
    }),

    // Calendar endpoints
    getEvents: builder.query<Event[], { start: string; end: string }>({
      query: ({ start, end }) => `calendar/events?start=${start}&end=${end}`,
      providesTags: ['Event'],
    }),

    getCalendars: builder.query<Calendar[], void>({
      query: () => 'calendar/calendars',
      providesTags: ['Calendar'],
    }),

    // Booking endpoints
    getBookings: builder.query<Booking[], void>({
      query: () => 'bookings',
      providesTags: ['Booking'],
    }),

    createBooking: builder.mutation<Booking, CreateBookingRequest>({
      query: (data) => ({
        url: `public/booking/${data.slug}`,
        method: 'POST',
        body: data,
      }),
      invalidatesTags: ['Booking'],
    }),

    // Community endpoints
    getCommunities: builder.query<Community[], void>({
      query: () => 'communities',
      providesTags: ['Community'],
    }),

    getCommunityEvents: builder.query<CommunityEvent[], string>({
      query: (communityId) => `communities/${communityId}/events`,
      providesTags: ['Event'],
    }),
  }),
});

export const {
  useGetUserQuery,
  useGetEventsQuery,
  useGetCalendarsQuery,
  useGetBookingsQuery,
  useCreateBookingMutation,
  useGetCommunitiesQuery,
  useGetCommunityEventsQuery,
} = api;
```

### 3. Main Navigation Structure

```typescript
// app/(tabs)/_layout.tsx
import { Tabs } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';

export default function TabLayout() {
  return (
    <Tabs
      screenOptions={{
        tabBarActiveTintColor: '#3B82F6',
        headerShown: false,
      }}
    >
      <Tabs.Screen
        name="index"
        options={{
          title: 'Calendar',
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="calendar" size={size} color={color} />
          ),
        }}
      />
      <Tabs.Screen
        name="bookings"
        options={{
          title: 'Bookings',
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="time" size={size} color={color} />
          ),
        }}
      />
      <Tabs.Screen
        name="communities"
        options={{
          title: 'Communities',
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="people" size={size} color={color} />
          ),
        }}
      />
      <Tabs.Screen
        name="profile"
        options={{
          title: 'Profile',
          tabBarIcon: ({ color, size }) => (
            <Ionicons name="person" size={size} color={color} />
          ),
        }}
      />
    </Tabs>
  );
}
```

### 4. Calendar Integration

```typescript
// components/calendar/CalendarView.tsx
import React from 'react';
import { Calendar } from 'react-native-calendars';
import { useGetEventsQuery } from '../../services/api';

interface CalendarViewProps {
  selectedDate: string;
  onDateSelect: (date: string) => void;
}

export function CalendarView({ selectedDate, onDateSelect }: CalendarViewProps) {
  const { data: events, isLoading } = useGetEventsQuery({
    start: startOfMonth(selectedDate),
    end: endOfMonth(selectedDate),
  });

  const markedDates = useMemo(() => {
    const marked: { [key: string]: any } = {};

    events?.forEach(event => {
      const date = format(new Date(event.starts_at), 'yyyy-MM-dd');
      marked[date] = {
        marked: true,
        dotColor: event.calendar.color,
        selectedColor: event.calendar.color,
      };
    });

    return {
      ...marked,
      [selectedDate]: {
        ...marked[selectedDate],
        selected: true,
        selectedColor: '#3B82F6',
      },
    };
  }, [events, selectedDate]);

  return (
    <Calendar
      current={selectedDate}
      onDayPress={(day) => onDateSelect(day.dateString)}
      markedDates={markedDates}
      theme={{
        todayTextColor: '#3B82F6',
        arrowColor: '#3B82F6',
        selectedDayBackgroundColor: '#3B82F6',
      }}
    />
  );
}
```

### 5. Offline Support

```typescript
// hooks/useOfflineSync.ts
import { useEffect } from 'react';
import NetInfo from '@react-native-async-storage/async-storage';
import AsyncStorage from '@react-native-async-storage/async-storage';

export function useOfflineSync() {
  useEffect(() => {
    const unsubscribe = NetInfo.addEventListener(state => {
      if (state.isConnected) {
        syncOfflineData();
      }
    });

    return unsubscribe;
  }, []);

  const syncOfflineData = async () => {
    try {
      const offlineActions = await AsyncStorage.getItem('offline_actions');
      if (offlineActions) {
        const actions = JSON.parse(offlineActions);
        // Process offline actions when connection is restored
        for (const action of actions) {
          await processOfflineAction(action);
        }
        await AsyncStorage.removeItem('offline_actions');
      }
    } catch (error) {
      console.error('Offline sync error:', error);
    }
  };
}
```

## Development Phases

### Phase 1: Core Setup (Week 1-2)
- [ ] Project initialization with Expo
- [ ] Authentication flow implementation
- [ ] Basic navigation structure
- [ ] API integration setup with RTK Query
- [ ] Basic UI components library

### Phase 2: Calendar Features (Week 3-4)
- [ ] Calendar view implementation
- [ ] Event listing and details
- [ ] Calendar management (visibility, colors)
- [ ] Google Calendar sync integration
- [ ] Event creation (basic)

### Phase 3: Booking System (Week 5-6)
- [ ] Booking page discovery
- [ ] Available slots display
- [ ] Booking creation flow
- [ ] Booking management for users
- [ ] Booking settings configuration

### Phase 4: Community Features (Week 7-8)
- [ ] Community listing and details
- [ ] Community event management
- [ ] Event creation and editing
- [ ] Speaker management
- [ ] Call for Speakers functionality

### Phase 5: Advanced Features (Week 9-10)
- [ ] Push notifications
- [ ] Offline support
- [ ] Advanced calendar features
- [ ] Analytics and insights
- [ ] Performance optimization

### Phase 6: Testing & Deployment (Week 11-12)
- [ ] Comprehensive testing
- [ ] Performance optimization
- [ ] App store preparation
- [ ] Beta testing
- [ ] Production deployment

## Security Considerations

1. **Token Storage**: Use Expo SecureStore for sensitive data
2. **API Security**: Implement proper request signing
3. **Biometric Auth**: Optional biometric authentication
4. **Data Encryption**: Encrypt sensitive offline data
5. **Certificate Pinning**: For production builds

## Performance Optimization

1. **Lazy Loading**: Implement route-based code splitting
2. **Image Optimization**: Use Expo Image for better performance
3. **List Virtualization**: Use FlatList for large data sets
4. **Caching**: Implement proper RTK Query caching strategies
5. **Bundle Analysis**: Regular bundle size monitoring

## Testing Strategy

1. **Unit Tests**: Jest with React Native Testing Library
2. **Integration Tests**: API integration testing
3. **E2E Tests**: Detox for end-to-end testing
4. **Performance Tests**: Flipper for performance monitoring
5. **Device Testing**: Test on various device sizes and OS versions

## Deployment

1. **EAS Build**: Use Expo Application Services for builds
2. **OTA Updates**: Implement over-the-air updates for quick fixes
3. **App Store Optimization**: Proper metadata and screenshots
4. **Beta Testing**: TestFlight (iOS) and Google Play Internal Testing
5. **Monitoring**: Implement crash reporting and analytics

## Post-Launch Considerations

1. **User Feedback**: Implement in-app feedback collection
2. **Analytics**: Track user engagement and feature usage
3. **Performance Monitoring**: Monitor app performance metrics
4. **Feature Flags**: Implement feature toggles for gradual rollouts
5. **Maintenance**: Regular updates and security patches

This comprehensive plan provides a roadmap for developing a full-featured CalSync mobile application that mirrors the web application's functionality while providing a native mobile experience.