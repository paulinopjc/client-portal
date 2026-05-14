<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

interface Props {
    user: {
        name: string
        email: string
        avatar_url: string | null
    }
}

const props = defineProps<Props>()

const form = useForm({
    name: props.user.name,
})

function submit() {
    form.put('/profile')
}
</script>

<template>
    <Head title="Edit Profile" />

    <div class="page-header">
        <h1 class="page-title">Edit Profile</h1>
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
                <p class="text-xs text-gray-400 mt-1">Email is linked to your Google account and cannot be changed.</p>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>
            </div>
        </form>
    </div>
</template>
