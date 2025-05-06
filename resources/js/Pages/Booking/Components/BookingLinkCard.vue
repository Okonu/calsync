<script setup>
defineProps({
    bookingUrl: String
});

defineEmits(['copy']);
</script>

<template>
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
            </svg>
            <h2 class="text-lg font-medium text-gray-900">Your Booking Link</h2>
        </div>

        <div class="p-5">
            <div v-if="bookingUrl">
                <div class="relative mb-3">
                    <input
                        type="text"
                        :value="bookingUrl"
                        readonly
                        class="w-full p-2.5 pr-10 text-sm border border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50"
                    />
                    <button
                        @click="$emit('copy')"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-indigo-600 transition-colors"
                        title="Copy to clipboard"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                            <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                        </svg>
                    </button>
                </div>

                <a :href="bookingUrl" target="_blank" class="flex justify-center items-center py-2 px-3 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                    </svg>
                    Preview Booking Page
                </a>
            </div>
            <div v-else class="text-center py-3">
                <p class="text-sm text-gray-500">Save your settings to generate a booking link</p>
            </div>
        </div>
    </div>
</template>
