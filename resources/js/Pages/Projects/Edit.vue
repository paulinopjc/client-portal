<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
    layout: AuthenticatedLayout,
})

interface ClientOption {
    id: number
    name: string
    company: string | null
}

interface Props {
    project: {
        id: number
        name: string
        client_id: number
        description: string | null
        deadline: string | null
    }
    clients: ClientOption[]
}

const props = defineProps<Props>()

const form = useForm({
    name: props.project.name,
    client_id: String(props.project.client_id),
    description: props.project.description || '',
    deadline: props.project.deadline || '',
})

function submit() {
    form.put(`/projects/${props.project.id}`)
}
</script>

<template>
    <Head :title="`Edit ${props.project.name}`" />

    <div class="page-header">
        <h1 class="page-title">Edit Project</h1>
        <Link :href="`/projects/${props.project.id}`" class="btn-secondary">Back to Project</Link>
    </div>

    <div class="max-w-2xl">
        <form @submit.prevent="submit" class="bg-white rounded-lg shadow-sm p-6">
            <div class="form-group">
                <label for="name" class="form-label">Project Name *</label>
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
                <label for="client_id" class="form-label">Client *</label>
                <select
                    id="client_id"
                    v-model="form.client_id"
                    class="form-select"
                    :class="{ 'border-red-500': form.errors.client_id }"
                >
                    <option value="">Select a client</option>
                    <option v-for="client in props.clients" :key="client.id" :value="String(client.id)">
                        {{ client.name }}{{ client.company ? ` (${client.company})` : '' }}
                    </option>
                </select>
                <div v-if="form.errors.client_id" class="form-error">{{ form.errors.client_id }}</div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea
                    id="description"
                    v-model="form.description"
                    class="form-textarea"
                    :class="{ 'border-red-500': form.errors.description }"
                    rows="4"
                />
                <div v-if="form.errors.description" class="form-error">{{ form.errors.description }}</div>
            </div>

            <div class="form-group">
                <label for="deadline" class="form-label">Deadline</label>
                <input
                    id="deadline"
                    v-model="form.deadline"
                    type="date"
                    class="form-input"
                    :class="{ 'border-red-500': form.errors.deadline }"
                />
                <div v-if="form.errors.deadline" class="form-error">{{ form.errors.deadline }}</div>
            </div>

            <div class="flex gap-3">
                <button
                    type="submit"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Saving...' : 'Update Project' }}
                </button>
                <Link :href="`/projects/${props.project.id}`" class="btn-secondary">Cancel</Link>
            </div>
        </form>
    </div>
</template>