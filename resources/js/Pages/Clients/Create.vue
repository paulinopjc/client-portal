<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
    layout: AuthenticatedLayout,
})

// useForm creates a reactive form object that tracks field values, dirty state,
// and validation errors. It also provides submit helpers.
const form = useForm({
    name: '',
    company: '',
    email: '',
    phone: '',
    notes: '',
})

function submit() {
    // form.post() sends a POST request with the form data.
    // If the server returns validation errors, they appear in form.errors automatically.
    // If the server redirects (success), Inertia follows the redirect.
    form.post('/clients')
}
</script>

<template>
    <Head title="New Client" />

    <div class="page-header">
        <h1 class="page-title">New Client</h1>
        <Link href="/clients" class="btn-secondary">Back to Clients</Link>
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
                    {{ form.processing ? 'Saving...' : 'Create Client' }}
                </button>
                <Link href="/clients" class="btn-secondary">Cancel</Link>
            </div>
        </form>
    </div>
</template>