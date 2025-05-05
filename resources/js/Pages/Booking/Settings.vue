<script setup>
import { ref, reactive } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    bookingPage: Object,
    accounts: Array,
    calendars: Array,
    bookingUrl: String,
});

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
    selected_calendars: props.bookingPage?.selected_calendars || [],
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

// Generate time options in local time
const timeOptions = [];
for (let hour = 0; hour < 24; hour++) {
    for (let minute = 0; minute < 60; minute += 30) {
        const h = hour.toString().padStart(2, '0');
        const m = minute.toString().padStart(2, '0');
        const timeValue = `${h}:${m}`;
        timeOptions.push({
            value: timeValue, // Store in 24h format for the server
            label: formatTimeForDisplay(timeValue) // Display in 12h format for the user
        });
    }
}

// Format time for display in user's local timezone
function formatTimeForDisplay(timeString) {
    // Create a date object using today's date and the time string
    const today = new Date();
    const [hours, minutes] = timeString.split(':');

    // Set the time components
    today.setHours(parseInt(hours, 10));
    today.setMinutes(parseInt(minutes, 10));

    // Format time in 12-hour format with AM/PM
    return today.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
}

// Convert UTC time to local time for display
function utcToLocal(timeString) {
    if (!timeString) return '';

    // Create a date using the current date and the UTC time
    const date = new Date();
    const [hours, minutes] = timeString.split(':');

    // Set UTC time
    date.setUTCHours(parseInt(hours, 10));
    date.setUTCMinutes(parseInt(minutes, 10));

    // Format time in 24-hour format for form value
    return `${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`;
}

// Convert local time to UTC for sending to server
function localToUtc(timeString) {
    if (!timeString) return '';

    // Create a date using the current date and the local time
    const date = new Date();
    const [hours, minutes] = timeString.split(':');

    // Set local time
    date.setHours(parseInt(hours, 10));
    date.setMinutes(parseInt(minutes, 10));

    // Format time in 24-hour UTC format
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
            .then(() => alert('Booking URL copied to clipboard!'))
            .catch(err => console.error('Could not copy URL: ', err));
    }
}

function submit() {
    const formData = { ...form };
    formData.start_time = localToUtc(form.start_time);
    formData.end_time = localToUtc(form.end_time);

    formData.post(route('booking.update-settings'));
}

if (props.bookingPage) {
    form.start_time = utcToLocal(props.bookingPage.start_time);
    form.end_time = utcToLocal(props.bookingPage.end_time);
}
</script>

