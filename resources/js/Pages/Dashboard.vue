<script setup>
import AuthenticatedLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    communities: Array,
});

const accounts = ref([]);
const calendars = ref([]);
const upcomingEvents = ref([]);
const isLoading = ref(true);
const todayEvents = ref([]);
const welcomeMessage = ref('');
const stats = ref({
    totalEvents: 0,
    todayEvents: 0,
    totalCalendars: 0,
    totalCommunities: 0
});
const showGettingStarted = ref(true);

function getGreeting() {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
}

onMounted(async () => {
    try {
        isLoading.value = true;
        welcomeMessage.value = getGreeting();

        const [accountsResponse, calendarsResponse, upcomingResponse, todayResponse] = await Promise.all([
            axios.get('/api/accounts'),
            axios.get('/api/calendars'),
            fetchUpcomingEvents(),
            fetchTodayEvents()
        ]);

        accounts.value = accountsResponse.data || [];
        calendars.value = calendarsResponse.data || [];
        upcomingEvents.value = upcomingResponse.data || [];
        todayEvents.value = todayResponse.data || [];

        stats.value = {
            totalEvents: upcomingEvents.value.length + todayEvents.value.length,
            todayEvents: todayEvents.value.length,
            totalCalendars: calendars.value.length,
            totalCommunities: props.communities?.length || 0
        };

        if (accounts.value.length > 0) {
            showGettingStarted.value = false;
        }

    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    } finally {
        isLoading.value = false;
    }
});

async function fetchUpcomingEvents() {
    const now = new Date();
    const nextWeek = new Date();
    nextWeek.setDate(now.getDate() + 7);

    return axios.get('/api/events', {
        params: {
            start: formatDateForAPI(now),
            end: formatDateForAPI(nextWeek),
            limit: 5
        }
    });
}

async function fetchTodayEvents() {
    const today = new Date();
    const tomorrow = new Date();
    tomorrow.setDate(today.getDate() + 1);
    tomorrow.setHours(0, 0, 0, 0);

    return axios.get('/api/events', {
        params: {
            start: formatDateForAPI(today),
            end: formatDateForAPI(tomorrow),
            limit: 10
        }
    });
}

function formatDateForAPI(date) {
    return date.toISOString();
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString(undefined, options);
}

