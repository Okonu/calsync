<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from './Components/PageHeader.vue';
import SettingsCard from './Components/SettingsCard.vue';
import BookingLinkCard from './Components/BookingLinkCard.vue';
import AvailabilityCard from './Components/AvailabilityCard.vue';
import CalendarIntegrationCard from './Components/CalendarIntegrationCard.vue';
import NotificationToast from './Components/NotificationToast.vue';

const props = defineProps({
    bookingPage: Object,
    accounts: Array,
    calendars: Array,
    bookingUrl: String,
});

const activeTab = ref('details');

const showSuccessNotification = ref(false);
const notificationMessage = ref('');

const tabDescriptions = {
    details: "Configure basic page information and meeting settings",
    availability: "Set which days and times you're available for meetings",
    calendars: "Choose which calendars to check for conflicts and where to add bookings"
};

const allCalendarIds = props.calendars ? props.calendars.map(cal => cal.id) : [];

const form = useForm({
    title: props.bookingPage?.title || 'My Booking Page',
    description: props.bookingPage?.description || '',
    slug: props.bookingPage?.slug || '',
    duration: props.bookingPage?.duration || 30,
    destination_calendar_id: props.bookingPage?.destination_calendar_id || '',
    available_days: props.bookingPage?.available_days || [1, 2, 3, 4, 5],
    start_time: props.bookingPage?.start_time || '09:00',
    end_time: props.bookingPage?.end_time || '17:00',
    buffer_before: props.bookingPage?.buffer_before || 0,
    buffer_after: props.bookingPage?.buffer_after || 0,
    include_meet: props.bookingPage?.include_meet ?? true,
    selected_calendars: props.bookingPage?.selected_calendars || allCalendarIds,
});

onMounted(() => {
    if (!props.bookingPage || !props.bookingPage.id || !props.bookingPage.selected_calendars || props.bookingPage.selected_calendars.length === 0) {
        form.selected_calendars = allCalendarIds;
    }
});

const dayLabels = [
    { value: 0, label: 'Sunday' },
    { value: 1, label: 'Monday' },
    { value: 2, label: 'Tuesday' },
    { value: 3, label: 'Wednesday' },
    { value: 4, label: 'Thursday' },
    { value: 5, label: 'Friday' },
    { value: 6, label: 'Saturday' },
];

const durationOptions = [
    { value: 15, label: '15 minutes' },
    { value: 30, label: '30 minutes' },
    { value: 45, label: '45 minutes' },
    { value: 60, label: '1 hour' },
    { value: 90, label: '1.5 hours' },
    { value: 120, label: '2 hours' },
];

const timeOptions = [];
for (let hour = 0; hour < 24; hour++) {
    for (let minute = 0; minute < 60; minute += 30) {
        const h = hour.toString().padStart(2, '0');
        const m = minute.toString().padStart(2, '0');
        const timeValue = `${h}:${m}`;
        timeOptions.push({
            value: timeValue,
            label: formatTimeForDisplay(timeValue)r
        });
    }
}

function formatTimeForDisplay(timeString) {
    const today = new Date();
    const [hours, minutes] = timeString.split(':');

    today.setHours(parseInt(hours, 10));
    today.setMinutes(parseInt(minutes, 10));

    return today.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
}

function utcToLocal(timeString) {
    if (!timeString) return '';

    const date = new Date();
    const [hours, minutes] = timeString.split(':');

    date.setUTCHours(parseInt(hours, 10));
    date.setUTCMinutes(parseInt(minutes, 10));

    return `${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`;
}

function localToUtc(timeString) {
    if (!timeString) return '';

    const date = new Date();
    const [hours, minutes] = timeString.split(':');

    date.setHours(parseInt(hours, 10));
    date.setMinutes(parseInt(minutes, 10));

    return `${date.getUTCHours().toString().padStart(2, '0')}:${date.getUTCMinutes().toString().padStart(2, '0')}`;
}

function toggleDay(dayValue) {
    const index = form.available_days.indexOf(dayValue);
    if (index > -1) {
        form.available_days.splice(index, 1);
    } else {
        form.available_days.push(dayValue);
    }
}

