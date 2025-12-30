<template>
    <div class="daily-log-form">
        <!-- Tasks Section -->
        <div class="log-section tasks-section">
            <div class="section-header">
                <div class="section-title-wrapper">
                    <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <div>
                        <h3 class="section-title">Tasks</h3>
                        <p class="section-subtitle">{{ tasks.length }} task{{ tasks.length !== 1 ? 's' : '' }} in log</p>
                    </div>
                </div>
            </div>

            <div class="section-content">
                <TaskList 
                    v-if="tasks.length" 
                    :tasks="tasks" 
                    @toggle-task="$emit('toggle-task', $event)"
                    @delete-task="$emit('delete-task', $event)"
                    @task-update="$emit('task-update', $event)"
                />

                <div v-else class="empty-state-tasks">
                    <div class="empty-icon-wrapper">
                        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="empty-text">No tasks in your log yet</p>
                    <p class="empty-hint">Select tasks from the left panel to add them</p>
                </div>
            </div>
        </div>

        <!-- Notes Section -->
        <div class="log-section notes-section">
            <div class="section-header">
                <div class="section-title-wrapper">
                    <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <div>
                        <h3 class="section-title">Daily Notes</h3>
                        <p class="section-subtitle">Add your thoughts and updates</p>
                    </div>
                </div>
            </div>

            <div class="section-content">
                <textarea 
                    :value="notes" 
                    @input="handleInput"
                    placeholder="What did you work on today? Any blockers, achievements, or important notes..." 
                    rows="5"
                    class="notes-textarea"
                ></textarea>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="submit-section">
            <button @click="$emit('submit')" class="submit-button">
                <svg class="submit-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Save Daily Log</span>
                <svg class="submit-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script>
import TaskList from '../TaskList.vue';

export default {
    name: 'DailyLogForm',
    components: {
        TaskList
    },
    props: {
        tasks: {
            type: Array,
            default: () => []
        },
        notes: {
            type: String,
            default: ''
        }
    },
    emits: ['update:notes', 'toggle-task', 'submit'],
    methods: {
        handleInput(event) {
            this.$emit('update:notes', event.target.value);
        }
    }
};
</script>

<style lang="scss" scoped>
.daily-log-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    height: 100%;
}

// Log Section Base Styles
.log-section {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.625rem;
    overflow: hidden;
    transition: all 0.2s;

    &:hover {
        border-color: #d1d5db;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    }
}

.section-header {
    padding: 0.875rem 1rem;
    background: white;
    border-bottom: 1px solid #e5e7eb;
}

.section-title-wrapper {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: #6366f1;
    flex-shrink: 0;
}

.section-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
    line-height: 1.2;
}

.section-subtitle {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0.125rem 0 0 0;
    font-weight: 400;
}

.section-content {
    padding: 1rem;
}

// Tasks Section
.tasks-section {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
}

.empty-state-tasks {
    text-align: center;
    padding: 2.5rem 1rem;
    color: #6b7280;

    .empty-icon-wrapper {
        width: 3.5rem;
        height: 3.5rem;
        margin: 0 auto 1rem;
        background: #f3f4f6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .empty-icon {
        width: 1.75rem;
        height: 1.75rem;
        color: #9ca3af;
        opacity: 0.6;
    }

    .empty-text {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin: 0 0 0.25rem 0;
    }

    .empty-hint {
        font-size: 0.8125rem;
        color: #9ca3af;
        margin: 0;
    }
}

// Notes Section
.notes-section {
    flex-shrink: 0;
}

.notes-textarea {
    width: 100%;
    padding: 0.875rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    resize: vertical;
    font-family: inherit;
    transition: all 0.2s;
    background: white;
    min-height: 120px;
    line-height: 1.6;
    color: #111827;

    &:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    &::placeholder {
        color: #9ca3af;
    }
}

// Submit Section
.submit-section {
    flex-shrink: 0;
    padding-top: 0.5rem;
    border-top: 1px solid #e5e7eb;
}

.submit-button {
    width: 100%;
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.625rem;
    transition: all 0.3s;
    box-shadow: 0 2px 4px 0 rgba(16, 185, 129, 0.2);
    position: relative;
    overflow: hidden;

    &::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    &:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px 0 rgba(16, 185, 129, 0.3);

        &::before {
            left: 100%;
        }

        .submit-arrow {
            transform: translateX(4px);
        }
    }

    &:active {
        transform: translateY(0);
    }
}

.submit-icon {
    width: 1.125rem;
    height: 1.125rem;
    flex-shrink: 0;
}

.submit-arrow {
    width: 1rem;
    height: 1rem;
    transition: transform 0.3s;
    flex-shrink: 0;
}

// Responsive
@media (max-width: 768px) {
    .daily-log-form {
        gap: 0.75rem;
    }

    .section-header {
        padding: 0.75rem;
    }

    .section-content {
        padding: 0.75rem;
    }

    .submit-button {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
    }
}
</style>

