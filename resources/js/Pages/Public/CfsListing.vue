<template>
    <div class="min-h-screen bg-gray-50">
        <Head :title="`Call for Speakers - ${community.name}`" />

        <!-- Community Header -->
        <div class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <Link :href="`/community/${community.slug}`" class="flex items-center space-x-3">
                            <div
                                class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold"
                                :style="{ backgroundColor: community.color }"
                            >
                                <img
                                    v-if="community.logo_url"
                                    :src="community.logo_url"
                                    :alt="community.name"
                                    class="w-full h-full rounded-full object-cover"
                                >
                                <span v-else>{{ community.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ community.name }}</h1>
                                <p class="text-sm text-gray-500">Call for Speakers</p>
                            </div>
                        </Link>
                    </div>
                    <Link
                        :href="`/community/${community.slug}`"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Community
                    </Link>
                </div>
            </div>
        </div>

        <!-- CFS Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Filter Bar -->
            <div class="mb-8 bg-white rounded-lg shadow-sm p-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Status:</label>
                        <select v-model="selectedStatus" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Statuses</option>
                            <option value="open">Open</option>
                            <option value="closing_soon">Closing Soon</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Sort by:</label>
                        <select v-model="sortBy" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="newest">Newest First</option>
                            <option value="deadline">Deadline</option>
                            <option value="title">Title</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- No CFS State -->
            <div v-if="filteredCfs.length === 0" class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No calls for speakers found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ cfs.data.length === 0 ? 'This community has no calls for speakers yet.' : 'No calls for speakers match your current filters.' }}
                </p>
                <div v-if="hasActiveFilters" class="mt-4">
                    <button
                        @click="clearFilters"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- CFS Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="cfsItem in filteredCfs" :key="cfsItem.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <div class="p-6">
                        <!-- CFS Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ cfsItem.title }}</h3>
                                <div class="flex items-center space-x-2 mb-2">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="getStatusClass(cfsItem)"
                                    >
                                        {{ getStatusLabel(cfsItem) }}
                                    </span>
                                    <span v-if="isClosingSoon(cfsItem)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Closing Soon
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- CFS Description -->
                        <p v-if="cfsItem.description" class="text-gray-600 text-sm mb-4 line-clamp-3">
                            {{ cfsItem.description }}
                        </p>

                        <!-- CFS Meta Information -->
                        <div class="space-y-2 mb-4">
                            <div v-if="cfsItem.closes_at" class="flex items-center text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>
                                    {{ isOpen(cfsItem) ? 'Closes' : 'Closed' }} {{ formatDate(cfsItem.closes_at) }}
                                </span>
                            </div>

                            <div v-if="cfsItem.events && cfsItem.events.length > 0" class="flex items-center text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ cfsItem.events.length }} linked event{{ cfsItem.events.length !== 1 ? 's' : '' }}</span>
                            </div>

                            <div v-if="cfsItem.total_applications !== null" class="flex items-center text-sm text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ cfsItem.total_applications }} application{{ cfsItem.total_applications !== 1 ? 's' : '' }}</span>
                            </div>
                        </div>

                        <!-- Time Remaining -->
                        <div v-if="isOpen(cfsItem) && cfsItem.closes_at" class="mb-4">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">Time Remaining</span>
                                    <span class="text-sm font-semibold" :class="isClosingSoon(cfsItem) ? 'text-red-600' : 'text-green-600'">
                                        {{ getTimeRemaining(cfsItem.closes_at) }}
                                    </span>
                                </div>
                                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                    <div
                                        class="h-2 rounded-full transition-all duration-300"
                                        :class="isClosingSoon(cfsItem) ? 'bg-red-500' : 'bg-green-500'"
                                        :style="{ width: getProgressPercentage(cfsItem) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-3">
                            <Link
                                :href="`/community/${community.slug}/cfs/${cfsItem.slug}`"
                                class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                {{ isOpen(cfsItem) ? 'Apply Now' : 'View Details' }}
                            </Link>
                            <Link
                                :href="`/community/${community.slug}/cfs/${cfsItem.slug}`"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="cfs.links && cfs.links.length > 3" class="mt-8 flex justify-center">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <template v-for="(link, index) in cfs.links" :key="index">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                            :class="{
                                'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active,
                                'rounded-l-md': index === 0,
                                'rounded-r-md': index === cfs.links.length - 1
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
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    community: Object,
    cfs: Object,
});

const selectedStatus = ref('');
const sortBy = ref('newest');

const filteredCfs = computed(() => {
    let filtered = [...props.cfs.data];

    // Filter by status
    if (selectedStatus.value) {
        filtered = filtered.filter(cfsItem => {
            switch (selectedStatus.value) {
                case 'open':
                    return isOpen(cfsItem);
                case 'closing_soon':
                    return isClosingSoon(cfsItem);
                case 'closed':
                    return !isOpen(cfsItem);
                default:
                    return true;
            }
        });
    }

    // Sort CFS items
    filtered.sort((a, b) => {
        switch (sortBy.value) {
            case 'title':
                return a.title.localeCompare(b.title);
            case 'deadline':
                if (!a.closes_at && !b.closes_at) return 0;
                if (!a.closes_at) return 1;
                if (!b.closes_at) return -1;
                return new Date(a.closes_at) - new Date(b.closes_at);
            case 'newest':
            default:
                return new Date(b.created_at) - new Date(a.created_at);
        }
    });

    return filtered;
});

const hasActiveFilters = computed(() => {
    return selectedStatus.value;
});

function isOpen(cfsItem) {
    if (!cfsItem.closes_at) return true;
    return new Date(cfsItem.closes_at) > new Date();
}

function isClosingSoon(cfsItem) {
    if (!cfsItem.closes_at || !isOpen(cfsItem)) return false;
    const now = new Date();
    const closesAt = new Date(cfsItem.closes_at);
    const diffDays = Math.ceil((closesAt - now) / (1000 * 60 * 60 * 24));
    return diffDays <= 7;
}

function getStatusClass(cfsItem) {
    if (isOpen(cfsItem)) {
        return isClosingSoon(cfsItem) ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
    }
    return 'bg-gray-100 text-gray-800';
}

function getStatusLabel(cfsItem) {
    if (isOpen(cfsItem)) {
        return 'Open';
    }
    return 'Closed';
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function getTimeRemaining(closesAt) {
    const now = new Date();
    const deadline = new Date(closesAt);
    const diffTime = deadline - now;

    if (diffTime < 0) return 'Closed';

    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 1) {
        const diffHours = Math.ceil(diffTime / (1000 * 60 * 60));
        if (diffHours <= 24) {
            return `${diffHours} hour${diffHours !== 1 ? 's' : ''}`;
        }
        return '1 day';
    } else if (diffDays < 7) {
        return `${diffDays} days`;
    } else {
        const diffWeeks = Math.ceil(diffDays / 7);
        return `${diffWeeks} week${diffWeeks !== 1 ? 's' : ''}`;
    }
}

function getProgressPercentage(cfsItem) {
    if (!cfsItem.opens_at || !cfsItem.closes_at) return 0;

    const now = new Date();
    const opensAt = new Date(cfsItem.opens_at);
    const closesAt = new Date(cfsItem.closes_at);

    if (now < opensAt) return 0;
    if (now > closesAt) return 100;

    const totalDuration = closesAt - opensAt;
    const elapsed = now - opensAt;

    return Math.max(0, Math.min(100, (elapsed / totalDuration) * 100));
}

function clearFilters() {
    selectedStatus.value = '';
    sortBy.value = 'newest';
}
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
