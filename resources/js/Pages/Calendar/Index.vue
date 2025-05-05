<script setup>
import {computed, onMounted, ref, watch} from 'vue';
import {Head, Link} from '@inertiajs/vue3';
import axios from 'axios';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import momentPlugin from '@fullcalendar/moment';
import AppLayout from '@/Layouts/AppLayout.vue';
import EventDetails from './Partials/EventDetails.vue';
import ColorPicker from './Partials/ColorPicker.vue';

const props = defineProps({
    accounts: Array,
    calendars: Array,
});

const calendarRef = ref(null);
const selectedCalendars = ref(props.calendars.filter(cal => cal.is_visible).map(cal => cal.id));
const selectedAccounts = ref(props.accounts.map(acc => acc.id)); // All accounts selected by default
const selectedView = ref('dayGridMonth');
const eventDetailsVisible = ref(false);
const selectedEvent = ref(null);
const isLoading = ref(false);

const calendarsByAccount = computed(() => {
    const grouped = {};

    props.accounts.forEach(account => {
        grouped[account.id] = {
            account: account,
            calendars: props.calendars.filter(cal => cal.google_account_id === account.id)
        };
    });

    return grouped;
});

const urls = {
    googleRedirect: '/auth/google',
    googleConnectRedirect: '/connect/google',
    accountSettings: '/app/account-settings'
};

const localTimezone = computed(() => {
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    if (timezone.includes('/')) {
        return timezone.split('/').pop().replace(/_/g, ' ');
    }
    return timezone;
});

const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, momentPlugin],
    initialView: selectedView.value,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    events: fetchEvents,
    eventClick: handleEventClick,
    eventTimeFormat: {
        hour: 'numeric',
        minute: '2-digit',
        meridiem: 'short'
    },
    dayMaxEvents: true,
    views: {
        timeGrid: {
            dayMaxEventRows: 6
        }
    },
    height: 'auto',
    timeZone: 'local',
}));

function fetchEvents(info, successCallback, failureCallback) {
    isLoading.value = true;

    axios.get('/api/events', {
        params: {
            start: info.startStr,
            end: info.endStr,
            calendars: selectedCalendars.value,
            accounts: selectedAccounts.value
        }
    })
        .then(response => {
            successCallback(response.data);
            isLoading.value = false;
        })
        .catch(error => {
            failureCallback(error);
            isLoading.value = false;
        });
}

function handleEventClick(info) {
    axios.get(`/api/events/${info.event.id}`)
        .then(response => {
            selectedEvent.value = response.data;
            eventDetailsVisible.value = true;
        })
        .catch(error => {
            console.error('Failed to load event details', error);
        });
}

function closeEventDetails() {
    eventDetailsVisible.value = false;
    selectedEvent.value = null;
}

function refreshCalendar() {
    if (calendarRef.value) {
        calendarRef.value.getApi().refetchEvents();
    }
}

function toggleCalendarVisibility(calendarId, isVisible) {
    const index = selectedCalendars.value.indexOf(calendarId);

    if (isVisible && index === -1) {
        selectedCalendars.value.push(calendarId);
    } else if (!isVisible && index !== -1) {
        selectedCalendars.value.splice(index, 1);
    }

    axios.patch(`/api/calendars/${calendarId}/visibility`, {
        is_visible: isVisible
    });

    refreshCalendar();
}

function toggleAccountVisibility(accountId, isVisible) {
    const accountIndex = selectedAccounts.value.indexOf(accountId);

    if (isVisible && accountIndex === -1) {
        selectedAccounts.value.push(accountId);
    } else if (!isVisible && accountIndex !== -1) {
        selectedAccounts.value.splice(accountIndex, 1);
    }

    const accountCalendars = props.calendars.filter(cal => cal.google_account_id === accountId);

    accountCalendars.forEach(calendar => {
        const calendarIndex = selectedCalendars.value.indexOf(calendar.id);

        if (isVisible && calendarIndex === -1) {
            selectedCalendars.value.push(calendar.id);
        } else if (!isVisible && calendarIndex !== -1) {
            selectedCalendars.value.splice(calendarIndex, 1);
        }
    });

    refreshCalendar();
}

function updateCalendarColor(calendarId, newColor) {
    axios.patch(`/api/calendars/${calendarId}/color`, {
        color: newColor
    })
        .then(() => {
            const calendarIndex = props.calendars.findIndex(cal => cal.id === calendarId);
            if (calendarIndex !== -1) {
                props.calendars[calendarIndex].color = newColor;
            }

            refreshCalendar();
        })
        .catch(error => {
            console.error('Failed to update calendar color', error);
        });
}

function viewChanged(newView) {
    selectedView.value = newView;
    if (calendarRef.value) {
        calendarRef.value.getApi().changeView(newView);
    }
}

watch([selectedCalendars, selectedAccounts], () => {
    refreshCalendar();
});

onMounted(() => {
    //
});
</script>