<template>
    <AppLayout title="Booking Settings">
        <Head title="Booking Settings" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h1 class="text-2xl font-semibold mb-6">Booking Page Settings</h1>

                    <div v-if="bookingUrl" class="mb-8 p-4 bg-gray-50 rounded-lg">
                        <h2 class="text-lg font-medium mb-2">Your Booking Link</h2>
                        <div class="flex items-center">
                            <input
                                type="text"
                                :value="bookingUrl"
                                readonly
                                class="flex-1 p-2 border border-gray-300 rounded-md shadow-sm mr-2"
                            />
                            <button @click="copyBookingUrl" class="bg-indigo-600 text-white px-4 py-2 rounded-md">
                                Copy Link
                            </button>
                        </div>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h2 class="text-lg font-medium">Page Details</h2>

                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">Page Title</label>
                                    <input
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    ></textarea>
                                </div>

                                <div>
                                    <label for="slug" class="block text-sm font-medium text-gray-700">Page URL</label>
                                    <div class="mt-1 flex rounded-md shadow-sm">
                                        <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">
                                            {{ getBaseUrl() }}/book/
                                        </span>
                                        <input
                                            id="slug"
                                            v-model="form.slug"
                                            type="text"
                                            class="block w-full flex-1 rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        />
                                    </div>
                                    <div v-if="form.errors.slug" class="text-red-500 text-sm mt-1">{{ form.errors.slug }}</div>
                                </div>
                            </div>

                            <!-- Meeting Settings -->
                            <div class="space-y-4">
                                <h2 class="text-lg font-medium">Meeting Settings</h2>

                                <div>
                                    <label for="duration" class="block text-sm font-medium text-gray-700">Meeting Duration</label>
                                    <select
                                        id="duration"
                                        v-model="form.duration"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option v-for="option in durationOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.include_meet"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-700">Add Google Meet video conferencing</span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="buffer_before" class="block text-sm font-medium text-gray-700">Buffer Before (min)</label>
                                        <input
                                            id="buffer_before"
                                            v-model="form.buffer_before"
                                            type="number"
                                            min="0"
                                            max="60"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </div>

                                    <div>
                                        <label for="buffer_after" class="block text-sm font-medium text-gray-700">Buffer After (min)</label>
                                        <input
                                            id="buffer_after"
                                            v-model="form.buffer_after"
                                            type="number"
                                            min="0"
                                            max="60"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Availability Settings -->
                        <div class="mt-8 space-y-4">
                            <h2 class="text-lg font-medium">Availability Settings</h2>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Available Days</label>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <button
                                        v-for="day in dayLabels"
                                        :key="day.value"
                                        type="button"
                                        :class="[
                                            'px-3 py-2 rounded-md text-sm font-medium',
                                            form.available_days.includes(day.value)
                                                ? 'bg-indigo-100 text-indigo-800 border-indigo-300'
                                                : 'bg-gray-100 text-gray-800 border-gray-300'
                                        ]"
                                        @click="toggleDay(day.value)"
                                    >
                                        {{ day.label }}
                                    </button>
                                </div>
                                <div v-if="form.errors.available_days" class="text-red-500 text-sm mt-1">{{ form.errors.available_days }}</div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time (Your Local Time)</label>
                                    <select
                                        id="start_time"
                                        v-model="form.start_time"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option v-for="option in timeOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="end_time" class="block text-sm font-medium text-gray-700">End Time (Your Local Time)</label>
                                    <select
                                        id="end_time"
                                        v-model="form.end_time"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option v-for="option in timeOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Calendar Selection -->
                        <div class="mt-8 space-y-4">
                            <div class="flex justify-between items-center">
                                <h2 class="text-lg font-medium">Check Availability Against These Calendars</h2>
                                <div class="flex space-x-2">
                                    <button
                                        type="button"
                                        @click="selectAllCalendars"
                                        class="text-xs text-indigo-600 hover:text-indigo-800"
                                    >
                                        Select All
                                    </button>
                                    <button
                                        type="button"
                                        @click="clearAllCalendars"
                                        class="text-xs text-indigo-600 hover:text-indigo-800"
                                    >
                                        Clear All
                                    </button>
                                </div>
                            </div>

                            <p class="text-sm text-gray-600">
                                Select which calendars to check for availability. Bookings will only be offered when you're available across all selected calendars.
                            </p>

                            <div class="max-h-60 overflow-y-auto border rounded-md p-4">
                                <div v-if="accounts.length === 0" class="text-gray-500">
                                    No Google accounts connected yet.
                                </div>

                                <div v-else>
                                    <div v-for="account in accounts" :key="account.id" class="mb-4">
                                        <div class="flex items-center mb-2">
                                            <div
                                                class="w-4 h-4 rounded-full mr-2"
                                                :style="{ backgroundColor: account.color }"
                                            ></div>
                                            <span class="font-medium">{{ account.name }} ({{ account.email }})</span>
                                            <span v-if="account.is_primary" class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">
                                                Primary
                                            </span>
                                        </div>

                                        <div class="ml-6 space-y-2">
                                            <div v-for="calendar in calendars.filter(c => c.google_account_id === account.id)" :key="calendar.id">
                                                <label class="flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        :value="calendar.id"
                                                        v-model="form.selected_calendars"
                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    />
                                                    <span
                                                        class="w-3 h-3 rounded-full mx-2"
                                                        :style="{ backgroundColor: calendar.color }"
                                                    ></span>
                                                    <span class="text-sm">{{ calendar.name }}</span>
                                                    <span v-if="calendar.is_primary" class="ml-2 text-xs text-gray-500">(Primary)</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.selected_calendars" class="text-red-500 text-sm mt-1">{{ form.errors.selected_calendars }}</div>
                        </div>

                        <!-- Destination Calendar Selection -->
                        <div class="mt-6 space-y-4">
                            <h3 class="text-md font-medium">Destination Calendar (Optional)</h3>
                            <p class="text-sm text-gray-600">
                                Choose a calendar where new appointments will be added. If not specified, your primary account's default calendar will be used.
                            </p>

                            <div>
                                <select
                                    id="destination_calendar_id"
                                    v-model="form.destination_calendar_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Use primary account's default calendar</option>
                                    <optgroup
                                        v-for="account in accounts"
                                        :key="account.id"
                                        :label="account.name + ' (' + account.email + ')'"
                                    >
                                        <option
                                            v-for="calendar in calendars.filter(c => c.google_account_id === account.id)"
                                            :key="calendar.id"
                                            :value="calendar.id"
                                        >
                                            {{ calendar.name }}{{ calendar.is_primary ? ' (Primary)' : '' }}
                                        </option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                <span v-if="form.processing">Saving...</span>
                                <span v-else>Save Settings</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
