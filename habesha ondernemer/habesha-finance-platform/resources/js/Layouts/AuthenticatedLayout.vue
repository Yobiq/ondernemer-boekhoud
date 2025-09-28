<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);

const navigationSections = [
    {
        title: 'MAIN',
        items: [
            {
                name: 'Dashboard',
                href: 'dashboard',
                icon: 'M3 4a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 14.846 4.632 16 6.414 16H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 6H6.28l-.31-1.243A1 1 0 005 4H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z',
                current: () => route().current('dashboard')
            },
            {
                name: 'Clients',
                href: 'clients.index',
                icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
                current: () => route().current('clients.*')
            },
            {
                name: 'Projects',
                href: 'projects.index',
                icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                current: () => route().current('projects.*')
            }
        ]
    },
    {
        title: 'FINANCE',
        items: [
            {
                name: 'Invoices',
                href: 'invoices.index',
                icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                current: () => route().current('invoices.*')
            },
            {
                name: 'Expenses',
                href: 'expenses.index',
                icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                current: () => route().current('expenses.*')
            },
            {
                name: 'Payments',
                href: 'payments.index',
                icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                current: () => route().current('payments.*')
            }
        ]
    }
];
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile sidebar -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-50 lg:hidden">
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="sidebarOpen = false"></div>
            <div class="relative flex w-64 flex-col bg-white shadow-xl">
                <div class="flex h-16 items-center justify-between px-4">
                    <Link :href="route('dashboard')" @click="sidebarOpen = false">
                        <ApplicationLogo class="h-8 w-auto" />
                    </Link>
                    <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <nav class="flex-1 px-2 py-4 space-y-6">
                    <div v-for="section in navigationSections" :key="section.title" class="space-y-1">
                        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ section.title }}
                        </h3>
                        <div class="space-y-1">
                            <Link
                                v-for="item in section.items"
                                :key="item.name"
                                :href="route(item.href)"
                                @click="sidebarOpen = false"
                                :class="[
                                    item.current() 
                                        ? 'bg-blue-50 border-blue-500 text-blue-700' 
                                        : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                                    'group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200'
                                ]"
                            >
                                <svg class="mr-3 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                {{ item.name }}
                            </Link>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Desktop sidebar -->
        <div :class="[
            'hidden lg:fixed lg:inset-y-0 lg:flex lg:flex-col transition-all duration-300 ease-in-out',
            sidebarCollapsed ? 'lg:w-16' : 'lg:w-64'
        ]">
            <div class="flex flex-grow flex-col overflow-y-auto bg-white border-r border-gray-200">
                <!-- Logo -->
                <div class="flex h-16 shrink-0 items-center px-4">
                    <Link :href="route('dashboard')" class="flex items-center">
                        <ApplicationLogo :class="sidebarCollapsed ? 'h-6 w-6' : 'h-8 w-auto'" />
                        <span v-if="!sidebarCollapsed" class="ml-2 text-lg font-semibold text-gray-900">Habesha Finance</span>
                    </Link>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-2 py-4 space-y-6">
                    <div v-for="section in navigationSections" :key="section.title" class="space-y-1">
                        <h3 v-if="!sidebarCollapsed" class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ section.title }}
                        </h3>
                        <div class="space-y-1">
                            <Link
                                v-for="item in section.items"
                                :key="item.name"
                                :href="route(item.href)"
                                :class="[
                                    item.current() 
                                        ? 'bg-blue-50 border-blue-500 text-blue-700' 
                                        : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                                    'group flex items-center px-3 py-2 text-sm font-medium rounded-md border-l-4 transition-colors duration-200',
                                    sidebarCollapsed ? 'justify-center' : ''
                                ]"
                                :title="sidebarCollapsed ? item.name : ''"
                            >
                                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span v-if="!sidebarCollapsed" class="ml-3">{{ item.name }}</span>
                            </Link>
                        </div>
                    </div>
                </nav>

                <!-- User menu -->
                <div class="flex shrink-0 border-t border-gray-200 p-4">
                    <div v-if="!sidebarCollapsed" class="group block w-full flex-shrink-0">
                        <div class="flex items-center">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                                    {{ $page.props.auth.user.name }}
                                </p>
                                <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700">
                                    {{ $page.props.auth.user.email }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-2 flex space-x-2">
                            <Link
                                :href="route('profile.edit')"
                                class="text-xs text-gray-500 hover:text-gray-700"
                            >
                                Profiel
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="text-xs text-gray-500 hover:text-gray-700"
                            >
                                Uitloggen
                            </Link>
                        </div>
                    </div>
                    <div v-else class="flex justify-center w-full">
                        <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                            <span class="text-sm font-medium text-white">
                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div :class="[
            'transition-all duration-300 ease-in-out',
            sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64'
        ]">
            <!-- Top bar -->
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <!-- Mobile menu button -->
                <button
                    type="button"
                    class="-m-2.5 p-2.5 text-gray-700 lg:hidden"
                    @click="sidebarOpen = true"
                >
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Desktop sidebar toggle -->
                <button
                    type="button"
                    class="hidden lg:block -m-2.5 p-2.5 text-gray-700 hover:text-gray-900"
                    @click="sidebarCollapsed = !sidebarCollapsed"
                >
                    <span class="sr-only">Toggle sidebar</span>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="!sidebarCollapsed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1"></div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- Language Switcher -->
                        <LanguageSwitcher :current-language="$page.props.locale" />
                        
                        <!-- User dropdown -->
                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="-m-1.5 flex items-center p-1.5"
                                    >
                                        <span class="sr-only">Open user menu</span>
                                        <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">
                                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                            </span>
                                        </div>
                                        <span class="hidden lg:flex lg:items-center">
                                            <span class="ml-4 text-sm font-semibold leading-6 text-gray-900" aria-hidden="true">
                                                {{ $page.props.auth.user.name }}
                                            </span>
                                            <svg class="ml-2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">
                                        Profiel
                                    </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">
                                        Uitloggen
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="py-6">
                <div class="px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>