function formatTimeOnly(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function getTimeRemaining(dateString) {
    const eventTime = new Date(dateString);
    const now = new Date();
    const diff = eventTime - now;

    if (diff < 0) return "Ongoing";

    const minutes = Math.floor(diff / 1000 / 60);
    if (minutes < 60) return `${minutes} min`;

    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} hr`;

    const days = Math.floor(hours / 24);
    return `${days} days`;
}

function getEventStatusClass(dateString) {
    const eventTime = new Date(dateString);
    const now = new Date();
    const diff = eventTime - now;

    if (diff < 0) return "bg-indigo-100 text-indigo-800";

    const minutes = Math.floor(diff / 1000 / 60);
    if (minutes < 60) return "bg-yellow-100 text-yellow-800";

    return "bg-green-100 text-green-800";
}

const accountsWithCalendarCount = computed(() => {
    return accounts.value.map(account => {
        const accountCalendars = calendars.value.filter(cal => cal.google_account_id === account.id);
        return {
            ...account,
            calendarCount: accountCalendars.length
        };
    });
});

const hasNoAccounts = computed(() => {
    return !isLoading.value && accounts.value.length === 0;
});

const hasNoEvents = computed(() => {
    return !isLoading.value && upcomingEvents.value.length === 0 && todayEvents.value.length === 0;
});

const hasNoCommunities = computed(() => {
    return !props.communities || props.communities.length === 0;
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
                <div class="flex space-x-3">
                    <Link
                        href="/calendar"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                        </svg>
                        View Calendar
                    </Link>
                    <Link
                        href="/communities"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                        </svg>
                        Communities
                    </Link>
                    <Link
                        href="/help"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        Help
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <div class="overflow-hidden bg-gradient-to-r from-indigo-500 to-purple-600 shadow-sm sm:rounded-lg mb-6 text-white">
                    <div class="p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-xl font-bold mb-2">{{ welcomeMessage }}, {{ $page.props.auth.user.name }}!</h3>
                                <p class="text-indigo-100">
                                    Manage your calendar, communities, and events from one place.
                                </p>
                            </div>
                            <div class="hidden md:block">
                                <div class="text-right">
                                    <div class="text-3xl font-bold">{{ new Date().toLocaleDateString(undefined, { weekday: 'long' }) }}</div>
                                    <div>{{ new Date().toLocaleDateString(undefined, { month: 'long', day: 'numeric', year: 'numeric' }) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-5 bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200">
                            <div class="flex justify-between items-center">
                                <h3 class="font-semibold text-green-800">Today's Events</h3>
                                <div class="bg-green-200 text-green-800 text-xl font-bold rounded-full h-10 w-10 flex items-center justify-center">
                                    {{ stats.todayEvents }}
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <Link href="/calendar" class="text-green-600 hover:text-green-800 text-sm font-medium flex items-center">
                                <span>View today's schedule</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-5 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                            <div class="flex justify-between items-center">
                                <h3 class="font-semibold text-blue-800">Connected Calendars</h3>
                                <div class="bg-blue-200 text-blue-800 text-xl font-bold rounded-full h-10 w-10 flex items-center justify-center">
                                    {{ stats.totalCalendars }}
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <Link href="/booking/settings" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                <span>Manage booking page</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-5 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-purple-200">
                            <div class="flex justify-between items-center">
                                <h3 class="font-semibold text-purple-800">My Communities</h3>
                                <div class="bg-purple-200 text-purple-800 text-xl font-bold rounded-full h-10 w-10 flex items-center justify-center">
                                    {{ stats.totalCommunities }}
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <Link href="/communities" class="text-purple-600 hover:text-purple-800 text-sm font-medium flex items-center">
                                <span>Manage communities</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-5 bg-gradient-to-r from-orange-50 to-orange-100 border-b border-orange-200">
                            <div class="flex justify-between items-center">
                                <h3 class="font-semibold text-orange-800">Connected Accounts</h3>
                                <div class="bg-orange-200 text-orange-800 text-xl font-bold rounded-full h-10 w-10 flex items-center justify-center">
                                    {{ accounts.length }}
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <a href="/connect/google" class="text-orange-600 hover:text-orange-800 text-sm font-medium flex items-center">
                                <span>Connect Google account</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="isLoading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
                    <p class="mt-4 text-gray-600">Loading your data...</p>
                </div>

                <div v-else>
                    <!-- Communities Quick View -->
                    <div v-if="!hasNoCommunities" class="mb-6 bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                            <div class="flex justify-between items-center">
                                <h3 class="font-semibold text-gray-800 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                    </svg>
                                    My Communities
                                </h3>
                                <Link href="/communities" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                    View All
                                </Link>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div
                                    v-for="community in communities"
                                    :key="community.id"
                                    class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-indigo-300 transition-colors"
                                >
                                    <img
                                        :src="community.logo_url"
                                        :alt="community.name"
                                        class="w-10 h-10 rounded-full mr-3"
                                    >
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ community.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ community.events_count }} events
                                        </p>
                                    </div>
                                    <Link
                                        :href="community.dashboard_url"
                                        class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                    >
                                        Manage
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No Accounts Warning -->
                    <div v-if="hasNoAccounts" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">No accounts connected</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Connect your Google account to start syncing your calendars and using all features.</p>
                                </div>
                                <div class="mt-3">
                                    <a
                                        href="/connect/google"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                    >
                                        Connect Google Account
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <!-- Main Content Area -->
                        <div class="md:col-span-8 space-y-6">
                            <!-- Today's Schedule -->
                            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                                    <h3 class="font-semibold text-gray-800 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                        Today's Schedule
                                    </h3>
                                </div>

                                <div v-if="todayEvents.length === 0" class="p-6 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No events today</h3>
                                    <p class="mt-1 text-sm text-gray-500">Enjoy your free day! Or schedule something new.</p>
                                </div>

                                <div v-else class="divide-y divide-gray-100">
                                    <div v-for="event in todayEvents" :key="event.id" class="p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center">
                                            <div class="w-1 h-12 rounded-full mr-4" :style="{ backgroundColor: event.color || event.calendar.color }"></div>
                                            <div class="flex-grow">
                                                <div class="font-medium">{{ event.title }}</div>
                                                <div class="text-sm text-gray-500 flex items-center">
                                                    <span>{{ formatTimeOnly(event.starts_at) }} - {{ formatTimeOnly(event.ends_at) }}</span>
                                                    <span class="mx-2">•</span>
                                                    <span>{{ event.calendar.name }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                                    :class="getEventStatusClass(event.starts_at)"
                                                >
                                                    {{ getTimeRemaining(event.starts_at) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 px-6 py-3 text-right">
                                    <Link
                                        href="/calendar"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-900 inline-flex items-center"
                                    >
                                        View full calendar
                                        <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>
                                </div>
                            </div>

                            <!-- Upcoming Events -->
                            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                                    <h3 class="font-semibold text-gray-800 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                        </svg>
                                        Upcoming Events
                                    </h3>
                                </div>

                                <div v-if="upcomingEvents.length === 0" class="p-6 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No upcoming events</h3>
                                    <p class="mt-1 text-sm text-gray-500">Your calendar is clear for the next few days.</p>
                                </div>

                                <div v-else class="divide-y divide-gray-100">
                                    <div v-for="event in upcomingEvents" :key="event.id" class="p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center">
                                            <div class="w-1 h-12 rounded-full mr-4" :style="{ backgroundColor: event.color || event.calendar.color }"></div>
                                            <div class="flex-grow">
                                                <div class="font-medium">{{ event.title }}</div>
                                                <div class="text-sm text-gray-500">{{ formatDate(event.starts_at) }}</div>
                                                <div class="text-xs text-gray-400">{{ event.calendar.name }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="md:col-span-4 space-y-6">
                            <!-- Connected Accounts -->
                            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                                    <div class="flex justify-between items-center">
                                        <h3 class="font-semibold text-gray-800 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                            </svg>
                                            Connected Accounts
                                        </h3>
                                        <a
                                            href="/connect/google"
                                            class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                                            Add
                                        </a>
                                    </div>
                                </div>

                                <div class="p-4">
                                    <div v-if="accounts.length === 0" class="text-gray-500 text-sm">
                                        No Google accounts connected yet.
                                    </div>

                                    <div v-else>
                                        <div v-for="account in accountsWithCalendarCount" :key="account.id"
                                             class="flex items-center mb-3 pb-3 border-b border-gray-100 last:border-b-0 last:pb-0 last:mb-0">
                                            <div class="h-8 w-8 rounded-full mr-3 flex items-center justify-center"
                                                 :style="{ backgroundColor: account.color }">
                                                <span class="text-white font-bold">{{ account.name.charAt(0) }}</span>
                                            </div>
                                            <div class="flex-grow">
                                                <div class="font-medium">{{ account.name }}</div>
                                                <div class="text-sm text-gray-500 flex items-center">
                                                    <span class="truncate max-w-[150px]">{{ account.email }}</span>
                                                    <span v-if="account.is_primary" class="ml-1 px-1 bg-gray-100 text-gray-600 rounded text-xs">Primary</span>
                                                </div>
                                            </div>
                                            <div class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">
                                                {{ account.calendarCount }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                                    <h3 class="font-semibold text-gray-800 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd" />
                                        </svg>
                                        Quick Actions
                                    </h3>
                                </div>

                                <div class="p-4 space-y-3">
                                    <Link
                                        href="/calendar"
                                        class="flex items-center p-3 bg-indigo-50 hover:bg-indigo-100 rounded-md transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm font-medium">View Calendar</span>
                                    </Link>

                                    <Link
                                        href="/communities"
                                        class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-md transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                        </svg>
                                        <span class="text-sm font-medium">Manage Communities</span>
                                    </Link>

                                    <Link
                                        href="/booking/settings"
                                        class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-md transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm font-medium">Booking Page Settings</span>
                                    </Link>

                                    <a
                                        href="/connect/google"
                                        class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-md transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm font-medium">Connect Account</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Getting Started Guide -->
                    <div v-if="showGettingStarted || hasNoCommunities" class="mt-6 bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                            <h3 class="font-semibold text-gray-800 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                                </svg>
                                {{ hasNoCommunities ? 'Get Started with Communities' : 'Getting Started' }}
                            </h3>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                <div class="text-center">
                                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-100 text-indigo-600 mx-auto mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                                        {{ hasNoCommunities ? 'Create Your First Community' : '1. Connect Your Account' }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        {{ hasNoCommunities ? 'Build your community for events and call for speakers.' : 'Start by connecting your Google account to sync your calendars.' }}
                                    </p>
                                    <div class="mt-4">
                                        <Link
                                            :href="hasNoCommunities ? '/communities/create' : '/connect/google'"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                                        >
                                            {{ hasNoCommunities ? 'Create Community' : 'Connect Google' }}
                                        </Link>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-100 text-indigo-600 mx-auto mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                                        {{ hasNoCommunities ? 'Create Events & CFS' : '2. Choose Your Calendars' }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        {{ hasNoCommunities ? 'Set up events and call for speakers for your community.' : 'Select which calendars you want to manage and display.' }}
                                    </p>
                                    <div class="mt-4">
                                        <Link
                                            :href="hasNoCommunities ? '/help' : '/calendar'"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                                        >
                                            {{ hasNoCommunities ? 'Learn More' : 'View Calendars' }}
                                        </Link>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-100 text-indigo-600 mx-auto mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                                        {{ hasNoCommunities ? 'Manage Your Community' : '3. Set Up Booking Page' }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        {{ hasNoCommunities ? 'Review applications, manage speakers, and publish events.' : 'Create a booking page so others can schedule meetings with you.' }}
                                    </p>
                                    <div class="mt-4">
                                        <Link
                                            :href="hasNoCommunities ? '/communities' : '/booking/settings'"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                                        >
                                            {{ hasNoCommunities ? 'View Communities' : 'Booking Settings' }}
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-center">
                                <Link href="/help" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                    Learn more in our Help Center
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
