<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

// Get shared page data (auth user, flash messages) from the HandleInertiaRequests middleware
const page = usePage()

const avatarLoaded = ref(false)
const avatarError = ref(false)

// Sidebar toggle state for mobile
const sidebarOpen = ref(false)

// Flash message visibility
const showFlash = ref(true)

// Navigation items for the sidebar
const navItems = [
    { name: 'Dashboard', href: '/dashboard', icon: 'dashboard' },
    { name: 'Clients', href: '/clients', icon: 'clients' },
    { name: 'Projects', href: '/projects', icon: 'projects' },
    { name: 'Users', href: '/users', icon: 'users', adminOnly: true },
]

// Check if a nav item is the current page
function isActive(href: string): boolean {
    const url = page.url
    if (href === '/dashboard') {
        return url === '/dashboard' || url === '/'
    }
    return url.startsWith(href)
}

// Close sidebar when navigating on mobile
function closeSidebar() {
    sidebarOpen.value = false
}

// Handle logout via POST request
function logout() {
    router.post('/logout')
}

// Reset flash visibility when the page changes
watch(() => page.props.flash, () => {
    showFlash.value = true
}, { deep: true })

// Compute user initials for the avatar fallback
function getInitials(name: string): string {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const dropdownOpen = ref(false)

function handleOutsideClick(e: MouseEvent) {
    const el = document.getElementById('user-dropdown')
    if (el && !el.contains(e.target as Node)) {
        dropdownOpen.value = false
    }
}
onMounted(() => document.addEventListener('mousedown', handleOutsideClick))
onUnmounted(() => document.removeEventListener('mousedown', handleOutsideClick))
</script>

<template>
    <div class="app-layout">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="navbar-left">
                <button class="navbar-hamburger" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle sidebar">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12h18M3 6h18M3 18h18" />
                    </svg>
                </button>
                <Link href="/dashboard" class="navbar-brand">Client Portal</Link>
            </div>

            <div class="navbar-right">
                <div id="user-dropdown" class="relative">
                    <button class="navbar-user" @click="dropdownOpen = !dropdownOpen">
                        <div class="navbar-avatar">
                            <img
                                v-if="(page.props.auth as any)?.user?.avatar_url && !avatarError"
                                :src="(page.props.auth as any).user.avatar_url"
                                :alt="(page.props.auth as any).user.name"
                                :style="avatarLoaded ? '' : 'display:none'"
                                @load="avatarLoaded = true"
                                @error="avatarError = true"
                            />
                            <span v-if="!(page.props.auth as any)?.user?.avatar_url || !avatarLoaded || avatarError">
                                {{ getInitials((page.props.auth as any)?.user?.name || 'U') }}
                            </span>
                        </div>
                        <span class="hidden sm:inline">{{ (page.props.auth as any)?.user?.name }}</span>
                        <svg class="hidden sm:inline ml-1" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </button>

                    <div v-if="dropdownOpen" class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-900">{{ (page.props.auth as any)?.user?.name }}</p>
                            <p class="text-xs text-gray-500">{{ (page.props.auth as any)?.user?.email }}</p>
                        </div>
                        <Link
                            href="/profile"
                            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            @click="dropdownOpen = false"
                        >
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                            Edit Profile
                        </Link>
                        <button
                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-gray-50"
                            @click="logout"
                        >
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 3H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h4M12 12l3-3-3-3M7 9h8" />
                            </svg>
                            Sign out
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Mobile sidebar overlay -->
        <div
            class="sidebar-overlay"
            :class="{ 'is-visible': sidebarOpen }"
            @click="closeSidebar"
        />

        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'is-open': sidebarOpen }">
            <div class="sidebar-section-label">Navigation</div>
            <ul class="sidebar-nav">
                <template v-for="item in navItems" :key="item.href">
                    <li
                        v-if="!item.adminOnly || (page.props.auth as any)?.user?.role === 'admin'"
                        class="sidebar-nav-item"
                    >
                        <Link
                            :href="item.href"
                            class="sidebar-nav-link"
                            :class="{ 'is-active': isActive(item.href) }"
                            @click="closeSidebar"
                        >
                            <!-- Dashboard icon -->
                            <svg v-if="item.icon === 'dashboard'" class="sidebar-nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" />
                            </svg>
                            <!-- Clients icon -->
                            <svg v-else-if="item.icon === 'clients'" class="sidebar-nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <!-- Projects icon -->
                            <svg v-else-if="item.icon === 'projects'" class="sidebar-nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                            <!-- Users icon -->
                            <svg v-else-if="item.icon === 'users'" class="sidebar-nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>{{ item.name }}</span>
                        </Link>
                    </li>
                </template>
            </ul>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            <!-- Flash messages -->
            <div
                v-if="showFlash && (page.props.flash as any)?.success"
                class="flash-message flash-message--success"
            >
                <span>{{ (page.props.flash as any).success }}</span>
                <button class="flash-message-close" @click="showFlash = false">&times;</button>
            </div>
            <div
                v-if="showFlash && (page.props.flash as any)?.error"
                class="flash-message flash-message--error"
            >
                <span>{{ (page.props.flash as any).error }}</span>
                <button class="flash-message-close" @click="showFlash = false">&times;</button>
            </div>

            <slot />
        </main>
    </div>
</template>