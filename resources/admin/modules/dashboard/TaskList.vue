<template>
    <div class="task-panel">
        <div v-for="task in tasks" :key="task.id" class="task-card"
            :class="{
                'task-completed': task.status === 'completed',
                'task-blocked': task.status === 'blocked',
                'task-in-progress': task.status === 'in-progress'
            }">
            <!-- Left -->
            <div class="task-left">
                <div class="task-checkbox-wrapper">
                    <input 
                        type="checkbox" 
                        class="task-checkbox" 
                        :checked="task.status === 'completed'"
                        @change="handleStatusChange(task, 'completed')"
                    />
                </div>

                <div class="task-info">
                    <div class="task-title">
                        {{ task.title }}
                    </div>

                    <div class="task-meta">
                        <span class="points">
                            {{ task.complete_weight || 0 }} / {{ task.weight || 1 }} pts
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right -->
            <div class="task-right">
                <!-- Weight and Hours Inputs (only show if not completed) -->
                <template v-if="task.status !== 'completed'">
                    <div class="input-group">
                        <label class="input-label">Weight</label>
                        <input 
                            placeholder="Weight" 
                            type="number" 
                            min="0" 
                            :max="task.weight || 1"
                            v-model.number="task.complete_weight" 
                            class="weight-input"
                            @input="handleTaskUpdate(task)"
                        />
                    </div>

                    <div class="input-group">
                        <label class="input-label">Hours</label>
                        <input 
                            placeholder="Hours" 
                            type="number" 
                            min="0" 
                            step="0.5"
                            v-model.number="task.hours" 
                            class="weight-input"
                            @input="handleTaskUpdate(task)"
                        />
                    </div>
                </template>

                <!-- Status Selector -->
                <div class="status-selector">
                    <button
                        v-for="statusOption in statusOptions"
                        :key="statusOption.value"
                        @click="handleStatusChange(task, statusOption.value)"
                        :class="['status-btn', statusOption.value, { active: task.status === statusOption.value }]"
                        :title="statusOption.label"
                    >
                        <svg class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="statusOption.value === 'completed'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <path v-else-if="statusOption.value === 'blocked'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="status-text">{{ statusOption.shortLabel }}</span>
                    </button>
                </div>

                <!-- Blocker Note (shown when blocked) -->
                <div v-if="task.status === 'blocked'" class="blocker-note-section">
                    <input
                        type="text"
                        v-model="task.blocker_reason"
                        @input="handleBlockerReasonChange(task)"
                        @blur="handleBlockerReasonChange(task)"
                        @keyup.enter="handleBlockerReasonChange(task)"
                        class="blocker-input"
                        :class="{ error: task.status === 'blocked' && !task.blocker_reason }"
                        placeholder="Why is this task blocked? (required)"
                    />
                    <span v-if="task.status === 'blocked' && !task.blocker_reason" class="error-message">
                        Blocker reason is required
                    </span>
                </div>

                <!-- Delete Button -->
                <button @click="deleteTask(task)" class="delete-btn" title="Remove from log">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        tasks: {
            type: Array,
            required: true,
        },
    },

    data() {
        return {
            statusOptions: [
                { value: 'in-progress', label: 'In Progress', shortLabel: 'In Progress' },
                { value: 'completed', label: 'Completed', shortLabel: 'Done' },
                { value: 'blocked', label: 'Blocked', shortLabel: 'Blocked' }
            ]
        };
    },

    computed: {
        totalCompletedWeight() {
            return this.tasks.reduce(
                (sum, t) => sum + (t.complete_weight || 0),
                0
            );
        }
    },

    emits: ['toggle-task', 'delete-task', 'task-update'],

    methods: {
        async handleStatusChange(task, newStatus) {
            const oldStatus = task.status;
            
            // Initialize status if not set
            if (!task.status) {
                task.status = 'in-progress';
            }

            // Handle checkbox click for completed
            if (newStatus === 'completed' && task.status === 'completed') {
                // Uncheck - go back to in-progress
                task.status = 'in-progress';
                task.complete_weight = 0;
            } else {
                // Set new status
                task.status = newStatus;
                
                // Auto-set complete_weight when completed
                if (newStatus === 'completed') {
                    task.complete_weight = task.weight || 1;
                } else if (newStatus === 'blocked') {
                    // Clear complete_weight when blocked
                    task.complete_weight = 0;
                    // Initialize blocker_reason if not set
                    if (!task.blocker_reason) {
                        task.blocker_reason = '';
                    }
                } else if (newStatus === 'in-progress') {
                    // Reset blocker reason when switching to in-progress
                    task.blocker_reason = '';
                }
            }

            this.$emit('toggle-task', task);
            await this.handleTaskUpdate(task);
        },

        async handleTaskUpdate(task) {
            // Ensure status defaults to in-progress if not set
            if (!task.status) {
                task.status = 'in-progress';
            }

            // Validate blocker reason when blocked
            if (task.status === 'blocked' && (!task.blocker_reason || task.blocker_reason.trim() === '')) {
                // Don't save if blocker reason is missing
                return;
            }

            // Emit update event which will trigger auto-save
            this.$emit('task-update', task);
        },

        async handleBlockerReasonChange(task) {
            // Auto-save when blocker reason is entered
            if (task.status === 'blocked' && task.blocker_reason && task.blocker_reason.trim() !== '') {
                await this.handleTaskUpdate(task);
            }
        },

        deleteTask(task) {
            if (confirm('Are you sure you want to remove this task from your log?')) {
                this.$emit('delete-task', task);
            }
        },
    },

    mounted() {
        // Initialize default status for tasks that don't have one
        this.tasks.forEach(task => {
            if (!task.status) {
                task.status = 'in-progress';
            }
            if (!task.blocker_reason) {
                task.blocker_reason = '';
            }
        });
    }
};
</script>

