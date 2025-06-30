<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    community: Object,
    availableCfs: Array,
    eventTypes: Object,
});

const form = useForm({
    title: '',
    description: '',
    type: 'meetup',
    starts_at: '',
    ends_at: '',
    location: '',
    meeting_platform: '', // 'zoom', 'google_meet', 'teams', 'custom', 'physical'
    meeting_link: '',
    auto_create_google_meet: true,
    create_calendar_event: true,
    is_online: true,
    is_recurring: false,
    recurrence_settings: {
        frequency: 'weekly',
        interval: 1,
        days_of_week: [],
        end_date: '',
    },
    max_attendees: null,
    requires_approval: false,
    is_public: true,
    speaker_requirements: '',
    call_for_speakers_id: null,
    sessions: [],
});

const showSessionsSection = ref(false);
const showRecurrenceSettings = ref(false);
const isCreatingCalendarEvent = ref(false);

const eventTypeDescriptions = {
    webinar: 'Online presentation or workshop',
    workshop: 'Hands-on learning session',
    study_jam: 'Group study session',
    meetup: 'Casual community gathering',
    conference: 'Multi-session event',
    other: 'Custom event type',
};

const meetingPlatforms = [
    {
        value: 'google_meet',
        label: 'Google Meet',
        description: 'Auto-generate Google Meet link with calendar event',
        icon: '📹',
        requiresCalendar: true
    },
    {
        value: 'zoom',
        label: 'Zoom',
        description: 'Enter your Zoom meeting link',
        icon: '🔵',
        requiresCalendar: false
    },
    {
        value: 'teams',
        label: 'Microsoft Teams',
        description: 'Enter your Teams meeting link',
        icon: '💜',
        requiresCalendar: false
    },
    {
        value: 'webex',
        label: 'Cisco Webex',
        description: 'Enter your Webex meeting link',
        icon: '🟢',
        requiresCalendar: false
    },
    {
        value: 'discord',
        label: 'Discord',
        description: 'Enter your Discord server invite',
        icon: '🎮',
        requiresCalendar: false
    },
    {
        value: 'custom',
        label: 'Other Platform',
        description: 'Enter custom meeting link',
        icon: '🔗',
        requiresCalendar: false
    },
];

const physicalLocationSuggestions = [
    'Conference Center',
    'University Campus',
    'Co-working Space',
    'Office Building',
    'Hotel Conference Room',
    'Community Center',
    'Library Meeting Room',
    'Innovation Hub',
    'Tech Incubator',
    'Startup Office',
];

// Computed properties
const selectedPlatform = computed(() => {
    return meetingPlatforms.find(p => p.value === form.meeting_platform);
});

const showMeetingLinkInput = computed(() => {
    return form.is_online &&
        form.meeting_platform &&
        form.meeting_platform !== 'google_meet';
});

const showGoogleMeetOptions = computed(() => {
    return form.is_online &&
        form.meeting_platform === 'google_meet' &&
        props.community.calendar_email;
});

const canCreateCalendarEvent = computed(() => {
    return props.community.calendar_email &&
        form.starts_at &&
        form.ends_at;
});

const meetingLinkPlaceholder = computed(() => {
    switch (form.meeting_platform) {
        case 'zoom':
            return 'https://zoom.us/j/123456789';
        case 'teams':
            return 'https://teams.microsoft.com/l/meetup-join/...';
        case 'webex':
            return 'https://company.webex.com/meet/username';
        case 'discord':
            return 'https://discord.gg/invite-code';
        default:
            return 'https://example.com/meeting-link';
    }
});

const locationPlaceholder = computed(() => {
    if (form.is_online) {
        return selectedPlatform.value ? selectedPlatform.value.label : 'Online Platform';
    }
    return 'University of Nairobi, Room 101';
});

const meetingLinkHelper = computed(() => {
    switch (form.meeting_platform) {
        case 'zoom':
            return 'You can find your Zoom meeting ID in your Zoom account dashboard.';
        case 'teams':
            return 'Copy the meeting link from your Teams calendar invite or meeting details.';
        case 'webex':
            return 'Get your personal Webex room link from your Webex dashboard.';
        case 'discord':
            return 'Create a Discord server invite link with no expiration.';
        case 'google_meet':
            return 'Google Meet link will be automatically generated when the calendar event is created.';
        default:
            return 'Enter the complete meeting link including https://';
    }
});

