<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref, computed } from 'vue'

defineOptions({
    layout: AuthenticatedLayout,
})

interface User {
    id: number
    name: string
    avatar_url: string | null
}

interface Task {
    id: number
    title: string
    description: string | null
    status: string
    sort_order: number
    assignee: User | null
}

interface ActivityItem {
    id: number
    description: string
    metadata: Record<string, any> | null
    created_at: string
    user: {
        name: string
        avatar_url: string | null
    } | null
}

interface Props {
    project: {
        id: number
        name: string
        description: string | null
        status: string
        deadline: string | null
        created_at: string
        client: {
            id: number
            name: string
        }
        creator: {
            name: string
        }
        tasks: Task[]
    }
    activity: ActivityItem[]
    users: User[]
    taskStatuses: string[]
    can: {
        edit: boolean
        delete: boolean
    }
}

const props = defineProps<Props>()

// Status change handler
function changeStatus(newStatus: string) {
    if (confirm(`Change project status to "${newStatus}"?`)) {
        router.patch(`/projects/${props.project.id}/status`, {
            status: newStatus,
        })
    }
}

// Delete handler
function confirmDelete() {
    if (confirm(`Delete "${props.project.name}"? All tasks will be deleted too.`)) {
        router.delete(`/projects/${props.project.id}`)
    }
}

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

// Determine which status transitions are available
const availableStatuses = computed(() => {
    const current = props.project.status
    if (current === 'draft') return ['active']
    if (current === 'active') return ['completed']
    if (current === 'completed') return ['active']
    return []
})

// --- Task board logic (will be expanded in Chapter 10) ---

const newTaskTitle = ref('')

// Group tasks by status for the kanban columns
const todoTasks = computed(() =>
    props.project.tasks.filter(t => t.status === 'todo').sort((a, b) => a.sort_order - b.sort_order)
)
const inProgressTasks = computed(() =>
    props.project.tasks.filter(t => t.status === 'in_progress').sort((a, b) => a.sort_order - b.sort_order)
)
const doneTasks = computed(() =>
    props.project.tasks.filter(t => t.status === 'done').sort((a, b) => a.sort_order - b.sort_order)
)

// Add a new task to the Todo column
function addTask() {
    if (!newTaskTitle.value.trim()) return
    router.post(`/projects/${props.project.id}/tasks`, {
        title: newTaskTitle.value.trim(),
        status: 'todo',
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newTaskTitle.value = ''
        },
    })
}

// Move a task to a different status column
function moveTask(taskId: number, newStatus: string) {
    router.patch(`/tasks/${taskId}/status`, {
        status: newStatus,
    }, {
        preserveScroll: true,
    })
}

// Delete a task
function deleteTask(taskId: number) {
    if (confirm('Delete this task?')) {
        router.delete(`/tasks/${taskId}`, {
            preserveScroll: true,
        })
    }
}

// Drag and drop state
const draggedTaskId = ref<number | null>(null)
const dragOverColumn = ref<string | null>(null)

function onDragStart(taskId: number) {
    draggedTaskId.value = taskId
}

function onDragOver(event: DragEvent, status: string) {
    event.preventDefault()
    dragOverColumn.value = status
}

function onDragLeave() {
    dragOverColumn.value = null
}

function onDrop(newStatus: string) {
    if (draggedTaskId.value !== null) {
        moveTask(draggedTaskId.value, newStatus)
    }
    draggedTaskId.value = null
    dragOverColumn.value = null
}

function onDragEnd() {
    draggedTaskId.value = null
    dragOverColumn.value = null
}

