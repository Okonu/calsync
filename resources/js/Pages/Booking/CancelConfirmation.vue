<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    booking: Object,
});
</script>

<template>
    <div>
        <Head title="Booking Cancelled" />

        <div class="min-h-screen bg-gray-50 px-4 py-12">
            <div class="max-w-md mx-auto">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">Booking Cancelled</h1>
                </div>

                <!-- Main Content -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="p-6">
                        <div class="text-center mb-6">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                                <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h2 class="mt-4 text-lg font-medium text-gray-900">Your Booking Has Been Cancelled</h2>
                            <p class="mt-1 text-sm text-gray-500">
                                The meeting with {{ booking.with }} scheduled for {{ booking.starts_at }} has been cancelled.
                            </p>
                        </div>

                        <div class="text-center">
                            <a
                                :href="`/book/${booking.bookingPage.slug}`"
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
