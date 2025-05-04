<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    booking: Object,
});
</script>

<template>
    <div>
        <Head title="Booking Confirmed" />

        <div class="min-h-screen bg-gray-50 px-4 py-12">
            <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
                <div class="text-center py-6 bg-indigo-600 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h1 class="text-2xl font-bold mt-2">Booking Confirmed</h1>
                </div>

                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-lg font-medium text-gray-900">Appointment Details</h2>
                        <p class="mt-2 text-gray-600">
                            <span class="font-medium">Date and Time:</span> {{ booking.starts_at }} - {{ booking.ends_at }}
                        </p>
                        <p class="mt-1 text-gray-600">
                            <span class="font-medium">With:</span> {{ booking.with }}
                        </p>

                        <div v-if="booking.meeting_link" class="mt-4 p-3 bg-indigo-50 rounded-md">
                            <p class="text-sm text-indigo-800">
                                <span class="font-medium">Google Meet Link:</span>
                                <a :href="booking.meeting_link" target="_blank" class="block mt-1 text-indigo-600 hover:underline break-all">
                                    {{ booking.meeting_link }}
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-600">
                            A calendar invitation has been sent to your email.
                        </p>

                        <p class="mt-4 text-sm text-gray-600">
                            Need to make changes?
                            <a :href="`/book/cancel/${booking.uid}`" class="text-indigo-600 hover:underline">Cancel this booking</a>
                        </p>
                    </div>

                    <div class="mt-6 flex justify-center">
                        <a href="/" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
