import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import Index from '../Index.vue'

// Mock Inertia's modules since they depend on server-side context
vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot /></div>' },
    Link: { template: '<a><slot /></a>', props: ['href'] },
    router: {
        get: vi.fn(),
        delete: vi.fn(),
    },
    usePage: () => ({
        props: {
            auth: { user: { name: 'Test User', role: 'admin' } },
            flash: {},
        },
        url: '/clients',
    }),
}))

// Mock the layout (it relies on Inertia internals)
vi.mock('@/Layouts/AuthenticatedLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}))

describe('Clients/Index', () => {
    const defaultProps = {
        clients: {
            data: [
                {
                    id: 1,
                    name: 'Acme Corp',
                    company: 'Acme',
                    email: 'acme@example.com',
                    phone: '555-1234',
                    projects_count: 3,
                    creator: { name: 'Admin User' },
                },
                {
                    id: 2,
                    name: 'Beta LLC',
                    company: null,
                    email: null,
                    phone: null,
                    projects_count: 0,
                    creator: { name: 'Admin User' },
                },
            ],
            links: [],
            current_page: 1,
            last_page: 1,
            total: 2,
        },
        filters: {
            search: null,
        },
        can: {
            create: true,
        },
    }

    it('renders a table with client rows', () => {
        const wrapper = mount(Index, {
            props: defaultProps,
        })

        expect(wrapper.find('table').exists()).toBe(true)
        expect(wrapper.findAll('tbody tr')).toHaveLength(2)
    })

    it('displays client names', () => {
        const wrapper = mount(Index, {
            props: defaultProps,
        })

        expect(wrapper.text()).toContain('Acme Corp')
        expect(wrapper.text()).toContain('Beta LLC')
    })

    it('shows the search input', () => {
        const wrapper = mount(Index, {
            props: defaultProps,
        })

        expect(wrapper.find('input[type="text"]').exists()).toBe(true)
    })

    it('shows empty state when no clients exist', () => {
        const wrapper = mount(Index, {
            props: {
                ...defaultProps,
                clients: {
                    data: [],
                    links: [],
                    current_page: 1,
                    last_page: 1,
                    total: 0,
                },
            },
        })

        expect(wrapper.text()).toContain('No clients yet')
    })
})