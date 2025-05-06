<script setup>
defineProps({
    title: String,
    subtitle: String,
    isProcessing: Boolean
});

defineEmits(['save']);
</script>

<template>
    <div class="bg-white shadow-sm rounded-xl mb-4 border border-gray-200">
        <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ title }}</h1>
                <p v-if="subtitle" class="mt-1 text-sm text-gray-500">{{ subtitle }}</p>
            </div>

            <button
                type="button"
                @click="$emit('save')"
                :disabled="isProcessing"
                class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor" v-if="!isProcessing">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" v-else>
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span v-if="isProcessing">Saving...</span>
                <span v-else>Save Settings</span>
            </button>
        </div>
    </div>
</template>
