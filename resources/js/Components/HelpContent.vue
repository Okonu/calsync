<script setup>
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
    activeCategory: String,
    activeArticle: String,
    currentArticle: Object,
    searchQuery: String,
    filteredArticles: Array
});

const emit = defineEmits(['setActiveArticle', 'update:searchQuery']);

function getIconSvg(iconName) {
    switch (iconName) {
        case 'rocket':
            return `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" />
              </svg>`;
        case 'calendar':
            return `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
              </svg>`;
        case 'sync':
            return `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
              </svg>`;
        case 'settings':
            return `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
              </svg>`;
        default:
            return `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
              </svg>`;
    }
}

function updateSearchQuery(event) {
    emit('update:searchQuery', event.target.value);
}
</script>

<template>
    <div>
        <!-- Search Bar -->
        <div class="max-w-3xl mx-auto mb-8">
            <div class="relative">
                <input
                    :value="searchQuery"
                    @input="updateSearchQuery"
                    type="text"
                    placeholder="Search for help articles..."
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div v-if="searchQuery" class="max-w-3xl mx-auto mb-8">
            <h2 class="text-xl font-semibold mb-4">Search Results</h2>

            <div v-if="filteredArticles.length === 0" class="bg-white rounded-lg shadow-sm p-6 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">No results found</h3>
                <p class="mt-1 text-gray-500">Try different keywords or browse our help categories below.</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow-sm divide-y divide-gray-200">
                <button
                    v-for="article in filteredArticles"
                    :key="article.id"
                    class="block w-full text-left px-6 py-4 hover:bg-gray-50 transition-colors"
                    @click="$emit('setActiveArticle',
            categories.find(cat => cat.title === article.category).id,
            article.id
          )"
                >
                    <h3 class="text-lg font-medium text-gray-900">{{ article.title }}</h3>
                    <p class="text-sm text-gray-500">{{ article.category }}</p>
                </button>
            </div>
        </div>

        <!-- Main Help Content -->
        <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Sidebar -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden sticky top-6">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-medium text-gray-800">Help Categories</h3>
                    </div>

                    <nav class="p-2">
                        <ul class="space-y-1">
                            <li v-for="category in categories" :key="category.id">
                                <button
                                    @click="$emit('setActiveArticle', category.id, category.articles[0].id)"
                                    class="w-full flex items-center px-3 py-2 text-sm rounded-md transition-colors"
                                    :class="activeCategory === category.id ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50'"
                                >
                                    <span v-html="getIconSvg(category.icon)" class="mr-3"></span>
                                    <span>{{ category.title }}</span>
                                </button>
                            </li>
                        </ul>
                    </nav>

                    <div class="bg-indigo-50 p-4 border-t border-indigo-100">
                        <h4 class="text-sm font-medium text-indigo-800 mb-2">Need more help?</h4>
                        <p class="text-xs text-indigo-700 mb-3">
                            Can't find what you're looking for in our help center?
                        </p>
                        <a
                            href="mailto:support@example.com"
                            class="block w-full text-center px-3 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition-colors"
                        >
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="md:col-span-3">
                <!-- Category Articles -->
                <div class="mb-6">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                            <h2 class="text-xl font-semibold text-gray-800">
                                {{ categories.find(cat => cat.id === activeCategory)?.title }}
                            </h2>
                        </div>

                        <div class="divide-y divide-gray-100">
                            <button
                                v-for="article in categories.find(cat => cat.id === activeCategory)?.articles"
                                :key="article.id"
                                @click="$emit('setActiveArticle', activeCategory, article.id)"
                                class="block w-full text-left px-6 py-4 hover:bg-gray-50 transition-colors"
                                :class="activeArticle === article.id ? 'bg-gray-50' : ''"
                            >
                                <h3 class="text-lg font-medium text-gray-900">{{ article.title }}</h3>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Article Content -->
                <div v-if="currentArticle" class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                        <h2 class="text-xl font-semibold text-gray-800">{{ currentArticle.title }}</h2>
                    </div>

                    <div class="p-6">
                        <p class="text-gray-700 whitespace-pre-line">{{ currentArticle.content }}</p>

                        <div class="mt-8 flex justify-between items-center pt-4 border-t border-gray-200">
                            <div class="text-sm text-gray-500">
                                Was this article helpful?
                            </div>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 bg-green-50 text-green-700 rounded-md hover:bg-green-100 text-sm transition-colors">Yes</button>
                                <button class="px-3 py-1 bg-gray-50 text-gray-700 rounded-md hover:bg-gray-100 text-sm transition-colors">No</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
