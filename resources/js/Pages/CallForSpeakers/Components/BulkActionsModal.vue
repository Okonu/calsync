<template>
    <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Actions</h3>
                <p class="text-sm text-gray-600 mb-4">{{ selectedCount }} applications selected</p>

                <div class="space-y-3">
                    <button
                        @click="$emit('bulk-approve')"
                        class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
                    >
                        Approve Selected
                    </button>
                    <button
                        @click="$emit('bulk-reject')"
                        class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
                    >
                        Reject Selected
                    </button>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 bg-white text-gray-500 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-100"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    show: Boolean,
    selectedCount: Number
});

defineEmits(['close', 'bulk-approve', 'bulk-reject']);
</script>
