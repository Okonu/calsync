<template>
    <AppLayout :title="`Edit ${event.title} - ${community.name}`">
        <Head :title="`Edit ${event.title} - ${community.name}`" />

        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="`/communities/${community.slug}`" class="text-indigo-600 hover:text-indigo-900">
                        {{ community.name }}
                    </Link>
                    <span class="text-gray-400">/</span>
                    <Link :href="`/communities/${community.slug}/events`" class="text-indigo-600 hover:text-indigo-900">
                        Events
                    </Link>
                    <span class="text-gray-400">/</span>
                    <Link :href="`/communities/${community.slug}/events/${event.slug}`" class="text-indigo-600 hover:text-indigo-900">
                        {{ event.title }}
                    </Link>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-800">Edit</span>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Status Warning -->
                <div v-if="hasStarted" class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Caution: Event Has Started
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>This event has already started. Some changes may affect confirmed speakers and attendees.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else-if="hasConfirmedSpeakers" class="mb-6 bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Event Has Confirmed Speakers
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>This event has confirmed speakers. Consider notifying them of any major changes.</p>
                            </div>
                        </div>
                    </div>
                </div>

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
                                        <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</div>
                                    </div>

                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700">
                                            Status *
                                        </label>
                                        <select
                                            id="status"
                                            v-model="form.status"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        >
                                            <option value="draft">Draft</option>
                                            <option value="published">Published</option>
                                            <option value="cancelled">Cancelled</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                        <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
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
                                            :disabled="hasStarted"
                                        >
                                        <p v-if="hasStarted" class="text-xs text-yellow-600 mt-1">
                                            Cannot change start time for events that have started
                                        </p>
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
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Location</h3>

                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <input
                                            id="is_online"
                                            v-model="form.is_online"
                                            type="checkbox"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        >
                                        <label for="is_online" class="ml-2 block text-sm text-gray-900">
                                            Online event
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="location" class="block text-sm font-medium text-gray-700">
                                                {{ form.is_online ? 'Online Platform' : 'Venue' }}
                                            </label>
                                            <input
                                                id="location"
                                                v-model="form.location"
                                                type="text"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                :placeholder="form.is_online ? 'Zoom, Google Meet, etc.' : 'University of Nairobi, Room 101'"
                                            >
                                            <div v-if="form.errors.location" class="text-red-500 text-sm mt-1">{{ form.errors.location }}</div>
                                        </div>

                                        <div>
                                            <label for="meeting_link" class="block text-sm font-medium text-gray-700">
                                                Meeting Link
                                            </label>
                                            <input
                                                id="meeting_link"
                                                v-model="form.meeting_link"
                                                type="url"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="https://zoom.us/j/123456789"
                                            >
                                            <div v-if="form.errors.meeting_link" class="text-red-500 text-sm mt-1">{{ form.errors.meeting_link }}</div>
                                        </div>
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

                            <!-- Call for Speakers Link -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Call for Speakers</h3>

                                <div>
                                    <label for="call_for_speakers_id" class="block text-sm font-medium text-gray-700">
                                        Link to Call for Speakers
                                    </label>
                                    <select
                                        id="call_for_speakers_id"
                                        v-model="form.call_for_speakers_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option :value="null">No CFS linked</option>
                                        <option v-for="cfs in availableCfs" :key="cfs.id" :value="cfs.id">
                                            {{ cfs.title }} ({{ cfs.status }})
                                        </option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Link this event to an existing call for speakers</p>
                                </div>
                            </div>

                            <!-- Existing Sessions Summary -->
                            <div v-if="event.sessions && event.sessions.length > 0" class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">
                                    Event Sessions
                                    <span class="text-sm font-normal text-gray-500">({{ event.sessions.length }} sessions)</span>
                                </h3>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div v-for="session in event.sessions" :key="session.id" class="bg-white rounded p-3 border border-gray-200">
                                            <h4 class="font-medium text-gray-900 text-sm">{{ session.title }}</h4>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ formatSessionTime(session) }}
                                            </p>
                                            <div class="flex items-center justify-between mt-2">
                                                <span class="text-xs text-gray-600">
                                                    {{ session.current_speakers || 0 }}/{{ session.max_speakers }} speakers
                                                </span>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                      :class="getSessionStatusClass(session.status)">
                                                    {{ session.status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-center">
                                        <Link
                                            :href="`/communities/${community.slug}/events/${event.slug}/sessions`"
                                            class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800"
                                        >
                                            Manage Sessions
                                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Speakers Summary -->
                            <div v-if="event.speakers && event.speakers.length > 0" class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">
                                    Event Speakers
                                    <span class="text-sm font-normal text-gray-500">({{ event.speakers.length }} speakers)</span>
                                </h3>

                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div v-for="speaker in event.speakers.slice(0, 6)" :key="speaker.id" class="flex items-center space-x-3 bg-white rounded p-3 border border-gray-200">
                                            <img
                                                :src="speaker.photo_url || getAvatarUrl(speaker.name)"
                                                :alt="speaker.name"
                                                class="w-8 h-8 rounded-full"
                                            >
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ speaker.name }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ speaker.topic_title || speaker.company }}</p>
                                            </div>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                  :class="getSpeakerStatusClass(speaker.status)">
                                                {{ speaker.status }}
                                            </span>
                                        </div>
                                    </div>

                                    <div v-if="event.speakers.length > 6" class="mt-3 text-center text-sm text-gray-500">
                                        ... and {{ event.speakers.length - 6 }} more speakers
                                    </div>

                                    <div class="mt-4 text-center">
                                        <Link
                                            :href="`/communities/${community.slug}/events/${event.slug}/speakers`"
                                            class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800"
                                        >
                                            Manage Speakers
                                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                            <Link
                                :href="`/communities/${community.slug}/events/${event.slug}`"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancel
                            </Link>
                            <div class="flex space-x-3">
                                <button
                                    v-if="form.status === 'draft'"
                                    type="button"
                                    @click="publishEvent"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    Publish Event
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    community: Object,
    event: Object,
    availableCfs: Array,
    eventTypes: Object,
});