const calendarEventBenefits = computed(() => {
    const benefits = [
        'Automatic calendar invites for speakers',
        'Sync with community calendar',
        'Reminder notifications',
    ];

    if (form.meeting_platform === 'google_meet') {
        benefits.unshift('Auto-generated Google Meet link');
    }

    return benefits;
});

// Watchers
watch(() => form.is_online, (isOnline) => {
    if (isOnline) {
        form.location = '';
        if (!form.meeting_platform) {
            form.meeting_platform = props.community.calendar_email ? 'google_meet' : 'zoom';
        }
    } else {
        form.meeting_platform = '';
        form.meeting_link = '';
        form.auto_create_google_meet = false;
    }
});

watch(() => form.meeting_platform, (platform) => {
    if (platform === 'google_meet') {
        form.meeting_link = '';
        form.auto_create_google_meet = true;
        form.create_calendar_event = true;
    } else {
        form.auto_create_google_meet = false;
    }

    // Set platform name as location for reference
    if (platform && form.is_online) {
        const platformData = meetingPlatforms.find(p => p.value === platform);
        if (platformData && platform !== 'custom') {
            form.location = platformData.label;
        }
    }
});

// Functions
function addSession() {
    form.sessions.push({
        title: '',
        description: '',
        starts_at: '',
        ends_at: '',
        max_speakers: 1,
        allows_applications: true,
        block_on_application: true,
        location: '',
        meeting_link: '',
        requirements: '',
    });
}

function removeSession(index) {
    form.sessions.splice(index, 1);
}

function selectMeetingPlatform(platform) {
    form.meeting_platform = platform.value;

    // Auto-enable calendar creation for Google Meet
    if (platform.value === 'google_meet') {
        form.create_calendar_event = true;
    }
}

async function submit() {
    if (!form.is_recurring) {
        form.recurrence_settings = null;
    }

    // Show loading state if creating calendar event
    if (form.create_calendar_event && canCreateCalendarEvent.value) {
        isCreatingCalendarEvent.value = true;
    }

    form.post(route('communities.events.store', props.community.slug), {
        onSuccess: () => {
            isCreatingCalendarEvent.value = false;
        },
        onError: () => {
            isCreatingCalendarEvent.value = false;
        }
    });
}

const canAddSessions = computed(() => {
    return form.sessions.length < 20;
});
</script>

