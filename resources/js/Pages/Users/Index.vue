<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'

defineOptions({
    layout: AuthenticatedLayout,
})

interface User {
    id: number
    name: string
    email: string
    role: string
    is_active: boolean
}

interface PaginatedUsers {
    data: User[]
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
    users: PaginatedUsers
    filters: {
        search: string | null
    }
    can: {
        create: boolean
    }
}

const props = defineProps<Props>()
const page = usePage()

const search = ref(props.filters.search || '')

let searchTimeout: ReturnType<typeof setTimeout>

watch(search, (value) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get('/users', { search: value || undefined }, {
            preserveState: true,
            replace: true,
        })
    }, 300)
})

function confirmDelete(user: User) {
    if (confirm(`Are you sure you want to delete "${user.name}"?`)) {
        router.delete(`/users/${user.id}`)
    }
}

function isSelf(user: User): boolean {
    return (page.props.auth as any)?.user?.id === user.id
}
</script>

<template>
    <Head title="Users" />

    <div class="page-header">
        <h1 class="page-title">Users</h1>
        <Link v-if="props.can.create" href="/users/create" class="btn-primary">
            + New User
        </Link>
    </div>

    <!-- Search bar -->
    <div class="mb-6">
        <input
            v-model="search"
            type="text"
            placeholder="Search by name or email..."
            class="form-input"
            style="max-width: 400px;"
        />
    </div>

    <!-- Users table -->
    <table v-if="props.users.data.length > 0" class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="user in props.users.data" :key="user.id">
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>
                    <span
                        class="inline-block px-2 py-0.5 rounded text-xs font-medium"
                        :class="user.role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700'"
                    >
                        {{ user.role }}
                    </span>
                </td>
                <td>
                    <span
                        class="inline-block px-2 py-0.5 rounded text-xs font-medium"
                        :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'"
                    >
                        {{ user.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <div class="flex gap-2">
                        <Link :href="`/users/${user.id}/edit`" class="btn-secondary text-xs">
                            Edit
                        </Link>
                        <button
                            class="btn-danger text-xs"
                            :disabled="isSelf(user)"
                            :title="isSelf(user) ? 'You cannot delete your own account' : undefined"
                            @click="!isSelf(user) && confirmDelete(user)"
                            :style="isSelf(user) ? 'opacity: 0.4; cursor: not-allowed;' : ''"
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
        <p v-if="search">No users match "{{ search }}".</p>
        <p v-else>No users yet. Click "New User" to add one.</p>
    </div>

    <!-- Pagination -->
    <div v-if="props.users.last_page > 1" class="flex gap-2 mt-6 justify-center">
        <template v-for="link in props.users.links" :key="link.label">
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
