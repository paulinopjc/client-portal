<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'

defineOptions({
    layout: AuthenticatedLayout,
})

interface Client {
    id: number
    name: string
    company: string | null
    email: string | null
    phone: string | null
    projects_count: number
    creator: {
        name: string
    }
}

interface PaginatedClients {
    data: Client[]
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
    clients: PaginatedClients
    filters: {
        search: string | null
    }
}

const props = defineProps<Props>()

// Local search state, initialized from the server-provided filter value
const search = ref(props.filters.search || '')

// Debounce timer reference
let searchTimeout: ReturnType<typeof setTimeout>

// Watch the search input and send a request after a short delay.
// This prevents sending a request on every keystroke.
watch(search, (value) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get('/clients', { search: value || undefined }, {
            preserveState: true,
            replace: true,
        })
    }, 300)
})

function confirmDelete(client: Client) {
    if (confirm(`Are you sure you want to delete "${client.name}"? This will also delete all their projects and tasks.`)) {
        router.delete(`/clients/${client.id}`)
    }
}
</script>

<template>
    <Head title="Clients" />

    <div class="page-header">
        <h1 class="page-title">Clients</h1>
        <Link href="/clients/create" class="btn-primary">
            + New Client
        </Link>
    </div>

    <!-- Search bar -->
    <div class="mb-6">
        <input
            v-model="search"
            type="text"
            placeholder="Search by name or company..."
            class="form-input"
            style="max-width: 400px;"
        />
    </div>

    <!-- Clients table -->
    <table v-if="props.clients.data.length > 0" class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Company</th>
                <th>Email</th>
                <th>Projects</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="client in props.clients.data" :key="client.id">
                <td>
                    <Link :href="`/clients/${client.id}`" class="text-blue-600 hover:underline font-medium">
                        {{ client.name }}
                    </Link>
                </td>
                <td>{{ client.company || '-' }}</td>
                <td>{{ client.email || '-' }}</td>
                <td>{{ client.projects_count }}</td>
                <td>
                    <div class="flex gap-2">
                        <Link :href="`/clients/${client.id}/edit`" class="btn-secondary text-xs">
                            Edit
                        </Link>
                        <button
                            class="btn-danger text-xs"
                            @click="confirmDelete(client)"
                        >
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Empty state -->
    <div v-else class="text-center py-12 text-gray-400">
        <p v-if="search">No clients match "{{ search }}".</p>
        <p v-else>No clients yet. Click "New Client" to add one.</p>
    </div>

    <!-- Pagination -->
    <div v-if="props.clients.last_page > 1" class="flex gap-2 mt-6 justify-center">
        <template v-for="link in props.clients.links" :key="link.label">
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