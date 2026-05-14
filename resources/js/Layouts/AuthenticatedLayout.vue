<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

// Get shared page data (auth user, flash messages) from the HandleInertiaRequests middleware
const page = usePage()

// Sidebar toggle state for mobile
const sidebarOpen = ref(false)

// Flash message visibility
const showFlash = ref(true)

// Navigation items for the sidebar
const navItems = [
    { name: 'Dashboard', href: '/dashboard', icon: 'dashboard' },
    { name: 'Clients', href: '/clients', icon: 'clients' },
    { name: 'Projects', href: '/projects', icon: 'projects' },
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
                <div class="navbar-user">
                    <div class="navbar-avatar">
                        <img
                            v-if="(page.props.auth as any)?.user?.avatar_url"
                            :src="(page.props.auth as any).user.avatar_url"
                            :alt="(page.props.auth as any).user.name"
                        />
                        <span v-else>{{ getInitials((page.props.auth as any)?.user?.name || 'U') }}</span>
                    </div>
                    <span class="hidden sm:inline">{{ (page.props.auth as any)?.user?.name }}</span>
                </div>
                <button class="navbar-logout" @click="logout" title="Sign out">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 3H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h4M12 12l3-3-3-3M7 9h8" />
                    </svg>
                </button>
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
                <li v-for="item in navItems" :key="item.href" class="sidebar-nav-item">
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
                        <span>{{ item.name }}</span>
                    </Link>
                </li>
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