<template>
    <div class="min-h-screen bg-gray-50">
        <Head title="Discover Communities" />

        <!-- Hero Section -->
        <div class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl">
                        Discover Communities
                    </h1>
                    <p class="mt-4 text-xl text-gray-600 max-w-3xl mx-auto">
                        Find amazing tech communities, join events, and share your knowledge as a speaker
                    </p>

                    <!-- Search Bar -->
                    <div class="mt-8 max-w-md mx-auto">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search communities..."
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Stats -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <div class="flex flex-wrap items-center gap-6">
                    <!-- Filter Options -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Category:</label>
                        <select v-model="selectedCategory" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Categories</option>
                            <option value="tech">Technology</option>
                            <option value="design">Design</option>
                            <option value="business">Business</option>
                            <option value="data">Data Science</option>
                            <option value="cloud">Cloud Computing</option>
                            <option value="mobile">Mobile Development</option>
                            <option value="web">Web Development</option>
                            <option value="ai">AI & Machine Learning</option>
                            <option value="devops">DevOps</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Location:</label>
                        <select v-model="selectedLocation" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Locations</option>
                            <option value="online">Online Only</option>
                            <option value="nairobi">Nairobi</option>
                            <option value="mombasa">Mombasa</option>
                            <option value="kisumu">Kisumu</option>
                            <option value="eldoret">Eldoret</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Activity:</label>
                        <select v-model="selectedActivity" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Communities</option>
                            <option value="active">Active Events</option>
                            <option value="recruiting">Open CFS</option>
                            <option value="new">Recently Joined</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Sort:</label>
                        <select v-model="sortBy" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="newest">Newest First</option>
                            <option value="popular">Most Popular</option>
                            <option value="active">Most Active</option>
                            <option value="alphabetical">A-Z</option>
                        </select>
                    </div>

                    <!-- Results Count -->
                    <div class="flex-1 text-right">
                        <span class="text-sm text-gray-500">
                            {{ filteredCommunities.length }} of {{ communities.length }} communities
                        </span>
                    </div>
                </div>
            </div>

            <!-- Communities Grid -->
            <div v-if="filteredCommunities.length === 0" class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No communities found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Try adjusting your search or filter criteria.
                </p>
                <button
                    @click="clearFilters"
                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
                >
                    Clear Filters
                </button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="community in filteredCommunities"
                    :key="community.id"
                    class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200"
                >
                    <!-- Community Header -->
                    <div class="p-6">
                        <div class="flex items-center space-x-4 mb-4">
                            <div
                                class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold"
                                :style="{ backgroundColor: community.color }"
                            >
                                <img
                                    v-if="community.logo_url"
                                    :src="community.logo_url"
                                    :alt="community.name"
                                    class="w-full h-full rounded-full object-cover"
                                >
                                <span v-else>{{ community.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900 truncate">
                                    {{ community.name }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    by {{ community.owner.name }}
                                </p>
                            </div>
                            <div class="flex space-x-1">
                                <span v-if="community.hasActiveEvents" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                                <span v-if="community.hasOpenCfs" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Recruiting
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <p v-if="community.description" class="text-gray-600 text-sm mb-4 line-clamp-3">
                            {{ community.description }}
                        </p>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="text-center">
                                <div class="text-lg font-semibold text-gray-900">{{ community.stats.upcomingEvents }}</div>
                                <div class="text-xs text-gray-500">Upcoming Events</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-gray-900">{{ community.stats.openCfs }}</div>
                                <div class="text-xs text-gray-500">Open CFS</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-semibold text-gray-900">{{ community.stats.totalSpeakers }}</div>
                                <div class="text-xs text-gray-500">Speakers</div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div v-if="community.tags && community.tags.length > 0" class="mb-4">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="tag in community.tags.slice(0, 3)"
                                    :key="tag"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                >
                                    {{ tag }}
                                </span>
                                <span
                                    v-if="community.tags.length > 3"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                >
                                    +{{ community.tags.length - 3 }}
                                </span>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div v-if="community.recentActivity" class="mb-4 text-xs text-gray-500">
                            <span class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ community.recentActivity }}
                            </span>
                        </div>

                        <!-- Social Links -->
                        <div v-if="community.social_links" class="flex items-center space-x-3 mb-4">
                            <a v-if="community.social_links.website" :href="community.social_links.website" target="_blank" class="text-gray-400 hover:text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9" />
                                </svg>
                            </a>
                            <a v-if="community.social_links.twitter" :href="community.social_links.twitter" target="_blank" class="text-gray-400 hover:text-blue-500">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84" />
                                </svg>
                            </a>
                            <a v-if="community.social_links.linkedin" :href="community.social_links.linkedin" target="_blank" class="text-gray-400 hover:text-blue-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a v-if="community.social_links.github" :href="community.social_links.github" target="_blank" class="text-gray-400 hover:text-gray-900">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-2">
                            <Link
                                :href="`/community/${community.slug}`"
                                class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                View Community
                            </Link>
                            <Link
                                v-if="community.hasUpcomingEvents"
                                :href="`/community/${community.slug}/events`"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </Link>
                            <Link
                                v-if="community.hasOpenCfs"
                                :href="`/community/${community.slug}/cfs`"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Load More Button -->
            <div v-if="hasMore" class="mt-8 text-center">
                <button
                    @click="loadMore"
                    :disabled="loading"
                    class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                    <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ loading ? 'Loading...' : 'Load More Communities' }}
                </button>
            </div>
        </div>

        <!-- Featured Communities Section -->
        <div v-if="featuredCommunities.length > 0" class="bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900">Featured Communities</h2>
                    <p class="mt-4 text-lg text-gray-600">
                        Discover some of the most active and engaging communities in our platform
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="community in featuredCommunities"
                        :key="community.id"
                        class="text-center group"
                    >
                        <Link :href="`/community/${community.slug}`" class="block">
                            <div
                                class="w-20 h-20 mx-auto rounded-full flex items-center justify-center text-white font-bold text-2xl mb-4 group-hover:scale-105 transition-transform duration-200"
                                :style="{ backgroundColor: community.color }"
                            >
                                <img
                                    v-if="community.logo_url"
                                    :src="community.logo_url"
                                    :alt="community.name"
                                    class="w-full h-full rounded-full object-cover"
                                >
                                <span v-else>{{ community.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600">{{ community.name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ community.stats.totalEvents }} events</p>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-indigo-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-white">Ready to Build Your Community?</h2>
                    <p class="mt-4 text-lg text-indigo-200">
                        Join thousands of organizers creating amazing tech events and fostering knowledge sharing.
                    </p>
                    <div class="mt-8 flex justify-center space-x-4">
                        <a
                            href="/communities/create"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Create Community
                        </a>
                        <a
                            href="/help"
                            class="inline-flex items-center px-6 py-3 border border-white text-base font-medium rounded-md text-white bg-transparent hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white"
                        >
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    communities: Array,
    featuredCommunities: Array,
    categories: Array,
    locations: Array,
});

