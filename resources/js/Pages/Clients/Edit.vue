<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
    layout: AuthenticatedLayout,
})

interface Props {
    client: {
        id: number
        name: string
        company: string | null
        email: string | null
        phone: string | null
        notes: string | null
    }
}

const props = defineProps<Props>()

// Pre-populate the form with the existing client data.
// useForm makes a deep copy, so editing the form doesn't mutate the original props.
const form = useForm({
    name: props.client.name,
    company: props.client.company || '',
    email: props.client.email || '',
    phone: props.client.phone || '',
    notes: props.client.notes || '',
})

function submit() {
    // form.put() sends a PUT request to update the existing client
    form.put(`/clients/${props.client.id}`)
}
</script>

<template>
    <Head :title="`Edit ${props.client.name}`" />

    <div class="page-header">
        <h1 class="page-title">Edit Client</h1>
        <Link :href="`/clients/${props.client.id}`" class="btn-secondary">Back to Client</Link>
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
                <label for="company" class="form-label">Company</label>
                <input
                    id="company"
                    v-model="form.company"
                    type="text"
                    class="form-input"
                    :class="{ 'border-red-500': form.errors.company }"
                />
                <div v-if="form.errors.company" class="form-error">{{ form.errors.company }}</div>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
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
                <label for="phone" class="form-label">Phone</label>
                <input
                    id="phone"
                    v-model="form.phone"
                    type="text"
                    class="form-input"
                    :class="{ 'border-red-500': form.errors.phone }"
                />
                <div v-if="form.errors.phone" class="form-error">{{ form.errors.phone }}</div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">Notes</label>
                <textarea
                    id="notes"
                    v-model="form.notes"
                    class="form-textarea"
                    :class="{ 'border-red-500': form.errors.notes }"
                    rows="4"
                />
                <div v-if="form.errors.notes" class="form-error">{{ form.errors.notes }}</div>
            </div>

            <div class="flex gap-3">
                <button
                    type="submit"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Saving...' : 'Update Client' }}
                </button>
                <Link :href="`/clients/${props.client.id}`" class="btn-secondary">Cancel</Link>
            </div>
        </form>
    </div>
</template>