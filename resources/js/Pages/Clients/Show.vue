<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
    layout: AuthenticatedLayout,
})

interface Project {
    id: number
    name: string
    status: string
    deadline: string | null
    tasks_count: number
}

interface Props {
    client: {
        id: number
        name: string
        company: string | null
        email: string | null
        phone: string | null
        notes: string | null
        created_at: string
        creator: {
            name: string
        }
        projects: Project[]
    }
    can: {
        edit: boolean
        delete: boolean
    }
}

const props = defineProps<Props>()

function confirmDelete() {
    if (confirm(`Are you sure you want to delete "${props.client.name}"? This will also delete all their projects and tasks.`)) {
        router.delete(`/clients/${props.client.id}`)
    }
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}
</script>

<template>
    <Head :title="props.client.name" />

    <div class="page-header">
        <h1 class="page-title">{{ props.client.name }}</h1>
        <div class="flex gap-2">
            <Link v-if="props.can.edit" :href="`/clients/${props.client.id}/edit`" class="btn-secondary">Edit</Link>
            <button v-if="props.can.delete" class="btn-danger" @click="confirmDelete">Delete</button>
        </div>
    </div>

    <!-- Client details card -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Company</h3>
                <p class="text-gray-800">{{ props.client.company || 'Not specified' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Email</h3>
                <p class="text-gray-800">
                    <a v-if="props.client.email" :href="`mailto:${props.client.email}`" class="text-blue-600 hover:underline">
                        {{ props.client.email }}
                    </a>
                    <span v-else>Not specified</span>
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Phone</h3>
                <p class="text-gray-800">{{ props.client.phone || 'Not specified' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Added by</h3>
                <p class="text-gray-800">{{ props.client.creator.name }} on {{ formatDate(props.client.created_at) }}</p>
            </div>
        </div>

        <div v-if="props.client.notes" class="mt-6 pt-6 border-t border-gray-100">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Notes</h3>
            <p class="text-gray-800 whitespace-pre-line">{{ props.client.notes }}</p>
        </div>
    </div>

    <!-- Client's projects -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Projects ({{ props.client.projects.length }})</h2>
            <Link :href="`/projects/create?client_id=${props.client.id}`" class="btn-primary text-sm">
                + New Project
            </Link>
        </div>

        <table v-if="props.client.projects.length > 0" class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>Tasks</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="project in props.client.projects" :key="project.id">
                    <td>
                        <Link :href="`/projects/${project.id}`" class="text-blue-600 hover:underline font-medium">
                            {{ project.name }}
                        </Link>
                    </td>
                    <td>
                        <span :class="`badge badge--${project.status}`">
                            {{ project.status }}
                        </span>
                    </td>
                    <td>{{ project.deadline ? formatDate(project.deadline) : 'No deadline' }}</td>
                    <td>{{ project.tasks_count }}</td>
                </tr>
            </tbody>
        </table>

        <p v-else class="text-gray-400 text-center py-8">
            No projects yet. Click "New Project" to create one for this client.
        </p>
    </div>
</template>