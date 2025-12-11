<template>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ cfs.title }}</h1>
            <div class="flex items-center space-x-4 mt-2">
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                    :class="getStatusInfo(cfs.status).class"
                >
                    {{ getStatusInfo(cfs.status).label }}
                </span>
                <span class="text-sm text-gray-500">
                    {{ getStatusInfo(cfs.status).description }}
                </span>
                <span v-if="isClosingSoon" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    Closing Soon
                </span>
                <span v-if="!cfs.is_public" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                    Private
                </span>
            </div>
        </div>

        <!-- Dynamic Action Buttons -->
        <div class="flex space-x-2">
            <button
                v-for="action in availableActions"
                :key="action.key"
                @click="handleStatusUpdate(action.status, action)"
                :disabled="isUpdating"
                :class="[
                    'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150',
                    action.class,
                    isUpdating ? 'opacity-50 cursor-not-allowed' : ''
                ]"
            >
                <div v-html="getActionIcon(action.icon)" class="mr-1"></div>
                <span v-if="isUpdating">Processing...</span>
                <span v-else>{{ action.label }}</span>
            </button>
        </div>
    </div>

    <!-- Status Timeline -->
    <div v-if="cfs.status !== 'draft'" class="mb-6 p-4 bg-gray-50 rounded-lg">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Status History</h4>
        <div class="flex items-center space-x-6 text-sm text-gray-600">
            <div v-if="cfs.created_at" class="flex items-center">
                <svg class="h-4 w-4 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
                Created {{ formatDate(cfs.created_at) }}
            </div>
            <div v-if="cfs.opens_at && cfs.status !== 'draft'" class="flex items-center">
                <svg class="h-4 w-4 mr-1 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Published {{ formatDate(cfs.opens_at) }}
            </div>
            <div v-if="cfs.closes_at && cfs.status === 'closed'" class="flex items-center">
                <svg class="h-4 w-4 mr-1 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                Closed {{ formatDate(cfs.closes_at) }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    cfs: Object,
    availableActions: Array,
    isUpdating: Boolean
});

const emit = defineEmits(['status-update']);

const isClosingSoon = computed(() => {
    if (!props.cfs.closes_at || props.cfs.status !== 'open') return false;
    const now = new Date();
    const closesAt = new Date(props.cfs.closes_at);
    const diffDays = Math.ceil((closesAt - now) / (1000 * 60 * 60 * 24));
    return diffDays <= 7;
});

function handleStatusUpdate(newStatus, action) {
    if (action.confirm && !confirm(action.confirm)) {
        return;
    }
    emit('status-update', newStatus, action);
}

function getStatusInfo(status) {
    const statusConfig = {
        draft: {
            label: 'Draft',
            class: 'bg-yellow-100 text-yellow-800',
            description: 'Not yet published'
        },
        open: {
            label: 'Open',
            class: 'bg-green-100 text-green-800',
            description: 'Accepting applications'
        },
        closed: {
            label: 'Closed',
            class: 'bg-red-100 text-red-800',
            description: 'No longer accepting applications'
        },
        archived: {
            label: 'Archived',
            class: 'bg-gray-100 text-gray-800',
            description: 'Archived and read-only'
        }
    };
    return statusConfig[status] || statusConfig.draft;
}

function getActionIcon(iconType) {
    const icons = {
        publish: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>`,
        close: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>`,
        reopen: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>`,
        archive: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l6 6 6-6" />
        </svg>`,
        draft: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>`,
        restore: `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
        </svg>`
    };
    return icons[iconType] || '';
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>
