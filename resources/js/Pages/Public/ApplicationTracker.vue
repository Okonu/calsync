<template>
    <div class="min-h-screen bg-gray-50">
        <Head :title="`Application Status - ${application.cfs.title}`" />

        <!-- Header -->
        <div class="bg-white shadow-sm">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-900">Application Status</h1>
                    <p class="mt-2 text-lg text-gray-600">Track your speaker application progress</p>
                </div>
            </div>
        </div>

        <!-- Application Status -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="px-6 py-8">
                    <!-- Status Header -->
                    <div class="text-center mb-8">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4"
                             :class="getStatusIconClass(application.status)">
                            <svg v-if="application.status === 'pending'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg v-else-if="application.status === 'approved'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg v-else-if="application.status === 'rejected'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            {{ getStatusTitle(application.status) }}
                        </h2>
                        <p class="text-lg text-gray-600">
                            {{ getStatusDescription(application.status) }}
                        </p>
                    </div>

                    <!-- Application Details -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-4">Application Details</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Application ID</dt>
                                        <dd class="text-sm text-gray-600 font-mono">{{ application.uid }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Applicant</dt>
                                        <dd class="text-sm text-gray-600">{{ application.applicant_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Topic</dt>
                                        <dd class="text-sm text-gray-600">{{ application.topic_title || 'Not specified' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Application Target</dt>
                                        <dd class="text-sm text-gray-600">
                                            {{ application.target.title }} ({{ application.target.type }})
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-4">Timeline</h3>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Submitted</dt>
                                        <dd class="text-sm text-gray-600">{{ formatDateTime(application.created_at) }}</dd>
                                    </div>
                                    <div v-if="application.reviewed_at">
                                        <dt class="text-sm font-medium text-gray-900">Reviewed</dt>
                                        <dd class="text-sm text-gray-600">{{ formatDateTime(application.reviewed_at) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Status</dt>
                                        <dd>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                  :class="getStatusBadgeClass(application.status)">
                                                {{ getStatusLabel(application.status) }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Rejection Reason -->
                    <div v-if="application.status === 'rejected' && application.rejection_reason" class="border-t border-gray-200 pt-6 mt-6">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Feedback</h3>
                        <div class="bg-red-50 border border-red-200 rounded-md p-4">
                            <p class="text-sm text-red-700">{{ application.rejection_reason }}</p>
                        </div>
                    </div>

                    <!-- Call for Speakers Info -->
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <h3 class="text-sm font-medium text-gray-900 mb-4">Call for Speakers</h3>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ application.cfs.title }}</h4>
                                    <p class="text-sm text-gray-600">by {{ application.cfs.community.name }}</p>
                                </div>
                                <Link :href="`/community/${application.cfs.community.slug}/cfs/${application.cfs.slug}`"
                                      class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                                    View CFS
                                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <Link :href="`/community/${application.cfs.community.slug}`"
                                  class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                View Community
                            </Link>

                            <button v-if="application.status === 'pending'"
                                    @click="showWithdrawModal = true"
                                    class="inline-flex items-center justify-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Withdraw Application
                            </button>

                            <button @click="copyTrackingLink"
                                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Copy Tracking Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Application Progress</h3>
                </div>
                <div class="px-6 py-6">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <!-- Submitted -->
                            <li>
                                <div class="relative pb-8">
                                    <div class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                                    <div class="relative flex items-start space-x-3">
                                        <div class="relative">
                                            <div class="h-8 w-8 bg-green-500 rounded-full ring-8 ring-white flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div>
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-900">Application Submitted</span>
                                                </div>
                                                <p class="mt-0.5 text-sm text-gray-500">
                                                    {{ formatDateTime(application.created_at) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- Under Review -->
                            <li>
                                <div class="relative pb-8">
                                    <div v-if="application.status !== 'pending'" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                                    <div class="relative flex items-start space-x-3">
                                        <div class="relative">
                                            <div class="h-8 w-8 rounded-full ring-8 ring-white flex items-center justify-center"
                                                 :class="application.status === 'pending' ? 'bg-yellow-500' : 'bg-green-500'">
                                                <svg v-if="application.status === 'pending'" class="w-4 h-4 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <svg v-else class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div>
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-900">
                                                        {{ application.status === 'pending' ? 'Under Review' : 'Review Completed' }}
                                                    </span>
                                                </div>
                                                <p class="mt-0.5 text-sm text-gray-500">
                                                    {{ application.status === 'pending' ? 'Your application is being reviewed by the organizers' : formatDateTime(application.reviewed_at) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>

                            <!-- Final Status -->
                            <li v-if="application.status !== 'pending'">
                                <div class="relative flex items-start space-x-3">
                                    <div class="relative">
                                        <div class="h-8 w-8 rounded-full ring-8 ring-white flex items-center justify-center"
                                             :class="application.status === 'approved' ? 'bg-green-500' : 'bg-red-500'">
                                            <svg v-if="application.status === 'approved'" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            <svg v-else class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div>
                                            <div class="text-sm">
                                                <span class="font-medium text-gray-900">
                                                    {{ application.status === 'approved' ? 'Application Approved' : 'Application Declined' }}
                                                </span>
                                            </div>
                                            <p class="mt-0.5 text-sm text-gray-500">
                                                {{ formatDateTime(application.reviewed_at) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Withdraw Modal -->
        <div v-if="showWithdrawModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mt-2">Withdraw Application</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to withdraw your application? This action cannot be undone.
                        </p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button
                            @click="withdrawApplication"
                            :disabled="withdrawForm.processing"
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:opacity-50"
                        >
                            {{ withdrawForm.processing ? 'Withdrawing...' : 'Withdraw Application' }}
                        </button>
                        <button
                            @click="showWithdrawModal = false"
                            class="mt-3 px-4 py-2 bg-white text-gray-500 text-base font-medium rounded-md w-full shadow-sm border border-gray-300 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    application: Object,
});

const showWithdrawModal = ref(false);
const withdrawForm = useForm({});

function getStatusIconClass(status) {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-600';
        case 'approved':
            return 'bg-green-100 text-green-600';
        case 'rejected':
            return 'bg-red-100 text-red-600';
        default:
            return 'bg-gray-100 text-gray-600';
    }
}

function getStatusBadgeClass(status) {
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

function getStatusTitle(status) {
    switch (status) {
        case 'pending':
            return 'Application Under Review';
        case 'approved':
            return 'Application Approved!';
        case 'rejected':
            return 'Application Declined';
        case 'withdrawn':
            return 'Application Withdrawn';
        default:
            return 'Application Status';
    }
}

function getStatusDescription(status) {
    switch (status) {
        case 'pending':
            return 'Your application is being reviewed by the event organizers. We\'ll notify you once a decision is made.';
        case 'approved':
            return 'Congratulations! Your speaker application has been approved. The organizers will contact you with next steps.';
        case 'rejected':
            return 'Thank you for your application. Unfortunately, we were unable to accept your proposal for this event.';
        case 'withdrawn':
            return 'Your application has been withdrawn at your request.';
        default:
            return 'Check the status of your speaker application.';
    }
}

function getStatusLabel(status) {
    switch (status) {
        case 'pending':
            return 'Under Review';
        case 'approved':
            return 'Approved';
        case 'rejected':
            return 'Declined';
        case 'withdrawn':
            return 'Withdrawn';
        default:
            return status;
    }
}

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function copyTrackingLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Tracking link copied to clipboard!');
    }).catch(() => {
        alert('Failed to copy link. Please copy the URL from your browser.');
    });
}

function withdrawApplication() {
    withdrawForm.patch(`/applications/track/${props.application.uid}/withdraw`, {
        onSuccess: () => {
            showWithdrawModal.value = false;
        }
    });
}
</script>
