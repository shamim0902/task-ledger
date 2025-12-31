<template>
    <div v-if="show" class="modal-overlay" @click.self.stop.prevent="$emit('close')">
        <div class="modal-content task-select-modal" @click.stop>
            <div class="modal-header">
                <h3 class="modal-title">
                    <svg class="modal-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Select Tasks
                </h3>
                <button type="button" @click.stop="$emit('close')" class="modal-close" title="Close">
                    <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <!-- Board Selector -->
                <div class="board-selector-wrapper">
                    <label class="selector-label">
                        <svg class="selector-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Select Board
                    </label>
                    <select v-model="selectedBoardId" @change="handleBoardChange" class="board-select">
                        <option value="">All Boards</option>
                        <option v-for="board in boards" :key="board.id" :value="board.id">
                            {{ board.title || board.name }}
                        </option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="search-wrapper">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Search tasks by title..."
                        class="search-input"
                    />
                </div>

                <!-- Loading State -->
                <div v-if="loadingBoards" class="loading-state">
                    <div class="spinner"></div>
                    <p>Loading boards...</p>
                </div>

                <!-- Tasks Grid -->
                <div v-else-if="filteredTasks.length > 0" class="tasks-grid">
                    <div
                        v-for="task in filteredTasks"
                        :key="task.id"
                        :class="['task-card', { 'is-selected': isTaskInLog(task), 'has-subtasks': task.subtasks && task.subtasks.length > 0 }]"
                    >
                        <div 
                            class="task-card-main"
                            @click.stop.prevent="selectTask(task, $event)"
                            role="button"
                            tabindex="0"
                            @keyup.enter.stop.prevent="selectTask(task, $event)"
                        >
                            <div class="task-card-header">
                                <h4 class="task-card-title">{{ task.title }}</h4>
                                <svg v-if="isTaskInLog(task)" class="check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="task-card-meta">
                                <span class="task-board">{{ task.board?.title }}</span>
                                <span class="task-weight">{{ task.weight }} pts</span>
                            </div>
                            <div v-if="task.subtasks && task.subtasks.length > 0" class="task-subtasks-toggle">
                                <button 
                                    type="button"
                                    @click.stop.prevent="toggleSubtasks(task.id)"
                                    class="subtask-toggle-btn"
                                >
                                    <svg 
                                        class="subtask-icon" 
                                        :class="{ 'expanded': expandedTasks[task.id] }"
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                    <span>{{ task.subtasks.length }} subtask{{ task.subtasks.length !== 1 ? 's' : '' }}</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Subtasks List -->
                        <div v-if="task.subtasks && task.subtasks.length > 0 && expandedTasks[task.id]" class="subtasks-list">
                            <div
                                v-for="subtask in task.subtasks"
                                :key="subtask.id"
                                @click.stop.prevent="selectSubtask(task, subtask, $event)"
                                role="button"
                                tabindex="0"
                                @keyup.enter.stop.prevent="selectSubtask(task, subtask, $event)"
                                :class="['subtask-card', { 'is-selected': isSubtaskInLog(subtask) }]"
                            >
                                <div class="subtask-card-header">
                                    <div class="subtask-indent">
                                        <svg class="subtask-connector" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                    <h5 class="subtask-card-title">{{ subtask.title }}</h5>
                                    <svg v-if="isSubtaskInLog(subtask)" class="check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="subtask-card-meta">
                                    <span v-if="subtask.weight" class="task-weight">{{ subtask.weight }} pts</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="empty-state">
                    <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="empty-text">
                        {{ searchQuery ? 'No tasks found matching your search' : (selectedBoardId ? 'No tasks available in this board' : 'Please select a board to view tasks') }}
                    </p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" @click.stop="$emit('close')" class="btn-cancel">Close</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TaskSelectModal',
    props: {
        show: {
            type: Boolean,
            default: false
        },
        tasks: {
            type: Array,
            default: () => []
        },
        selectedTaskIds: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            searchQuery: '',
            selectedBoardId: '',
            boards: [],
            loadingBoards: false,
            expandedTasks: {} // Track which tasks have expanded subtasks
        };
    },
    computed: {
        filteredTasks() {
            let filtered = this.tasks || [];

            // Filter by selected board
            if (this.selectedBoardId) {
                filtered = filtered.filter(task => 
                    task?.board_id === parseInt(this.selectedBoardId) || 
                    task?.board?.id === parseInt(this.selectedBoardId)
                );
            }

            // Filter by search query (including subtasks)
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(task => {
                    // Check if task title matches
                    if (task?.title?.toLowerCase()?.includes(query)) {
                        return true;
                    }
                    // Check if any subtask title matches
                    if (task?.subtasks && task.subtasks.length > 0) {
                        const hasMatchingSubtask = task.subtasks.some(subtask => 
                            subtask?.title?.toLowerCase()?.includes(query)
                        );
                        // Auto-expand task if subtask matches
                        if (hasMatchingSubtask && !this.expandedTasks[task.id]) {
                            this.$set(this.expandedTasks, task.id, true);
                        }
                        return hasMatchingSubtask;
                    }
                    return false;
                });
            }

            return filtered;
        }
    },
    emits: ['close', 'select', 'select-all-subtasks'],
    methods: {
        async loadBoards() {
            this.loadingBoards = true;
            try {
                const response = await this.$get('pm/boards');
                this.boards = response || [];
            } catch (error) {
                console.error('Error loading boards:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to load boards'
                });
            } finally {
                this.loadingBoards = false;
            }
        },
        handleBoardChange() {
            // Reset search when board changes
            this.searchQuery = '';
        },
        selectTask(task, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
            }
            
            // If task has subtasks, add all subtasks instead of the main task
            if (task.subtasks && task.subtasks.length > 0) {
                // Emit all subtasks as a batch
                const subtasksWithParent = task.subtasks.map(subtask => ({
                    ...subtask,
                    parent_task_id: task.id,
                    parent_task: task,
                    is_subtask: true
                }));
                this.$nextTick(() => {
                    this.$emit('select-all-subtasks', {
                        task: task,
                        subtasks: subtasksWithParent
                    });
                });
            } else {
                // No subtasks, add the main task
                this.$nextTick(() => {
                    this.$emit('select', task);
                });
            }
        },
        selectSubtask(parentTask, subtask, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
            }
            // Create a subtask object with parent task info
            const subtaskWithParent = {
                ...subtask,
                parent_task_id: parentTask.id,
                parent_task: parentTask,
                is_subtask: true
            };
            // Use nextTick to ensure event is fully processed
            this.$nextTick(() => {
                this.$emit('select', subtaskWithParent);
            });
        },
        toggleSubtasks(taskId) {
            this.$set(this.expandedTasks, taskId, !this.expandedTasks[taskId]);
        },
        isTaskInLog(task) {
            return this.selectedTaskIds.includes(task.id);
        },
        isSubtaskInLog(subtask) {
            return this.selectedTaskIds.includes(subtask.id);
        }
    },
    watch: {
        show(newVal) {
            if (newVal) {
                // Load boards when modal opens
                this.loadBoards();
            } else {
                // Reset when modal closes
                this.searchQuery = '';
                this.selectedBoardId = '';
                this.expandedTasks = {};
            }
        }
    },
    mounted() {
        // Load boards on mount if modal is already open
        if (this.show) {
            this.loadBoards();
        }
    }
};
</script>

