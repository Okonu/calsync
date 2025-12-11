<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    community: Object,
    applicationTypes: Object,
});

const form = useForm({
    title: '',
    description: '',
    guidelines: '',
    opens_at: '',
    closes_at: '',
    is_public: true,
    requires_login: false,
    show_application_count: false,
    allow_multiple_applications: false,
    application_type: 'session',
    required_fields: ['bio', 'topic_title', 'topic_description', 'experience_level'],
    custom_questions: [],
    auto_approve: false,
    acceptance_email_template: '',
    rejection_email_template: '',
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
    form.custom_questions.splice(index, 1);
}

function addOption(questionIndex) {
    form.custom_questions[questionIndex].options.push('');
}

function removeOption(questionIndex, optionIndex) {
    form.custom_questions[questionIndex].options.splice(optionIndex, 1);
}

function submit() {
    // Clean up custom questions
    form.custom_questions = form.custom_questions.map(q => ({
        ...q,
        options: q.options.filter(opt => opt.trim() !== '')
    })).filter(q => q.question.trim() !== '');

    form.post(route('communities.cfs.store', props.community.slug), {
        onSuccess: () => {
            // Redirect handled by controller
        }
    });
}
</script>

<template>
    <AppLayout title="Create Call for Speakers">
        <Head :title="`Create Call for Speakers - ${community.name}`" />

        <template #header>
            <div class="flex items-center space-x-4">
                <a :href="`/communities/${community.slug}`" class="text-indigo-600 hover:text-indigo-900">
                    {{ community.name }}
                </a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800">Create Call for Speakers</span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
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
                                            placeholder="AWS Pwani Speakers Call - March 2024"
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
                                            placeholder="We're looking for speakers to share their AWS knowledge and experience..."
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
                                            placeholder="Guidelines for speakers, presentation format, duration, etc..."
                                        ></textarea>
                                        <p class="text-xs text-gray-500 mt-1">Provide guidance on presentation format, duration, technical requirements, etc.</p>
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
                                        >
                                            <option v-for="(label, value) in applicationTypes" :key="value" :value="value">
                                                {{ label }}
                                            </option>
                                        </select>
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
                                                    >
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="auto_approve" class="font-medium text-gray-700">
                                                        Auto-approve applications
                                                    </label>
                                                    <p class="text-gray-500">Applications approved automatically</p>
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
                                                    >
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="allow_multiple_applications" class="font-medium text-gray-700">
                                                        Allow multiple applications
                                                    </label>
                                                    <p class="text-gray-500">Same person can apply multiple times</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Required Fields -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Required Application Fields</h3>

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
                                    <h3 class="text-lg font-medium text-gray-900">Custom Questions</h3>
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
                                                    placeholder="What interests you most about AWS?"
                                                    required
                                                >
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Question Type</label>
                                                <select
                                                    v-model="question.type"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                            <a
                                :href="`/communities/${community.slug}/cfs`"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Creating...' : 'Create Call for Speakers' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
