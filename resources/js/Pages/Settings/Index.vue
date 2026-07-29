<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';

const accounts = ref([]);
const calendars = ref([]);
const isLoading = ref(true);
const showDeleteModal = ref(false);
const accountToDelete = ref(null);
const syncingAccounts = ref([]);
const notification = ref(null);

const showDeleteAccountModal = ref(false);
const deletionPreview = ref(null);
const isLoadingPreview = ref(false);
const cancelCalendarEvents = ref(true);
const deleteConfirmationText = ref('');
const isDeletingAccount = ref(false);
const deleteAccountError = ref('');

const apiTokens = ref([]);
const isLoadingTokens = ref(true);
const showCreateTokenModal = ref(false);
const newTokenName = ref('');
const isCreatingToken = ref(false);
const createTokenError = ref('');
const newlyCreatedToken = ref(null);

onMounted(async () => {
    await loadData();
    await loadApiTokens();
});

async function loadApiTokens() {
    try {
        isLoadingTokens.value = true;
        const response = await axios.get('/api/developer/tokens');
        apiTokens.value = response.data.tokens || [];
    } catch (error) {
        console.error('Error loading API keys:', error);
    } finally {
        isLoadingTokens.value = false;
    }
}

function openCreateTokenModal() {
    newTokenName.value = '';
    createTokenError.value = '';
    newlyCreatedToken.value = null;
    showCreateTokenModal.value = true;
}

async function submitCreateToken() {
    if (!newTokenName.value.trim()) {
        createTokenError.value = 'Give this key a name so you remember what it\'s for.';
        return;
    }

    isCreatingToken.value = true;
    createTokenError.value = '';

    try {
        const response = await axios.post('/api/developer/tokens', {
            name: newTokenName.value.trim(),
        });

        newlyCreatedToken.value = response.data.plain_text_token;
        apiTokens.value.unshift(response.data.token);
    } catch (error) {
        console.error('Error creating API key:', error);
        createTokenError.value = 'Failed to create the key. Please try again.';
    } finally {
        isCreatingToken.value = false;
    }
}

async function revokeToken(token) {
    if (!confirm(`Revoke the API key "${token.name}"? Anything using it will stop working immediately.`)) {
        return;
    }

    try {
        await axios.delete(`/api/developer/tokens/${token.id}`);
        apiTokens.value = apiTokens.value.filter(t => t.id !== token.id);
        showNotification('API key revoked', 'success');
    } catch (error) {
        console.error('Error revoking API key:', error);
        showNotification('Failed to revoke API key', 'error');
    }
}

async function loadData() {
    try {
        isLoading.value = true;

        const accountsResponse = await axios.get('/api/accounts');
        accounts.value = accountsResponse.data || [];

        const calendarsResponse = await axios.get('/api/calendars');
        calendars.value = calendarsResponse.data || [];

        accounts.value = accounts.value.map(account => {
            const accountCalendars = calendars.value.filter(cal => 
                (account.provider === 'google' && cal.google_account_id === account.id) ||
                (account.provider === 'microsoft' && cal.microsoft_account_id === account.id)
            );
            return {
                ...account,
                calendars: accountCalendars
            };
        });
    } catch (error) {
        console.error('Error loading account data:', error);
        showNotification('Error loading account data', 'error');
    } finally {
        isLoading.value = false;
    }
}

async function updateAccountColor(account, newColor) {
    try {
        await axios.patch(`/api/accounts/${account.id}/color`, {
            color: newColor
        });

        account.color = newColor;
        showNotification('Account color updated successfully', 'success');
    } catch (error) {
        console.error('Error updating account color:', error);
        showNotification('Failed to update account color', 'error');
    }
}

async function toggleAccountStatus(account) {
    try {
        const newStatus = !account.is_active;

        await axios.patch(`/api/accounts/${account.id}/status`, {
            is_active: newStatus
        });

        account.is_active = newStatus;
        showNotification(`Account ${newStatus ? 'activated' : 'deactivated'} successfully`, 'success');
    } catch (error) {
        console.error('Error toggling account status:', error);
        showNotification('Failed to update account status', 'error');
    }
}

