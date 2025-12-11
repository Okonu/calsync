<template>
    <AppLayout :title="`${community.name} - Call for Speakers`">
        <Head :title="`${community.name} - Call for Speakers`" />

        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="`/communities/${community.slug}`" class="text-indigo-600 hover:text-indigo-900">
                        {{ community.name }}
                    </Link>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-800">Call for Speakers</span>
                </div>
                <Link
                    :href="`/communities/${community.slug}/cfs/create`"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create Call for Speakers
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filter Bar -->
                <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Status:</label>
                            <select v-model="selectedStatus" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Statuses</option>
                                <option value="draft">Draft</option>
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Sort by:</label>
                            <select v-model="sortBy" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="newest">Newest First</option>
                                <option value="deadline">Deadline</option>
                                <option value="title">Title</option>
                                <option value="applications">Applications</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Applications:</label>
                            <select v-model="applicationFilter" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All CFS</option>
                                <option value="pending">Has Pending</option>
                                <option value="none">No Applications</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- No CFS State -->
                <div v-if="cfs.data.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No calls for speakers</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first call for speakers.</p>
                        <div class="mt-6">
                            <Link
                                :href="`/communities/${community.slug}/cfs/create`"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Create Call for Speakers
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- CFS Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="cfsItem in filteredCfs" :key="cfsItem.id" class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <!-- CFS Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <Link
                                        :href="`/communities/${community.slug}/cfs/${cfsItem.slug}`"
                                        class="text-lg font-semibold text-gray-900 hover:text-indigo-600 line-clamp-2"
                                    >
                                        {{ cfsItem.title }}
                                    </Link>
                                    <div class="flex items-center space-x-2 mt-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="getStatusClass(cfsItem.status)"
                                        >
                                            {{ getStatusLabel(cfsItem.status) }}
                                        </span>
                                        <span v-if="isClosingSoon(cfsItem)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Closing Soon
                                        </span>
                                        <span v-if="!cfsItem.is_public" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Private
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-1 ml-2">
                                    <!-- Quick Actions Dropdown -->
                                    <div class="relative" v-click-away="() => openDropdown !== cfsItem.id">
                                        <button
                                            @click="openDropdown = openDropdown === cfsItem.id ? null : cfsItem.id"
                                            class="p-1 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01" />
                                            </svg>
                                        </button>
                                        <div v-if="openDropdown === cfsItem.id" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                            <div class="py-1">
                                                <Link
                                                    :href="`/communities/${community.slug}/cfs/${cfsItem.slug}`"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    View & Manage
                                                </Link>
                                                <Link
                                                    :href="`/communities/${community.slug}/cfs/${cfsItem.slug}/edit`"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    Edit Settings
                                                </Link>
                                                <Link
                                                    v-if="cfsItem.is_public && cfsItem.status === 'open'"
                                                    :href="cfsItem.public_url"
                                                    target="_blank"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                >
                                                    View Public Page
                                                </Link>
                                                <button
                                                    v-if="cfsItem.status === 'draft'"
                                                    @click="publishCfs(cfsItem)"
                                                    class="block w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-gray-100"
                                                >
                                                    Publish CFS
                                                </button>
                                                <button
                                                    v-if="cfsItem.status === 'open'"
                                                    @click="closeCfs(cfsItem)"
                                                    class="block w-full text-left px-4 py-2 text-sm text-orange-700 hover:bg-gray-100"
                                                >
                                                    Close CFS
                                                </button>
                                                <div class="border-t border-gray-100"></div>
                                                <button
                                                    @click="archiveCfs(cfsItem)"
                                                    class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-gray-100"
                                                >
                                                    Archive
                                                </button>
                                            </div>
                                        </div>
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

                                <div class="flex items-center text-sm text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ cfsItem.events_count }} linked event{{ cfsItem.events_count !== 1 ? 's' : '' }}</span>
                                </div>

                                <div class="flex items-center text-sm text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>{{ cfsItem.applications_count }} application{{ cfsItem.applications_count !== 1 ? 's' : '' }}</span>
                                </div>
                            </div>

                            <!-- Application Stats -->
                            <div v-if="cfsItem.applications_count > 0" class="mb-4">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Applications</span>
                                        <span class="font-medium text-gray-900">{{ cfsItem.applications_count }}</span>
                                    </div>
                                    <div class="mt-2 grid grid-cols-3 gap-2 text-xs">
                                        <div class="text-center">
                                            <div class="font-medium text-yellow-600">{{ getPendingCount(cfsItem) }}</div>
                                            <div class="text-gray-500">Pending</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-medium text-green-600">{{ getApprovedCount(cfsItem) }}</div>
                                            <div class="text-gray-500">Approved</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-medium text-red-600">{{ getRejectedCount(cfsItem) }}</div>
                                            <div class="text-gray-500">Rejected</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-2">
                                <Link
                                    :href="`/communities/${community.slug}/cfs/${cfsItem.slug}`"
                                    class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    {{ cfsItem.applications_count > 0 ? 'Manage' : 'View' }}
                                </Link>
                                <Link
                                    v-if="cfsItem.is_public && cfsItem.status !== 'draft'"
                                    :href="cfsItem.public_url"
                                    target="_blank"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
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
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    community: Object,
    cfs: Object,
});

