<template>
    <div class="min-h-screen bg-gray-50">
        <Head :title="`${event.title} - ${community.name}`" />

        <!-- Event Header -->
        <div class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <!-- Breadcrumb -->
                        <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                            <Link :href="`/community/${community.slug}`" class="hover:text-gray-700">
                                {{ community.name }}
                            </Link>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <Link :href="`/community/${community.slug}/events`" class="hover:text-gray-700">
                                Events
                            </Link>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="text-gray-900">{{ event.title }}</span>
                        </nav>

                        <div class="flex items-center space-x-3 mb-4">
                            <h1 class="text-3xl font-bold text-gray-900">{{ event.title }}</h1>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 capitalize">
                                {{ event.type }}
                            </span>
                            <span v-if="event.is_online" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                Online Event
                            </span>
                        </div>

                        <p v-if="event.description" class="text-lg text-gray-600 mb-6">{{ event.description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Details -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Event Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Event Details</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Date & Time</p>
                                        <p class="text-sm text-gray-600">{{ formatEventDateTime(event) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Location</p>
                                        <p class="text-sm text-gray-600">
                                            {{ event.is_online ? 'Online Event' : (event.location || 'TBA') }}
                                        </p>
                                    </div>
                                </div>

                                <div v-if="event.meeting_link" class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Meeting Link</p>
                                        <a :href="event.meeting_link" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-800">
                                            Join Meeting
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div v-if="event.max_attendees" class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 3a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Capacity</p>
                                        <p class="text-sm text-gray-600">{{ event.max_attendees }} attendees</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H5m14 0v-2a2 2 0 00-2-2H7a2 2 0 00-2 2v2" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Organized by</p>
                                        <Link :href="`/community/${community.slug}`" class="text-sm text-indigo-600 hover:text-indigo-800">
                                            {{ community.name }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sessions -->
                    <div v-if="event.sessions && event.sessions.length > 0" class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Sessions</h2>

                        <div class="space-y-4">
                            <div v-for="session in event.sessions" :key="session.id" class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-medium text-gray-900">{{ session.title }}</h3>
                                        <p v-if="session.description" class="text-gray-600 mt-1">{{ session.description }}</p>
                                    </div>
                                    <div class="text-sm text-gray-500 text-right ml-4">
                                        <p>{{ formatSessionTime(session) }}</p>
                                        <p v-if="session.location">{{ session.location }}</p>
                                    </div>
                                </div>

                                <!-- Session Speakers -->
                                <div v-if="session.speakers && session.speakers.length > 0" class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Speakers</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div v-for="speaker in session.speakers" :key="speaker.id" class="flex items-center space-x-3">
                                            <img
                                                :src="speaker.photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(speaker.name)}&background=6366f1&color=fff`"
                                                :alt="speaker.name"
                                                class="w-10 h-10 rounded-full"
                                            >
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900">{{ speaker.name }}</p>
                                                <p v-if="speaker.company" class="text-xs text-gray-500">{{ speaker.company }}</p>
                                                <p v-if="speaker.topic_title" class="text-xs text-indigo-600">{{ speaker.topic_title }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Session Meeting Link -->
                                <div v-if="session.meeting_link" class="mt-4">
                                    <a :href="session.meeting_link" target="_blank" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        Join Session
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Speakers (if no sessions) -->
                    <div v-else-if="event.speakers && event.speakers.length > 0" class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Speakers</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="speaker in event.speakers" :key="speaker.id" class="flex items-start space-x-4">
                                <img
                                    :src="speaker.photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(speaker.name)}&background=6366f1&color=fff`"
                                    :alt="speaker.name"
                                    class="w-16 h-16 rounded-full"
                                >
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900">{{ speaker.name }}</h3>
                                    <p v-if="speaker.company && speaker.job_title" class="text-sm text-gray-600">
                                        {{ speaker.job_title }} at {{ speaker.company }}
                                    </p>
                                    <p v-else-if="speaker.company" class="text-sm text-gray-600">{{ speaker.company }}</p>
                                    <p v-else-if="speaker.job_title" class="text-sm text-gray-600">{{ speaker.job_title }}</p>

                                    <div v-if="speaker.topic_title" class="mt-2">
                                        <p class="text-sm font-medium text-indigo-600">{{ speaker.topic_title }}</p>
                                        <p v-if="speaker.topic_description" class="text-sm text-gray-600 mt-1">{{ speaker.topic_description }}</p>
                                    </div>

                                    <p v-if="speaker.bio" class="text-sm text-gray-600 mt-2">{{ speaker.bio }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Apply CTA -->
                    <div v-if="canApply" class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-6 text-white mb-6">
                        <h3 class="text-lg font-semibold mb-2">Want to speak at this event?</h3>
                        <p class="text-indigo-100 text-sm mb-4">Submit your application through our call for speakers.</p>
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-white text-indigo-600 rounded-md font-medium hover:bg-gray-50 transition-colors">
                            Apply to Speak
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button
                                @click="addToCalendar"
                                class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Add to Calendar
                            </button>

                            <button
                                @click="shareEvent"
                                class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                                </svg>
                                Share Event
                            </button>
                        </div>
                    </div>

                    <!-- Community Info -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">About the Community</h3>
                        <div class="flex items-center space-x-3 mb-4">
                            <div
                                class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold"
                                :style="{ backgroundColor: community.color }"
                            >
                                <img
                                    v-if="community.logo_url"
                                    :src="community.logo_url"
                                    :alt="community.name"
                                    class="w-full h-full rounded-full object-cover"
                                >
                                <span v-else>{{ community.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">{{ community.name }}</h4>
                                <p v-if="community.description" class="text-sm text-gray-600">{{ community.description }}</p>
                            </div>
                        </div>
                        <Link :href="`/community/${community.slug}`" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                            View Community Profile
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    community: Object,
    event: Object,
    canApply: Boolean,
});

function formatEventDateTime(event) {
    const startDate = new Date(event.starts_at);
    const endDate = new Date(event.ends_at);

    const dateOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };

    const timeOptions = {
        hour: '2-digit',
        minute: '2-digit'
    };

    if (startDate.toDateString() === endDate.toDateString()) {
        return `${startDate.toLocaleDateString(undefined, dateOptions)} from ${startDate.toLocaleTimeString([], timeOptions)} to ${endDate.toLocaleTimeString([], timeOptions)}`;
    } else {
        return `${startDate.toLocaleDateString(undefined, { ...dateOptions, ...timeOptions })} - ${endDate.toLocaleDateString(undefined, { ...dateOptions, ...timeOptions })}`;
    }
}

function formatSessionTime(session) {
    const startDate = new Date(session.starts_at);
    const endDate = new Date(session.ends_at);

    return `${startDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} - ${endDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
}

function addToCalendar() {
    const startDate = new Date(props.event.starts_at);
    const endDate = new Date(props.event.ends_at);

    const googleCalendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(props.event.title)}&dates=${startDate.toISOString().replace(/[-:]/g, '').split('.')[0]}Z/${endDate.toISOString().replace(/[-:]/g, '').split('.')[0]}Z&details=${encodeURIComponent(props.event.description || '')}&location=${encodeURIComponent(props.event.location || '')}`;

    window.open(googleCalendarUrl, '_blank');
}

function shareEvent() {
    if (navigator.share) {
        navigator.share({
            title: props.event.title,
            text: props.event.description || `Join us for ${props.event.title}`,
            url: window.location.href,
        });
    } else {
        // Fallback to copying URL to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            // You might want to show a toast notification here
            alert('Event link copied to clipboard!');
        });
    }
}
</script>
