<template>
    <div class="task-overview">
        <div class="filter-tabs">
            <button
                v-for="filter in filters"
                :key="filter.value"
                @click="handleFilterChange(filter.value)"
                :class="['filter-tab', { active: currentFilter === filter.value }]"
            >
                {{ filter.label }}
                <span v-if="filter.count !== undefined" class="filter-count">({{ filter.count }})</span>
            </button>
        </div>

        <div v-if="loading" class="loading-state">
            <p>Loading tasks...</p>
        </div>
        <div v-else-if="tasks.length === 0" class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p>No tasks found</p>
        </div>
        <div v-else class="table-container">
            <table class="tasks-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Status</th>
                        <th>Board</th>
                        <th>Assignee</th>
                        <th>Hours</th>
                        <th>Points</th>
                        <th>Blocker</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="task in filteredTasks"
                        :key="task.id"
                        :class="['task-row', `task-${task.status}`]"
                    >
                        <td class="task-title-cell">
                            <span class="task-title">{{ task.title }}</span>
                        </td>
                        <td>
                            <div class="task-status-badge" :class="`badge-${task.status}`">
                                {{ formatStatus(task.status) }}
                            </div>
                        </td>
                        <td>
                            <div v-if="task.board" class="board-badge">
                                {{ task.board.title }}
                            </div>
                            <span v-else class="text-muted">—</span>
                        </td>
                        <td>
                            <div class="task-assignee">
                                <div class="assignee-avatar">{{ task.assignee.initials }}</div>
                                <span>{{ task.assignee.name }}</span>
                            </div>
                        </td>
                        <td>
                            <div v-if="task.hours" class="task-hours">
                                <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ task.hours }}h
                            </div>
                            <span v-else class="text-muted">—</span>
                        </td>
                        <td>
                            <span v-if="task.story_points" class="task-points">{{ task.story_points }} pts</span>
                            <span v-else class="text-muted">—</span>
                        </td>
                        <td>
                            <div v-if="task.blocker_reason" class="blocker-info">
                                <svg class="blocker-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>{{ task.blocker_reason }}</span>
                            </div>
                            <span v-else class="text-muted">—</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TaskOverview',
    props: {
        tasks: {
            type: Array,
            default: () => []
        },
        loading: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            currentFilter: 'all'
        };
    },
    computed: {
        filters() {
            const allCount = this.tasks.length;
            const inProgressCount = this.tasks.filter(t => t.status === 'in-progress').length;
            const completedCount = this.tasks.filter(t => t.status === 'completed').length;
            const blockedCount = this.tasks.filter(t => t.status === 'blocked').length;

            return [
                { label: 'All', value: 'all', count: allCount },
                { label: 'In Progress', value: 'in-progress', count: inProgressCount },
                { label: 'Completed', value: 'completed', count: completedCount },
                { label: 'Blocked', value: 'blocked', count: blockedCount }
            ];
        },
        filteredTasks() {
            if (this.currentFilter === 'all') {
                return this.tasks;
            }
            return this.tasks.filter(t => t.status === this.currentFilter);
        }
    },
    methods: {
        handleFilterChange(filter) {
            this.currentFilter = filter;
            this.$emit('filter-change', { status: filter });
        },
        formatStatus(status) {
            const statusMap = {
                'in-progress': 'In Progress',
                'completed': 'Completed',
                'blocked': 'Blocked'
            };
            return statusMap[status] || status;
        }
    }
};
</script>

<style lang="scss" scoped>
.task-overview {
    width: 100%;
}

.filter-tabs {
    display: flex;
    gap: 0.375rem;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 0.375rem 0.75rem;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 0.375rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;

    &:hover {
        border-color: #4f46e5;
        color: #4f46e5;
    }

    &.active {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
    }

    .filter-count {
        font-size: 0.625rem;
        opacity: 0.8;
    }
}

.loading-state,
.empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 0.75rem;
        opacity: 0.4;
        color: #9ca3af;
    }

    p {
        margin: 0;
        font-size: 0.8125rem;
    }
}

.table-container {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
}

.tasks-table {
    width: 100%;
    border-collapse: collapse;

    thead {
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;

        th {
            padding: 0.5rem 0.75rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.75rem;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    }

    tbody {
        tr {
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.2s;

            &:last-child {
                border-bottom: none;
            }

            &:hover {
                background-color: #f9fafb;
            }

            &.task-blocked {
                background: #fef2f2;

                &:hover {
                    background: #fee2e2;
                }
            }

            &.task-completed {
                background: #f0fdf4;

                &:hover {
                    background: #dcfce7;
                }
            }

            &.task-in-progress {
                background: #eff6ff;

                &:hover {
                    background: #dbeafe;
                }
            }
        }

        td {
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
            color: #374151;
            vertical-align: middle;
        }
    }
}

.task-title-cell {
    max-width: 300px;

    .task-title {
        font-weight: 600;
        color: #1f2937;
        line-height: 1.3;
        font-size: 0.8125rem;
        display: block;
    }
}

.task-status-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;

    &.badge-completed {
        background: #d1fae5;
        color: #065f46;
    }

    &.badge-in-progress {
        background: #dbeafe;
        color: #1e40af;
    }

    &.badge-blocked {
        background: #fee2e2;
        color: #991b1b;
    }
}

.board-badge {
    display: inline-block;
    background: #e0e7ff;
    color: #4338ca;
    padding: 0.125rem 0.375rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 500;
    white-space: nowrap;
}

.task-assignee {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    color: #6b7280;

    .assignee-avatar {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.6875rem;
        flex-shrink: 0;
    }
}

.task-hours {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;

    .icon-small {
        width: 0.875rem;
        height: 0.875rem;
    }
}

.task-points {
    font-weight: 500;
    color: #6b7280;
    font-size: 0.75rem;
}

.blocker-info {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    color: #991b1b;
    font-size: 0.75rem;

    .blocker-icon {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
    }
}

.text-muted {
    color: #9ca3af;
}

@media (max-width: 1024px) {
    .table-container {
        overflow-x: auto;
    }

    .tasks-table {
        min-width: 900px;
    }
}
</style>