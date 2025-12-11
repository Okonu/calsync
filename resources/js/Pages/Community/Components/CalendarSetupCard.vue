<script setup>
defineProps({
    accounts: Array,
    calendars: Array,
    selectedCalendarEmail: String,
    destinationCalendarId: String
});

defineEmits(['update:selectedCalendarEmail', 'update:destinationCalendarId', 'select-all', 'clear-all']);
</script>

<template>
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
            <h2 class="text-lg font-medium text-gray-900">Calendar Integration</h2>
        </div>

        <div class="p-5 space-y-6">
            <!-- Information Banner -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">
                            Calendar Integration Benefits
                        </h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>When you create events and sessions, they'll be automatically added to your selected calendar with speaker invites and meeting details.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Email Selection -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Calendar Account</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Choose which Google Calendar account will be used for community events.
                </p>

                <div v-if="accounts.length === 0" class="p-5 text-center border rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Google accounts connected</h3>
                    <p class="mt-1 text-sm text-gray-500">Connect a Google account to enable calendar integration.</p>
                    <div class="mt-6">
                        <a
                            href="/connect/google"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Connect Google Calendar
                        </a>
                    </div>
                </div>

                <div v-else class="space-y-4">
                    <!-- Account Selection -->
                    <div>
                        <label for="calendar_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Calendar Account
                        </label>
                        <select
                            id="calendar_email"
                            :value="selectedCalendarEmail"
                            @change="$emit('update:selectedCalendarEmail', $event.target.value)"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Choose calendar account...</option>
                            <option v-for="account in accounts" :key="account.id" :value="account.email">
                                {{ account.name }} ({{ account.email }})
                                {{ account.is_primary ? ' - Primary' : '' }}
                            </option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Events will be created using this Google Calendar account
                        </p>
                    </div>

                    <!-- Connect Additional Account Button -->
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Need a different account?</span>
                        <a
                            href="/connect/google"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Connect Another Account
                        </a>
                    </div>
                </div>
            </div>

            <!-- Destination Calendar Selection -->
            <div v-if="selectedCalendarEmail" class="border-t border-gray-200 pt-6">
                <h3 class="text-md font-medium text-gray-900 mb-2">Destination Calendar</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Choose a specific calendar where new events will be created. If not specified, events will be added to the primary calendar.
                </p>

                <div class="relative">
                    <select
                        id="destination_calendar_id"
                        :value="destinationCalendarId"
                        @change="$emit('update:destinationCalendarId', $event.target.value)"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 pl-4 pr-10 py-2.5"
                    >
                        <option value="">Use primary calendar</option>
                        <optgroup
                            v-for="account in accounts.filter(acc => acc.email === selectedCalendarEmail)"
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

            <!-- Calendar Features -->
            <div v-if="selectedCalendarEmail" class="border-t border-gray-200 pt-6">
                <h3 class="text-md font-medium text-gray-900 mb-3">What this enables</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-start space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Automatic Event Creation</p>
                            <p class="text-xs text-gray-500">Events and sessions automatically added to calendar</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Speaker Invitations</p>
                            <p class="text-xs text-gray-500">Speakers automatically invited to calendar events</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Google Meet Integration</p>
                            <p class="text-xs text-gray-500">Auto-generate Google Meet links for online events</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Reminder Notifications</p>
                            <p class="text-xs text-gray-500">Automatic reminders for you and speakers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