const searchQuery = ref('');
const selectedCategory = ref('');
const selectedLocation = ref('');
const selectedActivity = ref('');
const sortBy = ref('newest');
const loading = ref(false);
const hasMore = ref(true);

const filteredCommunities = computed(() => {
    let filtered = [...props.communities];

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(community =>
            community.name.toLowerCase().includes(query) ||
            community.description?.toLowerCase().includes(query) ||
            community.owner.name.toLowerCase().includes(query)
        );
    }

    // Category filter
    if (selectedCategory.value) {
        filtered = filtered.filter(community =>
            community.category === selectedCategory.value ||
            community.tags?.includes(selectedCategory.value)
        );
    }

    // Location filter
    if (selectedLocation.value) {
        if (selectedLocation.value === 'online') {
            filtered = filtered.filter(community => community.isOnlineOnly);
        } else {
            filtered = filtered.filter(community =>
                community.location?.toLowerCase().includes(selectedLocation.value.toLowerCase())
            );
        }
    }

    // Activity filter
    if (selectedActivity.value) {
        switch (selectedActivity.value) {
            case 'active':
                filtered = filtered.filter(community => community.hasActiveEvents);
                break;
            case 'recruiting':
                filtered = filtered.filter(community => community.hasOpenCfs);
                break;
            case 'new':
                const oneMonthAgo = new Date();
                oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);
                filtered = filtered.filter(community =>
                    new Date(community.created_at) > oneMonthAgo
                );
                break;
        }
    }

    // Sort
    filtered.sort((a, b) => {
        switch (sortBy.value) {
            case 'alphabetical':
                return a.name.localeCompare(b.name);
            case 'popular':
                return (b.stats.totalSpeakers + b.stats.totalEvents) - (a.stats.totalSpeakers + a.stats.totalEvents);
            case 'active':
                return (b.stats.upcomingEvents + b.stats.openCfs) - (a.stats.upcomingEvents + a.stats.openCfs);
            case 'newest':
            default:
                return new Date(b.created_at) - new Date(a.created_at);
        }
    });

    return filtered;
});

function clearFilters() {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedLocation.value = '';
    selectedActivity.value = '';
    sortBy.value = 'newest';
}

function loadMore() {
    loading.value = true;
    // Simulate API call - replace with actual implementation
    setTimeout(() => {
        loading.value = false;
        hasMore.value = false; // Set to false when no more communities to load
    }, 1000);
}

onMounted(() => {
    // Add any initialization logic here
});
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
