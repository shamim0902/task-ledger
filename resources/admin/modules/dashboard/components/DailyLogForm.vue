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
                    <p class="empty-hint">Select tasks from board or create custom tasks</p>
                    <button @click.stop="$emit('open-task-select')" class="btn-pick-tasks">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span>Pick from boards</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Submit Today's Reports Section -->
        <div class="log-section submit-section-card">
            <div class="section-header">
                <div class="section-title-wrapper">
                    <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h3 class="section-title">Submit Today's Reports</h3>
                        <p class="section-subtitle">Add notes and submit your daily log</p>
                    </div>
                </div>
            </div>

            <div class="section-content">
                <!-- Notes Section -->
                <div class="notes-section" :class="{ expanded: showNotes }">
                    <button class="notes-toggle-btn" @click.stop="toggleNotes">
                        <svg class="notes-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span class="notes-toggle-text">
                            <span v-if="notes && !showNotes">{{ notes.length > 40 ? notes.substring(0, 40) + '...' : notes }}</span>
                            <span v-else>{{ showNotes ? 'Hide notes' : 'Add notes' }}</span>
                        </span>
                        <svg class="notes-chevron" :class="{ expanded: showNotes }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div class="notes-content-wrapper" :class="{ 'is-expanded': showNotes }">
                        <textarea 
                            :value="notes" 
                            @input="handleInput"
                            placeholder="What did you work on today? Any blockers, achievements, or important notes..." 
                            rows="5"
                            class="notes-textarea"
                            ref="notesTextarea"
                        ></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="submit-button-wrapper">
                    <button type="button" @click.stop="$emit('submit')" class="submit-button">
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
    emits: ['update:notes', 'toggle-task', 'submit', 'open-task-select'],
    data() {
        return {
            showNotes: false
        };
    },
    watch: {
        notes(newVal) {
            // Auto-expand if notes have content
            if (newVal && newVal.trim().length > 0) {
                this.showNotes = true;
            }
        }
    },
    mounted() {
        // Auto-expand if notes already have content
        if (this.notes && this.notes.trim().length > 0) {
            this.showNotes = true;
        }
    },
    methods: {
        handleInput(event) {
            this.$emit('update:notes', event.target.value);
        },
        toggleNotes(event) {
            event.preventDefault();
            event.stopPropagation();
            this.showNotes = !this.showNotes;
            // Focus textarea when expanded
            if (this.showNotes) {
                this.$nextTick(() => {
                    if (this.$refs.notesTextarea) {
                        this.$refs.notesTextarea.focus();
                    }
                });
            }
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
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    justify-content: space-between;
    align-items: center;

    &:hover {
        background: #f9fafb;
    }
}

.section-title-wrapper {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    min-width: 0;
}

.section-title-content {
    flex: 1;
    min-width: 0;
}

.expand-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: #6b7280;
    transition: transform 0.3s ease;
    flex-shrink: 0;

    &.expanded {
        transform: rotate(180deg);
    }
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

    &.clickable-link {
        color: #6366f1;
        font-weight: 500;
        cursor: pointer;
        transition: color 0.2s;

        &:hover {
            color: #4f46e5;
            text-decoration: underline;
        }
    }
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
    background: white;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
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
        margin: 0 0 1rem 0;
    }
}

.btn-pick-tasks {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    background: white;
    color: #6366f1;
    border: 1.5px solid #6366f1;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 0.5rem;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        background: #6366f1;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(99, 102, 241, 0.2);
    }

    &:active {
        transform: translateY(0);
    }
}

// Submit Section Card
.submit-section-card {
    flex-shrink: 0;
    background: white;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

// Notes Section
.notes-section {
    margin-bottom: 1rem;
    overflow: hidden;
}

.notes-toggle-btn {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 0.875rem;
    background: transparent;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #6366f1;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
    margin-bottom: 0.5rem;

    &:hover {
        background: #f3f4f6;
        color: #4f46e5;
    }

    &:active {
        background: #e5e7eb;
    }
}

.notes-icon {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
    color: #6366f1;
}

.notes-toggle-text {
    flex: 1;
    min-width: 0;
    text-align: left;
    color: #6366f1;
    font-weight: 500;
}

.notes-chevron {
    width: 1rem;
    height: 1rem;
    color: #9ca3af;
    transition: transform 0.3s ease;
    flex-shrink: 0;

    &.expanded {
        transform: rotate(180deg);
        color: #6366f1;
    }
}

.notes-content-wrapper {
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    padding: 0;
    transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                opacity 0.2s ease,
                padding 0.3s ease;

    &.is-expanded {
        max-height: 500px;
        opacity: 1;
        padding: 0.75rem 0;
    }
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

// Submit Button Wrapper
.submit-button-wrapper {
    display: flex;
    justify-content: flex-end;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
    margin-top: 0.75rem;
}

.submit-button {
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
        width: 100%;
    }

    .submit-button-wrapper {
        justify-content: stretch;
    }
}
</style>

