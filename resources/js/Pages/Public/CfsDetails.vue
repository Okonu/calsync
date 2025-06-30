<template>
    <div class="min-h-screen bg-gray-50">
        <Head :title="`${cfs.title} - ${community.name}`" />

        <!-- CFS Header -->
        <div class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Breadcrumb -->
                <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                    <Link :href="`/community/${community.slug}`" class="hover:text-gray-700">
                        {{ community.name }}
                    </Link>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <Link :href="`/community/${community.slug}/cfs`" class="hover:text-gray-700">
                        Call for Speakers
                    </Link>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-gray-900">{{ cfs.title }}</span>
                </nav>

                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-4">
                            <h1 class="text-3xl font-bold text-gray-900">{{ cfs.title }}</h1>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :class="getStatusClass(cfs)"
                            >
                                {{ getStatusLabel(cfs) }}
                            </span>
                            <span v-if="isClosingSoon(cfs)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Closing Soon
                            </span>
                        </div>

                        <p v-if="cfs.description" class="text-lg text-gray-600 mb-6">{{ cfs.description }}</p>

                        <!-- CFS Meta Information -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div v-if="cfs.closes_at" class="flex items-center text-sm text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>
                                    {{ isOpen(cfs) ? 'Closes' : 'Closed' }} {{ formatDate(cfs.closes_at) }}
                                </span>
                            </div>

                            <div v-if="applicationCount !== null" class="flex items-center text-sm text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ applicationCount }} application{{ applicationCount !== 1 ? 's' : '' }}</span>
                            </div>

                            <div class="flex items-center text-sm text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H5m14 0v-2a2 2 0 00-2-2H7a2 2 0 00-2 2v2" />
                                </svg>
                                <span>by {{ community.name }}</span>
                            </div>
                        </div>

                        <!-- Time Remaining -->
                        <div v-if="isOpen(cfs) && cfs.closes_at" class="mb-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Time Remaining</span>
                                    <span class="text-sm font-semibold" :class="isClosingSoon(cfs) ? 'text-red-600' : 'text-green-600'">
                                        {{ getTimeRemaining(cfs.closes_at) }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div
                                        class="h-2 rounded-full transition-all duration-300"
                                        :class="isClosingSoon(cfs) ? 'bg-red-500' : 'bg-green-500'"
                                        :style="{ width: getProgressPercentage(cfs) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Application Form -->
                    <div v-if="canApply" class="bg-white rounded-lg shadow-sm p-6 mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Submit Your Application</h2>

                        <form @submit.prevent="submitApplication">
                            <!-- Personal Information -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Name *</label>
                                        <input
                                            v-model="applicationForm.applicant_name"
                                            type="text"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                        <div v-if="applicationForm.errors.applicant_name" class="text-red-500 text-sm mt-1">{{ applicationForm.errors.applicant_name }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email *</label>
                                        <input
                                            v-model="applicationForm.applicant_email"
                                            type="email"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                        <div v-if="applicationForm.errors.applicant_email" class="text-red-500 text-sm mt-1">{{ applicationForm.errors.applicant_email }}</div>
                                    </div>
                                    <div v-if="isFieldRequired('phone')" class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Phone {{ isFieldRequired('phone') ? '*' : '' }}</label>
                                        <input
                                            v-model="applicationForm.applicant_phone"
                                            type="tel"
                                            :required="isFieldRequired('phone')"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                        <div v-if="applicationForm.errors.applicant_phone" class="text-red-500 text-sm mt-1">{{ applicationForm.errors.applicant_phone }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Application Target -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Application Target</h3>
                                <div class="space-y-4">
                                    <div v-if="availableTargets.events && availableTargets.events.length > 0">
                                        <label class="flex items-center">
                                            <input
                                                v-model="applicationForm.target_type"
                                                type="radio"
                                                value="event"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                            >
                                            <span class="ml-2 text-sm text-gray-900">Apply to speak at an event</span>
                                        </label>
                                        <div v-if="applicationForm.target_type === 'event'" class="mt-2 ml-6">
                                            <select
                                                v-model="applicationForm.community_event_id"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="">Select an event</option>
                                                <option v-for="event in availableTargets.events" :key="event.id" :value="event.id">
                                                    {{ event.title }} - {{ formatDate(event.starts_at) }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div v-if="availableTargets.sessions && availableTargets.sessions.length > 0">
                                        <label class="flex items-center">
                                            <input
                                                v-model="applicationForm.target_type"
                                                type="radio"
                                                value="session"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                            >
                                            <span class="ml-2 text-sm text-gray-900">Apply to speak at a specific session</span>
                                        </label>
                                        <div v-if="applicationForm.target_type === 'session'" class="mt-2 ml-6">
                                            <select
                                                v-model="applicationForm.event_session_id"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="">Select a session</option>
                                                <option v-for="session in availableTargets.sessions" :key="session.id" :value="session.id">
                                                    {{ session.title }} ({{ session.communityEvent.title }})
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="applicationForm.errors.target_type" class="text-red-500 text-sm mt-1">{{ applicationForm.errors.target_type }}</div>
                            </div>

                            <!-- Speaker Information -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Speaker Information</h3>
                                <div class="space-y-4">
                                    <div v-if="isFieldRequired('bio') || !isFieldRequired('bio')">
                                        <label class="block text-sm font-medium text-gray-700">Bio {{ isFieldRequired('bio') ? '*' : '' }}</label>
                                        <textarea
                                            v-model="applicationForm.bio"
                                            rows="4"
                                            :required="isFieldRequired('bio')"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Tell us about yourself, your background, and experience..."
                                        ></textarea>
                                        <div v-if="applicationForm.errors.bio" class="text-red-500 text-sm mt-1">{{ applicationForm.errors.bio }}</div>
                                    </div>

                                    <div v-if="isFieldRequired('previous_speaking_experience')">
                                        <label class="block text-sm font-medium text-gray-700">Previous Speaking Experience {{ isFieldRequired('previous_speaking_experience') ? '*' : '' }}</label>
                                        <textarea
                                            v-model="applicationForm.previous_speaking_experience"
                                            rows="3"
                                            :required="isFieldRequired('previous_speaking_experience')"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Describe your previous speaking experience..."
                                        ></textarea>
                                        <div v-if="applicationForm.errors.previous_speaking_experience" class="text-red-500 text-sm mt-1">{{ applicationForm.errors.previous_speaking_experience }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Topic Information -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Topic Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Topic Title {{ isFieldRequired('topic_title') ? '*' : '' }}</label>
                                        <input
                                            v-model="applicationForm.topic_title"
                                            type="text"
                                            :required="isFieldRequired('topic_title')"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="The title of your presentation"
                                        >
                                        <div v-if="applicationForm.errors.topic_title" class="text-red-500 text-sm mt-1">{{ applicationForm.errors.topic_title }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Topic Description {{ isFieldRequired('topic_description') ? '*' : '' }}</label>
                                        <textarea
                                            v-model="applicationForm.topic_description"
                                            rows="4"
                                            :required="isFieldRequired('topic_description')"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Describe what you'll be presenting about..."
                                        ></textarea>
                                        <div v-if="applicationForm.errors.topic_description" class="text-red-500 text-sm mt-1">{{ applicationForm.errors.topic_description }}</div>
                                    </div>

                                    <div v-if="isFieldRequired('topic_outline')">
                                        <label class="block text-sm font-medium text-gray-700">Topic Outline {{ isFieldRequired('topic_outline') ? '*' : '' }}</label>
                                        <textarea
                                            v-model="applicationForm.topic_outline"
                                            rows="6"
                                            :required="isFieldRequired('topic_outline')"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Provide a detailed outline of your presentation..."
                                        ></textarea>
                                        <div v-if="applicationForm.errors.topic_outline" class="text-red-500 text-sm mt-1">{{ applicationForm.errors.topic_outline }}</div>
                                    </div>

                                    <div v-if="isFieldRequired('experience_level')">
                                        <label class="block text-sm font-medium text-gray-700">Experience Level {{ isFieldRequired('experience_level') ? '*' : '' }}</label>
                                        <select
                                            v-model="applicationForm.experience_level"
                                            :required="isFieldRequired('experience_level')"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">Select experience level</option>
                                            <option value="beginner">Beginner</option>
                                            <option value="intermediate">Intermediate</option>
                                            <option value="advanced">Advanced</option>
                                            <option value="expert">Expert</option>
                                        </select>
                                        <div v-if="applicationForm.errors.experience_level" class="text-red-500 text-sm mt-1">{{ applicationForm.errors.experience_level }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Questions -->
                            <div v-if="cfs.custom_questions && cfs.custom_questions.length > 0" class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Questions</h3>
                                <div class="space-y-4">
                                    <div v-for="(question, index) in cfs.custom_questions" :key="index">
                                        <label class="block text-sm font-medium text-gray-700">
                                            {{ question.question }} {{ question.required ? '*' : '' }}
                                        </label>

                                        <!-- Text Input -->
                                        <input
                                            v-if="question.type === 'text'"
                                            v-model="applicationForm.custom_responses[index]"
                                            type="text"
                                            :required="question.required"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >

                                        <!-- Textarea -->
                                        <textarea
                                            v-else-if="question.type === 'textarea'"
                                            v-model="applicationForm.custom_responses[index]"
                                            rows="3"
                                            :required="question.required"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        ></textarea>

                                        <!-- Select -->
                                        <select
                                            v-else-if="question.type === 'select'"
                                            v-model="applicationForm.custom_responses[index]"
                                            :required="question.required"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">Select an option</option>
                                            <option v-for="option in question.options" :key="option" :value="option">
                                                {{ option }}
                                            </option>
                                        </select>

                                        <!-- Radio -->
                                        <div v-else-if="question.type === 'radio'" class="mt-1 space-y-2">
                                            <label v-for="option in question.options" :key="option" class="flex items-center">
                                                <input
                                                    v-model="applicationForm.custom_responses[index]"
                                                    type="radio"
                                                    :value="option"
                                                    :required="question.required"
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                                >
                                                <span class="ml-2 text-sm text-gray-900">{{ option }}</span>
                                            </label>
                                        </div>

                                        <!-- Checkbox -->
                                        <div v-else-if="question.type === 'checkbox'" class="mt-1 space-y-2">
                                            <label v-for="option in question.options" :key="option" class="flex items-center">
                                                <input
                                                    :checked="applicationForm.custom_responses[index] && applicationForm.custom_responses[index].includes(option)"
                                                    @change="handleCheckboxChange(index, option, $event)"
                                                    type="checkbox"
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                >
                                                <span class="ml-2 text-sm text-gray-900">{{ option }}</span>
                                            </label>
                                        </div>

                                        <div v-if="applicationForm.errors[`custom_responses.${index}`]" class="text-red-500 text-sm mt-1">
                                            {{ applicationForm.errors[`custom_responses.${index}`] }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- File Attachments -->
                            <div class="mb-8">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Attachments (Optional)</label>
                                <input
                                    ref="attachmentInput"
                                    type="file"
                                    multiple
                                    accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif"
                                    @change="handleAttachments"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                >
                                <p class="text-xs text-gray-500 mt-1">
                                    You can upload up to 5 files. Supported formats: PDF, Word documents, text files, images (max 10MB each)
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="applicationForm.processing"
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    <svg v-if="applicationForm.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ applicationForm.processing ? 'Submitting...' : 'Submit Application' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Closed Application State -->
                    <div v-else class="bg-white rounded-lg shadow-sm p-6 mb-8">
                        <div class="text-center py-8">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Applications Closed</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                This call for speakers is not currently accepting applications.
                            </p>
                        </div>
                    </div>

                    <!-- Guidelines -->
                    <div v-if="cfs.guidelines" class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Speaker Guidelines</h2>
                        <div class="prose max-w-none text-gray-600">
                            <p class="whitespace-pre-wrap">{{ cfs.guidelines }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Apply CTA -->
                    <div v-if="canApply" class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-6 text-white mb-6">
                        <h3 class="text-lg font-semibold mb-2">Ready to Apply?</h3>
                        <p class="text-indigo-100 text-sm mb-4">Join our community of amazing speakers and share your knowledge.</p>
                        <button
                            @click="scrollToApplication"
                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-white text-indigo-600 rounded-md font-medium hover:bg-gray-50 transition-colors"
                        >
                            Apply Now
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </button>
                    </div>

                    <!-- Community Info -->
                    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">About the Community</h3>
                        <div class="flex items-center space-x-3 mb-4">
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
                                <h4 class="font-medium text-gray-900">{{ community.name }}</h4>
                                <p v-if="community.description" class="text-sm text-gray-600">{{ community.description }}</p>
                            </div>
                        </div>
                        <Link :href="`/community/${community.slug}`" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                            View Community Profile
                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>

                    <!-- Linked Events -->
                    <div v-if="availableTargets.events && availableTargets.events.length > 0" class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Related Events</h3>
                        <div class="space-y-3">
                            <div v-for="event in availableTargets.events" :key="event.id" class="border border-gray-200 rounded-lg p-3">
                                <h4 class="font-medium text-gray-900 text-sm">{{ event.title }}</h4>
                                <p v-if="event.description" class="text-xs text-gray-600 mt-1 line-clamp-2">{{ event.description }}</p>
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ formatDate(event.starts_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    community: Object,
    cfs: Object,
    availableTargets: Object,
    canApply: Boolean,
    applicationCount: Number,
});

const attachmentInput = ref(null);

const applicationForm = useForm({
    applicant_name: '',
    applicant_email: '',
    applicant_phone: '',
    target_type: '',
    community_event_id: null,
    event_session_id: null,
    bio: '',
    topic_title: '',
    topic_description: '',
    topic_outline: '',
    experience_level: '',
    previous_speaking_experience: '',
    custom_responses: {},
    attachments: [],
});

function isOpen(cfs) {
    if (!cfs.closes_at) return true;
    return new Date(cfs.closes_at) > new Date();
}

function isClosingSoon(cfs) {
    if (!cfs.closes_at || !isOpen(cfs)) return false;
    const now = new Date();
    const closesAt = new Date(cfs.closes_at);
    const diffDays = Math.ceil((closesAt - now) / (1000 * 60 * 60 * 24));
    return diffDays <= 7;
}

function getStatusClass(cfs) {
    if (isOpen(cfs)) {
        return isClosingSoon(cfs) ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
    }
    return 'bg-gray-100 text-gray-800';
}

function getStatusLabel(cfs) {
    if (isOpen(cfs)) {
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

function getProgressPercentage(cfs) {
    if (!cfs.opens_at || !cfs.closes_at) return 0;

    const now = new Date();
    const opensAt = new Date(cfs.opens_at);
    const closesAt = new Date(cfs.closes_at);

    if (now < opensAt) return 0;
    if (now > closesAt) return 100;

    const totalDuration = closesAt - opensAt;
    const elapsed = now - opensAt;

    return Math.max(0, Math.min(100, (elapsed / totalDuration) * 100));
}

function isFieldRequired(field) {
    return props.cfs.required_fields && props.cfs.required_fields.includes(field);
}

function handleAttachments(event) {
    const files = Array.from(event.target.files);
    if (files.length > 5) {
        alert('You can only upload up to 5 files.');
        return;
    }

    applicationForm.attachments = files;
}

function handleCheckboxChange(questionIndex, option, event) {
    if (!applicationForm.custom_responses[questionIndex]) {
        applicationForm.custom_responses[questionIndex] = [];
    }

    if (event.target.checked) {
        if (!applicationForm.custom_responses[questionIndex].includes(option)) {
            applicationForm.custom_responses[questionIndex].push(option);
        }
    } else {
        applicationForm.custom_responses[questionIndex] = applicationForm.custom_responses[questionIndex].filter(item => item !== option);
    }
}

function scrollToApplication() {
    const form = document.querySelector('form');
    if (form) {
        form.scrollIntoView({ behavior: 'smooth' });
    }
}

function submitApplication() {
    const formData = new FormData();

    // Add form fields
    Object.keys(applicationForm.data()).forEach(key => {
        if (key === 'attachments') {
            applicationForm.attachments.forEach((file, index) => {
                formData.append(`attachments[${index}]`, file);
            });
        } else if (key === 'custom_responses') {
            Object.keys(applicationForm.custom_responses).forEach(responseKey => {
                const value = applicationForm.custom_responses[responseKey];
                if (Array.isArray(value)) {
                    value.forEach((item, index) => {
                        formData.append(`custom_responses[${responseKey}][${index}]`, item);
                    });
                } else {
                    formData.append(`custom_responses[${responseKey}]`, value);
                }
            });
        } else if (applicationForm[key] !== null && applicationForm[key] !== '') {
            formData.append(key, applicationForm[key]);
        }
    });

    applicationForm.post(`/community/${props.community.slug}/cfs/${props.cfs.slug}/apply`, {
        data: formData,
        forceFormData: true,
        onSuccess: (response) => {
            if (response.props?.flash?.tracking_uid) {
                window.location.href = `/applications/track/${response.props.flash.tracking_uid}`;
            }
        }
    });
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