// Get initials from a name string
function getInitials(name: string): string {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

// Editing task inline
const editingTaskId = ref<number | null>(null)
const editForm = useForm({
    title: '',
    description: '',
    assigned_to: '' as string | number,
})

function startEditing(task: Task) {
    editingTaskId.value = task.id
    editForm.title = task.title
    editForm.description = task.description || ''
    editForm.assigned_to = task.assignee ? String(task.assignee.id) : ''
}

function cancelEditing() {
    editingTaskId.value = null
}

function saveTask(taskId: number) {
    editForm.put(`/tasks/${taskId}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingTaskId.value = null
        },
    })
}
</script>

<template>
    <Head :title="props.project.name" />

    <div class="page-header">
        <div>
            <h1 class="page-title">{{ props.project.name }}</h1>
            <p class="text-gray-500 text-sm mt-1">
                <Link :href="`/clients/${props.project.client.id}`" class="text-blue-600 hover:underline">
                    {{ props.project.client.name }}
                </Link>
            </p>
        </div>
        <div class="flex gap-2 items-center">
            <span :class="`badge badge--${props.project.status}`">
                {{ formatStatus(props.project.status) }}
            </span>
            <button
                v-for="status in availableStatuses"
                :key="status"
                class="btn-secondary text-xs"
                @click="changeStatus(status)"
            >
                Move to {{ formatStatus(status) }}
            </button>
            <Link v-if="props.can.edit" :href="`/projects/${props.project.id}/edit`" class="btn-secondary">Edit</Link>
            <button v-if="props.can.delete" class="btn-danger" @click="confirmDelete">Delete</button>
        </div>
    </div>

    <!-- Project details -->
    <div v-if="props.project.description || props.project.deadline" class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-if="props.project.description">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Description</h3>
                <p class="text-gray-800 whitespace-pre-line">{{ props.project.description }}</p>
            </div>
            <div v-if="props.project.deadline">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Deadline</h3>
                <p class="text-gray-800">{{ formatDate(props.project.deadline) }}</p>
            </div>
        </div>
    </div>

    <!-- Task Board (Kanban) -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Task Board</h2>

        <div class="kanban-board">
            <!-- Todo Column -->
            <div
                class="kanban-column"
                :class="{ 'is-drag-over': dragOverColumn === 'todo' }"
                @dragover="onDragOver($event, 'todo')"
                @dragleave="onDragLeave"
                @drop="onDrop('todo')"
            >
                <div class="kanban-column-header kanban-column-header--todo">
                    <span class="kanban-column-title">To Do</span>
                    <span class="kanban-column-count">{{ todoTasks.length }}</span>
                </div>
                <div class="kanban-card-list">
                    <div
                        v-for="task in todoTasks"
                        :key="task.id"
                        class="kanban-card"
                        :class="{ 'is-dragging': draggedTaskId === task.id }"
                        draggable="true"
                        @dragstart="onDragStart(task.id)"
                        @dragend="onDragEnd"
                    >
                        <!-- Edit mode -->
                        <div v-if="editingTaskId === task.id">
                            <input v-model="editForm.title" class="form-input mb-2" placeholder="Task title" />
                            <textarea v-model="editForm.description" class="form-textarea mb-2" rows="2" placeholder="Description" />
                            <select v-model="editForm.assigned_to" class="form-select mb-2">
                                <option value="">Unassigned</option>
                                <option v-for="user in props.users" :key="user.id" :value="String(user.id)">{{ user.name }}</option>
                            </select>
                            <div class="flex gap-2">
                                <button class="btn-primary text-xs" @click="saveTask(task.id)">Save</button>
                                <button class="btn-secondary text-xs" @click="cancelEditing">Cancel</button>
                            </div>
                        </div>
                        <!-- Display mode -->
                        <div v-else>
                            <div class="kanban-card-title">{{ task.title }}</div>
                            <div v-if="task.description" class="kanban-card-description">{{ task.description }}</div>
                            <div class="kanban-card-footer">
                                <div class="kanban-card-assignee">
                                    <template v-if="task.assignee">
                                        <div class="kanban-card-assignee-avatar">
                                            <img v-if="task.assignee.avatar_url" :src="task.assignee.avatar_url" :alt="task.assignee.name" />
                                            <span v-else>{{ getInitials(task.assignee.name) }}</span>
                                        </div>
                                        {{ task.assignee.name }}
                                    </template>
                                </div>
                                <div class="flex gap-1">
                                    <button class="text-xs text-blue-600 hover:underline" @click="startEditing(task)">Edit</button>
                                    <button class="text-xs text-gray-400 hover:text-gray-600" @click="moveTask(task.id, 'in_progress')">&#9654;</button>
                                    <button class="text-xs text-red-400 hover:text-red-600" @click="deleteTask(task.id)">&#10005;</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add task form -->
                <form @submit.prevent="addTask" class="mt-2">
                    <div class="flex gap-2">
                        <input
                            v-model="newTaskTitle"
                            type="text"
                            placeholder="Add a task..."
                            class="form-input text-sm"
                        />
                        <button type="submit" class="btn-primary text-xs whitespace-nowrap">Add</button>
                    </div>
                </form>
            </div>

            <!-- In Progress Column -->
            <div
                class="kanban-column"
                :class="{ 'is-drag-over': dragOverColumn === 'in_progress' }"
                @dragover="onDragOver($event, 'in_progress')"
                @dragleave="onDragLeave"
                @drop="onDrop('in_progress')"
            >
                <div class="kanban-column-header kanban-column-header--in_progress">
                    <span class="kanban-column-title">In Progress</span>
                    <span class="kanban-column-count">{{ inProgressTasks.length }}</span>
                </div>
                <div class="kanban-card-list">
                    <div
                        v-for="task in inProgressTasks"
                        :key="task.id"
                        class="kanban-card"
                        :class="{ 'is-dragging': draggedTaskId === task.id }"
                        draggable="true"
                        @dragstart="onDragStart(task.id)"
                        @dragend="onDragEnd"
                    >
                        <div v-if="editingTaskId === task.id">
                            <input v-model="editForm.title" class="form-input mb-2" placeholder="Task title" />
                            <textarea v-model="editForm.description" class="form-textarea mb-2" rows="2" placeholder="Description" />
                            <select v-model="editForm.assigned_to" class="form-select mb-2">
                                <option value="">Unassigned</option>
                                <option v-for="user in props.users" :key="user.id" :value="String(user.id)">{{ user.name }}</option>
                            </select>
                            <div class="flex gap-2">
                                <button class="btn-primary text-xs" @click="saveTask(task.id)">Save</button>
                                <button class="btn-secondary text-xs" @click="cancelEditing">Cancel</button>
                            </div>
                        </div>
                        <div v-else>
                            <div class="kanban-card-title">{{ task.title }}</div>
                            <div v-if="task.description" class="kanban-card-description">{{ task.description }}</div>
                            <div class="kanban-card-footer">
                                <div class="kanban-card-assignee">
                                    <template v-if="task.assignee">
                                        <div class="kanban-card-assignee-avatar">
                                            <img v-if="task.assignee.avatar_url" :src="task.assignee.avatar_url" :alt="task.assignee.name" />
                                            <span v-else>{{ getInitials(task.assignee.name) }}</span>
                                        </div>
                                        {{ task.assignee.name }}
                                    </template>
                                </div>
                                <div class="flex gap-1">
                                    <button class="text-xs text-blue-600 hover:underline" @click="startEditing(task)">Edit</button>
                                    <button class="text-xs text-gray-400 hover:text-gray-600" @click="moveTask(task.id, 'todo')">&#9664;</button>
                                    <button class="text-xs text-gray-400 hover:text-gray-600" @click="moveTask(task.id, 'done')">&#9654;</button>
                                    <button class="text-xs text-red-400 hover:text-red-600" @click="deleteTask(task.id)">&#10005;</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Done Column -->
            <div
                class="kanban-column"
                :class="{ 'is-drag-over': dragOverColumn === 'done' }"
                @dragover="onDragOver($event, 'done')"
                @dragleave="onDragLeave"
                @drop="onDrop('done')"
            >
                <div class="kanban-column-header kanban-column-header--done">
                    <span class="kanban-column-title">Done</span>
                    <span class="kanban-column-count">{{ doneTasks.length }}</span>
                </div>
                <div class="kanban-card-list">
                    <div
                        v-for="task in doneTasks"
                        :key="task.id"
                        class="kanban-card"
                        :class="{ 'is-dragging': draggedTaskId === task.id }"
                        draggable="true"
                        @dragstart="onDragStart(task.id)"
                        @dragend="onDragEnd"
                    >
                        <div v-if="editingTaskId === task.id">
                            <input v-model="editForm.title" class="form-input mb-2" placeholder="Task title" />
                            <textarea v-model="editForm.description" class="form-textarea mb-2" rows="2" placeholder="Description" />
                            <select v-model="editForm.assigned_to" class="form-select mb-2">
                                <option value="">Unassigned</option>
                                <option v-for="user in props.users" :key="user.id" :value="String(user.id)">{{ user.name }}</option>
                            </select>
                            <div class="flex gap-2">
                                <button class="btn-primary text-xs" @click="saveTask(task.id)">Save</button>
                                <button class="btn-secondary text-xs" @click="cancelEditing">Cancel</button>
                            </div>
                        </div>
                        <div v-else>
                            <div class="kanban-card-title">{{ task.title }}</div>
                            <div v-if="task.description" class="kanban-card-description">{{ task.description }}</div>
                            <div class="kanban-card-footer">
                                <div class="kanban-card-assignee">
                                    <template v-if="task.assignee">
                                        <div class="kanban-card-assignee-avatar">
                                            <img v-if="task.assignee.avatar_url" :src="task.assignee.avatar_url" :alt="task.assignee.name" />
                                            <span v-else>{{ getInitials(task.assignee.name) }}</span>
                                        </div>
                                        {{ task.assignee.name }}
                                    </template>
                                </div>
                                <div class="flex gap-1">
                                    <button class="text-xs text-blue-600 hover:underline" @click="startEditing(task)">Edit</button>
                                    <button class="text-xs text-gray-400 hover:text-gray-600" @click="moveTask(task.id, 'in_progress')">&#9664;</button>
                                    <button class="text-xs text-red-400 hover:text-red-600" @click="deleteTask(task.id)">&#10005;</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Activity -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Activity</h2>

        <ul v-if="props.activity.length > 0" class="activity-list">
            <li v-for="item in props.activity" :key="item.id" class="activity-item">
                <div class="activity-item-content">
                    <div class="activity-item-avatar">
                        <img
                            v-if="item.user?.avatar_url"
                            :src="item.user.avatar_url"
                            :alt="item.user.name"
                        />
                        <span v-else>{{ item.user ? getInitials(item.user.name) : '?' }}</span>
                    </div>
                    <div class="activity-item-text">
                        {{ item.description }}
                        <template v-if="item.metadata?.old_status">
                            <span :class="`badge badge--${item.metadata.old_status}`">{{ formatStatus(item.metadata.old_status) }}</span>
                            &#8594;
                            <span :class="`badge badge--${item.metadata.new_status}`">{{ formatStatus(item.metadata.new_status) }}</span>
                        </template>
                    </div>
                </div>
                <span class="activity-item-time">{{ item.created_at }}</span>
            </li>
        </ul>

        <p v-else class="text-gray-400 text-center py-4">No activity yet.</p>
    </div>
</template>