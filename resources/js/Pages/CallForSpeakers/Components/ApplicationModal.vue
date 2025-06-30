<template>
    <div v-if="application" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Application Details</h3>
                    <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-3">Applicant Information</h4>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-900">Name</dt>
                                <dd class="text-sm text-gray-600">{{ application.applicant_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-900">Email</dt>
                                <dd class="text-sm text-gray-600">{{ application.applicant_email }}</dd>
                            </div>
                            <div v-if="application.applicant_phone">
                                <dt class="text-sm font-medium text-gray-900">Phone</dt>
                                <dd class="text-sm text-gray-600">{{ application.applicant_phone }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-3">Application Status</h4>
                        <div class="space-y-2">
                            <div class="flex items-center space-x-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="getApplicationStatusClass(application.status)"
                                >
                                    {{ getApplicationStatusLabel(application.status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">Applied: {{ formatDateTime(application.created_at) }}</p>
                            <p v-if="application.reviewed_at" class="text-sm text-gray-600">
                                Reviewed: {{ formatDateTime(application.reviewed_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="application.topic_title || application.topic_description" class="mt-6">
                    <h4 class="text-sm font-medium text-gray-500 mb-3">Topic Information</h4>
                    <div v-if="application.topic_title" class="mb-3">
                        <dt class="text-sm font-medium text-gray-900">Title</dt>
                        <dd class="text-sm text-gray-600">{{ application.topic_title }}</dd>
                    </div>
                    <div v-if="application.topic_description" class="mb-3">
                        <dt class="text-sm font-medium text-gray-900">Description</dt>
                        <dd class="text-sm text-gray-600 whitespace-pre-wrap">{{ application.topic_description }}</dd>
                    </div>
                </div>

                <div v-if="application.bio" class="mt-6">
                    <h4 class="text-sm font-medium text-gray-500 mb-3">Biography</h4>
                    <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ application.bio }}</p>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 bg-white text-gray-500 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    >
                        Close
                    </button>
                    <button
                        v-if="application.status === 'pending'"
                        @click="$emit('reject')"
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
                    >
                        Reject
                    </button>
                    <button
                        v-if="application.status === 'pending'"
                        @click="$emit('approve')"
                        class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
                    >
                        Approve
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    application: Object
});

defineEmits(['close', 'approve', 'reject']);

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

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>
