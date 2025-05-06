<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import HelpContent from '@/Components/HelpContent.vue'

const props = defineProps({
    auth: Object,
});

const activeCategory = ref('getting-started');
const activeArticle = ref('welcome-to-synqs');

const searchQuery = ref('');

const categories = ref([
    {
        id: 'getting-started',
        title: 'Getting Started',
        icon: 'rocket',
        articles: [
            {
                id: 'welcome-to-synqs',
                title: 'Welcome to Synqs',
                content: 'Synqs is your all-in-one calendar management platform. With Synqs, you can connect multiple Google Calendars, manage your availability, and allow others to book time with you through a personalized booking page.'
            },
            {
                id: 'connecting-google-account',
                title: 'Connecting Your Google Account',
                content: 'To get started with Synqs, you\'ll need to connect your Google account. Click on "Connect Google Account" button from the dashboard or calendar page. You\'ll be redirected to Google\'s authentication page where you can grant Synqs permission to access your calendars.'
            },
            {
                id: 'managing-calendars',
                title: 'Managing Your Calendars',
                content: 'Once you\'ve connected your Google account, your calendars will automatically appear in Synqs. You can toggle which calendars are visible in your Synqs calendar view. Simply check or uncheck the calendars in the sidebar of the Calendar page.'
            },
            {
                id: 'adding-multiple-accounts',
                title: 'Adding Multiple Google Accounts',
                content: 'Synqs allows you to connect multiple Google accounts to view all your calendars in one place. To add another account, click on "Connect Account" from the dashboard or "Add Account" from the calendar sidebar.'
            }
        ]
    },
    {
        id: 'booking-page',
        title: 'Booking Page',
        icon: 'calendar',
        articles: [
            {
                id: 'setting-up-booking-page',
                title: 'Setting Up Your Booking Page',
                content: 'Your Synqs booking page allows others to schedule time with you based on your availability. To set up your booking page, navigate to "Booking Settings" from the dashboard or main menu.'
            },
            {
                id: 'customizing-availability',
                title: 'Customizing Your Availability',
                content: 'You can customize which days and times you\'re available for meetings. Select your available days, set your available hours, and choose which calendars should be checked for conflicts. Synqs will only show times when you\'re free across all selected calendars.'
            },
            {
                id: 'sharing-booking-link',
                title: 'Sharing Your Booking Link',
                content: 'Once your booking page is set up, you can share your unique booking link with others. They\'ll be able to see your available times and schedule meetings with you. Your booking link can be found at the top of the Booking Settings page.'
            },
            {
                id: 'buffer-times',
                title: 'Setting Buffer Times',
                content: 'Buffer times allow you to add padding before or after meetings. This gives you time to prepare for your next meeting or take a break. You can set buffer times in the Booking Settings page.'
            }
        ]
    },
    {
        id: 'calendar-sync',
        title: 'Calendar Sync',
        icon: 'sync',
        articles: [
            {
                id: 'how-syncing-works',
                title: 'How Calendar Syncing Works',
                content: 'Synqs automatically syncs with your Google calendars. When you connect your Google account, we fetch your calendars and events. Any changes made to your Google calendars will be reflected in Synqs within minutes.'
            },
            {
                id: 'sync-frequency',
                title: 'Sync Frequency and Updates',
                content: 'Synqs syncs your calendars automatically in the background. Your calendar data is refreshed every few minutes to ensure you\'re seeing the most up-to-date information. When viewing the calendar page, data is refreshed automatically.'
            },
            {
                id: 'troubleshooting-sync',
                title: 'Troubleshooting Sync Issues',
                content: 'If you\'re experiencing issues with calendar syncing, try these steps: 1) Refresh the page, 2) Check if your Google account is still connected, 3) Reconnect your Google account if necessary, 4) Contact support if issues persist.'
            }
        ]
    },
    {
        id: 'account-settings',
        title: 'Account Settings',
        icon: 'settings',
        articles: [
            {
                id: 'manage-connected-accounts',
                title: 'Managing Connected Accounts',
                content: 'You can manage your connected Google accounts from the Calendar page. You can add new accounts, disconnect existing ones, or set a primary account for your booking page.'
            },
            {
                id: 'calendar-colors',
                title: 'Customizing Calendar Colors',
                content: 'Each calendar can have its own color to help you visually distinguish between different calendars. You can change a calendar\'s color by clicking on the color picker next to the calendar name in the calendar sidebar.'
            },
            {
                id: 'account-security',
                title: 'Account Security',
                content: 'Synqs uses OAuth to connect to your Google account securely. We never store your Google password. You can revoke Synqs\' access to your Google account at any time through your Google account settings.'
            }
        ]
    }
]);

const currentArticle = computed(() => {
    const category = categories.value.find(cat => cat.id === activeCategory.value);
    if (!category) return null;

    return category.articles.find(article => article.id === activeArticle.value);
});

const allArticles = computed(() => {
    return categories.value.flatMap(category => {
        return category.articles.map(article => ({
            ...article,
            category: category.title
        }));
    });
});

const filteredArticles = computed(() => {
    if (!searchQuery.value.trim()) return [];

    const query = searchQuery.value.toLowerCase();

    return allArticles.value.filter(article =>
        article.title.toLowerCase().includes(query) ||
        article.content.toLowerCase().includes(query)
    );
});

function setActiveArticle(categoryId, articleId) {
    activeCategory.value = categoryId;
    activeArticle.value = articleId;
}
</script>

<template>
    <AppLayout title="Help Center">
        <Head title="Help Center" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Help Center</h1>
                    <Link href="/dashboard" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        BACK TO DASHBOARD
                    </Link>
                </div>

                <HelpContent
                    :categories="categories"
                    :activeCategory="activeCategory"
                    :activeArticle="activeArticle"
                    :currentArticle="currentArticle"
                    :searchQuery="searchQuery"
                    :filteredArticles="filteredArticles"
                    v-model:searchQuery="searchQuery"
                    @setActiveArticle="setActiveArticle"
                />
            </div>
        </div>
    </AppLayout>
</template>
