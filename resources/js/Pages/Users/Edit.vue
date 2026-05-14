<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
    layout: AuthenticatedLayout,
})

interface Props {
    user: {
        id: number
        name: string
        email: string
        role: string
        is_active: boolean
    }
    roles: string[]
}

const props = defineProps<Props>()

const form = useForm({
    name: props.user.name,
    role: props.user.role,
    is_active: props.user.is_active,
})

function submit() {
    form.put(`/users/${props.user.id}`)
}
</script>

<template>
    <Head :title="`Edit ${props.user.name}`" />

    <div class="page-header">
        <h1 class="page-title">Edit User</h1>
        <Link href="/users" class="btn-secondary">Back to Users</Link>
    </div>

    <div class="max-w-2xl">
        <form @submit.prevent="submit" class="bg-white rounded-lg shadow-sm p-6">
            <div class="form-group">
                <label for="name" class="form-label">Name *</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="form-input"
                    :class="{ 'border-red-500': form.errors.name }"
                />
                <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input
                    type="email"
                    :value="props.user.email"
                    class="form-input"
                    style="background: #f9fafb; color: #6b7280;"
                    disabled
                />
                <p class="text-xs text-gray-400 mt-1">Email is linked to the user's Google account and cannot be changed.</p>
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Role *</label>
                <select
                    id="role"
                    v-model="form.role"
                    class="form-input"
                    :class="{ 'border-red-500': form.errors.role }"
                >
                    <option v-for="role in props.roles" :key="role" :value="role">
                        {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                    </option>
                </select>
                <div v-if="form.errors.role" class="form-error">{{ form.errors.role }}</div>
            </div>

            <div class="form-group">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="w-4 h-4"
                    />
                    <span class="form-label" style="margin-bottom: 0;">Active</span>
                </label>
                <div v-if="form.errors.is_active" class="form-error">{{ form.errors.is_active }}</div>
            </div>

            <div class="flex gap-3">
                <button
                    type="submit"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Saving...' : 'Update User' }}
                </button>
                <Link href="/users" class="btn-secondary">Cancel</Link>
            </div>
        </form>
    </div>
</template>
