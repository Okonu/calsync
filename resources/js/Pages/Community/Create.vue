<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const logoPreview = ref(null);
const logoInput = ref(null);
const accounts = ref([]);
const calendars = ref([]);
const isLoadingCalendars = ref(false);
const showCalendarOptions = ref(false);
const showConnectAccountOption = ref(false);

const form = useForm({
    name: '',
    description: '',
    website: '',
    contact_email: '',
    calendar_email: '',
    destination_calendar_id: '',
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    color: '#4285F4',
    logo: null,
    is_public: true,
    social_links: {
        twitter: '',
        linkedin: '',
        github: '',
        website: '',
    },
});

const timezones = [
    'Africa/Nairobi',
    'America/New_York',
    'America/Los_Angeles',
    'Europe/London',
    'Europe/Paris',
    'Asia/Tokyo',
    'Asia/Shanghai',
    'Australia/Sydney',
    'UTC',
];

const colorOptions = [
    '#4285F4', '#EA4335', '#FBBC05', '#34A853',
    '#8E24AA', '#16A085', '#F39C12', '#E74C3C',
    '#3498DB', '#2ECC71', '#9B59B6', '#1ABC9C',
];

const previewUrl = computed(() => {
    if (form.name) {
        return `/community/${form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')}`;
    }
    return '/community/your-community-name';
});

const filteredCalendars = computed(() => {
    if (!form.calendar_email) return [];

    return calendars.value.filter(calendar =>
        calendar.googleAccount?.email === form.calendar_email
    );
});

const availableEmails = computed(() => {
    const emails = accounts.value
        .filter(account => account?.email) // Filter out accounts without email
        .map(account => account.email);
    return [...new Set(emails)];
});

onMounted(async () => {
    await loadAccounts();

    // Restore form data if coming back from Google auth
    const storedFormData = sessionStorage.getItem('pendingCommunityForm');
    if (storedFormData) {
        try {
            const parsedData = JSON.parse(storedFormData);
            Object.keys(parsedData).forEach(key => {
                if (key in form) {
                    form[key] = parsedData[key];
                }
            });
            sessionStorage.removeItem('pendingCommunityForm');
            showCalendarOptions.value = true;

            // Reload accounts in case new one was added
            loadAccounts();
        } catch (error) {
            console.error('Error restoring form data:', error);
        }
    }
});

async function loadAccounts() {
    try {
        isLoadingCalendars.value = true;
        const [accountsResponse, calendarsResponse] = await Promise.all([
            axios.get('/api/accounts'),
            axios.get('/api/calendars')
        ]);

        accounts.value = accountsResponse.data || [];
        calendars.value = calendarsResponse.data || [];

        // Set default calendar email to primary account
        const primaryAccount = accounts.value.find(account => account?.is_primary);
        if (primaryAccount?.email && !form.calendar_email) {
            form.calendar_email = primaryAccount.email;
            showCalendarOptions.value = true;
        }
    } catch (error) {
        console.error('Error loading accounts:', error);
        // Set fallback empty arrays to prevent further errors
        accounts.value = [];
        calendars.value = [];
    } finally {
        isLoadingCalendars.value = false;
    }
}

function handleCalendarEmailChange() {
    form.destination_calendar_id = '';
    showCalendarOptions.value = !!form.calendar_email;

    // Check if this email exists in current accounts
    const accountExists = accounts.value.some(account => account?.email === form.calendar_email);
    if (!accountExists && form.calendar_email) {
        // Show connect account option
        showConnectAccountOption.value = true;
    } else {
        showConnectAccountOption.value = false;
    }
}

function connectNewAccount() {
    // Store the current form data in session storage so we can restore it after Google auth
    sessionStorage.setItem('pendingCommunityForm', JSON.stringify({
        ...form.data(),
        calendar_email: form.calendar_email
    }));

    // Redirect to Google auth with a special parameter indicating this is for community calendar
    const params = new URLSearchParams({
        community_calendar: 'true',
        target_email: form.calendar_email || ''
    });

    window.location.href = `/connect/google?${params.toString()}`;
}

