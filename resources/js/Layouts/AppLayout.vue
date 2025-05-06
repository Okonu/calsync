<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const props = defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const urls = {
    dashboard: '/dashboard',
    calendar: '/calendar',
    bookingSettings: '/booking/settings',
    bookingList: '/booking/list',
    googleConnectRedirect: '/connect/google',
    logout: '/logout'
};

const page = usePage();
const user = computed(() => page.props.auth?.user || null);

const isCurrentPath = (path) => {
    return window.location.pathname === path;
};

const logout = () => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = urls.logout;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }

    document.body.appendChild(form);
    form.submit();
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="urls.dashboard">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="urls.dashboard" :active="isCurrentPath(urls.dashboard)">
                                    Dashboard
                                </NavLink>
                                <NavLink :href="urls.calendar" :active="isCurrentPath(urls.calendar)">
                                    Calendar
                                </NavLink>

                                <!-- Booking Dropdown -->
                                <div class="hidden sm:flex sm:items-center">
                                    <div class="relative ml-4">
                                        <Dropdown align="left" width="48">
                                            <template #trigger>
                                                <span class="inline-flex rounded-md">
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                                        :class="[isCurrentPath(urls.bookingSettings) || isCurrentPath(urls.bookingList) ? 'text-gray-900' : 'text-gray-500']"
                                                    >
                                                        Booking
                                                        <svg
                                                            class="ml-2 -mr-0.5 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke-width="1.5"
                                                            stroke="currentColor"
                                                        >
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>

                                            <template #content>
                                                <DropdownLink :href="urls.bookingSettings">
                                                    Booking Settings
                                                </DropdownLink>
                                                <DropdownLink :href="urls.bookingList">
                                                    View Bookings
                                                </DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative" v-if="user">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ user.name }}

                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>

                                        <!-- Connect Another Google Account Option -->
                                        <DropdownLink :href="urls.googleConnectRedirect">
                                            Connect Google Account
                                        </DropdownLink>

                                        <div class="border-t border-gray-200"></div>

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = ! showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="urls.dashboard" :active="isCurrentPath(urls.dashboard)">
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="urls.calendar" :active="isCurrentPath(urls.calendar)">
                            Calendar
                        </ResponsiveNavLink>

                        <!-- Mobile Booking Links -->
                        <ResponsiveNavLink :href="urls.bookingSettings" :active="isCurrentPath(urls.bookingSettings)">
                            Booking Settings
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="urls.bookingList" :active="isCurrentPath(urls.bookingList)">
                            View Bookings
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200" v-if="user">
                        <div class="flex items-center px-4">
                            <div>
                                <div class="font-medium text-base text-gray-800">{{ user.name }}</div>
                                <div class="font-medium text-sm text-gray-500">{{ user.email }}</div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <!-- Connect Google Account -->
                            <ResponsiveNavLink :href="urls.googleConnectRedirect" :active="isCurrentPath(urls.googleConnectRedirect)">
                                Connect Google Account
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header"></slot>
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot></slot>
            </main>
        </div>
    </div>
</template>
