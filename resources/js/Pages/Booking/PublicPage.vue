<script setup>
import { ref, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { format, parseISO, addDays, startOfWeek, isValid, isBefore, isToday } from 'date-fns';

const props = defineProps({
    bookingPage: Object,
});

const currentStep = ref(1);
const selectedDate = ref('');
const selectedSlot = ref(null);
const availableSlots = ref([]);
const isLoadingSlots = ref(false);
const calendarDays = ref([]);

const bookingForm = ref({
    name: '',
    email: '',
    notes: '',
    date: '',
    time: '',
});

const formErrors = ref({});
const isSubmitting = ref(false);
const bookingConfirmed = ref(false);
const bookingData = ref(null);

const now = ref(new Date());

setInterval(() => {
    now.value = new Date();
}, 60000);

function generateCalendarDays() {
    const startDate = startOfWeek(new Date());
    const days = [];

    for (let i = 0; i < 42; i++) {
        const date = addDays(startDate, i);
        const dateStr = format(date, 'yyyy-MM-dd');
        const isCurrentDay = format(date, 'yyyy-MM-dd') === format(now.value, 'yyyy-MM-dd');
        const isPastDay = isBefore(date, now.value) && !isCurrentDay;

        days.push({
            date: dateStr,
            day: format(date, 'd'),
            weekday: format(date, 'E'),
            isToday: isCurrentDay,
            isPast: isPastDay,
        });
    }

    calendarDays.value = days;
}

async function fetchTimeSlots(date) {
    if (!date || !isValid(parseISO(date))) return;

    selectedSlot.value = null;
    isLoadingSlots.value = true;

    try {
        const response = await axios.get(`/book/${props.bookingPage.slug}/slots`, {
            params: { date }
        });

        const slots = response.data.slots || [];
        const currentTime = now.value;
        const isSelectedDateToday = format(parseISO(date), 'yyyy-MM-dd') === format(currentTime, 'yyyy-MM-dd');

        if (isSelectedDateToday) {
            availableSlots.value = slots.filter(slot => {
                const slotTime = new Date(`${date}T${slot.start}:00`);
                return slotTime > currentTime;
            });
        } else {
            availableSlots.value = slots;
        }
    } catch (error) {
        console.error('Error fetching time slots:', error);
        availableSlots.value = [];
    } finally {
        isLoadingSlots.value = false;
    }
}

function selectDate(date) {
    const selectedDateObj = new Date(date);
    const today = new Date(format(now.value, 'yyyy-MM-dd'));

    if (isBefore(selectedDateObj, today)) {
        return;
    }

    selectedDate.value = date;
    fetchTimeSlots(date);
}

function selectTimeSlot(slot) {
    selectedSlot.value = slot;
}

function nextStep() {
    if (currentStep.value === 1 && selectedSlot.value) {
        bookingForm.value.date = selectedDate.value;
        bookingForm.value.time = selectedSlot.value.start;
        currentStep.value = 2;
    } else if (currentStep.value === 2) {
        submitBooking();
    }
}

function prevStep() {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
}

async function submitBooking() {
    formErrors.value = {};
    isSubmitting.value = true;

    try {
        const response = await axios.post(`/book/${props.bookingPage.slug}`, bookingForm.value);

        if (response.data && response.data.booking) {
            bookingData.value = response.data.booking;
            bookingConfirmed.value = true;
            currentStep.value = 3;
        } else {
            console.error('Unexpected response format:', response.data);
            throw new Error('Booking was not confirmed');
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            formErrors.value = error.response.data.errors;
        } else {
            console.error('Error creating booking:', error);
            formErrors.value = { general: ['Failed to create booking. Please try again.'] };
        }
    } finally {
        isSubmitting.value = false;
    }
}

function resetBooking() {
    bookingForm.value = {
        name: '',
        email: '',
        notes: '',
        date: '',
        time: '',
    };
    selectedDate.value = '';
    selectedSlot.value = null;
    availableSlots.value = [];
    bookingConfirmed.value = false;
    currentStep.value = 1;
    formErrors.value = {};
}

function formatTimeDisplay(timeString) {
    const dateStr = selectedDate.value;
    const utcDateTimeStr = `${dateStr}T${timeString}:00.000Z`;

    const utcDate = new Date(utcDateTimeStr);

    const hours = utcDate.getHours();
    const minutes = utcDate.getMinutes().toString().padStart(2, '0');
    const suffix = hours >= 12 ? 'PM' : 'AM';
    const displayHour = (hours % 12) || 12;

    return `${displayHour}:${minutes} ${suffix}`;
}

watch(now, () => {
    generateCalendarDays();
});

generateCalendarDays();

watch(selectedDate, (newDate) => {
    if (newDate) {
        fetchTimeSlots(newDate);
    }
});
</script>

<template>
    <div>
        <Head :title="bookingPage.title" />

        <div class="min-h-screen bg-gray-50 px-4 py-12">
            <div class="max-w-3xl mx-auto">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">{{ bookingPage.title }}</h1>
                    <p v-if="bookingPage.description" class="mt-2 text-gray-600">{{ bookingPage.description }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ bookingPage.duration }} minute meeting with {{ bookingPage.user.name }}</p>
                </div>

                <!-- Steps Indicator -->
                <div class="mb-8">
                    <div class="flex items-center justify-center">
                        <div
                            v-for="step in 3"
                            :key="step"
                            class="flex items-center"
                        >
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium"
                                :class="[
                                    currentStep === step ? 'bg-indigo-600 text-white' :
                                    currentStep > step ? 'bg-indigo-200 text-indigo-800' : 'bg-gray-200 text-gray-700'
                                ]"
                            >
                                {{ step }}
                            </div>

                            <div
                                v-if="step < 3"
                                class="w-16 h-1 mx-2"
                                :class="[
                                    currentStep > step ? 'bg-indigo-400' : 'bg-gray-300'
                                ]"
                            ></div>
                        </div>
                    </div>

                    <div class="mt-2 flex justify-center text-sm">
                        <div class="flex space-x-16">
                            <span :class="currentStep >= 1 ? 'text-indigo-600' : 'text-gray-500'">Select Time</span>
                            <span :class="currentStep >= 2 ? 'text-indigo-600' : 'text-gray-500'">Your Details</span>
                            <span :class="currentStep >= 3 ? 'text-indigo-600' : 'text-gray-500'">Confirmation</span>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <!-- Step 1: Select Date & Time -->
                    <div v-if="currentStep === 1" class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Select a Date & Time</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Calendar -->
                            <div>
                                <div class="grid grid-cols-7 mb-2">
                                    <div v-for="weekday in ['S', 'M', 'T', 'W', 'T', 'F', 'S']" :key="weekday" class="text-center text-sm font-medium text-gray-500">
                                        {{ weekday }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-7 gap-1">
                                    <button
                                        v-for="day in calendarDays"
                                        :key="day.date"
                                        class="py-2 rounded-md text-sm"
                                        :class="[
                                            day.isToday ? 'font-bold' : '',
                                            day.isPast ? 'text-gray-300 cursor-not-allowed' : 'hover:bg-gray-100',
                                            selectedDate === day.date ? 'bg-indigo-100 text-indigo-800' : ''
                                        ]"
                                        @click="selectDate(day.date)"
                                        :disabled="day.isPast"
                                    >
                                        {{ day.day }}
                                    </button>
                                </div>
                            </div>

                            <!-- Time Slots -->
                            <div>
                                <h3 class="text-sm font-medium text-gray-700 mb-2">
                                    {{ selectedDate ? format(parseISO(selectedDate), 'EEEE, MMMM d, yyyy') : 'Select a date to view available times' }}
                                </h3>

                                <div v-if="isLoadingSlots" class="flex justify-center py-8">
                                    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
                                </div>

                                <div v-else-if="!selectedDate" class="text-center py-8 text-gray-500">
                                    Please select a date to see available time slots
                                </div>

                                <div v-else-if="availableSlots.length === 0" class="text-center py-8 text-gray-500">
                                    No available time slots for this date
                                </div>

                                <div v-else class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto">
                                    <button
                                        v-for="slot in availableSlots"
                                        :key="`${slot.start}-${slot.end}`"
                                        class="py-2 px-4 border rounded-md text-sm text-center"
                                        :class="[
                                                selectedSlot && selectedSlot.start === slot.start
                                                    ? 'bg-indigo-100 text-indigo-800 border-indigo-300'
                                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                                            ]"
                                        @click="selectTimeSlot(slot)"
                                    >
                                        {{ formatTimeDisplay(slot.start) }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Your Details -->
                    <div v-if="currentStep === 2" class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Your Details</h2>

                        <div class="mb-6 p-4 bg-gray-50 rounded-md">
                            <div class="text-sm">
                                <p class="font-medium text-gray-900">
                                    {{ format(parseISO(selectedDate), 'EEEE, MMMM d, yyyy') }}
                                </p>
                                <p class="text-gray-700">
                                    {{ formatTimeDisplay(selectedSlot.start) }} - {{ formatTimeDisplay(selectedSlot.end) }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input
                                    id="name"
                                    v-model="bookingForm.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <div v-if="formErrors.name" class="text-red-500 text-sm mt-1">{{ formErrors.name[0] }}</div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input
                                    id="email"
                                    v-model="bookingForm.email"
                                    type="email"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <div v-if="formErrors.email" class="text-red-500 text-sm mt-1">{{ formErrors.email[0] }}</div>
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes</label>
                                <textarea
                                    id="notes"
                                    v-model="bookingForm.notes"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <div v-if="formErrors.notes" class="text-red-500 text-sm mt-1">{{ formErrors.notes[0] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Confirmation -->
                    <div v-if="currentStep === 3 && bookingConfirmed" class="p-6">
                        <div class="text-center mb-6">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                                <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h2 class="mt-4 text-lg font-medium text-gray-900">Booking Confirmed</h2>
                            <p class="mt-1 text-sm text-gray-500">
                                You're scheduled with {{ bookingPage.user.name }}
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-md p-4 mb-6">
                            <div class="text-sm">
                                <p class="font-medium text-gray-900">{{ bookingData.starts_at }}</p>
                                <p v-if="bookingData.meeting_link" class="mt-2">
                                    <a :href="bookingData.meeting_link" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                                        Join with Google Meet
                                    </a>
                                </p>
                            </div>
                        </div>

                        <div class="text-center">
                            <p class="text-sm text-gray-500">A calendar invitation has been sent to {{ bookingForm.email }}</p>

                            <!-- Cancel Booking Link -->
                            <p class="mt-2 text-sm text-gray-500">
                                Need to make changes?
                                <a :href="`/book/cancel/${bookingData.uid}`" class="text-indigo-600 hover:underline">
                                    Cancel this booking
                                </a>
                            </p>

                            <div class="mt-4">
                                <button
                                    @click="resetBooking"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Book Another Time
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step Navigation -->
                    <div v-if="currentStep < 3" class="px-6 py-4 bg-gray-50 flex justify-between">
                        <button
                            v-if="currentStep > 1"
                            @click="prevStep"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Back
                        </button>
                        <div v-else></div>

                        <button
                            @click="nextStep"
                            :disabled="(currentStep === 1 && !selectedSlot) || isSubmitting"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-gray-300 disabled:cursor-not-allowed"
                        >
                            <span v-if="currentStep === 1">Continue</span>
                            <span v-else-if="currentStep === 2 && isSubmitting">Scheduling...</span>
                            <span v-else-if="currentStep === 2">Schedule Event</span>
                        </button>
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