const selectedStatus = ref('');
const sortBy = ref('newest');
const applicationFilter = ref('');
const openDropdown = ref(null);

const filteredCfs = computed(() => {
    let filtered = [...props.cfs.data];

    if (selectedStatus.value) {
        filtered = filtered.filter(cfsItem => cfsItem.status === selectedStatus.value);
    }

    if (applicationFilter.value === 'pending') {
        filtered = filtered.filter(cfsItem => getPendingCount(cfsItem) > 0);
    } else if (applicationFilter.value === 'none') {
        filtered = filtered.filter(cfsItem => cfsItem.applications_count === 0);
    }

    filtered.sort((a, b) => {
        switch (sortBy.value) {
            case 'title':
                return a.title.localeCompare(b.title);
            case 'deadline':
                if (!a.closes_at && !b.closes_at) return 0;
                if (!a.closes_at) return 1;
                if (!b.closes_at) return -1;
                return new Date(a.closes_at) - new Date(b.closes_at);
            case 'applications':
                return b.applications_count - a.applications_count;
            case 'newest':
            default:
                return new Date(b.created_at) - new Date(a.created_at);
        }
    });

    return filtered;
});

function isOpen(cfsItem) {
    return cfsItem.status === 'open' && (!cfsItem.closes_at || new Date(cfsItem.closes_at) > new Date());
}

function isClosingSoon(cfsItem) {
    if (!cfsItem.closes_at || !isOpen(cfsItem)) return false;
    const now = new Date();
    const closesAt = new Date(cfsItem.closes_at);
    const diffDays = Math.ceil((closesAt - now) / (1000 * 60 * 60 * 24));
    return diffDays <= 7;
}

function getStatusClass(status) {
    switch (status) {
        case 'open':
            return 'bg-green-100 text-green-800';
        case 'closed':
            return 'bg-red-100 text-red-800';
        case 'draft':
            return 'bg-yellow-100 text-yellow-800';
        case 'archived':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getStatusLabel(status) {
    switch (status) {
        case 'open':
            return 'Open';
        case 'closed':
            return 'Closed';
        case 'draft':
            return 'Draft';
        case 'archived':
            return 'Archived';
        default:
            return status;
    }
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function getPendingCount(cfsItem) {
    return cfsItem.pending_applications || 0;
}

function getApprovedCount(cfsItem) {
    return cfsItem.approved_applications || 0;
}

function getRejectedCount(cfsItem) {
    return cfsItem.rejected_applications || 0;
}

const statusUpdateForm = useForm({});

function publishCfs(cfsItem) {
    if (confirm('Are you sure you want to publish this call for speakers? It will become public and start accepting applications.')) {
        statusUpdateForm.patch(`/api/communities/${props.community.slug}/cfs/${cfsItem.slug}/status`, {
            data: { status: 'open' },
            onSuccess: () => {
                cfsItem.status = 'open';
                openDropdown.value = null;
            }
        });
    }
}

function closeCfs(cfsItem) {
    if (confirm('Are you sure you want to close this call for speakers? No new applications will be accepted.')) {
        statusUpdateForm.patch(`/api/communities/${props.community.slug}/cfs/${cfsItem.slug}/status`, {
            data: { status: 'closed' },
            onSuccess: () => {
                cfsItem.status = 'closed';
                openDropdown.value = null;
            }
        });
    }
}

function archiveCfs(cfsItem) {
    if (confirm('Are you sure you want to archive this call for speakers? This action cannot be undone.')) {
        statusUpdateForm.patch(`/api/communities/${props.community.slug}/cfs/${cfsItem.slug}/status`, {
            data: { status: 'archived' },
            onSuccess: () => {
                cfsItem.status = 'archived';
                openDropdown.value = null;
            }
        });
    }
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