async function syncAccount(account) {
    try {
        syncingAccounts.value.push(account.id);

        await axios.post(`/api/accounts/${account.id}/sync`);

        showNotification('Calendar sync initiated successfully', 'success');
    } catch (error) {
        console.error('Error syncing account:', error);
        showNotification('Failed to sync account calendars', 'error');
    } finally {
        syncingAccounts.value = syncingAccounts.value.filter(id => id !== account.id);
    }
}

function confirmDelete(account) {
    accountToDelete.value = account;
    showDeleteModal.value = true;
}

async function deleteAccount() {
    if (!accountToDelete.value) return;

    try {
        await axios.delete(`/api/accounts/${accountToDelete.value.id}`);

        accounts.value = accounts.value.filter(
            account => account.id !== accountToDelete.value.id
        );

        showNotification('Account deleted successfully', 'success');
    } catch (error) {
        console.error('Error deleting account:', error);
        showNotification('Failed to delete account', 'error');
    } finally {
        showDeleteModal.value = false;
        accountToDelete.value = null;
    }
}

function showNotification(message, type = 'info') {
    notification.value = {
        message,
        type
    };

    setTimeout(() => {
        notification.value = null;
    }, 5000);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

async function openDeleteAccountModal() {
    showDeleteAccountModal.value = true;
    deleteAccountError.value = '';
    deleteConfirmationText.value = '';
    cancelCalendarEvents.value = true;
    isLoadingPreview.value = true;

    try {
        const response = await axios.get('/api/account/deletion-preview');
        deletionPreview.value = response.data;
    } catch (error) {
        console.error('Error loading deletion preview:', error);
        deletionPreview.value = null;
    } finally {
        isLoadingPreview.value = false;
    }
}

function closeDeleteAccountModal() {
    if (isDeletingAccount.value) return;
    showDeleteAccountModal.value = false;
}

async function submitDeleteAccount() {
    if (deleteConfirmationText.value.trim().toUpperCase() !== 'DELETE') {
        deleteAccountError.value = 'Type DELETE to confirm.';
        return;
    }

    isDeletingAccount.value = true;
    deleteAccountError.value = '';

    try {
        await axios.delete('/api/account', {
            data: {
                confirmation: deleteConfirmationText.value,
                cancel_calendar_events: cancelCalendarEvents.value,
            },
        });

        window.location.href = '/';
    } catch (error) {
        console.error('Error deleting account:', error);
        deleteAccountError.value = error.response?.data?.message || 'Failed to delete your account. Please try again.';
        isDeletingAccount.value = false;
    }
}
</script>

<template>
    <Head title="Account Settings" />

    <AppLayout title="Account Settings">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Account Settings
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Notification -->
                <div v-if="notification"
                     class="mb-6 p-4 rounded-md transition-all duration-300"
                     :class="{
                         'bg-green-50 text-green-800 border border-green-200': notification.type === 'success',
                         'bg-red-50 text-red-800 border border-red-200': notification.type === 'error',
                         'bg-blue-50 text-blue-800 border border-blue-200': notification.type === 'info'
                     }">
                    {{ notification.message }}
                </div>

                <!-- Actions Header -->
                <div class="flex justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Manage Connected Accounts</h3>
                    <div class="flex space-x-4">
                        <a href="/connect/google"
                           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z"/>
                            </svg>
                            Google
                        </a>
                        <a href="/connect/microsoft"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="h-4 w-4 mr-2" viewBox="0 0 23 23" fill="currentColor">
                                <path d="M0 0h11v11H0zM12 0h11v11H12zM0 12h11v11H0zM12 12h11v11H12z"/>
                            </svg>
                            Microsoft
                        </a>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="isLoading" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 flex justify-center items-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
                        <p class="ml-4 text-gray-600">Loading account data...</p>
                    </div>
                </div>

                <!-- No Accounts State -->
                <div v-else-if="accounts.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-600">
                        <p class="mb-4">You don't have any accounts connected yet.</p>
                        <div class="flex space-x-4">
                            <a href="/connect/google" class="text-indigo-600 hover:text-indigo-800">
                                Connect Google
                            </a>
                            <a href="/connect/microsoft" class="text-blue-600 hover:text-blue-800">
                                Connect Microsoft
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Accounts Table -->
                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Account
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Color
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Calendars
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="account in accounts" :key="account.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center"
                                             :style="{ backgroundColor: account.color }">
                                            <span class="text-white font-bold">{{ account.name.charAt(0) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ account.name }}
                                                <span v-if="account.is_primary" class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Primary
                                                    </span>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ account.email }}
                                                <span class="ml-2 text-xs text-gray-400">({{ account.provider }})</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="color" v-model="account.color" @change="updateAccountColor(account, account.color)"
                                           class="w-8 h-8 rounded border-0 shadow-sm" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button @click="toggleAccountStatus(account)"
                                            :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ account.is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ account.calendars ? account.calendars.length : 0 }} calendars
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(account.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="syncAccount(account)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4 inline-flex items-center"
                                            :disabled="syncingAccounts.includes(account.id)">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span v-if="syncingAccounts.includes(account.id)">Syncing...</span>
                                        <span v-else>Sync</span>
                                    </button>
                                    <button @click="confirmDelete(account)"
                                            class="text-red-600 hover:text-red-900 inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Calendar Management Section -->
                <div v-if="accounts.length > 0" class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Calendar Details</h3>

                        <div v-for="account in accounts" :key="`cal-${account.id}`" class="mb-6">
                            <h4 class="text-md font-medium text-gray-800 mb-2 flex items-center">
                                <div class="w-4 h-4 rounded-full mr-2" :style="{ backgroundColor: account.color }"></div>
                                {{ account.name }} ({{ account.email }})
                            </h4>

                            <div v-if="!account.calendars || account.calendars.length === 0"
                                 class="ml-6 text-sm text-gray-500">
                                No calendars found for this account.
                            </div>

                            <div v-else class="ml-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="calendar in account.calendars" :key="calendar.id"
                                     class="border rounded-md p-3 flex items-start">
                                    <div class="w-3 h-3 rounded-full mt-1 mr-2" :style="{ backgroundColor: calendar.color }"></div>
                                    <div>
                                        <div class="text-sm font-medium">{{ calendar.name }}</div>
                                        <div class="flex mt-1">
                                            <span class="text-xs px-2 rounded-full mr-2"
                                                  :class="calendar.is_visible ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                                {{ calendar.is_visible ? 'Visible' : 'Hidden' }}
                                            </span>
                                            <span v-if="calendar.is_primary"
                                                  class="text-xs px-2 rounded-full bg-blue-100 text-blue-800">
                                                Primary
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Developer API -->
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Developer API</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Generate an API key to check availability and create bookings on your booking
                                    page from another app or site. See the
                                    <a href="/docs" target="_blank" class="text-indigo-600 hover:text-indigo-800">docs</a>
                                    for endpoints and examples.
                                </p>
                            </div>
                            <button @click="openCreateTokenModal" type="button"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150 whitespace-nowrap">
                                Generate new key
                            </button>
                        </div>

                        <div v-if="isLoadingTokens" class="mt-4 text-sm text-gray-500">Loading keys&hellip;</div>

                        <div v-else-if="apiTokens.length === 0" class="mt-4 text-sm text-gray-500">
                            No API keys yet.
                        </div>

                        <table v-else class="mt-4 min-w-full divide-y divide-gray-200">
                            <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last used</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                            <tr v-for="token in apiTokens" :key="token.id">
                                <td class="px-3 py-2 text-sm text-gray-900">{{ token.name }}</td>
                                <td class="px-3 py-2 text-sm text-gray-500">{{ formatDate(token.created_at) }}</td>
                                <td class="px-3 py-2 text-sm text-gray-500">{{ token.last_used_at ? formatDate(token.last_used_at) : 'Never' }}</td>
                                <td class="px-3 py-2 text-right">
                                    <button @click="revokeToken(token)" class="text-red-600 hover:text-red-900 text-sm">Revoke</button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-red-700">Danger Zone</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Permanently delete your Synqs account, connected calendar accounts, booking pages, and
                            communities. This cannot be undone.
                        </p>
                        <button @click="openDeleteAccountModal" type="button"
                                class="mt-4 inline-flex items-center px-4 py-2 border border-red-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition ease-in-out duration-150">
                            Delete my account
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Connected Account Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Delete Account
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to disconnect and delete this account?
                                    All associated calendars and events will be removed from the application.
                                    This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="deleteAccount" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button @click="showDeleteModal = false" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Synqs Account Modal -->
        <div v-if="showDeleteAccountModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 w-full text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Delete your Synqs account
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    This permanently deletes your profile, connected calendar accounts, booking
                                    pages, and any communities you own. This cannot be undone.
                                </p>
                            </div>

                            <div v-if="isLoadingPreview" class="mt-4 text-sm text-gray-500">
                                Checking your account&hellip;
                            </div>

                            <ul v-else-if="deletionPreview" class="mt-4 text-sm text-gray-600 list-disc list-inside space-y-1">
                                <li v-if="deletionPreview.google_accounts">{{ deletionPreview.google_accounts }} connected Google account(s)</li>
                                <li v-if="deletionPreview.microsoft_accounts">{{ deletionPreview.microsoft_accounts }} connected Microsoft account(s)</li>
                                <li v-if="deletionPreview.communities">{{ deletionPreview.communities }} community(ies) you own</li>
                                <li v-if="deletionPreview.upcoming_bookings_with_events">
                                    {{ deletionPreview.upcoming_bookings_with_events }} upcoming booking(s) with a calendar event
                                </li>
                            </ul>

                            <div v-if="deletionPreview && deletionPreview.upcoming_bookings_with_events > 0" class="mt-4 bg-amber-50 border border-amber-200 rounded-md p-3">
                                <label class="flex items-start space-x-2">
                                    <input type="checkbox" v-model="cancelCalendarEvents"
                                           class="mt-0.5 rounded border-gray-300 text-red-600 focus:ring-red-500" />
                                    <span class="text-sm text-amber-900">
                                        Also cancel the calendar events created by your upcoming bookings, and notify
                                        the guests. If left unchecked, those events stay on your connected calendars.
                                    </span>
                                </label>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">
                                    Type <span class="font-mono font-semibold">DELETE</span> to confirm
                                </label>
                                <input v-model="deleteConfirmationText" type="text"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                       :disabled="isDeletingAccount" />
                                <p v-if="deleteAccountError" class="mt-2 text-sm text-red-600">{{ deleteAccountError }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="submitDeleteAccount" type="button" :disabled="isDeletingAccount"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        {{ isDeletingAccount ? 'Deleting…' : 'Permanently delete my account' }}
                    </button>
                    <button @click="closeDeleteAccountModal" type="button" :disabled="isDeletingAccount"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Create API Key Modal -->
        <div v-if="showCreateTokenModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ newlyCreatedToken ? 'API key created' : 'Generate a new API key' }}
                    </h3>

                    <template v-if="!newlyCreatedToken">
                        <p class="mt-2 text-sm text-gray-500">
                            Give it a name so you know where it's used, e.g. "Mundly site".
                        </p>
                        <input v-model="newTokenName" type="text" placeholder="Key name"
                               class="mt-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               :disabled="isCreatingToken" @keyup.enter="submitCreateToken" />
                        <p v-if="createTokenError" class="mt-2 text-sm text-red-600">{{ createTokenError }}</p>
                    </template>

                    <template v-else>
                        <p class="mt-2 text-sm text-gray-500">
                            Copy this key now — for your security, you won't be able to see it again.
                        </p>
                        <code class="mt-3 block w-full break-all rounded-md bg-gray-900 text-green-400 text-xs p-3">{{ newlyCreatedToken }}</code>
                        <p class="mt-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-md p-2">
                            Keep this secret and use it only from a server you control. Never embed it in
                            client-side JavaScript on a public page.
                        </p>
                    </template>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex flex-row-reverse gap-3">
                    <button v-if="!newlyCreatedToken" @click="submitCreateToken" type="button" :disabled="isCreatingToken"
                            class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm disabled:opacity-50">
                        {{ isCreatingToken ? 'Creating…' : 'Create key' }}
                    </button>
                    <button @click="showCreateTokenModal = false" type="button"
                            class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                        {{ newlyCreatedToken ? 'Done' : 'Cancel' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