function getBaseUrl() {
    return typeof window !== 'undefined' ? window.location.origin : '';
}

function selectAllCalendars() {
    form.selected_calendars = props.calendars.map(cal => cal.id);
}

function clearAllCalendars() {
    form.selected_calendars = [];
}

function copyBookingUrl() {
    if (props.bookingUrl && typeof navigator !== 'undefined') {
        navigator.clipboard.writeText(props.bookingUrl)
            .then(() => {
                showNotification('Booking link copied to clipboard!');
            })
            .catch(err => console.error('Could not copy URL: ', err));
    }
}

function showNotification(message) {
    notificationMessage.value = message;
    showSuccessNotification.value = true;

    setTimeout(() => {
        showSuccessNotification.value = false;
    }, 3000);
}

function submit() {
    const formData = { ...form };
    formData.start_time = localToUtc(form.start_time);
    formData.end_time = localToUtc(form.end_time);

    formData.post(route('booking.update-settings'), {
        onSuccess: () => {
            showNotification('Settings saved successfully!');
        }
    });
}

if (props.bookingPage) {
    form.start_time = utcToLocal(props.bookingPage.start_time);
    form.end_time = utcToLocal(props.bookingPage.end_time);
}
</script>

<template>
    <AppLayout title="Booking Settings">
        <Head title="Booking Settings" />

        <div class="py-4 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <PageHeader
                    title="Booking Settings"
                    subtitle="Configure your booking page and availability"
                    :is-processing="form.processing"
                    @save="submit"
                />

                <!-- Main Content -->
                <div class="mt-4 grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Left Column -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Booking Link Card -->
                        <BookingLinkCard
                            v-if="bookingUrl"
                            :booking-url="bookingUrl"
                            @copy="copyBookingUrl"
                        />

                        <!-- Tabs Navigation Card -->
                        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Navigation</h3>
                            </div>

                            <div class="p-1">
                                <nav class="space-y-1" aria-label="Settings Navigation">
                                    <button
                                        @click="activeTab = 'details'"
                                        class="w-full text-left px-3 py-3 rounded-md transition-colors"
                                        :class="activeTab === 'details' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50'"
                                    >
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <span class="block font-medium">Page Details</span>
                                                <span class="block text-xs text-gray-500 mt-0.5">{{ tabDescriptions.details }}</span>
                                            </div>
                                        </div>
                                    </button>

                                    <button
                                        @click="activeTab = 'availability'"
                                        class="w-full text-left px-3 py-3 rounded-md transition-colors"
                                        :class="activeTab === 'availability' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50'"
                                    >
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <span class="block font-medium">Availability</span>
                                                <span class="block text-xs text-gray-500 mt-0.5">{{ tabDescriptions.availability }}</span>
                                            </div>
                                        </div>
                                    </button>

                                    <button
                                        @click="activeTab = 'calendars'"
                                        class="w-full text-left px-3 py-3 rounded-md transition-colors relative"
                                        :class="activeTab === 'calendars' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50'"
                                    >
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <span class="block font-medium">Calendars</span>
                                                <span class="block text-xs text-gray-500 mt-0.5">{{ tabDescriptions.calendars }}</span>
                                            </div>
                                        </div>

                                        <span
                                            v-if="form.selected_calendars.length > 0"
                                            class="absolute right-3 top-3 bg-indigo-100 text-indigo-800 py-0.5 px-2 rounded-full text-xs"
                                        >
                                            {{ form.selected_calendars.length }}
                                        </span>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="lg:col-span-3">
                        <form @submit.prevent="submit">
                            <!-- Page Details Tab -->
                            <div v-show="activeTab === 'details'">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Page Details Card -->
                                    <SettingsCard title="Page Details" icon="document">
                                        <div class="space-y-4">
                                            <div>
                                                <label for="title" class="block text-sm font-medium text-gray-700">Page Title</label>
                                                <input
                                                    id="title"
                                                    v-model="form.title"
                                                    type="text"
                                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="e.g. Coffee Chat with Jane"
                                                />
                                                <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                                            </div>

                                            <div>
                                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                                <textarea
                                                    id="description"
                                                    v-model="form.description"
                                                    rows="3"
                                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="Briefly describe what this meeting is about"
                                                ></textarea>
                                            </div>

                                            <div>
                                                <label for="slug" class="block text-sm font-medium text-gray-700">Page URL</label>
                                                <div class="mt-1 flex rounded-lg shadow-sm">
                                                    <span class="inline-flex items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">
                                                        {{ getBaseUrl() }}/book/
                                                    </span>
                                                    <input
                                                        id="slug"
                                                        v-model="form.slug"
                                                        type="text"
                                                        class="block w-full flex-1 rounded-none rounded-r-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                        placeholder="your-meeting-name"
                                                    />
                                                </div>
                                                <div v-if="form.errors.slug" class="text-red-500 text-sm mt-1">{{ form.errors.slug }}</div>
                                            </div>
                                        </div>
                                    </SettingsCard>

                                    <!-- Meeting Settings Card -->
                                    <SettingsCard title="Meeting Settings" icon="clock">
                                        <div class="space-y-4">
                                            <div>
                                                <label for="duration" class="block text-sm font-medium text-gray-700">Meeting Duration</label>
                                                <select
                                                    id="duration"
                                                    v-model="form.duration"
                                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                >
                                                    <option v-for="option in durationOptions" :key="option.value" :value="option.value">
                                                        {{ option.label }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="flex items-center p-3 bg-indigo-50 rounded-lg">
                                                <input
                                                    id="include_meet"
                                                    type="checkbox"
                                                    v-model="form.include_meet"
                                                    class="h-5 w-5 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                />
                                                <label for="include_meet" class="ml-3 text-sm font-medium text-gray-700">
                                                    Add Google Meet video conferencing
                                                </label>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label for="buffer_before" class="block text-sm font-medium text-gray-700">Buffer Before</label>
                                                    <div class="mt-1 relative rounded-md shadow-sm">
                                                        <input
                                                            id="buffer_before"
                                                            v-model="form.buffer_before"
                                                            type="number"
                                                            min="0"
                                                            max="60"
                                                            class="block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                        />
                                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                            <span class="text-gray-500 sm:text-sm">min</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label for="buffer_after" class="block text-sm font-medium text-gray-700">Buffer After</label>
                                                    <div class="mt-1 relative rounded-md shadow-sm">
                                                        <input
                                                            id="buffer_after"
                                                            v-model="form.buffer_after"
                                                            type="number"
                                                            min="0"
                                                            max="60"
                                                            class="block w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                                        />
                                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                            <span class="text-gray-500 sm:text-sm">min</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </SettingsCard>
                                </div>
                            </div>

                            <!-- Availability Tab -->
                            <div v-show="activeTab === 'availability'">
                                <AvailabilityCard
                                    title="Availability Settings"
                                    :days="dayLabels"
                                    :selected-days="form.available_days"
                                    :time-options="timeOptions"
                                    v-model:start-time="form.start_time"
                                    v-model:end-time="form.end_time"
                                    @toggle-day="toggleDay"
                                />
                            </div>

                            <!-- Calendars Tab -->
                            <div v-show="activeTab === 'calendars'">
                                <CalendarIntegrationCard
                                    :accounts="accounts"
                                    :calendars="calendars"
                                    v-model:selected-calendars="form.selected_calendars"
                                    v-model:destination-calendar-id="form.destination_calendar_id"
                                    @select-all="selectAllCalendars"
                                    @clear-all="clearAllCalendars"
                                />
                            </div>

                            <div class="mt-6 lg:hidden">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full flex justify-center items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-medium text-white shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors"
                                >
                                    <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" v-if="form.processing">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor" v-else>
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span v-if="form.processing">Saving...</span>
                                    <span v-else>Save Settings</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <NotificationToast
                    :show="showSuccessNotification"
                    :message="notificationMessage"
                    type="success"
                />
            </div>
        </div>
    </AppLayout>
</template>
