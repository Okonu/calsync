<template>
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Applications</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Manage and review speaker applications
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Filter Dropdown -->
                    <select
                        :value="selectedFilter"
                        @change="$emit('filter-change', $event.target.value)"
                        class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">All Applications</option>
                        <option value="pending">Pending Review</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>

                    <!-- Bulk Actions -->
                    <div v-if="selectedApplications.length > 0" class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">{{ selectedApplications.length }} selected</span>
                        <button
                            @click="$emit('bulk-actions')"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Bulk Actions
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications List -->
        <div v-if="applications.length === 0" class="p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No applications</h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ selectedFilter ? 'No applications match your current filter.' : 'No applications have been submitted yet.' }}
            </p>
        </div>

        <ul v-else class="divide-y divide-gray-200">
            <ApplicationRow
                v-for="application in applications"
                :key="application.id"
                :application="application"
                :is-selected="selectedApplications.includes(application.id)"
                @toggle-select="toggleSelection(application.id)"
                @approve="$emit('approve', application)"
                @reject="$emit('reject', application)"
                @view="$emit('view', application)"
            />
        </ul>
    </div>
</template>

<script setup>
import ApplicationRow from './ApplicationRow.vue';

defineProps({
    applications: Array,
    selectedFilter: String,
    selectedApplications: Array
});

const emit = defineEmits([
    'filter-change',
    'bulk-actions',
    'approve',
    'reject',
    'view',
    'selection-change'
]);

function toggleSelection(applicationId) {
    emit('selection-change', applicationId);
}
</script>
