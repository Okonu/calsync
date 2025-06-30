<template>
    <li class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <input
                    type="checkbox"
                    :checked="isSelected"
                    @change="$emit('toggle-select')"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                >
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-3">
                        <p class="text-sm font-medium text-gray-900">{{ application.applicant_name }}</p>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="getApplicationStatusClass(application.status)"
                        >
                            {{ getApplicationStatusLabel(application.status) }}
                        </span>
                    </div>
                    <div class="mt-1">
                        <p class="text-sm text-gray-600">{{ application.topic_title || 'No topic specified' }}</p>
                        <div class="flex items-center mt-1 text-xs text-gray-500 space-x-4">
                            <span>{{ application.applicant_email }}</span>
                            <span>•</span>
                            <span>Applied {{ formatDate(application.created_at) }}</span>
                            <span v-if="application.eventSession || application.communityEvent">•</span>
                            <span v-if="application.eventSession" class="text-indigo-600">
                                Session: {{ application.eventSession.title }}
                            </span>
                            <span v-else-if="application.communityEvent" class="text-indigo-600">
                                Event: {{ application.communityEvent.title }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button
                    v-if="application.status === 'pending'"
                    @click="$emit('approve')"
                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                    Approve
                </button>
                <button
                    v-if="application.status === 'pending'"
                    @click="$emit('reject')"
                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    Reject
                </button>
                <button
                    @click="$emit('view')"
                    class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    View Details
                </button>
            </div>
        </div>
    </li>
</template>

<script setup>
defineProps({
    application: Object,
    isSelected: Boolean
});

defineEmits(['toggle-select', 'approve', 'reject', 'view']);

function getApplicationStatusClass(status) {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'withdrawn':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getApplicationStatusLabel(status) {
    switch (status) {
        case 'pending':
            return 'Pending';
        case 'approved':
            return 'Approved';
        case 'rejected':
            return 'Rejected';
        case 'withdrawn':
            return 'Withdrawn';
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
</script>
