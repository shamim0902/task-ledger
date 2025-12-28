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
                        v-model="task.blocker_reason"
                        @input="handleTaskUpdate(task)"
                        placeholder="Why is this task blocked? (required)"
                        class="blocker-input"
                        :class="{ 'error': !task.blocker_reason || task.blocker_reason.trim() === '' }"
                        required
                    />
                    <div v-if="!task.blocker_reason || task.blocker_reason.trim() === ''" class="error-message">
                        Blocker reason is required
                    </div>
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
        handleStatusChange(task, newStatus) {
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
            this.handleTaskUpdate(task);
        },

        handleTaskUpdate(task) {
            // Ensure status defaults to in-progress if not set
            if (!task.status) {
                task.status = 'in-progress';
            }

            // Validate blocker reason when blocked
            if (task.status === 'blocked' && !task.blocker_reason) {
                // Don't prevent update, but mark as needing attention
                return;
            }

            this.$emit('task-update', task);
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
    font-size: 12px;
    color: #4a505b;
    font-weight: 600;
}
.task-panel {
    background: #ffffff;
    border-radius: 12px;
    padding: 14px;
}

.task-card {
    // display: flex;
    // justify-content: space-between;
    // align-items: flex-start;
    padding: 14px;
    border-radius: 10px;
    border: 2px solid #eef2f7;
    background: #f9fafb;
    margin-bottom: 12px;
    transition: all 0.2s ease;
    gap: 12px;

    &:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    &.task-completed {
        background: #f0fdf4;
        border-color: #bbf7d0;

        .task-title {
            text-decoration: line-through;
            color: #6b7280;
        }
    }

    &.task-blocked {
        background: #fef2f2;
        border-color: #fecaca;
        border-left: 4px solid #ef4444;
    }

    &.task-in-progress {
        background: #eff6ff;
        border-color: #dbeafe;
    }
}

.task-left {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    flex: 1;
    min-width: 0;
    margin-bottom: 8px;
}

.task-checkbox-wrapper {
    flex-shrink: 0;
    margin-top: 2px;
}

.task-checkbox {
    width: 18px;
    height: 18px;
    accent-color: #22c55e;
    cursor: pointer;
    flex-shrink: 0;
}

.task-info {
    flex: 1;
    min-width: 0;
    display: flex;
    gap: 20px;
    justify-content: space-between;
}

.task-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 4px;
    word-wrap: break-word;
}

.task-meta {
    font-size: 12px;
    color: #6b7280;
}

.task-right {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    flex-wrap: wrap;
    flex-shrink: 0;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.input-label {
    font-size: 10px;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.weight-input {
    width: 70px;
    height: 20px;
    padding: 6px 8px;
    font-size: 13px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    text-align: center;
    transition: all 0.2s;

    &:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
}

.status-selector {
    display: flex;
    gap: 4px;
    background: #f3f4f6;
    padding: 2px;
    border-radius: 8px;
}

.status-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    border: none;
    background: transparent;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;

    .status-icon {
        width: 14px;
        height: 14px;
    }

    .status-text {
        white-space: nowrap;
    }

    &:hover {
        background: rgba(255, 255, 255, 0.5);
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
    min-width: 200px;
    flex: 1;
}

.blocker-note-section {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.blocker-input {
    width: 100%;
    padding: 6px 10px;
    font-size: 12px;
    border-radius: 6px;
    border: 2px solid #fee2e2;
    background: white;
    color: #991b1b;
    transition: all 0.2s;

    &::placeholder {
        color: #fca5a5;
    }

    &:focus {
        outline: none;
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    &.error {
        border-color: #dc2626;
        background: #fef2f2;
    }
}

.error-message {
    font-size: 11px;
    color: #dc2626;
    font-weight: 500;
    margin-top: 2px;
}

.delete-btn {
    width: 2rem;
    height: 2rem;
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
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        background: #fecaca;
        transform: scale(1.1);
    }

    &:active {
        transform: scale(0.95);
    }
}

// Responsive adjustments
@media (max-width: 768px) {
    .task-card {
        flex-direction: column;
        align-items: stretch;
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
    }
}
</style>
