<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <h3 class="text-sm font-medium text-gray-500 mb-2">Timeline</h3>
            <div class="space-y-2 text-sm">
                <div v-if="cfs.opens_at" class="flex items-center text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Opens: {{ formatDate(cfs.opens_at) }}
                </div>
                <div v-if="cfs.closes_at" class="flex items-center text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Closes: {{ formatDate(cfs.closes_at) }}
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500 mb-2">Application Type</h3>
            <p class="text-sm text-gray-900">{{ getApplicationTypeLabel(cfs.application_type) }}</p>
            <div class="mt-2">
                <p class="text-xs text-gray-500">
                    {{ cfs.required_fields?.length || 0 }} required fields
                    <span v-if="cfs.custom_questions?.length">, {{ cfs.custom_questions.length }} custom questions</span>
                </p>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500 mb-2">Settings</h3>
            <div class="space-y-1 text-xs">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" :class="cfs.requires_login ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span :class="cfs.requires_login ? 'text-gray-900' : 'text-gray-500'">Requires Login</span>
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" :class="cfs.show_application_count ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span :class="cfs.show_application_count ? 'text-gray-900' : 'text-gray-500'">Show Count</span>
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" :class="cfs.allow_multiple_applications ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span :class="cfs.allow_multiple_applications ? 'text-gray-900' : 'text-gray-500'">Multiple Apps</span>
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" :class="cfs.auto_approve ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span :class="cfs.auto_approve ? 'text-gray-900' : 'text-gray-500'">Auto Approve</span>
                </div>
            </div>
        </div>
    </div>

    <div v-if="cfs.description" class="mt-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
        <p class="text-gray-900 whitespace-pre-wrap">{{ cfs.description }}</p>
    </div>
</template>

<script setup>
defineProps({
    cfs: Object
});

function getApplicationTypeLabel(type) {
    switch (type) {
        case 'event':
            return 'Apply to entire events';
        case 'session':
            return 'Apply to specific sessions';
        case 'both':
            return 'Allow both options';
        default:
            return type;
    }
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>
