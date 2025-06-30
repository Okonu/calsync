<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CalendarSetupCard from './Components/CalendarSetupCard.vue';
import CalendarAvailabilityCard from './Components/CalendarAvailabilityCard.vue';
import axios from 'axios';

const props = defineProps({
    community: Object,
});

const logoPreview = ref(null);
const logoInput = ref(null);
const showDeleteModal = ref(false);
const accounts = ref([]);
const calendars = ref([]);
const isLoadingCalendars = ref(false);

const form = useForm({
    name: props.community.name,
    description: props.community.description,
    website: props.community.website,
    contact_email: props.community.contact_email,
    calendar_email: props.community.calendar_email || '',
    destination_calendar_id: props.community.destination_calendar_id || '',
    availability_calendars: props.community.availability_calendars || [],
    timezone: props.community.timezone,
    color: props.community.color,
    logo: null,
    is_public: props.community.is_public,
    social_links: props.community.social_links || {
        twitter: '',
        linkedin: '',
        github: '',
        website: '',
    },
});

const deleteForm = useForm({});

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

const filteredCalendars = computed(() => {
    if (!form.calendar_email) return [];

    return calendars.value.filter(calendar =>
        calendar.googleAccount.email === form.calendar_email
    );
});

const availableEmails = computed(() => {
    const emails = accounts.value.map(account => account.email);
    return [...new Set(emails)];
});

onMounted(async () => {
    await loadAccountsAndCalendars();
});

async function loadAccountsAndCalendars() {
    try {
        isLoadingCalendars.value = true;
        const [accountsResponse, calendarsResponse] = await Promise.all([
            axios.get('/api/accounts'),
            axios.get('/api/calendars')
        ]);

        accounts.value = accountsResponse.data || [];
        calendars.value = calendarsResponse.data || [];

        // Set default availability calendars if not set
        if (!form.availability_calendars.length && calendars.value.length > 0) {
            form.availability_calendars = calendars.value.map(cal => cal.id);
        }
    } catch (error) {
        console.error('Error loading accounts and calendars:', error);
    } finally {
        isLoadingCalendars.value = false;
    }
}

function selectAllCalendars() {
    form.availability_calendars = calendars.value.map(cal => cal.id);
}

function clearAllCalendars() {
    form.availability_calendars = [];
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

function updateCommunity() {
    form.patch(route('communities.update', props.community.slug), {
        preserveScroll: true,
        onSuccess: () => {
            // Success
        }
    });
}

function deleteCommunity() {
    deleteForm.delete(route('communities.destroy', props.community.slug), {
        onSuccess: () => {
            // Redirect
        }
    });
}
</script>

<template>
    <AppLayout :title="`${community.name} Settings`">
        <Head :title="`${community.name} Settings`" />

        <template #header>
            <div class="flex items-center space-x-4">
                <a :href="`/communities/${community.slug}`" class="text-indigo-600 hover:text-indigo-900">
                    {{ community.name }}
                </a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800">Settings</span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="updateCommunity">
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
                                        <img v-else-if="community.logo_url" :src="community.logo_url" alt="Logo" class="w-full h-full rounded-full object-cover">
                                        <span v-else>{{ form.name ? form.name.charAt(0).toUpperCase() : 'C' }}</span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900">{{ form.name || 'Community Name' }}</h4>
                                        <p class="text-sm text-gray-600">{{ form.description || 'Community description will appear here' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">/community/{{ community.slug }}</p>
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

                                <!-- Calendar Setup Card -->
                                <div class="mb-6">
                                    <CalendarSetupCard
                                        :accounts="accounts"
                                        :calendars="calendars"
                                        :selected-calendar-email="form.calendar_email"
                                        :destination-calendar-id="form.destination_calendar_id"
                                        @update:selected-calendar-email="form.calendar_email = $event"
                                        @update:destination-calendar-id="form.destination_calendar_id = $event"
                                    />
                                </div>

                                <!-- Calendar Availability Card -->
                                <div>
                                    <CalendarAvailabilityCard
                                        :accounts="accounts"
                                        :calendars="calendars"
                                        :selected-calendars="form.availability_calendars"
                                        @update:selected-calendars="form.availability_calendars = $event"
                                        @select-all="selectAllCalendars"
                                        @clear-all="clearAllCalendars"
                                    />
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
                                            <img v-else-if="community.logo_url" :src="community.logo_url" alt="Logo" class="w-full h-full rounded-full object-cover">
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
                                                Change Logo
                                            </button>
                                            <button
                                                v-if="logoPreview || community.logo_url"
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
                                        >
                                    </div>
                                    <div v-if="form.errors.color" class="text-red-500 text-sm mt-1">{{ form.errors.color }}</div>
                                </div>
                            </div>

                            <!-- Social Links -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Social Links</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="twitter" class="block text-sm font-medium text-gray-700">Twitter/X</label>
                                        <input
                                            id="twitter"
                                            v-model="form.social_links.twitter"
                                            type="url"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                    </div>
                                    <div>
                                        <label for="linkedin" class="block text-sm font-medium text-gray-700">LinkedIn</label>
                                        <input
                                            id="linkedin"
                                            v-model="form.social_links.linkedin"
                                            type="url"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                    </div>
                                    <div>
                                        <label for="github" class="block text-sm font-medium text-gray-700">GitHub</label>
                                        <input
                                            id="github"
                                            v-model="form.social_links.github"
                                            type="url"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Privacy Settings -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Privacy Settings</h3>
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
                                            Public communities can be discovered and viewed by anyone.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Danger Zone -->
                            <div class="border-t border-red-200 pt-8">
                                <h3 class="text-lg font-medium text-red-900 mb-4">Danger Zone</h3>
                                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">Delete Community</h3>
                                            <div class="mt-2 text-sm text-red-700">
                                                <p>Once you delete this community, there is no going back. All events, calls for speakers, and applications will be permanently deleted.</p>
                                            </div>
                                            <div class="mt-4">
                                                <button
                                                    type="button"
                                                    @click="showDeleteModal = true"
                                                    class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                >
                                                    Delete Community
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                            <a
                                :href="`/communities/${community.slug}`"
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
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Delete Confirmation Modal -->
                <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div class="mt-3 text-center">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mt-2">Delete Community</h3>
                            <div class="mt-2 px-7 py-3">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete "{{ community.name }}"? This action cannot be undone and will permanently delete all associated data.
                                </p>
                            </div>
                            <div class="items-center px-4 py-3">
                                <button
                                    @click="deleteCommunity"
                                    :disabled="deleteForm.processing"
                                    class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:opacity-50"
                                >
                                    {{ deleteForm.processing ? 'Deleting...' : 'Delete Community' }}
                                </button>
                                <button
                                    @click="showDeleteModal = false"
                                    class="mt-3 px-4 py-2 bg-white text-gray-500 text-base font-medium rounded-md w-full shadow-sm border border-gray-300 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
