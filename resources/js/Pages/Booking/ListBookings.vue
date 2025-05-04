<script setup>
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import { format, parseISO } from 'date-fns';

const props = defineProps({
    bookings: Array,
});

const showCancelModal = ref(false);
const bookingToCancel = ref(null);
const isLoading = ref(false);

function formatDate(dateString) {
    if (!dateString) return '';
    return format(parseISO(dateString), 'EEE, MMM d, yyyy');
}

function formatTime(dateString) {
    if (!dateString) return '';
    return format(parseISO(dateString), 'h:mm a');
}

function getStatusClass(status) {
    switch (status) {
        case 'confirmed':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function confirmCancel(booking) {
    bookingToCancel.value = booking;
    showCancelModal.value = true;
}

async function cancelBooking() {
    if (!bookingToCancel.value) return;

    try {
        isLoading.value = true;
        await axios.post(`/api/bookings/${bookingToCancel.value.id}/cancel`);

        const index = props.bookings.findIndex(b => b.id === bookingToCancel.value.id);
        if (index !== -1) {
            props.bookings[index].status = 'cancelled';
        }

        showCancelModal.value = false;
        bookingToCancel.value = null;
    } catch (error) {
        console.error('Error cancelling booking:', error);
    } finally {
        isLoading.value = false;
    }
}
</script>

<template>
    <AppLayout title="Manage Bookings">
        <Head title="Manage Bookings" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h1 class="text-xl font-semibold mb-6">Your Bookings</h1>

                        <!-- No Bookings State -->
                        <div v-if="bookings.length === 0" class="text-center py-8">
                            <p class="text-gray-600">You don't have any bookings yet.</p>
                            <p class="mt-2 text-gray-500">
                                Share your booking page link with others to get started.
                            </p>
                        </div>

                        <!-- Bookings Table -->
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Time
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="booking in bookings" :key="booking.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ booking.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ booking.booking_page.title }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ formatDate(booking.starts_at) }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ formatTime(booking.starts_at) }} - {{ formatTime(booking.ends_at) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="getStatusClass(booking.status)"
                                            >
                                                {{ booking.status }}
                                            </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ booking.email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div v-if="booking.status === 'confirmed'" class="flex space-x-2 justify-end">
                                            <button
                                                v-if="booking.meeting_link"
                                                @click="window.open(booking.meeting_link, '_blank')"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Join Meeting
                                            </button>
                                            <button
                                                @click="confirmCancel(booking)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                        <span v-else class="text-gray-400">No actions available</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Confirmation Modal -->
        <div v-if="showCancelModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Cancel Booking
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to cancel this booking? This action cannot be undone.
                                </p>
                                <div v-if="bookingToCancel" class="mt-2 p-3 bg-gray-50 rounded-md">
                                    <p class="text-sm text-gray-700">
                                        <span class="font-medium">Meeting with:</span> {{ bookingToCancel.name }}
                                    </p>
                                    <p class="text-sm text-gray-700">
                                        <span class="font-medium">Date:</span> {{ formatDate(bookingToCancel.starts_at) }}
                                    </p>
                                    <p class="text-sm text-gray-700">
                                        <span class="font-medium">Time:</span> {{ formatTime(bookingToCancel.starts_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button
                        @click="cancelBooking"
                        :disabled="isLoading"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        <span v-if="isLoading">Processing...</span>
                        <span v-else>Cancel Booking</span>
                    </button>
                    <button
                        @click="showCancelModal = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Back
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
