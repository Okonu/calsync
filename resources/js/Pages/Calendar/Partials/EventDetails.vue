<!-- resources/js/Pages/Calendar/Partials/EventDetails.vue -->
<script setup>
import { ref, computed } from 'vue';
import { format } from 'date-fns';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon, MapPinIcon, CalendarIcon, ClockIcon, UserIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    event: Object,
});

const emit = defineEmits(['close']);

const open = ref(true);

const closeModal = () => {
    open.value = false;
    emit('close');
};

const formattedDate = computed(() => {
    if (!props.event) return '';

    const start = new Date(props.event.starts_at);
    const end = new Date(props.event.ends_at);

    if (props.event.all_day) {
        if (start.toDateString() === end.toDateString()) {
            return format(start, 'EEEE, MMMM d, yyyy');
        } else {
            return `${format(start, 'MMMM d')} - ${format(end, 'MMMM d, yyyy')}`;
        }
    }

    if (start.toDateString() === end.toDateString()) {
        return format(start, 'EEEE, MMMM d, yyyy');
    }

    return `${format(start, 'MMM d, yyyy')} - ${format(end, 'MMM d, yyyy')}`;
});

const formattedTime = computed(() => {
    if (!props.event || props.event.all_day) return 'All day';

    const start = new Date(props.event.starts_at);
    const end = new Date(props.event.ends_at);

    return `${format(start, 'h:mm a')} - ${format(end, 'h:mm a')}`;
});

const textColor = computed(() => {
    if (!props.event) return '#000000';

    const hex = props.event.color.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);

    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance > 0.5 ? '#000000' : '#ffffff';
});
</script>

<template>
    <TransitionRoot appear :show="open" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-10">
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                            <div class="absolute top-0 right-0 pt-4 pr-4">
                                <button
                                    type="button"
                                    class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    @click="closeModal"
                                >
                                    <span class="sr-only">Close</span>
                                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                </button>
                            </div>

                            <div v-if="event" class="mt-2">
                                <!-- Event Title -->
                                <div
                                    class="px-4 py-2 rounded-lg mb-4 flex items-center"
                                    :style="{ backgroundColor: event.color, color: textColor }"
                                >
                                    <h3 class="text-lg font-semibold">{{ event.title }}</h3>
                                </div>

                                <!-- Event Details -->
                                <div class="space-y-3">
                                    <!-- Date & Time -->
                                    <div class="flex">
                                        <CalendarIcon class="h-5 w-5 text-gray-400 mr-2" />
                                        <div>
                                            <div class="font-medium">{{ formattedDate }}</div>
                                            <div class="text-sm text-gray-500">{{ formattedTime }}</div>
                                        </div>
                                    </div>

                                    <!-- Location -->
                                    <div v-if="event.location" class="flex">
                                        <MapPinIcon class="h-5 w-5 text-gray-400 mr-2" />
                                        <div class="text-gray-700">{{ event.location }}</div>
                                    </div>

                                    <!-- Calendar & Account -->
                                    <div class="flex">
                                        <ClockIcon class="h-5 w-5 text-gray-400 mr-2" />
                                        <div>
                                            <div class="font-medium">{{ event.calendar.name }}</div>
                                            <div class="text-sm text-gray-500">{{ event.account.email }}</div>
                                        </div>
                                    </div>

                                    <!-- Attendees -->
                                    <div v-if="event.attendees && event.attendees.length > 0" class="flex">
                                        <UserIcon class="h-5 w-5 text-gray-400 mr-2" />
                                        <div>
                                            <div class="font-medium">Attendees</div>
                                            <div class="text-sm text-gray-500">
                                                {{ event.attendees.length }} people
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div v-if="event.description" class="mt-4 border-t pt-4">
                                    <h4 class="text-sm font-medium text-gray-900">Description</h4>
                                    <div class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">
                                        {{ event.description }}
                                    </div>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
