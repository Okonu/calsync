<template>
    <div class="min-h-screen bg-gray-50">
        <Head :title="`Events - ${community.name}`" />

        <!-- Community Header -->
        <div class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <Link :href="`/community/${community.slug}`" class="flex items-center space-x-3">
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
                                <h1 class="text-2xl font-bold text-gray-900">{{ community.name }}</h1>
                                <p class="text-sm text-gray-500">Events</p>
                            </div>
                        </Link>
                    </div>
                    <Link
                        :href="`/community/${community.slug}`"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Community
                    </Link>
                </div>
            </div>
        </div>

        <!-- Events Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Filter Bar -->
            <div class="mb-8 bg-white rounded-lg shadow-sm p-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Filter by type:</label>
                        <select v-model="selectedType" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Types</option>
                            <option value="webinar">Webinar</option>
                            <option value="workshop">Workshop</option>
                            <option value="study_jam">Study Jam</option>
                            <option value="meetup">Meetup</option>
                            <option value="conference">Conference</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Location:</label>
                        <select v-model="selectedLocation" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Locations</option>
                            <option value="online">Online</option>
                            <option value="in-person">In Person</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Sort by:</label>
                        <select v-model="sortBy" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="date">Date</option>
                            <option value="title">Title</option>
                            <option value="type">Type</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- No Events State -->
            <div v-if="filteredEvents.length === 0" class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No events found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ events.data.length === 0 ? 'This community has no events yet.' : 'No events match your current filters.' }}
                </p>
                <div v-if="hasActiveFilters" class="mt-4">
                    <button
                        @click="clearFilters"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Events Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="event in filteredEvents" :key="event.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="p-6">
                        <!-- Event Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center text-sm text-gray-500 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ formatEventDate(event) }}
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ event.title }}</h3>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 capitalize ml-2">
                                {{ event.type }}
                            </span>
                        </div>

                        <!-- Event Description -->
                        <p v-if="event.description" class="text-gray-600 text-sm mb-4 line-clamp-3">
                            {{ event.description }}
                        </p>

                        <!-- Event Meta -->
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                {{ event.is_online ? 'Online' : (event.location || 'TBA') }}
                            </div>
                            <div v-if="event.total_speakers > 0" class="flex items-center text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ event.total_speakers }} speaker{{ event.total_speakers !== 1 ? 's' : '' }}
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="flex justify-between items-center">
                            <a :href="event.public_url" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                View Event
                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>

                            <div v-if="isUpcoming(event)" class="text-sm text-green-600 font-medium">
                                {{ getTimeUntilEvent(event) }}
                            </div>
                            <div v-else-if="isPast(event)" class="text-sm text-gray-500">
                                Past Event
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="events.links && events.links.length > 3" class="mt-8 flex justify-center">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <template v-for="(link, index) in events.links" :key="index">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                            :class="{
                                'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active,
                                'rounded-l-md': index === 0,
                                'rounded-r-md': index === events.links.length - 1
                            }"
                        ></Link>
                        <span
                            v-else
                            v-html="link.label"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500"
                        ></span>
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    community: Object,
    events: Object,
});

const selectedType = ref('');
const selectedLocation = ref('');
const sortBy = ref('date');

const filteredEvents = computed(() => {
    let filtered = [...props.events.data];

    // Filter by type
    if (selectedType.value) {
        filtered = filtered.filter(event => event.type === selectedType.value);
    }

    // Filter by location
    if (selectedLocation.value) {
        if (selectedLocation.value === 'online') {
            filtered = filtered.filter(event => event.is_online);
        } else if (selectedLocation.value === 'in-person') {
            filtered = filtered.filter(event => !event.is_online);
        }
    }

    // Sort events
    filtered.sort((a, b) => {
        switch (sortBy.value) {
            case 'title':
                return a.title.localeCompare(b.title);
            case 'type':
                return a.type.localeCompare(b.type);
            case 'date':
            default:
                return new Date(a.starts_at) - new Date(b.starts_at);
        }
    });

    return filtered;
});

const hasActiveFilters = computed(() => {
    return selectedType.value || selectedLocation.value;
});

function formatEventDate(event) {
    const startDate = new Date(event.starts_at);
    const endDate = new Date(event.ends_at);

    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };

    if (startDate.toDateString() === endDate.toDateString()) {
        return `${startDate.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })} at ${startDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    } else {
        return `${startDate.toLocaleDateString(undefined, options)} - ${endDate.toLocaleDateString(undefined, options)}`;
    }
}

function isUpcoming(event) {
    return new Date(event.starts_at) > new Date();
}

function isPast(event) {
    return new Date(event.ends_at) < new Date();
}

function getTimeUntilEvent(event) {
    const now = new Date();
    const eventStart = new Date(event.starts_at);
    const diffTime = eventStart - now;

    if (diffTime < 0) return '';

    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) {
        const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
        if (diffHours === 0) {
            const diffMinutes = Math.floor(diffTime / (1000 * 60));
            return `In ${diffMinutes} min`;
        }
        return `In ${diffHours}h`;
    } else if (diffDays === 1) {
        return 'Tomorrow';
    } else if (diffDays < 7) {
        return `In ${diffDays} days`;
    } else {
        return `In ${Math.floor(diffDays / 7)} week${Math.floor(diffDays / 7) !== 1 ? 's' : ''}`;
    }
}

function clearFilters() {
    selectedType.value = '';
    selectedLocation.value = '';
    sortBy.value = 'date';
}
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
