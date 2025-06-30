<template>
    <AppLayout :title="`Edit ${cfs.title} - ${community.name}`">
        <Head :title="`Edit ${cfs.title} - ${community.name}`" />

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
                    <Link :href="`/communities/${community.slug}/cfs/${cfs.slug}`" class="text-indigo-600 hover:text-indigo-900">
                        {{ cfs.title }}
                    </Link>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-800">Edit</span>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <!-- Status Warning -->
                            <div v-if="cfs.status === 'open' && hasApplications" class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">
                                            Caution: Live Call for Speakers
                                        </h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>This call for speakers is currently live and has received applications. Some changes may affect existing applicants.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Basic Information -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Call for Speakers Details</h3>

                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700">
                                            Title *
                                        </label>
                                        <input
                                            id="title"
                                            v-model="form.title"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        >
                                        <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                                    </div>

                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-700">
                                            Description
                                        </label>
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            rows="4"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        ></textarea>
                                        <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                                    </div>

                                    <div>
                                        <label for="guidelines" class="block text-sm font-medium text-gray-700">
                                            Speaker Guidelines
                                        </label>
                                        <textarea
                                            id="guidelines"
                                            v-model="form.guidelines"
                                            rows="4"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        ></textarea>
                                        <p class="text-xs text-gray-500 mt-1">Provide guidance on presentation format, duration, technical requirements, etc.</p>
                                        <div v-if="form.errors.guidelines" class="text-red-500 text-sm mt-1">{{ form.errors.guidelines }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Application Timeline -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Application Timeline</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="opens_at" class="block text-sm font-medium text-gray-700">
                                            Opens At
                                        </label>
                                        <input
                                            id="opens_at"
                                            v-model="form.opens_at"
                                            type="datetime-local"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                        <p class="text-xs text-gray-500 mt-1">Leave empty to open immediately</p>
                                        <div v-if="form.errors.opens_at" class="text-red-500 text-sm mt-1">{{ form.errors.opens_at }}</div>
                                    </div>

                                    <div>
                                        <label for="closes_at" class="block text-sm font-medium text-gray-700">
                                            Closes At
                                        </label>
                                        <input
                                            id="closes_at"
                                            v-model="form.closes_at"
                                            type="datetime-local"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                        <p class="text-xs text-gray-500 mt-1">Leave empty for no deadline</p>
                                        <div v-if="form.errors.closes_at" class="text-red-500 text-sm mt-1">{{ form.errors.closes_at }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Application Settings -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Application Settings</h3>

                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label for="application_type" class="block text-sm font-medium text-gray-700">
                                            Application Type *
                                        </label>
                                        <select
                                            id="application_type"
                                            v-model="form.application_type"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                            :disabled="hasApplications"
                                        >
                                            <option v-for="(label, value) in applicationTypes" :key="value" :value="value">
                                                {{ label }}
                                            </option>
                                        </select>
                                        <p v-if="hasApplications" class="text-xs text-yellow-600 mt-1">
                                            Cannot change application type after receiving applications
                                        </p>
                                        <div v-if="form.errors.application_type" class="text-red-500 text-sm mt-1">{{ form.errors.application_type }}</div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-4">
                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input
                                                        id="is_public"
                                                        v-model="form.is_public"
                                                        type="checkbox"
                                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                                    >
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="is_public" class="font-medium text-gray-700">
                                                        Public call for speakers
                                                    </label>
                                                    <p class="text-gray-500">Anyone can view and apply</p>
                                                </div>
                                            </div>

                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input
                                                        id="requires_login"
                                                        v-model="form.requires_login"
                                                        type="checkbox"
                                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                                    >
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="requires_login" class="font-medium text-gray-700">
                                                        Require login to apply
                                                    </label>
                                                    <p class="text-gray-500">Users must be logged in</p>
                                                </div>
                                            </div>

                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input
                                                        id="auto_approve"
                                                        v-model="form.auto_approve"
                                                        type="checkbox"
                                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                                        :disabled="hasApplications"
                                                    >
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="auto_approve" class="font-medium text-gray-700">
                                                        Auto-approve applications
                                                    </label>
                                                    <p class="text-gray-500">Applications approved automatically</p>
                                                    <p v-if="hasApplications" class="text-xs text-yellow-600">
                                                        Cannot change after receiving applications
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-4">
                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input
                                                        id="show_application_count"
                                                        v-model="form.show_application_count"
                                                        type="checkbox"
                                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                                    >
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="show_application_count" class="font-medium text-gray-700">
                                                        Show application count
                                                    </label>
                                                    <p class="text-gray-500">Display number of applications</p>
                                                </div>
                                            </div>

                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input
                                                        id="allow_multiple_applications"
                                                        v-model="form.allow_multiple_applications"
                                                        type="checkbox"
                                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                                        :disabled="hasApplications"
                                                    >
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="allow_multiple_applications" class="font-medium text-gray-700">
                                                        Allow multiple applications
                                                    </label>
                                                    <p class="text-gray-500">Same person can apply multiple times</p>
                                                    <p v-if="hasApplications" class="text-xs text-yellow-600">
                                                        Cannot change after receiving applications
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Required Fields -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Required Application Fields</h3>
                                <p v-if="hasApplications" class="text-sm text-yellow-600 mb-4">
                                    Changing required fields may affect existing applications. Consider carefully before making changes.
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div
                                        v-for="field in availableFields"
                                        :key="field.value"
                                        class="flex items-start"
                                    >
                                        <div class="flex items-center h-5">
                                            <input
                                                :id="`field-${field.value}`"
                                                type="checkbox"
                                                :checked="form.required_fields.includes(field.value)"
                                                @change="toggleRequiredField(field.value)"
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                            >
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label :for="`field-${field.value}`" class="font-medium text-gray-700">
                                                {{ field.label }}
                                            </label>
                                            <p class="text-gray-500">{{ field.description }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom Questions -->
                            <div class="mb-8">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">Custom Questions</h3>
                                        <p v-if="hasApplications" class="text-sm text-yellow-600 mt-1">
                                            Adding or modifying questions may affect existing applications
                                        </p>
                                    </div>
                                    <button
                                        v-if="canAddQuestions"
                                        type="button"
                                        @click="addCustomQuestion"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Add Question
                                    </button>
                                </div>

                                <div v-if="form.custom_questions.length === 0" class="text-center py-6 border-2 border-dashed border-gray-300 rounded-lg">
                                    <p class="text-gray-500">No custom questions added yet.</p>
                                    <button
                                        type="button"
                                        @click="addCustomQuestion"
                                        class="mt-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
                                    >
                                        Add First Question
                                    </button>
                                </div>

                                <div v-else class="space-y-6">
                                    <div
                                        v-for="(question, index) in form.custom_questions"
                                        :key="index"
                                        class="p-4 border border-gray-200 rounded-lg"
                                    >
                                        <div class="flex items-center justify-between mb-4">
                                            <h4 class="text-md font-medium text-gray-900">Question {{ index + 1 }}</h4>
                                            <button
                                                type="button"
                                                @click="removeCustomQuestion(index)"
                                                class="text-red-600 hover:text-red-900"
                                                :disabled="hasApplications"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="md:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">Question Text *</label>
                                                <input
                                                    v-model="question.question"
                                                    type="text"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    required
                                                >
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Question Type</label>
                                                <select
                                                    v-model="question.type"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    :disabled="hasApplications"
                                                >
                                                    <option v-for="type in questionTypes" :key="type.value" :value="type.value">
                                                        {{ type.label }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="flex items-center">
                                                <input
                                                    v-model="question.required"
                                                    type="checkbox"
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                >
                                                <label class="ml-2 block text-sm text-gray-900">
                                                    Required question
                                                </label>
                                            </div>

                                            <!-- Options for select/radio/checkbox -->
                                            <div v-if="questionNeedsOptions(question.type)" class="md:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Options</label>
                                                <div class="space-y-2">
                                                    <div
                                                        v-for="(option, optionIndex) in question.options"
                                                        :key="optionIndex"
                                                        class="flex items-center space-x-2"
                                                    >
                                                        <input
                                                            v-model="question.options[optionIndex]"
                                                            type="text"
                                                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            placeholder="Option text"
                                                        >
                                                        <button
                                                            v-if="question.options.length > 1"
                                                            type="button"
                                                            @click="removeOption(index, optionIndex)"
                                                            class="text-red-600 hover:text-red-900"
                                                            :disabled="hasApplications"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        @click="addOption(index)"
                                                        class="text-indigo-600 hover:text-indigo-900 text-sm"
                                                        :disabled="hasApplications"
                                                    >
                                                        + Add Option
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email Templates -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Email Templates (Optional)</h3>

                                <div class="space-y-6">
                                    <div>
                                        <label for="acceptance_email_template" class="block text-sm font-medium text-gray-700">
                                            Acceptance Email Template
                                        </label>
                                        <textarea
                                            id="acceptance_email_template"
                                            v-model="form.acceptance_email_template"
                                            rows="4"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Congratulations! Your speaking proposal has been accepted..."
                                        ></textarea>
                                        <p class="text-xs text-gray-500 mt-1">Custom message for accepted applications. Leave empty to use default template.</p>
                                        <div v-if="form.errors.acceptance_email_template" class="text-red-500 text-sm mt-1">{{ form.errors.acceptance_email_template }}</div>
                                    </div>

                                    <div>
                                        <label for="rejection_email_template" class="block text-sm font-medium text-gray-700">
                                            Rejection Email Template
                                        </label>
                                        <textarea
                                            id="rejection_email_template"
                                            v-model="form.rejection_email_template"
                                            rows="4"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Thank you for your application. Unfortunately..."
                                        ></textarea>
                                        <p class="text-xs text-gray-500 mt-1">Custom message for rejected applications. Leave empty to use default template.</p>
                                        <div v-if="form.errors.rejection_email_template" class="text-red-500 text-sm mt-1">{{ form.errors.rejection_email_template }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                            <Link
                                :href="`/communities/${community.slug}/cfs/${cfs.slug}`"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </div>
                </form>
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
    applicationTypes: Object,
});

const form = useForm({
    title: props.cfs.title,
    description: props.cfs.description,
    guidelines: props.cfs.guidelines,
    opens_at: props.cfs.opens_at ? new Date(props.cfs.opens_at).toISOString().slice(0, 16) : '',
    closes_at: props.cfs.closes_at ? new Date(props.cfs.closes_at).toISOString().slice(0, 16) : '',
    is_public: props.cfs.is_public,
    requires_login: props.cfs.requires_login,
    show_application_count: props.cfs.show_application_count,
    allow_multiple_applications: props.cfs.allow_multiple_applications,
    application_type: props.cfs.application_type,
    required_fields: props.cfs.required_fields || [],
    custom_questions: props.cfs.custom_questions || [],
    auto_approve: props.cfs.auto_approve,
    acceptance_email_template: props.cfs.acceptance_email_template,
    rejection_email_template: props.cfs.rejection_email_template,
});

const availableFields = [
    { value: 'bio', label: 'Biography', description: 'Speaker biography' },
    { value: 'topic_title', label: 'Topic Title', description: 'Title of the presentation' },
    { value: 'topic_description', label: 'Topic Description', description: 'Detailed description of the topic' },
    { value: 'topic_outline', label: 'Topic Outline', description: 'Outline or agenda of the presentation' },
    { value: 'experience_level', label: 'Experience Level', description: 'Beginner, Intermediate, Advanced, Expert' },
    { value: 'previous_speaking_experience', label: 'Speaking Experience', description: 'Previous speaking experience' },
    { value: 'phone', label: 'Phone Number', description: 'Contact phone number' },
];

const questionTypes = [
    { value: 'text', label: 'Short Text', description: 'Single line text input' },
    { value: 'textarea', label: 'Long Text', description: 'Multi-line text area' },
    { value: 'select', label: 'Dropdown', description: 'Select one from options' },
    { value: 'radio', label: 'Radio Buttons', description: 'Select one from options' },
    { value: 'checkbox', label: 'Checkboxes', description: 'Select multiple options' },
];

const hasApplications = computed(() => {
    return props.cfs.total_applications > 0;
});

const canAddQuestions = computed(() => {
    return form.custom_questions.length < 10;
});

const questionNeedsOptions = (type) => {
    return ['select', 'radio', 'checkbox'].includes(type);
};

function toggleRequiredField(field) {
    const index = form.required_fields.indexOf(field);
    if (index > -1) {
        form.required_fields.splice(index, 1);
    } else {
        form.required_fields.push(field);
    }
}

function addCustomQuestion() {
    form.custom_questions.push({
        question: '',
        type: 'text',
        required: false,
        options: [''],
    });
}

function removeCustomQuestion(index) {
    if (hasApplications.value) {
        if (!confirm('Removing this question may affect existing applications. Are you sure?')) {
            return;
        }
    }
    form.custom_questions.splice(index, 1);
}

function addOption(questionIndex) {
    form.custom_questions[questionIndex].options.push('');
}

function removeOption(questionIndex, optionIndex) {
    if (hasApplications.value) {
        if (!confirm('Removing this option may affect existing applications. Are you sure?')) {
            return;
        }
    }
    form.custom_questions[questionIndex].options.splice(optionIndex, 1);
}

function submit() {
    // Clean up custom questions
    form.custom_questions = form.custom_questions.map(q => ({
        ...q,
        options: q.options.filter(opt => opt.trim() !== '')
    })).filter(q => q.question.trim() !== '');

    form.patch(route('communities.cfs.update', [community.slug, props.cfs.id]), {
        onSuccess: () => {
            // Success handled by backend
        }
    });
}
</script>
