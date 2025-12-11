<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    community: Object,
    stats: Object,
    recentEvents: Array,
    recentCfs: Array,
});
</script>

<template>
    <AppLayout :title="`${community.name} Dashboard`">
        <Head :title="`${community.name} Dashboard`" />

        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <img
                        v-if="community.logo_url"
                        :src="community.logo_url"
                        :alt="community.name"
                        class="h-10 w-10 rounded-full"
                    >
                    <div
                        v-else
                        class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold"
                        :style="{ backgroundColor: community.color }"
                    >
                        {{ community.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold leading-tight text-gray-800">
                            {{ community.name }}
                        </h2>
                        <p class="text-sm text-gray-600">Community Dashboard</p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a
                        :href="`/communities/${community.slug}/settings`"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                    >
                        Settings
                    </a>
                    <a
                        :href="`/community/${community.slug}`"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        View Public Page
                    </a>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Events</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.total_events }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <a :href="`/communities/${community.slug}/events`" class="font-medium text-blue-700 hover:text-blue-900">
                                    View all events
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Upcoming Events</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.upcoming_events }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <a :href="`/communities/${community.slug}/events/create`" class="font-medium text-green-700 hover:text-green-900">
                                    Create new event
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Calls for Speakers</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.total_cfs }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <a :href="`/communities/${community.slug}/cfs`" class="font-medium text-purple-700 hover:text-purple-900">
                                    Manage CFS
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Confirmed Speakers</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.total_speakers }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <span class="font-medium text-orange-700">{{ stats.pending_applications }} pending applications</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Recent Events -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Recent Events</h3>
                                <a
                                    :href="`/communities/${community.slug}/events`"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                >
                                    View all
                                </a>
                            </div>
                        </div>
                        <div v-if="recentEvents.length === 0" class="p-6 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No events yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating your first event.</p>
                            <div class="mt-6">
                                <a
                                    :href="`/communities/${community.slug}/events/create`"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                                >
                                    Create Event
                                </a>
                            </div>
                        </div>
                        <div v-else>
                            <ul class="divide-y divide-gray-200">
                                <li v-for="event in recentEvents" :key="event.id" class="p-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <a
                                                :href="`/communities/${community.slug}/events/${event.slug}`"
                                                class="text-sm font-medium text-gray-900 hover:text-indigo-600"
                                            >
                                                {{ event.title }}
                                            </a>
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ new Date(event.starts_at).toLocaleDateString() }} •
                                                {{ event.sessions_count }} sessions •
                                                {{ event.speakers_count }} speakers
                                            </p>
                                            <p class="text-xs text-gray-400 mt-1 capitalize">{{ event.type }} • {{ event.status }}</p>
                                        </div>
                                        <div class="ml-4">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 text-green-800': event.status === 'published',
                                                    'bg-yellow-100 text-yellow-800': event.status === 'draft',
                                                    'bg-red-100 text-red-800': event.status === 'cancelled',
                                                    'bg-blue-100 text-blue-800': event.status === 'completed'
                                                }"
                                            >
                                                {{ event.status }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Recent CFS -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Calls for Speakers</h3>
                                <a
                                    :href="`/communities/${community.slug}/cfs`"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                >
                                    View all
                                </a>
                            </div>
                        </div>
                        <div v-if="recentCfs.length === 0" class="p-6 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No calls for speakers</h3>
                            <p class="mt-1 text-sm text-gray-500">Create your first call for speakers to find amazing speakers.</p>
                            <div class="mt-6">
                                <a
                                    :href="`/communities/${community.slug}/cfs/create`"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                                >
                                    Create CFS
                                </a>
                            </div>
                        </div>
                        <div v-else>
                            <ul class="divide-y divide-gray-200">
                                <li v-for="cfs in recentCfs" :key="cfs.id" class="p-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <a
                                                :href="`/communities/${community.slug}/cfs/${cfs.slug}`"
                                                class="text-sm font-medium text-gray-900 hover:text-indigo-600"
                                            >
                                                {{ cfs.title }}
                                            </a>
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ cfs.applications_count }} applications
                                            </p>
                                            <p v-if="cfs.closes_at" class="text-xs text-gray-400 mt-1">
                                                Closes: {{ new Date(cfs.closes_at).toLocaleDateString() }}
                                            </p>
                                        </div>
                                        <div class="ml-4">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 text-green-800': cfs.status === 'open',
                                                    'bg-yellow-100 text-yellow-800': cfs.status === 'draft',
                                                    'bg-red-100 text-red-800': cfs.status === 'closed',
                                                    'bg-gray-100 text-gray-800': cfs.status === 'archived'
                                                }"
                                            >
                                                {{ cfs.status }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
