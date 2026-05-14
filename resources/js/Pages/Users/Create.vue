<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
    layout: AuthenticatedLayout,
})

interface Props {
    roles: string[]
}

const props = defineProps<Props>()

const form = useForm({
    name: '',
    email: '',
    role: 'member',
    is_active: true,
})

function submit() {
    form.post('/users')
}
</script>

<template>
    <Head title="New User" />

    <div class="page-header">
        <h1 class="page-title">New User</h1>
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
                <label for="email" class="form-label">Email *</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="form-input"
                    :class="{ 'border-red-500': form.errors.email }"
                />
                <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
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
                    {{ form.processing ? 'Saving...' : 'Create User' }}
                </button>
                <Link href="/users" class="btn-secondary">Cancel</Link>
            </div>
        </form>
    </div>
</template>
