# Synqs - Calendar Sync & Booking Platform

I had called it CalSync initially but found the domain was already taken. I hope the name gods forgive me for the one I chose.

Synqs is a comprehensive web application that allows users to sync multiple Google Calendar accounts in one place and create booking pages for others to schedule meetings with them.

## 🌟 Features

### Calendar Management
- Connect multiple Google Calendar accounts
- View all your calendars in a unified interface
- Toggle visibility of individual calendars
- Customize calendar colors
- Sync calendar data automatically

### Booking Pages
- Create personalized booking pages
- Set availability (days and times)
- Configure meeting durations
- Add buffer times before and after meetings
- Automatically check existing events across all connected calendars
- Generate Google Meet links for virtual meetings

### Appointment Management
- Manage all incoming bookings in one place
- Cancel appointments with automatic notifications
- View booking history

## 🚀 Getting Started

### Prerequisites
- PHP 8.1+
- Composer
- Node.js and NPM
- MySQL/PostgreSQL database
- Google API credentials

### Installation

1. Clone the repository
   ```bash
   git@github.com:Okonu/calsync.git
   cd calsync
   ```

2. Install PHP dependencies
   ```bash
   composer install
   ```

3. Install JavaScript dependencies
   ```bash
   npm install
   ```

4. Create a `.env` file from the example and update with your configuration
   ```bash
   cp .env.example .env
   ```

5. Generate application key
   ```bash
   php artisan key:generate
   ```

6. Set up Google API credentials
    - Go to [Google Cloud Console](https://console.cloud.google.com/)
    - Create a new project
    - Enable the Google Calendar API
    - Create OAuth 2.0 credentials
    - Add authorized redirect URIs:
        - `{APP_URL}/auth/google/callback`
        - `{APP_URL}/connect/google/callback`

7. Update the `.env` file with your Google credentials
   ```
   GOOGLE_CLIENT_ID=your-client-id
   GOOGLE_CLIENT_SECRET=your-client-secret
   GOOGLE_REDIRECT_URI=your-app-url/auth/google/callback
   GOOGLE_CONNECT_REDIRECT_URI=your-app-url/connect/google/callback
   ```

8. Run database migrations
   ```bash
   php artisan migrate
   ```

9. Build frontend assets
   ```bash
   npm run dev
   ```

10. Start the development server
    ```bash
    php artisan serve
    ```

## 🔧 Technical Stack

### Backend
- Laravel PHP framework
- MySQL/PostgreSQL database
- Google Calendar API

### Frontend
- Vue.js 3 with Composition API
- Inertia.js for SPA-like experience with server-side routing
- Tailwind CSS for styling
- FullCalendar.js for calendar views

## 📋 System Architecture

### Models
- `User`: Application users
- `GoogleAccount`: Connected Google accounts
- `Calendar`: Individual calendars from Google accounts
- `Event`: Calendar events synced from Google
- `BookingPage`: User-created booking pages with availability settings
- `Booking`: Actual bookings made by guests

### Controllers
- `GoogleAuthController`: Handles Google OAuth authentication
- `CalendarController`: Manages calendar displays and event retrieval
- `BookingController`: Handles booking page configuration and booking creation
- `SettingsController`: Manages user settings and account management

### Services
- `GoogleCalendarService`: Handles communication with Google Calendar API

### Jobs
- `SyncGoogleCalendars`: Background job for syncing calendar data

## 📱 Usage Guide

### Connecting Google Accounts
1. Sign in with your primary Google account
2. Navigate to the Dashboard
3. Click "Connect Account" to add additional Google accounts

### Setting Up a Booking Page
1. Go to "Booking Settings"
2. Configure your booking page details (title, description, URL)
3. Set your availability (days, times, duration)
4. Select which calendars to check for conflicts
5. Save settings and share your booking URL

### Managing Calendars
1. Go to the Calendar view
2. Use the sidebar to toggle visibility of different calendars
3. Change calendar colors using the color picker
4. Switch between month, week, and day views

### Managing Bookings
1. Navigate to "Bookings"
2. View all upcoming and past bookings
3. Cancel bookings as needed

## 🤝 Contributing
Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License
This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgements
- [Laravel](https://laravel.com/) - The PHP framework used
- [Vue.js](https://vuejs.org/) - Frontend framework
- [Inertia.js](https://inertiajs.com/) - The SPA-like bridge for server-side apps
- [Tailwind CSS](https://tailwindcss.com/) - CSS framework
- [FullCalendar.js](https://fullcalendar.io/) - Calendar component
