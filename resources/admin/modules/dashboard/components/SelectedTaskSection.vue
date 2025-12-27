<template>
    <div v-if="task" class="selected-task-section">
        <div class="selected-task-header">
            <div class="selected-task-info">
                <h3 class="selected-task-title">{{ task.title }}</h3>
                <div class="task-meta">
                    <span class="meta-badge">{{ task.board?.title }}</span>
                    <span
                        :class="['status-badge', task.status === 'In Progress' ? 'status-progress' : 'status-todo']">
                        {{ task.status }}
                    </span>
                    <span class="weight-badge">{{ task.weight }} pts</span>
                </div>
            </div>
            <button @click="$emit('clear')" class="clear-button" title="Clear selection">
                <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <SubtasksList
            :subtasks="task.subtasks || []"
            :show-add-form="showAddSubtaskInput"
            @toggle-add-form="$emit('toggle-add-form')"
            @add-subtask="$emit('add-subtask', $event)"
            @mark-completed="$emit('mark-completed', $event)"
            @add-to-log="$emit('add-to-log', $event)"
        />
    </div>
</template>

<script>
import SubtasksList from './SubtasksList.vue';

export default {
    name: 'SelectedTaskSection',
    components: {
        SubtasksList
    },
    props: {
        task: {
            type: Object,
            default: null
        },
        showAddSubtaskInput: {
            type: Boolean,
            default: false
        }
    },
    emits: ['clear', 'toggle-add-form', 'add-subtask', 'mark-completed', 'add-to-log']
};
</script>

<style lang="scss" scoped>
.selected-task-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid #e5e7eb;
}

.selected-task-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    gap: 1rem;
}

.selected-task-info {
    flex: 1;

    .selected-task-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 0.75rem 0;
        line-height: 1.3;
    }
}

.task-meta {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.meta-badge {
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #4338ca;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1px solid rgba(67, 56, 202, 0.2);
}

.status-badge {
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;

    &.status-progress {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        border: 1px solid rgba(30, 64, 175, 0.2);
    }

    &.status-todo {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border: 1px solid rgba(146, 64, 14, 0.2);
    }
}

.weight-badge {
    background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
    color: #6b21a8;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 700;
    border: 1px solid rgba(107, 33, 168, 0.2);
}

.clear-button {
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 0.5rem;
    padding: 0.625rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;

    .icon-small {
        width: 1.25rem;
        height: 1.25rem;
    }

    &:hover {
        background: #fecaca;
        transform: scale(1.05);
    }

    &:active {
        transform: scale(0.95);
    }
}
</style>

