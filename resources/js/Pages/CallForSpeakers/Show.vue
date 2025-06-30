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
                        v-if="cfs.is_public && cfs.status !== 'draft'"
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
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ cfs.title }}</h1>
                                <div class="flex items-center space-x-4 mt-2">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                        :class="getStatusClass(cfs.status)"
                                    >
                                        {{ getStatusLabel(cfs.status) }}
                                    </span>
                                    <span v-if="isClosingSoon(cfs)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        Closing Soon
                                    </span>
                                    <span v-if="!cfs.is_public" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        Private
                                    </span>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    v-if="cfs.status === 'draft'"
                                    @click="updateStatus('open')"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    Publish
                                </button>
                                <button
                                    v-if="cfs.status === 'open'"
                                    @click="updateStatus('closed')"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                                >
                                    Close
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Timeline</h3>
                                <div class="space-y-2 text-sm">
                                    <div v-if="cfs.opens_at" class="flex items-center text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Opens: {{ formatDate(cfs.opens_at) }}
                                    </div>
                                    <div v-if="cfs.closes_at" class="flex items-center text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Closes: {{ formatDate(cfs.closes_at) }}
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Application Type</h3>
                                <p class="text-sm text-gray-900">{{ getApplicationTypeLabel(cfs.application_type) }}</p>
                                <div class="mt-2">
                                    <p class="text-xs text-gray-500">
                                        {{ cfs.required_fields?.length || 0 }} required fields
                                        <span v-if="cfs.custom_questions?.length">, {{ cfs.custom_questions.length }} custom questions</span>
                                    </p>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-2">Settings</h3>
                                <div class="space-y-1 text-xs">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" :class="cfs.requires_login ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span :class="cfs.requires_login ? 'text-gray-900' : 'text-gray-500'">Requires Login</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" :class="cfs.show_application_count ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span :class="cfs.show_application_count ? 'text-gray-900' : 'text-gray-500'">Show Count</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" :class="cfs.allow_multiple_applications ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span :class="cfs.allow_multiple_applications ? 'text-gray-900' : 'text-gray-500'">Multiple Apps</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" :class="cfs.auto_approve ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span :class="cfs.auto_approve ? 'text-gray-900' : 'text-gray-500'">Auto Approve</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="cfs.description" class="mt-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
                            <p class="text-gray-900 whitespace-pre-wrap">{{ cfs.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Applications</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.total_applications }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Pending Review</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.pending_applications }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Approved</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.approved_applications }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Rejected</dt>
                                        <dd class="text-lg font-medium text-gray-900">{{ stats.rejected_applications }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Applications Management -->
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
                                <select v-model="selectedFilter" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Applications</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>

                                <!-- Bulk Actions -->
                                <div v-if="selectedApplications.length > 0" class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-500">{{ selectedApplications.length }} selected</span>
                                    <button
                                        @click="showBulkActions = true"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Bulk Actions
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Applications List -->
                    <div v-if="filteredApplications.length === 0" class="p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No applications</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ selectedFilter ? 'No applications match your current filter.' : 'No applications have been submitted yet.' }}
                        </p>
                    </div>

                    <ul v-else class="divide-y divide-gray-200">
                        <li v-for="application in filteredApplications" :key="application.id" class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <input
                                        type="checkbox"
                                        :value="application.id"
                                        v-model="selectedApplications"
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
                                        @click="approveApplication(application)"
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        v-if="application.status === 'pending'"
                                        @click="rejectApplication(application)"
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    >
                                        Reject
                                    </button>
                                    <button
                                        @click="viewApplication(application)"
                                        class="inline-flex items-center px-3 py-1 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

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

        <!-- Application Details Modal -->
        <div v-if="selectedApplication" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Application Details</h3>
                        <button @click="selectedApplication = null" class="text-gray-400 hover:text-gray-600">
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
                                    <dd class="text-sm text-gray-600">{{ selectedApplication.applicant_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-900">Email</dt>
                                    <dd class="text-sm text-gray-600">{{ selectedApplication.applicant_email }}</dd>
                                </div>
                                <div v-if="selectedApplication.applicant_phone">
                                    <dt class="text-sm font-medium text-gray-900">Phone</dt>
                                    <dd class="text-sm text-gray-600">{{ selectedApplication.applicant_phone }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-3">Application Status</h4>
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="getApplicationStatusClass(selectedApplication.status)"
                                    >
                                        {{ getApplicationStatusLabel(selectedApplication.status) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600">Applied: {{ formatDateTime(selectedApplication.created_at) }}</p>
                                <p v-if="selectedApplication.reviewed_at" class="text-sm text-gray-600">
                                    Reviewed: {{ formatDateTime(selectedApplication.reviewed_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedApplication.topic_title || selectedApplication.topic_description" class="mt-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-3">Topic Information</h4>
                        <div v-if="selectedApplication.topic_title" class="mb-3">
                            <dt class="text-sm font-medium text-gray-900">Title</dt>
                            <dd class="text-sm text-gray-600">{{ selectedApplication.topic_title }}</dd>
                        </div>
                        <div v-if="selectedApplication.topic_description" class="mb-3">
                            <dt class="text-sm font-medium text-gray-900">Description</dt>
                            <dd class="text-sm text-gray-600 whitespace-pre-wrap">{{ selectedApplication.topic_description }}</dd>
                        </div>
                    </div>

                    <div v-if="selectedApplication.bio" class="mt-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-3">Biography</h4>
                        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ selectedApplication.bio }}</p>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            @click="selectedApplication = null"
                            class="px-4 py-2 bg-white text-gray-500 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        >
                            Close
                        </button>
                        <button
                            v-if="selectedApplication.status === 'pending'"
                            @click="rejectApplicationWithModal(selectedApplication)"
                            class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
                        >
                            Reject
                        </button>
                        <button
                            v-if="selectedApplication.status === 'pending'"
                            @click="approveApplicationWithModal(selectedApplication)"
                            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
                        >
                            Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Modal -->
        <div v-if="showBulkActions" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Actions</h3>
                    <p class="text-sm text-gray-600 mb-4">{{ selectedApplications.length }} applications selected</p>

                    <div class="space-y-3">
                        <button
                            @click="bulkApprove"
                            class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
                        >
                            Approve Selected
                        </button>
                        <button
                            @click="bulkReject"
                            class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
                        >
                            Reject Selected
                        </button>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            @click="showBulkActions = false"
                            class="px-4 py-2 bg-white text-gray-500 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-100"
                        >
                            Cancel
                        </button>
                    </div>
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
    stats: Object,
});

const selectedFilter = ref('');
const selectedApplications = ref([]);
const selectedApplication = ref(null);
const showBulkActions = ref(false);

const statusForm = useForm({});
const approveForm = useForm({});
const rejectForm = useForm({});
const bulkForm = useForm({});

const filteredApplications = computed(() => {
    if (!props.cfs.applications) return [];

    let filtered = [...props.cfs.applications];

    if (selectedFilter.value) {
        filtered = filtered.filter(app => app.status === selectedFilter.value);
    }

    return filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

function isOpen(cfs) {
    return cfs.status === 'open' && (!cfs.closes_at || new Date(cfs.closes_at) > new Date());
}

function isClosingSoon(cfs) {
    if (!cfs.closes_at || !isOpen(cfs)) return false;
    const now = new Date();
    const closesAt = new Date(cfs.closes_at);
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

function getApplicationTypeLabel(type) {
    switch (type) {
        case 'event':
            return 'Apply to entire events';
        case 'session':
            return 'Apply to specific sessions';
        case 'both':
            return 'Allow both options';
        default:
            return type;
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

function formatDateTime(dateString) {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function updateStatus(newStatus) {
    const confirmMessage = newStatus === 'open'
        ? 'Are you sure you want to publish this call for speakers? It will become public and start accepting applications.'
        : 'Are you sure you want to close this call for speakers? No new applications will be accepted.';

    if (confirm(confirmMessage)) {
        statusForm.patch(`/api/communities/${props.community.slug}/cfs/${props.cfs.slug}/status`, {
            data: { status: newStatus },
            onSuccess: () => {
                props.cfs.status = newStatus;
            }
        });
    }
}

function viewApplication(application) {
    selectedApplication.value = application;
}

function approveApplication(application) {
    approveForm.patch(`/api/cfs/${props.cfs.id}/applications/${application.id}/approve`, {
        onSuccess: () => {
            application.status = 'approved';
            application.reviewed_at = new Date().toISOString();
        }
    });
}

function rejectApplication(application) {
    const reason = prompt('Please provide a reason for rejection (optional):');
    if (reason !== null) {
        rejectForm.patch(`/api/cfs/${props.cfs.id}/applications/${application.id}/reject`, {
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
    approveApplication(application);
    selectedApplication.value = null;
}

function rejectApplicationWithModal(application) {
    rejectApplication(application);
    selectedApplication.value = null;
}

function bulkApprove() {
    if (confirm(`Are you sure you want to approve ${selectedApplications.value.length} applications?`)) {
        bulkForm.patch(`/api/cfs/${props.cfs.id}/applications/bulk-approve`, {
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
        bulkForm.patch(`/api/cfs/${props.cfs.id}/applications/bulk-reject`, {
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
</script>
