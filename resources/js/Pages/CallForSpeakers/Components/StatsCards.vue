<template>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <StatCard
            title="Total Applications"
            :value="stats.total_applications"
            icon="document"
            color="blue"
        />

        <StatCard
            title="Pending Review"
            :value="stats.pending_applications"
            icon="clock"
            color="yellow"
        />

        <StatCard
            title="Approved"
            :value="stats.approved_applications"
            icon="check"
            color="green"
        />

        <StatCard
            title="Rejected"
            :value="stats.rejected_applications"
            icon="x"
            color="red"
        />
    </div>
</template>

<script setup>
import StatCard from './StatCard.vue';

defineProps({
    stats: Object
});
</script>
