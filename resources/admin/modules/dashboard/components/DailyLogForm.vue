<template>
    <div class="log-entry-form">
        <div class="daily-log-header">
            <h2 class="daily-log-title">Daily Log</h2>
            <p class="log-date">Today: {{ todayDateString }}</p>
        </div>

        <TaskList 
            v-if="tasks.length" 
            :tasks="tasks" 
            @toggle-task="$emit('toggle-task', $event)" 
        />

        <div v-else class="empty-tasks">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p>No tasks added yet. Select a task and add subtasks to your log.</p>
        </div>

        <div class="form-group">
            <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Daily Notes
            </label>
            <textarea 
                :value="notes" 
                @input="handleInput"
                placeholder="Describe what you worked on today..." 
                rows="6"
                class="form-textarea"
            ></textarea>
        </div>

        <button @click="$emit('submit')" class="submit-button">
            <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Submit Daily Log
        </button>
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
    computed: {
        todayDateString() {
            return new Date().toISOString().split('T')[0];
        }
    },
    methods: {
        handleInput(event) {
            this.$emit('update:notes', event.target.value);
        }
    }
};
</script>

<style lang="scss" scoped>
.log-entry-form {
    border-radius: 1rem;
    padding: 2rem;
    background: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    width: calc(50% - 1.5rem);
    display: flex;
    flex-direction: column;
}

.daily-log-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;

    .daily-log-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 0.5rem 0;
    }

    .log-date {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
        font-weight: 500;
    }
}

.empty-tasks {
    text-align: center;
    padding: 2rem 1rem;
    background: #f9fafb;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    border: 2px dashed #e5e7eb;

    .empty-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 1rem;
        opacity: 0.4;
        color: #9ca3af;
    }

    p {
        margin: 0;
        color: #6b7280;
        font-size: 0.9375rem;
    }
}

.form-group {
    margin-bottom: 1.5rem;
    flex: 1;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.75rem;

    .label-icon {
        width: 1.125rem;
        height: 1.125rem;
        color: #4f46e5;
    }
}

.form-textarea {
    width: 100%;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.625rem;
    font-size: 0.9375rem;
    resize: vertical;
    font-family: inherit;
    transition: all 0.2s;
    background: #f9fafb;
    min-height: 120px;

    &:focus {
        outline: none;
        border-color: #4f46e5;
        background: white;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    &::placeholder {
        color: #9ca3af;
    }
}

.submit-button {
    width: 100%;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 0.625rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s;
    margin-top: auto;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);

    .icon-small {
        width: 1.25rem;
        height: 1.25rem;
    }

    &:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px -2px rgba(16, 185, 129, 0.4);
    }

    &:active {
        transform: translateY(0);
    }
}

@media (max-width: 1024px) {
    .log-entry-form {
        width: 100%;
    }
}
</style>

