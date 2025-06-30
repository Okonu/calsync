<template>
    <AppLayout :title="`${cfs.title} - ${community.name}`">
        <Head :title="`${cfs.title} - ${community.name}`" />

        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="`/communities/${community.slug}`" class="text-indigo-600 hover:text-indigo-900">
                        {{ community.name }}
                    </Link>
                    <span class="text-gray-400">/</span>
                    <Link :href="`/communities/${community.slug}/cfs`" class="text-indigo-600 hover:text-indigo-900">
                        Call for Speakers
                    </Link>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-800">{{ cfs.title }}</span>
                </div>
                <div class="flex space-x-3">
                    <Link
                        :href="`/communities/${community.slug}/cfs/${cfs.slug}/edit`"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                    >
                        Edit CFS
                    </Link>
                    <Link
                        v-if="cfs.is_public && cfs.status !== 'draft' && cfs.public_url"
                        :href="cfs.public_url"
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
                <!-- CFS Overview -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <!-- Status Header Component -->
                        <CfsStatusHeader
                            :cfs="cfs"
                            :available-actions="availableActions"
                            :is-updating="isUpdatingStatus"
                            @status-update="updateStatus"
                        />

                        <!-- Overview Card Component -->
                        <CfsOverviewCard :cfs="cfs" />
                    </div>
                </div>

                <!-- Stats Cards Component -->
                <div class="mb-8">
                    <StatsCards :stats="stats" />
                </div>

                <!-- Applications Table Component -->
                <ApplicationsTable
                    :applications="filteredApplications"
                    :selected-filter="selectedFilter"
                    :selected-applications="selectedApplications"
                    @filter-change="selectedFilter = $event"
                    @bulk-actions="showBulkActions = true"
                    @approve="approveApplication"
                    @reject="rejectApplication"
                    @view="viewApplication"
                    @selection-change="toggleApplicationSelection"
                />

                <!-- Linked Events -->
                <div v-if="cfs.events && cfs.events.length > 0" class="mt-8 bg-white shadow overflow-hidden sm:rounded-md">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Linked Events</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Events accepting applications from this call for speakers
                        </p>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        <li v-for="event in cfs.events" :key="event.id" class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <Link
                                        :href="`/communities/${community.slug}/events/${event.id}`"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                    >
                                        {{ event.title }}
                                    </Link>
                                    <p class="text-sm text-gray-500">{{ formatDate(event.starts_at) }}</p>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm text-gray-500">{{ event.sessions?.length || 0 }} sessions</span>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="getEventStatusClass(event.status)"
                                    >
                                        {{ event.status }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Application Details Modal Component -->
        <ApplicationModal
            :application="selectedApplication"
            @close="selectedApplication = null"
            @approve="approveApplicationWithModal"
            @reject="rejectApplicationWithModal"
        />

        <!-- Bulk Actions Modal Component -->
        <BulkActionsModal
            :show="showBulkActions"
            :selected-count="selectedApplications.length"
            @close="showBulkActions = false"
            @bulk-approve="bulkApprove"
            @bulk-reject="bulkReject"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CfsStatusHeader from './Components/CfsStatusHeader.vue';
import CfsOverviewCard from './Components/CfsOverviewCard.vue';
import StatsCards from './Components/StatsCards.vue';
import ApplicationsTable from './Components/ApplicationsTable.vue';
import ApplicationModal from './Components/ApplicationModal.vue';
import BulkActionsModal from './Components/BulkActionsModal.vue';

const props = defineProps({
    community: Object,
    cfs: Object,
    stats: Object,
});

// Reactive state
const selectedFilter = ref('');
const selectedApplications = ref([]);
const selectedApplication = ref(null);
const showBulkActions = ref(false);
const isUpdatingStatus = ref(false);
const availableActions = ref(props.cfs.available_actions || getDefaultActions(props.cfs.status));

// Forms - for application management and status updates
const approveForm = useForm({});
const rejectForm = useForm({});
const bulkForm = useForm({});
const statusForm = useForm({
    status: ''
});

// Computed
const filteredApplications = computed(() => {
    if (!props.cfs.applications) return [];

    let filtered = [...props.cfs.applications];

    if (selectedFilter.value) {
        filtered = filtered.filter(app => app.status === selectedFilter.value);
    }

    return filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

// Methods
function updateStatus(newStatus, action) {
    isUpdatingStatus.value = true;

    // Set the status on the form object directly
    statusForm.status = newStatus;

    statusForm.patch(route('communities.cfs.update-status', [props.community.slug, props.cfs.slug]), {
        onSuccess: () => {
            props.cfs.status = newStatus;
            availableActions.value = getDefaultActions(newStatus);
        },
        onError: (errors) => {
            console.error('Status update failed:', errors);
            if (errors.error) {
                alert(errors.error);
            }
        },
        onFinish: () => {
            isUpdatingStatus.value = false;
        }
    });
}

function getDefaultActions(status) {
    const actions = [];

    switch (status) {
        case 'draft':
            actions.push({
                key: 'publish',
                label: 'Publish',
                status: 'open',
                class: 'bg-green-600 hover:bg-green-700 text-white',
                icon: 'publish',
                confirm: 'Are you sure you want to publish this CFS? It will become public and start accepting applications.'
            });
            actions.push({
                key: 'archive',
                label: 'Archive',
                status: 'archived',
                class: 'bg-gray-600 hover:bg-gray-700 text-white',
                icon: 'archive'
            });
            break;

        case 'open':
            actions.push({
                key: 'close',
                label: 'Close',
                status: 'closed',
                class: 'bg-orange-600 hover:bg-orange-700 text-white',
                icon: 'close',
                confirm: 'Are you sure you want to close this CFS? No new applications will be accepted.'
            });
            actions.push({
                key: 'archive',
                label: 'Archive',
                status: 'archived',
                class: 'bg-gray-600 hover:bg-gray-700 text-white',
                icon: 'archive'
            });
            break;

        case 'closed':
            actions.push({
                key: 'reopen',
                label: 'Reopen',
                status: 'open',
                class: 'bg-blue-600 hover:bg-blue-700 text-white',
                icon: 'reopen',
                confirm: 'Are you sure you want to reopen this CFS for new applications?'
            });
            actions.push({
                key: 'unpublish',
                label: 'Back to Draft',
                status: 'draft',
                class: 'bg-yellow-600 hover:bg-yellow-700 text-white',
                icon: 'draft'
            });
            actions.push({
                key: 'archive',
                label: 'Archive',
                status: 'archived',
                class: 'bg-gray-600 hover:bg-gray-700 text-white',
                icon: 'archive'
            });
            break;

        case 'archived':
            actions.push({
                key: 'restore',
                label: 'Restore to Draft',
                status: 'draft',
                class: 'bg-indigo-600 hover:bg-indigo-700 text-white',
                icon: 'restore'
            });
            break;
    }

    return actions;
}

function toggleApplicationSelection(applicationId) {
    const index = selectedApplications.value.indexOf(applicationId);
    if (index > -1) {
        selectedApplications.value.splice(index, 1);
    } else {
        selectedApplications.value.push(applicationId);
    }
}

function viewApplication(application) {
    selectedApplication.value = application;
}

function approveApplication(application) {
    approveForm.patch(`/api/communities/${props.community.slug}/cfs/${props.cfs.slug}/applications/${application.id}/approve`, {
        onSuccess: () => {
            application.status = 'approved';
            application.reviewed_at = new Date().toISOString();
        }
    });
}

function rejectApplication(application) {
    const reason = prompt('Please provide a reason for rejection (optional):');
    if (reason !== null) {
        rejectForm.patch(`/api/communities/${props.community.slug}/cfs/${props.cfs.slug}/applications/${application.id}/reject`, {
            data: { reason },
            onSuccess: () => {
                application.status = 'rejected';
                application.reviewed_at = new Date().toISOString();
                application.rejection_reason = reason;
            }
        });
    }
}

function approveApplicationWithModal(application) {
    approveApplication(selectedApplication.value);
    selectedApplication.value = null;
}

function rejectApplicationWithModal(application) {
    rejectApplication(selectedApplication.value);
    selectedApplication.value = null;
}

function bulkApprove() {
    if (confirm(`Are you sure you want to approve ${selectedApplications.value.length} applications?`)) {
        bulkForm.patch(`/api/communities/${props.community.slug}/cfs/${props.cfs.slug}/applications/bulk-approve`, {
            data: { application_ids: selectedApplications.value },
            onSuccess: () => {
                selectedApplications.value.forEach(id => {
                    const app = props.cfs.applications.find(a => a.id === id);
                    if (app) {
                        app.status = 'approved';
                        app.reviewed_at = new Date().toISOString();
                    }
                });
                selectedApplications.value = [];
                showBulkActions.value = false;
            }
        });
    }
}

function bulkReject() {
    const reason = prompt('Please provide a reason for rejection (optional):');
    if (reason !== null && confirm(`Are you sure you want to reject ${selectedApplications.value.length} applications?`)) {
        bulkForm.patch(`/api/communities/${props.community.slug}/cfs/${props.cfs.slug}/applications/bulk-reject`, {
            data: {
                application_ids: selectedApplications.value,
                reason: reason
            },
            onSuccess: () => {
                selectedApplications.value.forEach(id => {
                    const app = props.cfs.applications.find(a => a.id === id);
                    if (app) {
                        app.status = 'rejected';
                        app.reviewed_at = new Date().toISOString();
                        app.rejection_reason = reason;
                    }
                });
                selectedApplications.value = [];
                showBulkActions.value = false;
            }
        });
    }
}

function getEventStatusClass(status) {
    switch (status) {
        case 'published':
            return 'bg-green-100 text-green-800';
        case 'draft':
            return 'bg-yellow-100 text-yellow-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        default:
            return 'bg-gray-100 text-gray-800';
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
