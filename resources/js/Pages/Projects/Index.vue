<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'

defineOptions({
    layout: AuthenticatedLayout,
})

interface Project {
    id: number
    name: string
    status: string
    deadline: string | null
    tasks_count: number
    client: {
        id: number
        name: string
    }
    creator: {
        name: string
    }
}

interface PaginatedProjects {
    data: Project[]
    links: Array<{
        url: string | null
        label: string
        active: boolean
    }>
    current_page: number
    last_page: number
    total: number
}

interface Props {
    projects: PaginatedProjects
    filters: {
        status: string | null
        search: string | null
    }
    statuses: string[]
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status || '')

let searchTimeout: ReturnType<typeof setTimeout>

// Apply filters whenever search or status changes
function applyFilters() {
    router.get('/projects', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    })
}

watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(applyFilters, 300)
})

watch(statusFilter, () => {
    applyFilters()
})

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}

function formatStatus(status: string): string {
    return status.replace(/_/g, ' ')
}
</script>

<template>
    <Head title="Projects" />

    <div class="page-header">
        <h1 class="page-title">Projects</h1>
        <Link href="/projects/create" class="btn-primary">
            + New Project
        </Link>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-4 mb-6">
        <input
            v-model="search"
            type="text"
            placeholder="Search projects..."
            class="form-input"
            style="max-width: 300px;"
        />
        <select
            v-model="statusFilter"
            class="form-select"
            style="max-width: 200px;"
        >
            <option value="">All Statuses</option>
            <option v-for="status in props.statuses" :key="status" :value="status">
                {{ formatStatus(status) }}
            </option>
        </select>
    </div>

    <!-- Projects table -->
    <table v-if="props.projects.data.length > 0" class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Client</th>
                <th>Status</th>
                <th>Deadline</th>
                <th>Tasks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="project in props.projects.data" :key="project.id">
                <td>
                    <Link :href="`/projects/${project.id}`" class="text-blue-600 hover:underline font-medium">
                        {{ project.name }}
                    </Link>
                </td>
                <td>
                    <Link :href="`/clients/${project.client.id}`" class="text-gray-600 hover:underline">
                        {{ project.client.name }}
                    </Link>
                </td>
                <td>
                    <span :class="`badge badge--${project.status}`">
                        {{ formatStatus(project.status) }}
                    </span>
                </td>
                <td>{{ project.deadline ? formatDate(project.deadline) : '-' }}</td>
                <td>{{ project.tasks_count }}</td>
                <td>
                    <Link :href="`/projects/${project.id}/edit`" class="btn-secondary text-xs">
                        Edit
                    </Link>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Empty state -->
    <div v-else class="text-center py-12 text-gray-400">
        <p v-if="search || statusFilter">No projects match your filters.</p>
        <p v-else>No projects yet. Click "New Project" to create one.</p>
    </div>

    <!-- Pagination -->
    <div v-if="props.projects.last_page > 1" class="flex gap-2 mt-6 justify-center">
        <template v-for="link in props.projects.links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                class="px-3 py-1 rounded text-sm"
                :class="link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'"
                v-html="link.label"
            />
            <span
                v-else
                class="px-3 py-1 rounded text-sm text-gray-400"
                v-html="link.label"
            />
        </template>
    </div>
</template>