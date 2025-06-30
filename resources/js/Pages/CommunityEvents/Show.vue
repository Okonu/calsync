<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    community: Object,
    event: Object,
});

const showAddSpeakerModal = ref(false);

const speakerForm = useForm({
    name: '',
    email: '',
    phone: '',
    bio: '',
    company: '',
    job_title: '',
    topic_title: '',
    topic_description: '',
    event_session_id: null,
    is_featured: false,
});

function addSpeaker() {
    speakerForm.post(`/api/communities/${props.community.slug}/events/${props.event.id}/speakers`, {
        onSuccess: () => {
            showAddSpeakerModal.value = false;
            speakerForm.reset();
        }
    });
}
</script>

<template>
    <AppLayout :title="`${event.title} - ${community.name}`">
        <Head :title="`${event.title} - ${community.name}`" />

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
                    <span class="text-gray-800">{{ event.title }}</span>
                </div>
                <div class="flex space-x-3">
                    <Link
                        :href="`/communities/${community.slug}/events/${event.slug}/edit`"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                    >
                        Edit Event
                    </Link>
                    <Link
                        v-if="event.is_public && event.status === 'published' && event.public_url"
                        :href="event.public_url"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        View Public
                    </Link>
                    <Link
                        v-else-if="event.is_public && event.status === 'published'"
                        :href="`/community/${community.slug}/events/${event.slug}`"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        View Public
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Event Header -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ event.title }}</h1>
                                <div class="flex items-center space-x-4 mt-2">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800': event.status === 'published',
                                            'bg-yellow-100 text-yellow-800': event.status === 'draft',
                                            'bg-red-100 text-red-800': event.status === 'cancelled',
                                            'bg-blue-100 text-blue-800': event.status === 'completed'
                                        }"
                                    >
                                        {{ event.status }}
                                    </span>
                                    <span class="text-sm text-gray-500 capitalize">{{ event.type }}</span>
                                    <span v-if="event.is_online" class="text-sm text-blue-600">Online Event</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Event Details</h3>
                                <div class="space-y-2">
                                    <div v-if="event.starts_at" class="flex items-center text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-gray-900">
                                            {{ new Date(event.starts_at).toLocaleDateString() }}
                                            at {{ new Date(event.starts_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                                        </span>
                                    </div>
                                    <div v-if="event.location" class="flex items-center text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="text-gray-900">{{ event.location }}</span>
                                    </div>
                                    <div v-if="event.meeting_link" class="flex items-center text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <a :href="event.meeting_link" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                            Join Meeting
                                        </a>
                                    </div>
                                    <div v-if="event.max_attendees" class="flex items-center text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 3a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span class="text-gray-900">Max {{ event.max_attendees }} attendees</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="event.call_for_speakers">
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Call for Speakers</h3>
                                <div class="bg-purple-50 border border-purple-200 rounded-md p-3">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-purple-900">{{ event.call_for_speakers.title }}</p>
                                            <Link
                                                :href="`/communities/${community.slug}/cfs/${event.call_for_speakers.id}`"
                                                class="text-xs text-purple-600 hover:text-purple-800"
                                            >
                                                Manage applications
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="event.description" class="mt-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ event.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Event Sessions -->
                <div v-if="event.sessions && event.sessions.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Sessions</h3>
                            <span class="text-sm text-gray-500">{{ event.sessions.length }} sessions</span>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-for="session in event.sessions" :key="session.id" class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-lg font-medium text-gray-900">{{ session.title }}</h4>
                                    <p v-if="session.description" class="mt-1 text-gray-600">{{ session.description }}</p>
                                    <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                        <span>{{ new Date(session.starts_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }} - {{ new Date(session.ends_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                                        <span>{{ session.current_speakers || 0 }}/{{ session.max_speakers }} speakers</span>
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800': session.status === 'available',
                                                'bg-yellow-100 text-yellow-800': session.status === 'pending',
                                                'bg-blue-100 text-blue-800': session.status === 'confirmed',
                                                'bg-red-100 text-red-800': session.status === 'full'
                                            }"
                                        >
                                            {{ session.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Session Speakers -->
                            <div v-if="session.speakers && session.speakers.length > 0" class="mt-4">
                                <h5 class="text-sm font-medium text-gray-700 mb-2">Speakers</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div v-for="speaker in session.speakers" :key="speaker.id" class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                        <img
                                            :src="speaker.photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(speaker.name)}&background=6366f1&color=fff`"
                                            :alt="speaker.name"
                                            class="h-10 w-10 rounded-full"
                                        >
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900">{{ speaker.name }}</p>
                                            <p v-if="speaker.company" class="text-xs text-gray-500">{{ speaker.company }}</p>
                                            <p v-if="speaker.topic_title" class="text-xs text-indigo-600">{{ speaker.topic_title }}</p>
                                        </div>
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800': speaker.status === 'confirmed',
                                                'bg-yellow-100 text-yellow-800': speaker.status === 'pending',
                                                'bg-red-100 text-red-800': speaker.status === 'declined'
                                            }"
                                        >
                                            {{ speaker.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Event Speakers (if no sessions) -->
                <div v-else-if="event.speakers && event.speakers.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Speakers</h3>
                            <button
                                @click="showAddSpeakerModal = true"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Add Speaker
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="speaker in event.speakers" :key="speaker.id" class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center space-x-4">
                                    <img
                                        :src="speaker.photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(speaker.name)}&background=6366f1&color=fff`"
                                        :alt="speaker.name"
                                        class="h-12 w-12 rounded-full"
                                    >
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ speaker.name }}</p>
                                        <p v-if="speaker.company" class="text-sm text-gray-500">{{ speaker.company }}</p>
                                        <p v-if="speaker.topic_title" class="text-sm text-indigo-600">{{ speaker.topic_title }}</p>
                                    </div>
                                </div>
                                <p v-if="speaker.bio" class="mt-3 text-sm text-gray-600 line-clamp-3">{{ speaker.bio }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Speaker Button (if no speakers) -->
                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No speakers yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding your first speaker.</p>
                        <div class="mt-6">
                            <button
                                @click="showAddSpeakerModal = true"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Add Speaker
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add Speaker Modal -->
                <div v-if="showAddSpeakerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-w-2xl">
                        <form @submit.prevent="addSpeaker">
                            <div class="mt-3">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Speaker</h3>

                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Name *</label>
                                            <input
                                                v-model="speakerForm.name"
                                                type="text"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Email *</label>
                                            <input
                                                v-model="speakerForm.email"
                                                type="email"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Company</label>
                                            <input
                                                v-model="speakerForm.company"
                                                type="text"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Job Title</label>
                                            <input
                                                v-model="speakerForm.job_title"
                                                type="text"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Topic Title</label>
                                        <input
                                            v-model="speakerForm.topic_title"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bio</label>
                                        <textarea
                                            v-model="speakerForm.bio"
                                            rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        ></textarea>
                                    </div>

                                    <div v-if="event.sessions && event.sessions.length > 0">
                                        <label class="block text-sm font-medium text-gray-700">Session</label>
                                        <select
                                            v-model="speakerForm.event_session_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">No specific session</option>
                                            <option v-for="session in event.sessions" :key="session.id" :value="session.id">
                                                {{ session.title }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="flex items-center">
                                        <input
                                            v-model="speakerForm.is_featured"
                                            type="checkbox"
                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                        >
                                        <label class="ml-2 block text-sm text-gray-900">
                                            Featured speaker
                                        </label>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end space-x-3 mt-6">
                                    <button
                                        type="button"
                                        @click="showAddSpeakerModal = false"
                                        class="px-4 py-2 bg-white text-gray-500 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="speakerForm.processing"
                                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300 disabled:opacity-50"
                                    >
                                        {{ speakerForm.processing ? 'Adding...' : 'Add Speaker' }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
