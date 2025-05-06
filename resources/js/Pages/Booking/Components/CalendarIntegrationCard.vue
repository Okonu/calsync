<script setup>
defineProps({
    accounts: Array,
    calendars: Array,
    selectedCalendars: Array,
    destinationCalendarId: String
});

defineEmits(['update:selectedCalendars', 'update:destinationCalendarId', 'select-all', 'clear-all']);
</script>

<template>
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd" />
                </svg>
                <h2 class="text-lg font-medium text-gray-900">Calendar Integration</h2>
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
            <!-- Check Availability Section -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Check Availability Against These Calendars</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Select which calendars to check for availability. Bookings will only be offered when you're available across all selected calendars.
                </p>

                <div v-if="accounts.length === 0" class="p-5 text-center border rounded-lg">
                    <p class="text-gray-500">No Google accounts connected yet.</p>
                </div>

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
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                                <div
                                    v-for="calendar in calendars.filter(c => c.google_account_id === account.id)"
                                    :key="calendar.id"
                                    class="flex items-center p-3 rounded-lg border"
                                    :class="selectedCalendars.includes(calendar.id) ? 'bg-indigo-50 border-indigo-200' : 'bg-white border-gray-200'"
                                >
                                    <input
                                        type="checkbox"
                                        :id="`calendar-${calendar.id}`"
                                        :value="calendar.id"
                                        :checked="selectedCalendars.includes(calendar.id)"
                                        @change="
                                            $emit(
                                                'update:selectedCalendars',
                                                $event.target.checked
                                                    ? [...selectedCalendars, calendar.id]
                                                    : selectedCalendars.filter(id => id !== calendar.id)
                                            )
                                        "
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <label :for="`calendar-${calendar.id}`" class="ml-2 cursor-pointer flex items-center">
                                        <span
                                            class="w-3 h-3 rounded-full mr-2 flex-shrink-0"
                                            :style="{ backgroundColor: calendar.color }"
                                        ></span>
                                        <span class="text-sm text-gray-700 truncate max-w-xs">
                                            {{ calendar.name }}
                                            <span v-if="calendar.is_primary" class="text-xs text-gray-500">(Primary)</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Destination Calendar Section -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-md font-medium text-gray-900 mb-2">Destination Calendar</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Choose a calendar where new appointments will be added. If not specified, your primary account's default calendar will be used.
                </p>

                <div class="relative">
                    <select
                        id="destination_calendar_id"
                        :value="destinationCalendarId"
                        @change="$emit('update:destinationCalendarId', $event.target.value)"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-4 pr-10 py-2.5"
                    >
                        <option value="">Use primary account's default calendar</option>
                        <optgroup
                            v-for="account in accounts"
                            :key="account.id"
                            :label="account.name + ' (' + account.email + ')'"
                        >
                            <option
                                v-for="calendar in calendars.filter(c => c.google_account_id === account.id)"
                                :key="calendar.id"
                                :value="calendar.id"
                            >
                                {{ calendar.name }}{{ calendar.is_primary ? ' (Primary)' : '' }}
                            </option>
                        </optgroup>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
