<script setup>
defineProps({
    accounts: Array,
    calendars: Array,
    selectedCalendars: Array
});

defineEmits(['update:selectedCalendars', 'select-all', 'clear-all']);
</script>

<template>
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                </svg>
                <h2 class="text-lg font-medium text-gray-900">Availability Checking</h2>
            </div>
            <div class="flex space-x-3">
                <button
                    type="button"
                    @click="$emit('select-all')"
                    class="px-3 py-1.5 text-sm font-medium bg-indigo-100 text-indigo-800 border border-indigo-300 rounded-lg hover:bg-indigo-200 transition-colors"
                >
                    Select All
                </button>
                <button
                    type="button"
                    @click="$emit('clear-all')"
                    class="px-3 py-1.5 text-sm font-medium bg-gray-100 text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-200 transition-colors"
                >
                    Clear All
                </button>
            </div>
        </div>

        <div class="p-5 space-y-6">
            <!-- Information -->
            <div>
                <p class="text-sm text-gray-600 mb-4">
                    Select which calendars to check when scheduling events. This helps avoid conflicts with your existing commitments.
                </p>
            </div>

            <!-- No Accounts State -->
            <div v-if="accounts.length === 0" class="p-5 text-center border rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Google accounts connected</h3>
                <p class="mt-1 text-sm text-gray-500">Connect a Google account to enable availability checking.</p>
            </div>

            <!-- Calendar Selection -->
            <div v-else class="space-y-6">
                <div v-for="account in accounts" :key="account.id" class="border border-gray-200 rounded-lg overflow-hidden">
                    <!-- Account Header -->
                    <div class="bg-gray-50 px-4 py-3 flex items-center">
                        <div
                            class="w-4 h-4 rounded-full mr-2 flex-shrink-0"
                            :style="{ backgroundColor: account.color }"
                        ></div>
                        <span class="font-medium text-gray-900">{{ account.name }}</span>
                        <span class="text-gray-500 text-sm ml-2">({{ account.email }})</span>
                        <span v-if="account.is_primary" class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">
                            Primary
                        </span>
                    </div>

                    <!-- Calendar List -->
                    <div class="p-4">
                        <div v-if="calendars.filter(c => c.google_account_id === account.id).length === 0" class="text-center py-4">
                            <p class="text-sm text-gray-500">No calendars found for this account</p>
                        </div>
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div
                                v-for="calendar in calendars.filter(c => c.google_account_id === account.id)"
                                :key="calendar.id"
                                class="flex items-center p-3 rounded-lg border cursor-pointer hover:bg-gray-50 transition-colors"
                                :class="selectedCalendars.includes(calendar.id) ? 'bg-indigo-50 border-indigo-200' : 'bg-white border-gray-200'"
                                @click="$emit('update:selectedCalendars',
                                    selectedCalendars.includes(calendar.id)
                                        ? selectedCalendars.filter(id => id !== calendar.id)
                                        : [...selectedCalendars, calendar.id]
                                )"
                            >
                                <input
                                    type="checkbox"
                                    :id="`availability-calendar-${calendar.id}`"
                                    :checked="selectedCalendars.includes(calendar.id)"
                                    @click.stop
                                    @change="$emit('update:selectedCalendars',
                                        $event.target.checked
                                            ? [...selectedCalendars, calendar.id]
                                            : selectedCalendars.filter(id => id !== calendar.id)
                                    )"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label :for="`availability-calendar-${calendar.id}`" class="ml-2 cursor-pointer flex items-center flex-1">
                                    <span
                                        class="w-3 h-3 rounded-full mr-2 flex-shrink-0"
                                        :style="{ backgroundColor: calendar.color }"
                                    ></span>
                                    <span class="text-sm text-gray-700 truncate">
                                        {{ calendar.name }}
                                        <span v-if="calendar.is_primary" class="text-xs text-gray-500">(Primary)</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selected Count -->
            <div v-if="selectedCalendars.length > 0" class="bg-green-50 border border-green-200 rounded-md p-3">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span class="text-sm font-medium text-green-800">
                        {{ selectedCalendars.length }} calendar{{ selectedCalendars.length === 1 ? '' : 's' }} selected for availability checking
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
