<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const accounts = ref([]);
const calendars = ref([]);
const upcomingEvents = ref([]);
const isLoading = ref(true);

onMounted(async () => {
    try {
        isLoading.value = true;

        const accountsResponse = await axios.get('/api/accounts');
        accounts.value = accountsResponse.data || [];

        const calendarsResponse = await axios.get('/api/calendars');
        calendars.value = calendarsResponse.data || [];

        const now = new Date();
        const nextWeek = new Date();
        nextWeek.setDate(now.getDate() + 7);

        const eventsResponse = await axios.get('/api/events', {
            params: {
                start: now.toISOString(),
                end: nextWeek.toISOString(),
                limit: 5
            }
        });
        upcomingEvents.value = eventsResponse.data || [];

    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    } finally {
        isLoading.value = false;
    }
});

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString(undefined, options);
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
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Calendar Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Welcome to CalSync</h3>
                        <p class="text-gray-600">
                            Your central hub for managing all your Google calendars in one place.
                        </p>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="isLoading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
                    <p class="mt-4 text-gray-600">Loading your calendar data...</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Connected Accounts -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-semibold text-gray-800">Connected Accounts</h3>
                                <a
                                    href="/connect/google"
                                    class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded">
                                    Connect
                                </a>
                            </div>

                            <div v-if="accounts.length === 0" class="text-gray-500 text-sm">
                                No Google accounts connected yet.
                            </div>

                            <div v-else>
                                <div v-for="account in accountsWithCalendarCount" :key="account.id"
                                     class="flex items-center mb-3 pb-3 border-b border-gray-100">
                                    <div class="h-8 w-8 rounded-full mr-3 flex items-center justify-center"
                                         :style="{ backgroundColor: account.color }">
                                        <span class="text-white font-bold">{{ account.name.charAt(0) }}</span>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ account.name }}</div>
                                        <div class="text-sm text-gray-500">{{ account.email }}</div>
                                    </div>
                                    <div class="ml-auto text-xs text-gray-500">
                                        {{ account.calendarCount }} calendars
                                        <span v-if="account.is_primary" class="ml-1 px-1 bg-gray-100 text-gray-600 rounded text-xs">Primary</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="font-semibold text-gray-800 mb-4">Upcoming Events</h3>

                            <div v-if="upcomingEvents.length === 0" class="text-gray-500 text-sm">
                                No upcoming events found.
                            </div>

                            <div v-else>
                                <div v-for="event in upcomingEvents" :key="event.id"
                                     class="mb-3 pb-3 border-b border-gray-100">
                                    <div class="flex items-center">
                                        <div class="w-2 h-10 rounded-full mr-3"
                                             :style="{ backgroundColor: event.color || event.calendar.color }"></div>
                                        <div>
                                            <div class="font-medium">{{ event.title }}</div>
                                            <div class="text-xs text-gray-500">{{ formatDate(event.starts_at) }}</div>
                                            <div class="text-xs text-gray-500">{{ event.calendar.name }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="font-semibold text-gray-800 mb-4">Quick Actions</h3>

                            <div class="space-y-3">
                                <Link
                                    href="/calendar"
                                    class="block w-full text-center py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">
                                    View Calendar
                                </Link>

                                <a
                                    href="/connect/google"
                                    class="block w-full text-center py-2 border border-indigo-600 text-indigo-600 hover:bg-indigo-50 rounded">
                                    Connect Account
                                </a>

                                <Link
                                    href="/settings"
                                    class="block w-full text-center py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">
                                    Manage Settings
                                </Link>

                                <Link
                                    href="/booking/settings"
                                    class="block w-full text-center py-2 border border-indigo-600 text-indigo-600 hover:bg-indigo-50 rounded">
                                    Manage Booking Page
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Tips -->
                <div v-if="accounts.length === 0" class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Getting Started</h3>
                        <ul class="mt-4 space-y-2 text-gray-600 list-disc pl-5">
                            <li>Connect your Google account using the "Connect" button above.</li>
                            <li>Your calendars will automatically sync with CalSync.</li>
                            <li>Go to the Calendar view to see all your events in one place.</li>
                            <li>Add multiple Google accounts to see all your calendars together.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
