<template>
    <div class="blocked-tasks-list">
        <div v-if="loading" class="loading-state">
            <p>Loading blocked tasks...</p>
        </div>
        <div v-else-if="tasks.length === 0" class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>No blocked tasks! Great work team!</p>
        </div>
        <div v-else class="tasks-list">
            <div
                v-for="task in tasks"
                :key="task.id"
                class="blocked-task-card"
            >
                <div class="task-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="task-content">
                    <div class="task-header">
                        <h3 class="task-title">{{ task.title }}</h3>
                        <div v-if="task.board" class="board-badge">
                            {{ task.board.title }}
                        </div>
                    </div>
                    <div class="task-meta">
                        <div class="task-assignee">
                            <div class="assignee-avatar">{{ task.assignee.initials }}</div>
                            <span>{{ task.assignee.name }}</span>
                        </div>
                    </div>
                    <div class="blocker-reason">
                        <strong>Blocker:</strong> {{ task.blocker_reason }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'BlockedTasksList',
    props: {
        tasks: {
            type: Array,
            default: () => []
        },
        loading: {
            type: Boolean,
            default: false
        }
    }
};
</script>

<style lang="scss" scoped>
.blocked-tasks-list {
    width: 100%;
}

.loading-state,
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        opacity: 0.4;
        color: #9ca3af;
    }

    p {
        margin: 0;
        font-size: 0.9375rem;
    }
}

.tasks-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.blocked-task-card {
    display: flex;
    gap: 1rem;
    padding: 1.25rem;
    background: #fef2f2;
    border: 2px solid #fee2e2;
    border-left: 4px solid #ef4444;
    border-radius: 0.75rem;
    transition: all 0.2s;

    &:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transform: translateX(4px);
    }
}

.task-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.5rem;
    background: #fee2e2;
    color: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;

    svg {
        width: 1.5rem;
        height: 1.5rem;
    }
}

.task-content {
    flex: 1;
    min-width: 0;
}

.task-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.task-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    flex: 1;
}

.board-badge {
    background: #fee2e2;
    color: #991b1b;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.task-meta {
    margin-bottom: 0.75rem;
}

.task-assignee {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;

    .assignee-avatar {
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.75rem;
    }
}

.blocker-reason {
    padding: 0.75rem;
    background: white;
    border-radius: 0.5rem;
    color: #991b1b;
    font-size: 0.875rem;
    line-height: 1.5;
    border: 1px solid #fecaca;

    strong {
        color: #dc2626;
    }
}

@media (max-width: 768px) {
    .blocked-task-card {
        flex-direction: column;
    }

    .task-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