function handleLogoChange(event) {
    const file = event.target.files[0];
    if (file) {
        form.logo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function removeLogo() {
    form.logo = null;
    logoPreview.value = null;
    if (logoInput.value) {
        logoInput.value.value = '';
    }
}

function submit() {
    form.post(route('communities.store'), {
        onSuccess: () => {
            // Clear any stored form data
            sessionStorage.removeItem('pendingCommunityForm');
        }
    });
}
</script>

<template>
    <AppLayout title="Create Community">
        <Head title="Create Community" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create New Community
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <!-- Community Preview -->
                            <div class="mb-8 p-4 bg-gray-50 rounded-lg border">
                                <h3 class="text-sm font-medium text-gray-700 mb-3">Preview</h3>
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="w-16 h-16 rounded-full flex items-center justify-center text-white font-bold text-xl"
                                        :style="{ backgroundColor: form.color }"
                                    >
                                        <img v-if="logoPreview" :src="logoPreview" alt="Logo" class="w-full h-full rounded-full object-cover">
                                        <span v-else>{{ form.name ? form.name.charAt(0).toUpperCase() : 'C' }}</span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ form.name || 'Community Name' }}</h4>
                                        <p class="text-sm text-gray-600">{{ form.description || 'Community description will appear here' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ previewUrl }}</p>
                                        <p v-if="form.calendar_email" class="text-xs text-blue-600 mt-1">
                                            📅 Events calendar: {{ form.calendar_email }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Basic Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div class="md:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                </div>

                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">
                                        Community Name *
                                    </label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="e.g. AWS Pwani, Vue.js Kenya, etc."
                                        required
                                    >
                                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                                </div>

                                <div>
                                    <label for="contact_email" class="block text-sm font-medium text-gray-700">
                                        Contact Email
                                    </label>
                                    <input
                                        id="contact_email"
                                        v-model="form.contact_email"
                                        type="email"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="contact@awspwani.org"
                                    >
                                    <div v-if="form.errors.contact_email" class="text-red-500 text-sm mt-1">{{ form.errors.contact_email }}</div>
                                </div>

                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700">
                                        Description
                                    </label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Tell people what your community is about..."
                                    ></textarea>
                                    <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                                </div>

                                <div>
                                    <label for="website" class="block text-sm font-medium text-gray-700">
                                        Website
                                    </label>
                                    <input
                                        id="website"
                                        v-model="form.website"
                                        type="url"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="https://awspwani.org"
                                    >
                                    <div v-if="form.errors.website" class="text-red-500 text-sm mt-1">{{ form.errors.website }}</div>
                                </div>

                                <div>
                                    <label for="timezone" class="block text-sm font-medium text-gray-700">
                                        Timezone *
                                    </label>
                                    <select
                                        id="timezone"
                                        v-model="form.timezone"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option v-for="tz in timezones" :key="tz" :value="tz">
                                            {{ tz }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.timezone" class="text-red-500 text-sm mt-1">{{ form.errors.timezone }}</div>
                                </div>
                            </div>

                            <!-- Calendar Configuration -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Calendar Configuration</h3>
                                <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-blue-800">
                                                Calendar Integration
                                            </h3>
                                            <div class="mt-2 text-sm text-blue-700">
                                                <p>Choose which Google Calendar account will be used for community events. When you create events and sessions, they'll be automatically added to this calendar.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="calendar_email" class="block text-sm font-medium text-gray-700">
                                            Calendar Email *
                                        </label>
                                        <div class="mt-1 relative">
                                            <select
                                                id="calendar_email"
                                                v-model="form.calendar_email"
                                                @change="handleCalendarEmailChange"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                required
                                            >
                                                <option value="">Select calendar email...</option>
                                                <option v-for="email in availableEmails" :key="email" :value="email">
                                                    {{ email }}
                                                    {{ accounts.find(acc => acc?.email === email)?.is_primary ? '(Primary)' : '' }}
                                                </option>
                                            </select>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Community events will be created in this Google Calendar account
                                        </p>
                                        <div v-if="form.errors.calendar_email" class="text-red-500 text-sm mt-1">{{ form.errors.calendar_email }}</div>

                                        <!-- Connect New Account Button -->
                                        <div class="mt-2">
                                            <button
                                                type="button"
                                                @click="connectNewAccount"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                Connect Different Google Account
                                            </button>
                                        </div>
                                    </div>

                                    <div v-if="showCalendarOptions">
                                        <label for="destination_calendar_id" class="block text-sm font-medium text-gray-700">
                                            Specific Calendar (Optional)
                                        </label>
                                        <select
                                            id="destination_calendar_id"
                                            v-model="form.destination_calendar_id"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :disabled="isLoadingCalendars"
                                        >
                                            <option value="">Use primary calendar</option>
                                            <option v-for="calendar in filteredCalendars" :key="calendar.id" :value="calendar.id">
                                                {{ calendar.name }}
                                                {{ calendar.is_primary ? '(Primary)' : '' }}
                                            </option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Leave empty to use the primary calendar of the selected account
                                        </p>
                                        <div v-if="form.errors.destination_calendar_id" class="text-red-500 text-sm mt-1">{{ form.errors.destination_calendar_id }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Visual Customization -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div class="md:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Visual Customization</h3>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Community Logo
                                    </label>
                                    <div class="flex items-center space-x-4">
                                        <div
                                            class="w-20 h-20 rounded-full flex items-center justify-center text-white font-bold text-xl border-2 border-gray-200"
                                            :style="{ backgroundColor: form.color }"
                                        >
                                            <img v-if="logoPreview" :src="logoPreview" alt="Logo" class="w-full h-full rounded-full object-cover">
                                            <span v-else>{{ form.name ? form.name.charAt(0).toUpperCase() : 'C' }}</span>
                                        </div>
                                        <div>
                                            <input
                                                ref="logoInput"
                                                type="file"
                                                accept="image/*"
                                                @change="handleLogoChange"
                                                class="hidden"
                                            >
                                            <button
                                                type="button"
                                                @click="$refs.logoInput.click()"
                                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            >
                                                Upload Logo
                                            </button>
                                            <button
                                                v-if="logoPreview"
                                                type="button"
                                                @click="removeLogo"
                                                class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                            >
                                                Remove
                                            </button>
                                            <p class="text-xs text-gray-500 mt-1">JPG, PNG up to 2MB</p>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.logo" class="text-red-500 text-sm mt-1">{{ form.errors.logo }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Brand Color
                                    </label>
                                    <div class="grid grid-cols-6 gap-2 mb-3">
                                        <button
                                            v-for="color in colorOptions"
                                            :key="color"
                                            type="button"
                                            @click="form.color = color"
                                            class="w-8 h-8 rounded-full border-2 transition-all"
                                            :style="{ backgroundColor: color }"
                                            :class="form.color === color ? 'border-gray-800 scale-110' : 'border-gray-300'"
                                        ></button>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input
                                            v-model="form.color"
                                            type="color"
                                            class="w-8 h-8 rounded border border-gray-300"
                                        >
                                        <input
                                            v-model="form.color"
                                            type="text"
                                            class="flex-1 text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="#4285F4"
                                        >
                                    </div>
                                    <div v-if="form.errors.color" class="text-red-500 text-sm mt-1">{{ form.errors.color }}</div>
                                </div>
                            </div>

                            <!-- Social Links -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Social Links (Optional)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="twitter" class="block text-sm font-medium text-gray-700">
                                            Twitter/X
                                        </label>
                                        <input
                                            id="twitter"
                                            v-model="form.social_links.twitter"
                                            type="url"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="https://twitter.com/awspwani"
                                        >
                                    </div>
                                    <div>
                                        <label for="linkedin" class="block text-sm font-medium text-gray-700">
                                            LinkedIn
                                        </label>
                                        <input
                                            id="linkedin"
                                            v-model="form.social_links.linkedin"
                                            type="url"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="https://linkedin.com/company/awspwani"
                                        >
                                    </div>
                                    <div>
                                        <label for="github" class="block text-sm font-medium text-gray-700">
                                            GitHub
                                        </label>
                                        <input
                                            id="github"
                                            v-model="form.social_links.github"
                                            type="url"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="https://github.com/awspwani"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Privacy Settings -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Privacy Settings</h3>
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
                                                Make community public
                                            </label>
                                            <p class="text-gray-500">
                                                Public communities can be discovered and viewed by anyone. You can change this later.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                            <a
                                href="/communities"
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
                                {{ form.processing ? 'Creating...' : 'Create Community' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
