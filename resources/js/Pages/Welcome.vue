<!-- resources/js/Pages/Welcome.vue -->
<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const isWideScreen = ref(false);

onMounted(() => {
    checkScreenWidth();

    window.addEventListener('resize', checkScreenWidth);
});

function checkScreenWidth() {
    isWideScreen.value = window.innerWidth >= 768;
}
</script>

<template>
    <Head title="CalSync" />

    <div style="position: relative; min-height: 100vh; background-color: #f3f4f6;">
        <!-- Navigation -->
        <div style="position: fixed; top: 0; right: 0; padding: 1.5rem; text-align: right; z-index: 10;">
            <a
                href="/auth/google"
                style="font-weight: 600; color: #4b5563; text-decoration: none;">
                Sign in with Google
            </a>
        </div>

        <div style="max-width: 80rem; margin-left: auto; margin-right: auto; padding: 0 1rem; padding-top: 3rem; padding-bottom: 3rem;">
            <!-- Logo -->
            <div style="display: flex; justify-content: center;">
                <div style="height: 4rem; width: 4rem; background-color: #4f46e5; border-radius: 0.5rem; padding: 0.5rem; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="height: 2.5rem; width: 2.5rem; color: white;">
                        <path d="M8 2V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 2V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3.5 9.09H20.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>

            <!-- Heading -->
            <div style="text-align: center; margin-top: 1.5rem;">
                <h1 style="font-size: 2.25rem; line-height: 2.5rem; font-weight: 800; color: #111827;">CalSync</h1>
                <p style="margin-top: 1rem; font-size: 1.25rem; line-height: 1.75rem; color: #4b5563;">
                    One place for all your calendars
                </p>
            </div>

            <!-- Feature Cards -->
            <div :style="{
        'margin-top': '4rem',
        'display': 'grid',
        'grid-template-columns': isWideScreen ? 'repeat(2, minmax(0, 1fr))' : 'repeat(1, minmax(0, 1fr))',
        'gap': '1.5rem'
      }">
                <!-- Card 1 -->
                <div style="padding: 1.5rem; background-color: white; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <div style="height: 3rem; width: 3rem; background-color: #eef2ff; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.5rem; width: 1.5rem; color: #4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 style="font-size: 1.25rem; line-height: 1.75rem; font-weight: 600; color: #111827;">Multiple Accounts, One View</h2>
                    <p style="margin-top: 0.5rem; color: #4b5563;">
                        Connect all your Google accounts and see all your calendars in one unified interface.
                    </p>
                </div>

                <!-- Card 2 -->
                <div style="padding: 1.5rem; background-color: white; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <div style="height: 3rem; width: 3rem; background-color: #eef2ff; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.5rem; width: 1.5rem; color: #4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h2 style="font-size: 1.25rem; line-height: 1.75rem; font-weight: 600; color: #111827;">Automatic Synchronization</h2>
                    <p style="margin-top: 0.5rem; color: #4b5563;">
                        Your calendars automatically stay up to date. Changes from Google Calendar are synced regularly.
                    </p>
                </div>

                <!-- Card 3 -->
                <div style="padding: 1.5rem; background-color: white; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <div style="height: 3rem; width: 3rem; background-color: #eef2ff; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.5rem; width: 1.5rem; color: #4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <h2 style="font-size: 1.25rem; line-height: 1.75rem; font-weight: 600; color: #111827;">Customizable Views</h2>
                    <p style="margin-top: 0.5rem; color: #4b5563;">
                        Filter and customize your calendar view. Choose which calendars to display and color-code them.
                    </p>
                </div>

                <!-- Card 4 -->
                <div style="padding: 1.5rem; background-color: white; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    <div style="height: 3rem; width: 3rem; background-color: #eef2ff; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.5rem; width: 1.5rem; color: #4f46e5;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h2 style="font-size: 1.25rem; line-height: 1.75rem; font-weight: 600; color: #111827;">Secure &amp; Private</h2>
                    <p style="margin-top: 0.5rem; color: #4b5563;">
                        Your calendar data remains secure. We use OAuth for authentication and never store your Google password.
                    </p>
                </div>
            </div>

            <!-- Call to Action -->
            <div style="margin-top: 3rem; text-align: center;">
                <a
                    href="/auth/google"
                    style="display: inline-flex; align-items: center; padding: 0.75rem 1.5rem; background-color: #4f46e5; color: white; font-weight: 600; font-size: 1rem; line-height: 1.5rem; border-radius: 0.375rem; text-decoration: none; transition: background-color 0.2s;">
                    <svg viewBox="0 0 24 24" width="24" height="24" style="margin-right: 0.5rem;">
                        <path fill="#ffffff" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#ffffff" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#ffffff" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" />
                        <path fill="#ffffff" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    Sign in with Google
                </a>
            </div>

            <!-- Footer -->
            <div style="margin-top: 4rem; border-top: 1px solid #e5e7eb; padding-top: 2rem; text-align: center; font-size: 0.875rem; color: #6b7280;">
                <p>CalSync &copy; {{ new Date().getFullYear() }}</p>
            </div>
        </div>
    </div>
</template>
