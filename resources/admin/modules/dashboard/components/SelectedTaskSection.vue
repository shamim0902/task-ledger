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
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.selected-task-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    gap: 0.75rem;
}

.selected-task-info {
    flex: 1;
    min-width: 0;

    .selected-task-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.5rem 0;
        line-height: 1.4;
    }
}

.task-meta {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.meta-badge {
    background: #eef2ff;
    color: #4338ca;
    padding: 0.25rem 0.625rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid #c7d2fe;
}

.status-badge {
    padding: 0.25rem 0.625rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;

    &.status-progress {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }

    &.status-todo {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
}

.weight-badge {
    background: #f3e8ff;
    color: #6b21a8;
    padding: 0.25rem 0.625rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 700;
    border: 1px solid #e9d5ff;
}

.clear-button {
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 0.375rem;
    padding: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 2rem;
    height: 2rem;

    .icon-small {
        width: 1rem;
        height: 1rem;
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

