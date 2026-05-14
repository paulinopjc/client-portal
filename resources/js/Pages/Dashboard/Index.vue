<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
    layout: AuthenticatedLayout,
})

// Type definitions for the props this page receives from the controller
interface ActivityItem {
    id: number
    action: string
    subject_type: string
    subject_id: number
    description: string
    metadata: Record<string, any> | null
    created_at: string
    user: {
        name: string
        avatar_url: string | null
    } | null
}

interface Props {
    stats: {
        totalClients: number
        activeProjects: number
        pendingTasks: number
    }
    recentActivity: ActivityItem[]
}

const props = defineProps<Props>()

// Get initials from a user's name for the avatar fallback
function getInitials(name: string): string {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

// Format action and subject_type into a readable label
function formatAction(action: string): string {
    return action.replace(/_/g, ' ')
}

// Get a display-friendly label for the subject type
function formatSubjectType(type: string): string {
    return type.charAt(0).toUpperCase() + type.slice(1)
}
</script>

<template>
    <Head title="Dashboard" />

    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
    </div>

    <!-- Stat Cards -->
    <div class="dashboard-stats">
        <div class="stat-card stat-card--clients">
            <div class="stat-card-content">
                <span class="stat-card-label">Total Clients</span>
                <span class="stat-card-value">{{ props.stats.totalClients }}</span>
            </div>
            <div class="stat-card-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>

        <div class="stat-card stat-card--projects">
            <div class="stat-card-content">
                <span class="stat-card-label">Active Projects</span>
                <span class="stat-card-value">{{ props.stats.activeProjects }}</span>
            </div>
            <div class="stat-card-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            </div>
        </div>

        <div class="stat-card stat-card--tasks">
            <div class="stat-card-content">
                <span class="stat-card-label">Pending Tasks</span>
                <span class="stat-card-value">{{ props.stats.pendingTasks }}</span>
            </div>
            <div class="stat-card-icon">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="activity-section">
        <div class="activity-section-header">
            <h2>Recent Activity</h2>
        </div>

        <ul v-if="props.recentActivity.length > 0" class="activity-list">
            <li v-for="item in props.recentActivity" :key="item.id" class="activity-item">
                <div class="activity-item-content">
                    <div class="activity-item-avatar">
                        <img
                            v-if="item.user?.avatar_url"
                            :src="item.user.avatar_url"
                            :alt="item.user.name"
                        />
                        <span v-else>{{ item.user ? getInitials(item.user.name) : '?' }}</span>
                    </div>
                    <div class="activity-item-text">
                        <strong>{{ item.user?.name || 'System' }}</strong>
                        {{ formatAction(item.action) }}
                        <span :class="`badge badge--${item.subject_type}`">
                            {{ formatSubjectType(item.subject_type) }}
                        </span>
                    </div>
                </div>
                <span class="activity-item-time">{{ item.created_at }}</span>
            </li>
        </ul>

        <div v-else class="text-center py-8 text-gray-400">
            <p>No activity yet. Start by adding a client.</p>
        </div>
    </div>
</template>