<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';

const googleAccounts = ref([]);
const calendars = ref([]);
const isLoading = ref(true);
const showDeleteModal = ref(false);
const accountToDelete = ref(null);
const syncingAccounts = ref([]);
const notification = ref(null);

onMounted(async () => {
    await loadData();
});

async function loadData() {
    try {
        isLoading.value = true;

        // Fetch accounts and calendars
        const accountsResponse = await axios.get('/api/accounts');
        googleAccounts.value = accountsResponse.data || [];

        const calendarsResponse = await axios.get('/api/calendars');
        calendars.value = calendarsResponse.data || [];

        // Group calendars by account
        googleAccounts.value = googleAccounts.value.map(account => {
            const accountCalendars = calendars.value.filter(cal => cal.google_account_id === account.id);
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

        googleAccounts.value = googleAccounts.value.filter(
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
                    <h3 class="text-lg font-medium text-gray-900">Manage Google Accounts</h3>
                    <a href="/connect/google"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Connect Account
                    </a>
                </div>

                <!-- Loading State -->
                <div v-if="isLoading" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 flex justify-center items-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
                        <p class="ml-4 text-gray-600">Loading account data...</p>
                    </div>
                </div>

                <!-- No Accounts State -->
                <div v-else-if="googleAccounts.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-600">
                        <p class="mb-4">You don't have any Google accounts connected yet.</p>
                        <a href="/connect/google" class="text-indigo-600 hover:text-indigo-800">
                            Connect your first Google account to get started
                        </a>
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
                            <tr v-for="account in googleAccounts" :key="account.id">
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
                                            <div class="text-sm text-gray-500">{{ account.email }}</div>
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
                <div v-if="googleAccounts.length > 0" class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Calendar Details</h3>

                        <div v-for="account in googleAccounts" :key="`cal-${account.id}`" class="mb-6">
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
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
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
                                    Are you sure you want to disconnect and delete this Google account?
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
    </AppLayout>
</template>