<style lang="scss" scoped>
.points {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 600;
}

.task-panel {
    background: transparent;
    border-radius: 0;
    padding: 0;
}

.task-card {
    padding: 0.15rem 20px;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    margin-bottom: 0.5rem;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: row;
    gap: 0.75rem;

    &:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    &.task-completed {
        background: #ecfdf5;
        border-color: #a7f3d0;

        .task-title {
            text-decoration: line-through;
            color: #6b7280;
        }
    }

    &.task-blocked {
        background: #fef2f2;
        border-color: #fecaca;
        border-left: 3px solid #ef4444;
    }

    &.task-in-progress {
        background: #eff6ff;
        border-color: #dbeafe;
    }
}

.task-left {
    display: flex;
    align-items: flex-start;
    align-items: center;
    gap: 0.625rem;
    flex: 1;
    min-width: 0;
}

.task-checkbox-wrapper {
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.task-checkbox {
    width: 16px;
    height: 16px;
    accent-color: #10b981;
    cursor: pointer;
    flex-shrink: 0;
}

.task-info {
    flex: 1;
    min-width: 0;
    display: flex;
    gap: 0.75rem;
    justify-content: space-between;
    align-items: flex-start;
}

.task-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
    word-wrap: break-word;
    line-height: 1.4;
}

.task-meta {
    font-size: 0.75rem;
    color: #6b7280;
    flex-shrink: 0;
}

.task-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    flex-shrink: 0;
}

.input-group {
    display: flex;
    align-items: center;
    flex-direction: row;
    gap: 0.125rem;
}

.input-label {
    font-size: 0.625rem;
    color: #6b7280;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.weight-input {
    width: 60px;
    padding: 0 0.5rem;
    font-size: 0.8125rem;
    border-radius: 0.375rem;
    border: 1px solid #d1d5db;
    text-align: center;
    transition: all 0.2s;
    background: white;

    &:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
    }
}

.status-selector {
    display: flex;
    gap: 0.125rem;
    background: #f3f4f6;
    padding: 0.125rem;
    border-radius: 0.375rem;
}

.status-btn {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.625rem;
    border: none;
    background: transparent;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: all 0.15s;
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;

    .status-icon {
        width: 0.875rem;
        height: 0.875rem;
    }

    .status-text {
        white-space: nowrap;
    }

    &:hover {
        background: rgba(255, 255, 255, 0.6);
        color: #374151;
    }

    &.in-progress {
        &.active {
            background: #3b82f6;
            color: white;
        }
    }

    &.completed {
        &.active {
            background: #10b981;
            color: white;
        }
    }

    &.blocked {
        &.active {
            background: #ef4444;
            color: white;
        }
    }
}

.blocker-note-section {
    min-width: 180px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.blocker-input {
    width: 100%;
    padding: 0.375rem 0.625rem;
    font-size: 0.75rem;
    border-radius: 0.375rem;
    border: 1px solid #fecaca;
    background: white;
    color: #991b1b;
    transition: all 0.2s;

    &::placeholder {
        color: #fca5a5;
    }

    &:focus {
        outline: none;
        border-color: #ef4444;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.1);
    }

    &.error {
        border-color: #dc2626;
        background: #fef2f2;
    }
}

.error-message {
    font-size: 0.625rem;
    color: #dc2626;
    font-weight: 500;
    margin-top: 0.125rem;
}

.delete-btn {
    width: 1.75rem;
    height: 1.75rem;
    border: none;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    padding: 0;
    flex-shrink: 0;

    svg {
        width: 0.875rem;
        height: 0.875rem;
    }

    &:hover {
        background: #fecaca;
        transform: scale(1.05);
    }

    &:active {
        transform: scale(0.95);
    }
}

// Responsive adjustments
@media (max-width: 768px) {
    .task-card {
        padding: 0.625rem;
    }

    .task-right {
        flex-direction: column;
        align-items: stretch;
        width: 100%;
    }

    .status-selector {
        width: 100%;
        justify-content: stretch;
    }

    .status-btn {
        flex: 1;
        justify-content: center;
    }

    .blocker-note-section {
        width: 100%;
        min-width: 0;
    }

    .weight-input {
        width: 100%;
    }
}
</style>