<style lang="scss" scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.task-select-modal {
    width: 100%;
    max-width: 900px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.2s ease-out;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
    background: white;
    border-radius: 0.5rem 0.5rem 0 0;
}

.modal-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.modal-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: #6366f1;
}

.modal-close {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    color: #6b7280;
    transition: color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;

    &:hover {
        color: #111827;
        background: #f3f4f6;
    }

    .icon-small {
        width: 1.25rem;
        height: 1.25rem;
    }
}

.modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem;
    background: #f9fafb;
}

.board-selector-wrapper {
    margin-bottom: 1rem;
}

.selector-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.selector-icon {
    width: 1rem;
    height: 1rem;
    color: #6366f1;
}

.board-select {
    width: 100%;
    padding: 0.625rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    background: white;
    color: #111827;
    cursor: pointer;
    transition: all 0.2s;

    &:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    &:hover {
        border-color: #9ca3af;
    }
}

.search-wrapper {
    position: relative;
    margin-bottom: 1.25rem;

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1.125rem;
        height: 1.125rem;
        color: #9ca3af;
        pointer-events: none;
    }

    .search-input {
        width: 100%;
        padding: 0.625rem 0.75rem 0.625rem 2.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        background: white;
        transition: all 0.2s;

        &:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
    }
}

.tasks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.task-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    transition: all 0.2s;
    position: relative;
    overflow: hidden;

    &.has-subtasks {
        padding-bottom: 0;
    }

    &:hover {
        border-color: #6366f1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    &.is-selected {
        border-color: #6366f1;
        background: #eef2ff;
    }
}

.task-card-main {
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        transform: translateY(-2px);
    }
}

.task-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.task-card-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
    line-height: 1.4;
    flex: 1;
}

.check-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: #10b981;
    flex-shrink: 0;
}

.task-card-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
}

.task-board {
    background: #eef2ff;
    color: #4338ca;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid #c7d2fe;
}

.task-weight {
    background: #fef3c7;
    color: #92400e;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid #fde68a;
}

.task-subtasks-toggle {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #f3f4f6;
}

.subtask-toggle-btn {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    background: transparent;
    border: none;
    color: #6b7280;
    font-size: 0.75rem;
    cursor: pointer;
    padding: 0.25rem 0;
    transition: color 0.2s;
    width: 100%;

    &:hover {
        color: #6366f1;
    }

    .subtask-icon {
        width: 0.875rem;
        height: 0.875rem;
        transition: transform 0.2s;
        
        &.expanded {
            transform: rotate(180deg);
        }
    }
}

.subtasks-list {
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    padding: 0.5rem 1rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.subtask-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
    margin-left: 1rem;
    position: relative;

    &:hover {
        border-color: #6366f1;
        box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
        transform: translateX(2px);
    }

    &.is-selected {
        border-color: #6366f1;
        background: #eef2ff;
    }
}

.subtask-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.subtask-indent {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    margin-left: -1rem;
    margin-right: 0.5rem;
}

.subtask-connector {
    width: 0.75rem;
    height: 0.75rem;
    color: #9ca3af;
}

.subtask-card-title {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #111827;
    margin: 0;
    line-height: 1.4;
    flex: 1;
}

.subtask-card-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.25rem;
}

.loading-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .spinner {
        width: 2rem;
        height: 2rem;
        margin: 0 auto 1rem;
        border: 3px solid #e5e7eb;
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    p {
        font-size: 0.875rem;
        margin: 0;
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 1rem;
        color: #d1d5db;
    }

    .empty-text {
        font-size: 0.875rem;
        margin: 0;
    }
}

.modal-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid #e5e7eb;
    background: white;
    border-radius: 0 0 0.5rem 0.5rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.btn-cancel {
    padding: 0.5rem 1rem;
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        background: #e5e7eb;
        border-color: #9ca3af;
    }
}

@media (max-width: 768px) {
    .tasks-grid {
        grid-template-columns: 1fr;
    }

    .task-select-modal {
        max-width: 100%;
        max-height: 95vh;
    }
}
</style>

