<script setup>
defineProps({
    title: String,
    days: Array,
    selectedDays: Array,
    timeOptions: Array,
    startTime: String,
    endTime: String
});

defineEmits(['toggle-day', 'update:startTime', 'update:endTime']);
</script>

<template>
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition-shadow duration-200">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
            <h2 class="text-lg font-medium text-gray-900">{{ title }}</h2>
        </div>

        <div class="p-5 space-y-6">
            <!-- Available Days Section -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-sm font-medium text-gray-700">Available Days</label>
                    <span class="text-xs text-gray-500">Click to select/deselect days</span>
                </div>

                <div class="grid grid-cols-7 gap-2">
                    <button
                        v-for="day in days"
                        :key="day.value"
                        type="button"
                        :class="[
                            'flex flex-col items-center justify-center px-2 py-3 rounded-lg text-sm font-medium transition-all',
                            selectedDays.includes(day.value)
                                ? 'bg-indigo-600 text-white shadow-md transform scale-105'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                        ]"
                        @click="$emit('toggle-day', day.value)"
                    >
                        <span>{{ day.label.slice(0, 3) }}</span>
                    </button>
                </div>

                <div class="text-xs text-gray-500 mt-2">
                    Highlighted days are your selected available days
                </div>
            </div>

            <!-- Available Hours Section -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time (Your Local Time)</label>
                    <select
                        id="start_time"
                        :value="startTime"
                        @input="$emit('update:startTime', $event.target.value)"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option v-for="option in timeOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700">End Time (Your Local Time)</label>
                    <select
                        id="end_time"
                        :value="endTime"
                        @input="$emit('update:endTime', $event.target.value)"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option v-for="option in timeOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <div class="sm:col-span-2 p-3 bg-yellow-50 rounded-lg border border-yellow-100 mt-1">
                    <p class="text-xs text-yellow-800">
                        <span class="font-medium">Note:</span> All times are displayed in your local timezone.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