const form = useForm({
    title: props.event.title,
    description: props.event.description,
    type: props.event.type,
    status: props.event.status,
    starts_at: props.event.starts_at ? new Date(props.event.starts_at).toISOString().slice(0, 16) : '',
    ends_at: props.event.ends_at ? new Date(props.event.ends_at).toISOString().slice(0, 16) : '',
    location: props.event.location,
    meeting_link: props.event.meeting_link,
    is_online: props.event.is_online,
    max_attendees: props.event.max_attendees,
    requires_approval: props.event.requires_approval,
    is_public: props.event.is_public,
    speaker_requirements: props.event.speaker_requirements,
    call_for_speakers_id: props.event.call_for_speakers_id,
});

const hasStarted = computed(() => {
    return props.event.starts_at && new Date(props.event.starts_at) < new Date();
});

const hasConfirmedSpeakers = computed(() => {
    return props.event.speakers && props.event.speakers.some(speaker => speaker.status === 'confirmed');
});

function formatSessionTime(session) {
    const startDate = new Date(session.starts_at);
    const endDate = new Date(session.ends_at);
    return `${startDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} - ${endDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
}

function getSessionStatusClass(status) {
    switch (status) {
        case 'available':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'confirmed':
            return 'bg-blue-100 text-blue-800';
        case 'full':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getSpeakerStatusClass(status) {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'confirmed':
            return 'bg-green-100 text-green-800';
        case 'declined':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getAvatarUrl(name) {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=6366f1&color=fff`;
}

function publishEvent() {
    form.status = 'published';
    submit();
}

function submit() {
    form.patch(route('communities.events.update', [props.community.slug, props.event.slug]), {
        onSuccess: () => {
            // Success
        }
    });
}
</script>
