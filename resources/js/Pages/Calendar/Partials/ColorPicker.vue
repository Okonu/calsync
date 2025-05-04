<!-- resources/js/Pages/Calendar/Partials/ColorPicker.vue -->
<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    initialColor: {
        type: String,
        default: '#4285F4'
    }
});

const emit = defineEmits(['colorSelected']);

const showPicker = ref(false);
const selectedColor = ref(props.initialColor);
const pickerRef = ref(null);

const colorOptions = [
    '#4285F4', // Google Blue
    '#EA4335', // Google Red
    '#FBBC05', // Google Yellow
    '#34A853', // Google Green
    '#8E24AA', // Purple
    '#16A085', // Turquoise
    '#F39C12', // Orange
    '#E74C3C', // Red
    '#3498DB', // Blue
    '#2ECC71', // Green
    '#9B59B6', // Purple
    '#1ABC9C', // Light Green
    '#D35400', // Dark Orange
    '#7F8C8D', // Gray
];

const selectColor = (color) => {
    selectedColor.value = color;
    emit('colorSelected', color);
    showPicker.value = false;
};

const togglePicker = () => {
    showPicker.value = !showPicker.value;
};

const handleClickOutside = (event) => {
    if (pickerRef.value && !pickerRef.value.contains(event.target)) {
        showPicker.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

watch(() => props.initialColor, (newValue) => {
    selectedColor.value = newValue;
});
</script>

<template>
    <div class="relative" ref="pickerRef">
        <button
            @click.stop="togglePicker"
            class="w-6 h-6 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            :style="{ backgroundColor: selectedColor }"
            title="Change color"
        ></button>

        <div
            v-if="showPicker"
            class="absolute right-0 mt-2 bg-white rounded-lg shadow-lg p-2 z-10 grid grid-cols-5 gap-1"
            style="width: 150px;"
        >
            <button
                v-for="color in colorOptions"
                :key="color"
                @click.stop="selectColor(color)"
                class="w-6 h-6 rounded-full border hover:scale-110 transition-transform"
                :style="{ backgroundColor: color, borderColor: color === selectedColor ? 'white' : color }"
                :class="{ 'ring-2 ring-white ring-offset-1 ring-offset-gray-800': color === selectedColor }"
            ></button>
        </div>
    </div>
</template>
