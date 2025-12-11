<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    community: Object,
    events: Object, // Paginated events
});
</script>

<template>
    <AppLayout :title="`${community.name} Events`">
        <Head :title="`${community.name} Events`" />

        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="`/communities/${community.slug}`" class="text-indigo-600 hover:text-indigo-900">
                        {{ community.name }}
                    </Link>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-800">Events</span>
                </div>
                <Link
                    :href="`/communities/${community.slug}/events/create`"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create Event
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="events.data.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No events</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first event.</p>
                        <div class="mt-6">
                            <Link
                                :href="`/communities/${community.slug}/events/create`"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Create Event
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white shadow overflow-hidden sm:rounded-md">
                    <ul class="divide-y divide-gray-200">
                        <li v-for="event in events.data" :key="event.id">
                            <Link :href="`/communities/${community.slug}/events/${event.slug}`" class="block hover:bg-gray-50">
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-indigo-600 truncate">
                                                    {{ event.title }}
                                                </p>
                                                <div class="ml-2 flex-shrink-0 flex">
                                                    <p
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                        :class="{
                                                            'bg-green-100 text-green-800': event.status === 'published',
                                                            'bg-yellow-100 text-yellow-800': event.status === 'draft',
                                                            'bg-red-100 text-red-800': event.status === 'cancelled',
                                                            'bg-blue-100 text-blue-800': event.status === 'completed'
                                                        }"
                                                    >
                                                        {{ event.status }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <p>
                                                    {{ new Date(event.starts_at).toLocaleDateString() }}
                                                    <span v-if="event.starts_at">
                                                        at {{ new Date(event.starts_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                                                    </span>
                                                </p>
                                                <span class="mx-2">•</span>
                                                <p class="capitalize">{{ event.type }}</p>
                                                <span v-if="event.is_online" class="mx-2">•</span>
                                                <p v-if="event.is_online" class="text-blue-600">Online</p>
                                            </div>
                                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                <p>{{ event.sessions_count }} sessions • {{ event.speakers_count }} speakers</p>
                                                <span v-if="event.location" class="mx-2">•</span>
                                                <p v-if="event.location" class="truncate">{{ event.location }}</p>
                                            </div>
                                            <p v-if="event.description" class="mt-2 text-sm text-gray-600 line-clamp-2">
                                                {{ event.description }}
                                            </p>
                                        </div>
                                        <div class="ml-5 flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </li>
                    </ul>

                    <!-- Pagination -->
                    <div v-if="events.links && events.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                v-if="events.prev_page_url"
                                :href="events.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="events.next_page_url"
                                :href="events.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing {{ events.from }} to {{ events.to }} of {{ events.total }} events
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <template v-for="(link, index) in events.links" :key="index">
                                        <Link
                                            v-if="link.url"
                                            :href="link.url"
                                            v-html="link.label"
                                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                            :class="{
                                                'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active,
                                                'rounded-l-md': index === 0,
                                                'rounded-r-md': index === events.links.length - 1
                                            }"
                                        ></Link>
                                        <span
                                            v-else
                                            v-html="link.label"
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500"
                                        ></span>
                                    </template>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