<template>
    <AppLayout title="Calendar">
        <Head title="Calendar" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-wrap md:flex-nowrap gap-4">
                    <!-- Sidebar -->
                    <div class="w-full md:w-1/4 space-y-4">
                        <!-- Accounts Section -->
                        <div class="bg-white shadow rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-2">Your Accounts</h2>

                            <div v-for="account in accounts" :key="account.id" class="mb-3">
                                <div class="flex items-center">
                                    <!-- Add account toggle checkbox -->
                                    <input
                                        type="checkbox"
                                        :id="`account-${account.id}`"
                                        :checked="selectedAccounts.includes(account.id)"
                                        @change="toggleAccountVisibility(account.id, $event.target.checked)"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    />
                                    <label :for="`account-${account.id}`" class="ml-2 flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center"
                                             :style="{ backgroundColor: account.color }">
                                            <span class="text-white font-bold">{{ account.name.charAt(0) }}</span>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium">{{ account.name }}</p>
                                            <p class="text-xs text-gray-500">{{ account.email }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-between">
                                <a href="/connect/google"
                                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                    </svg>
                                    Add Account
                                </a>
<!--                                <Link :href="urls.accountSettings" class="text-sm text-indigo-600 hover:text-indigo-500">-->
<!--                                    Manage-->
<!--                                </Link>-->
                            </div>
                        </div>

                        <!-- Calendars Section -->
                        <div class="bg-white shadow rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-2">Calendars</h2>

                            <!-- Group calendars by account -->
                            <div v-for="(group, accountId) in calendarsByAccount" :key="accountId" class="mb-4">
                                <div class="flex items-center mb-2">
                                    <div class="flex-shrink-0 h-6 w-6 rounded-full flex items-center justify-center"
                                         :style="{ backgroundColor: group.account.color }">
                                        <span class="text-white font-bold text-xs">{{ group.account.name.charAt(0) }}</span>
                                    </div>
                                    <span class="ml-2 text-sm font-medium">{{ group.account.name }}</span>
                                </div>

                                <div v-for="calendar in group.calendars" :key="calendar.id" class="mb-3 pl-4">
                                    <div class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :id="`calendar-${calendar.id}`"
                                            :checked="selectedCalendars.includes(calendar.id)"
                                            @change="toggleCalendarVisibility(calendar.id, $event.target.checked)"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        />
                                        <label :for="`calendar-${calendar.id}`" class="ml-2 flex items-center">
                                            <div class="flex-shrink-0 h-3 w-3 rounded-full mr-2"
                                                 :style="{ backgroundColor: calendar.color }"></div>
                                            <div>
                                                <div class="text-sm font-medium">{{ calendar.name }}</div>
                                            </div>
                                        </label>

                                        <ColorPicker
                                            :initial-color="calendar.color"
                                            @color-selected="updateCalendarColor(calendar.id, $event)"
                                            class="ml-auto"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div v-if="calendars.length === 0" class="text-gray-500 text-sm py-2">
                                No calendars found. Connect a Google account to see your calendars.
                            </div>

                            <div v-if="calendars.length > 0" class="mt-2 border-t pt-2 flex justify-between">
                                <button
                                    @click="selectedCalendars = calendars.map(c => c.id); selectedAccounts = accounts.map(a => a.id);"
                                    class="text-xs text-indigo-600 hover:text-indigo-500">
                                    Select All
                                </button>
                                <button
                                    @click="selectedCalendars = []; selectedAccounts = [];"
                                    class="text-xs text-indigo-600 hover:text-indigo-500">
                                    Clear All
                                </button>
                            </div>
                        </div>

                        <!-- View Options -->
                        <div class="bg-white shadow rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-2">View</h2>

                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input
                                        type="radio"
                                        id="view-month"
                                        value="dayGridMonth"
                                        v-model="selectedView"
                                        @change="viewChanged('dayGridMonth')"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    />
                                    <label for="view-month" class="ml-2 text-sm">
                                        Month
                                    </label>
                                </div>

                                <div class="flex items-center">
                                    <input
                                        type="radio"
                                        id="view-week"
                                        value="timeGridWeek"
                                        v-model="selectedView"
                                        @change="viewChanged('timeGridWeek')"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    />
                                    <label for="view-week" class="ml-2 text-sm">
                                        Week
                                    </label>
                                </div>

                                <div class="flex items-center">
                                    <input
                                        type="radio"
                                        id="view-day"
                                        value="timeGridDay"
                                        v-model="selectedView"
                                        @change="viewChanged('timeGridDay')"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    />
                                    <label for="view-day" class="ml-2 text-sm">
                                        Day
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar -->
                    <div class="w-full md:w-3/4">
                        <div class="bg-white shadow rounded-lg p-4 relative">
                            <!-- Loading Overlay -->
                            <div v-if="isLoading" class="absolute inset-0 bg-white/50 flex items-center justify-center rounded-lg z-10">
                                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
                            </div>

                            <div class="mb-2 text-sm text-gray-500 flex justify-end items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Times shown in {{ localTimezone }}</span>
                            </div>

                            <!-- Calendar Component -->
                            <FullCalendar
                                ref="calendarRef"
                                :options="calendarOptions"
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Details Modal -->
        <EventDetails
            v-if="eventDetailsVisible"
            :event="selectedEvent"
            @close="closeEventDetails"
        />
    </AppLayout>
</template>

<style>
.fc-theme-standard td, .fc-theme-standard th {
    @apply border-gray-200;
}

.fc .fc-toolbar {
    @apply flex-wrap gap-2;
}

.fc .fc-button-primary {
    @apply bg-indigo-600 border-indigo-600 shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-2 focus:ring-offset-2;
}

.fc .fc-button-primary:disabled {
    @apply bg-indigo-400 border-indigo-400;
}

.fc .fc-button-primary:not(:disabled).fc-button-active,
.fc .fc-button-primary:not(:disabled):active {
    @apply bg-indigo-800 border-indigo-800 shadow-none;
}

.fc .fc-day-today {
    @apply bg-indigo-50;
}

.fc-event {
    @apply cursor-pointer;
}
</style>