<template>
    <AppLayout title="Create Event">
        <Head :title="`Create Event - ${community.name}`" />

        <template #header>
            <div class="flex items-center space-x-4">
                <a :href="`/communities/${community.slug}`" class="text-indigo-600 hover:text-indigo-900">
                    {{ community.name }}
                </a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800">Create Event</span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <!-- Basic Information -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Event Details</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <label for="title" class="block text-sm font-medium text-gray-700">
                                            Event Title *
                                        </label>
                                        <input
                                            id="title"
                                            v-model="form.title"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="AWS Pwani Monthly Meetup"
                                            required
                                        >
                                        <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                                    </div>

                                    <div>
                                        <label for="type" class="block text-sm font-medium text-gray-700">
                                            Event Type *
                                        </label>
                                        <select
                                            id="type"
                                            v-model="form.type"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        >
                                            <option v-for="(label, value) in eventTypes" :key="value" :value="value">
                                                {{ label }}
                                            </option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">{{ eventTypeDescriptions[form.type] }}</p>
                                        <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</div>
                                    </div>

                                    <div>
                                        <label for="call_for_speakers_id" class="block text-sm font-medium text-gray-700">
                                            Link to Call for Speakers
                                        </label>
                                        <select
                                            id="call_for_speakers_id"
                                            v-model="form.call_for_speakers_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">No CFS linked</option>
                                            <option v-for="cfs in availableCfs" :key="cfs.id" :value="cfs.id">
                                                {{ cfs.title }} ({{ cfs.status }})
                                            </option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Link this event to an existing call for speakers</p>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="description" class="block text-sm font-medium text-gray-700">
                                            Description
                                        </label>
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            rows="4"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Describe your event..."
                                        ></textarea>
                                        <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Date & Time -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="starts_at" class="block text-sm font-medium text-gray-700">
                                            Start Date & Time
                                        </label>
                                        <input
                                            id="starts_at"
                                            v-model="form.starts_at"
                                            type="datetime-local"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                        <div v-if="form.errors.starts_at" class="text-red-500 text-sm mt-1">{{ form.errors.starts_at }}</div>
                                    </div>

                                    <div>
                                        <label for="ends_at" class="block text-sm font-medium text-gray-700">
                                            End Date & Time
                                        </label>
                                        <input
                                            id="ends_at"
                                            v-model="form.ends_at"
                                            type="datetime-local"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                        <div v-if="form.errors.ends_at" class="text-red-500 text-sm mt-1">{{ form.errors.ends_at }}</div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <div class="flex items-center">
                                            <input
                                                id="is_recurring"
                                                v-model="form.is_recurring"
                                                type="checkbox"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                @change="showRecurrenceSettings = form.is_recurring"
                                            >
                                            <label for="is_recurring" class="ml-2 block text-sm text-gray-900">
                                                This is a recurring event
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Recurrence Settings -->
                                    <div v-if="showRecurrenceSettings && form.is_recurring" class="md:col-span-2 p-4 bg-gray-50 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-900 mb-3">Recurrence Settings</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Frequency</label>
                                                <select
                                                    v-model="form.recurrence_settings.frequency"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                >
                                                    <option value="daily">Daily</option>
                                                    <option value="weekly">Weekly</option>
                                                    <option value="monthly">Monthly</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Every</label>
                                                <input
                                                    v-model="form.recurrence_settings.interval"
                                                    type="number"
                                                    min="1"
                                                    max="12"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                >
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                                <input
                                                    v-model="form.recurrence_settings.end_date"
                                                    type="date"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Location & Meeting Platform -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Location & Meeting Setup</h3>

                                <!-- Online/Offline Toggle -->
                                <div class="mb-6">
                                    <div class="flex items-center space-x-6">
                                        <div class="flex items-center">
                                            <input
                                                id="online"
                                                v-model="form.is_online"
                                                :value="true"
                                                type="radio"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                            >
                                            <label for="online" class="ml-2 block text-sm text-gray-900 font-medium">
                                                🌐 Online Event
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input
                                                id="physical"
                                                v-model="form.is_online"
                                                :value="false"
                                                type="radio"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                            >
                                            <label for="physical" class="ml-2 block text-sm text-gray-900 font-medium">
                                                📍 Physical Event
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Online Event Setup -->
                                <div v-if="form.is_online" class="space-y-6">
                                    <!-- Meeting Platform Selection -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-3">
                                            Choose Meeting Platform *
                                        </label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            <button
                                                v-for="platform in meetingPlatforms"
                                                :key="platform.value"
                                                type="button"
                                                @click="selectMeetingPlatform(platform)"
                                                class="relative p-4 border rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-all"
                                                :class="form.meeting_platform === platform.value
                                                    ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-200'
                                                    : 'border-gray-300'"
                                            >
                                                <div class="flex items-start space-x-3">
                                                    <span class="text-2xl">{{ platform.icon }}</span>
                                                    <div class="flex-1 text-left">
                                                        <h4 class="font-medium text-gray-900">{{ platform.label }}</h4>
                                                        <p class="text-xs text-gray-500 mt-1">{{ platform.description }}</p>
                                                        <div v-if="platform.requiresCalendar && !community.calendar_email"
                                                             class="mt-2 text-xs text-orange-600">
                                                            Requires calendar setup
                                                        </div>
                                                    </div>
                                                </div>
                                                <div v-if="form.meeting_platform === platform.value"
                                                     class="absolute top-2 right-2">
                                                    <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </button>
                                        </div>
                                        <div v-if="form.errors.meeting_platform" class="text-red-500 text-sm mt-1">{{ form.errors.meeting_platform }}</div>
                                    </div>

                                    <!-- Google Meet Auto-Creation -->
                                    <div v-if="showGoogleMeetOptions" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="flex items-start space-x-3">
                                            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            <div class="flex-1">
                                                <h4 class="text-sm font-medium text-blue-900">Google Meet Integration</h4>
                                                <p class="text-sm text-blue-700 mt-1">
                                                    We'll automatically create a Google Calendar event with a Google Meet link using your community calendar ({{ community.calendar_email }}).
                                                </p>
                                                <div class="mt-3">
                                                    <div class="flex items-center">
                                                        <input
                                                            id="create_calendar_event"
                                                            v-model="form.create_calendar_event"
                                                            type="checkbox"
                                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                        >
                                                        <label for="create_calendar_event" class="ml-2 text-sm text-blue-900">
                                                            Create calendar event with Google Meet
                                                        </label>
                                                    </div>
                                                    <div v-if="form.create_calendar_event" class="mt-2 ml-6">
                                                        <ul class="text-xs text-blue-700 space-y-1">
                                                            <li v-for="benefit in calendarEventBenefits" :key="benefit" class="flex items-center">
                                                                <svg class="w-3 h-3 text-blue-600 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                </svg>
                                                                {{ benefit }}
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Manual Meeting Link Input -->
                                    <div v-if="showMeetingLinkInput" class="space-y-4">
                                        <div>
                                            <label for="meeting_link" class="block text-sm font-medium text-gray-700">
                                                {{ selectedPlatform.label }} Meeting Link *
                                            </label>
                                            <input
                                                id="meeting_link"
                                                v-model="form.meeting_link"
                                                type="url"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                :placeholder="meetingLinkPlaceholder"
                                                required
                                            >
                                            <p class="text-xs text-gray-500 mt-1">{{ meetingLinkHelper }}</p>
                                            <div v-if="form.errors.meeting_link" class="text-red-500 text-sm mt-1">{{ form.errors.meeting_link }}</div>
                                        </div>

                                        <!-- Calendar Event Option for Other Platforms -->
                                        <div v-if="canCreateCalendarEvent" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center">
                                                <input
                                                    id="create_calendar_event_other"
                                                    v-model="form.create_calendar_event"
                                                    type="checkbox"
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                >
                                                <label for="create_calendar_event_other" class="ml-2 text-sm text-gray-900">
                                                    Also create Google Calendar event
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1 ml-6">
                                                This will create a calendar event with the {{ selectedPlatform.label }} link included.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Platform Display -->
                                    <div>
                                        <label for="location_online" class="block text-sm font-medium text-gray-700">
                                            Platform Display Name
                                        </label>
                                        <input
                                            id="location_online"
                                            v-model="form.location"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :placeholder="locationPlaceholder"
                                        >
                                        <p class="text-xs text-gray-500 mt-1">How the platform will be displayed to attendees</p>
                                        <div v-if="form.errors.location" class="text-red-500 text-sm mt-1">{{ form.errors.location }}</div>
                                    </div>
                                </div>

                                <!-- Physical Event Setup -->
                                <div v-else class="space-y-4">
                                    <div>
                                        <label for="location_physical" class="block text-sm font-medium text-gray-700">
                                            Venue Location *
                                        </label>
                                        <input
                                            id="location_physical"
                                            v-model="form.location"
                                            type="text"
                                            list="location_suggestions"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :placeholder="locationPlaceholder"
                                            required
                                        >
                                        <datalist id="location_suggestions">
                                            <option v-for="suggestion in physicalLocationSuggestions" :key="suggestion" :value="suggestion" />
                                        </datalist>
                                        <p class="text-xs text-gray-500 mt-1">Include full address for better attendee experience</p>
                                        <div v-if="form.errors.location" class="text-red-500 text-sm mt-1">{{ form.errors.location }}</div>
                                    </div>

                                    <!-- Optional Calendar Event for Physical Events -->
                                    <div v-if="canCreateCalendarEvent" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center">
                                            <input
                                                id="create_calendar_event_physical"
                                                v-model="form.create_calendar_event"
                                                type="checkbox"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            >
                                            <label for="create_calendar_event_physical" class="ml-2 text-sm text-gray-900">
                                                Create Google Calendar event
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 ml-6">
                                            This will create a calendar event with the venue location included.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Settings -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Event Settings</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="max_attendees" class="block text-sm font-medium text-gray-700">
                                            Maximum Attendees
                                        </label>
                                        <input
                                            id="max_attendees"
                                            v-model="form.max_attendees"
                                            type="number"
                                            min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Leave empty for unlimited"
                                        >
                                        <div v-if="form.errors.max_attendees" class="text-red-500 text-sm mt-1">{{ form.errors.max_attendees }}</div>
                                    </div>

                                    <div class="space-y-4">
                                        <div class="flex items-center">
                                            <input
                                                id="requires_approval"
                                                v-model="form.requires_approval"
                                                type="checkbox"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            >
                                            <label for="requires_approval" class="ml-2 block text-sm text-gray-900">
                                                Require approval for attendees
                                            </label>
                                        </div>

                                        <div class="flex items-center">
                                            <input
                                                id="is_public"
                                                v-model="form.is_public"
                                                type="checkbox"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            >
                                            <label for="is_public" class="ml-2 block text-sm text-gray-900">
                                                Make event public
                                            </label>
                                        </div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="speaker_requirements" class="block text-sm font-medium text-gray-700">
                                            Speaker Requirements
                                        </label>
                                        <textarea
                                            id="speaker_requirements"
                                            v-model="form.speaker_requirements"
                                            rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Any specific requirements for speakers..."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Sessions -->
                            <div class="mb-8">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Sessions (Optional)</h3>
                                    <button
                                        v-if="canAddSessions"
                                        type="button"
                                        @click="addSession"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Add Session
                                    </button>
                                </div>

                                <div v-if="form.sessions.length === 0" class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                                    <p class="text-gray-500">No sessions created yet. Sessions allow you to break your event into smaller parts with different speakers.</p>
                                    <button
                                        type="button"
                                        @click="addSession"
                                        class="mt-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
                                    >
                                        Add First Session
                                    </button>
                                </div>

                                <div v-else class="space-y-6">
                                    <div
                                        v-for="(session, index) in form.sessions"
                                        :key="index"
                                        class="p-4 border border-gray-200 rounded-lg"
                                    >
                                        <div class="flex items-center justify-between mb-4">
                                            <h4 class="text-md font-medium text-gray-900">Session {{ index + 1 }}</h4>
                                            <button
                                                type="button"
                                                @click="removeSession(index)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="md:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">Session Title *</label>
                                                <input
                                                    v-model="session.title"
                                                    type="text"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="Introduction to AWS"
                                                    required
                                                >
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Start Time *</label>
                                                <input
                                                    v-model="session.starts_at"
                                                    type="datetime-local"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    required
                                                >
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">End Time *</label>
                                                <input
                                                    v-model="session.ends_at"
                                                    type="datetime-local"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    required
                                                >
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Max Speakers *</label>
                                                <input
                                                    v-model="session.max_speakers"
                                                    type="number"
                                                    min="1"
                                                    max="10"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    required
                                                >
                                            </div>

                                            <div class="space-y-2">
                                                <div class="flex items-center">
                                                    <input
                                                        v-model="session.allows_applications"
                                                        type="checkbox"
                                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                    >
                                                    <label class="ml-2 block text-sm text-gray-900">
                                                        Allow applications
                                                    </label>
                                                </div>

                                                <div v-if="session.allows_applications" class="flex items-center">
                                                    <input
                                                        v-model="session.block_on_application"
                                                        type="checkbox"
                                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                    >
                                                    <label class="ml-2 block text-sm text-gray-900">
                                                        Block slot when someone applies
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                                <textarea
                                                    v-model="session.description"
                                                    rows="2"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="What will this session cover?"
                                                ></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                            <a
                                :href="`/communities/${community.slug}/events`"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                :disabled="form.processing || isCreatingCalendarEvent"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                <svg v-if="form.processing || isCreatingCalendarEvent" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing || isCreatingCalendarEvent
                                ? (isCreatingCalendarEvent ? 'Creating Calendar Event...' : 'Creating...')
                                : 'Create Event' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
