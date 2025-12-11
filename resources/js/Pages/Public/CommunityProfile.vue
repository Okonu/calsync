<template>
    <div class="min-h-screen bg-gray-50">
        <Head :title="community.name" />

        <!-- Community Header -->
        <div class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center space-x-6">
                    <div
                        class="w-24 h-24 rounded-full flex items-center justify-center text-white font-bold text-2xl"
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
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-gray-900">{{ community.name }}</h1>
                        <p v-if="community.description" class="mt-2 text-lg text-gray-600">{{ community.description }}</p>
                        <div class="mt-4 flex items-center space-x-4 text-sm text-gray-500">
                            <span v-if="community.contact_email" class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ community.contact_email }}
                            </span>
                            <span v-if="community.website" class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9" />
                                </svg>
                                <a :href="community.website" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                                    Website
                                </a>
                            </span>
                            <span class="text-gray-400">•</span>
                            <span>Organized by {{ community.owner.name }}</span>
                        </div>

                        <!-- Social Links -->
                        <div v-if="community.social_links" class="mt-4 flex space-x-4">
                            <a v-if="community.social_links.twitter" :href="community.social_links.twitter" target="_blank" class="text-gray-400 hover:text-blue-500">
                                <span class="sr-only">Twitter</span>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84" />
                                </svg>
                            </a>
                            <a v-if="community.social_links.linkedin" :href="community.social_links.linkedin" target="_blank" class="text-gray-400 hover:text-blue-600">
                                <span class="sr-only">LinkedIn</span>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a v-if="community.social_links.github" :href="community.social_links.github" target="_blank" class="text-gray-400 hover:text-gray-900">
                                <span class="sr-only">GitHub</span>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex space-x-8">
                    <button
                        @click="activeTab = 'events'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm',
                            activeTab === 'events'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        Upcoming Events
                        <span v-if="upcomingEvents.length > 0" class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs font-medium">
                            {{ upcomingEvents.length }}
                        </span>
                    </button>
                    <button
                        @click="activeTab = 'cfs'"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm',
                            activeTab === 'cfs'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        Call for Speakers
                        <span v-if="openCfs.length > 0" class="ml-2 bg-red-100 text-red-800 py-0.5 px-2.5 rounded-full text-xs font-medium">
                            {{ openCfs.length }} Open
                        </span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Events Tab -->
            <div v-if="activeTab === 'events'">
                <div v-if="upcomingEvents.length === 0" class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No upcoming events</h3>
                    <p class="mt-1 text-sm text-gray-500">Check back soon for new events from this community.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="event in upcomingEvents" :key="event.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ formatDate(event.starts_at) }}
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ event.title }}</h3>
                            <p v-if="event.description" class="text-gray-600 text-sm mb-4 line-clamp-3">{{ event.description }}</p>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    {{ event.is_online ? 'Online' : (event.location || 'TBA') }}
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 capitalize">
                                    {{ event.type }}
                                </span>
                            </div>

                            <div v-if="event.total_speakers > 0" class="mt-4 text-sm text-gray-500">
                                {{ event.total_speakers }} speaker{{ event.total_speakers !== 1 ? 's' : '' }}
                            </div>

                            <div class="mt-4">
                                <a :href="event.public_url" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    View Event
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="upcomingEvents.length > 0" class="mt-8 text-center">
                    <Link :href="`/community/${community.slug}/events`" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        View All Events
                    </Link>
                </div>
            </div>

            <!-- CFS Tab -->
            <div v-if="activeTab === 'cfs'">
                <div v-if="openCfs.length === 0" class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No open calls for speakers</h3>
                    <p class="mt-1 text-sm text-gray-500">There are currently no open calls for speakers from this community.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="cfs in openCfs" :key="cfs.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ cfs.title }}</h3>
                            <p v-if="cfs.description" class="text-gray-600 text-sm mb-4 line-clamp-3">{{ cfs.description }}</p>

                            <div class="flex items-center justify-between mb-4">
                                <div v-if="cfs.closes_at" class="text-sm text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Closes {{ formatDate(cfs.closes_at) }}
                                </div>
                                <div v-if="cfs.total_applications !== null" class="text-sm text-gray-500">
                                    {{ cfs.total_applications }} application{{ cfs.total_applications !== 1 ? 's' : '' }}
                                </div>
                            </div>

                            <div class="flex space-x-3">
                                <a :href="cfs.public_url" class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Apply Now
                                </a>
                                <a :href="cfs.public_url" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="openCfs.length > 0" class="mt-8 text-center">
                    <Link :href="`/community/${community.slug}/cfs`" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        View All Calls for Speakers
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    community: Object,
    upcomingEvents: Array,
    openCfs: Array,
});

const activeTab = ref('events');

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
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
