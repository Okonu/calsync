<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    booking: Object,
});

const isSubmitting = ref(false);
const errorMessage = ref('');
const isCancelled = ref(false);
const cancellationInfo = ref(null);

async function cancelBooking() {
    isSubmitting.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post(`/book/cancel/${props.booking.uid}`);
        isCancelled.value = true;
        cancellationInfo.value = response.data;
    } catch (error) {
        console.error('Error cancelling booking:', error);
        errorMessage.value = error.response?.data?.message || 'Failed to cancel booking. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <div>
        <Head :title="`Cancel Booking with ${booking.with}`" />

        <div class="min-h-screen bg-gray-50 px-4 py-12">
            <div class="max-w-md mx-auto">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">Cancel Booking</h1>
                </div>

                <!-- Main Content -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div v-if="!isCancelled" class="p-6">
                        <div class="mb-6 p-4 bg-gray-50 rounded-md">
                            <div class="text-sm">
                                <h2 class="font-medium text-gray-900 mb-2">Meeting Details</h2>
                                <p class="font-medium text-gray-800">
                                    {{ booking.with }}
                                </p>
                                <p class="text-gray-600">
                                    {{ booking.starts_at }}
                                </p>
                            </div>
                        </div>

                        <div class="text-center mb-6">
                            <p class="text-gray-600">Are you sure you want to cancel this booking?</p>
                        </div>

                        <div v-if="errorMessage" class="mb-4 text-center">
                            <p class="text-red-500">{{ errorMessage }}</p>
                        </div>

                        <div class="flex justify-between">
                            <a
                                href="/"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Go Back
                            </a>

                            <button
                                @click="cancelBooking"
                                :disabled="isSubmitting"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:bg-gray-300 disabled:cursor-not-allowed"
                            >
                                <span v-if="isSubmitting">Cancelling...</span>
                                <span v-else>Cancel Booking</span>
                            </button>
                        </div>
                    </div>

                    <div v-else class="p-6">
                        <div class="text-center mb-6">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                                <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h2 class="mt-4 text-lg font-medium text-gray-900">Booking Cancelled</h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Your booking with {{ booking.with }} has been cancelled.
                            </p>
                        </div>

                        <div class="text-center">
                            <a
                                :href="`/book/${cancellationInfo?.booking?.bookingPage?.slug || ''}`"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Book Another Time
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Powered By Footer -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-500">
                        by Okonu
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
