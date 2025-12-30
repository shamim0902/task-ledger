<template>
    <div class="task-ledger-app">
        <!-- Top Navigation Bar -->
        <nav class="app-navbar">
            <div class="navbar-brand">
                <div class="brand-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="brand-text">
                    <h1 class="brand-title">Task Ledger</h1>
                    <p class="brand-subtitle">Daily Work Tracker</p>
                </div>
            </div>
            <div class="navbar-actions">
                <div class="view-switcher">
                    <button
                        @click="currentView = 'create'"
                        :class="['view-btn', { active: currentView === 'create' }]"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Create Log</span>
                    </button>
                    <button
                        @click="currentView = 'history'"
                        :class="['view-btn', { active: currentView === 'history' }]"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>History</span>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <div class="app-content">
            <!-- Quick Stats Dashboard -->
            <div class="stats-dashboard">
                <div class="stat-card stat-primary">
                    <div class="stat-icon-wrapper">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
        </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ todayLog.tasks.length }}</div>
                        <div class="stat-label">Tasks Today</div>
                    </div>
                </div>
                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ totalHoursToday.toFixed(1) }}h</div>
                        <div class="stat-label">Hours Logged</div>
                    </div>
                </div>
                <div class="stat-card stat-warning">
                    <div class="stat-icon-wrapper">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ totalStoryPoints }}</div>
                        <div class="stat-label">Story Points</div>
                    </div>
                </div>
                <div class="stat-card stat-info">
                    <div class="stat-icon-wrapper">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ todayDate }}</div>
                        <div class="stat-label">Today's Date</div>
                    </div>
                </div>
        </div>

                <!-- Create Log View -->
            <div v-if="currentView === 'create'" class="create-view">
                <div class="unified-panel">
                    <!-- Compact Header -->
                    <div class="unified-header">
                        <div class="header-top">
                            <div class="header-title-section">
                                <h2 class="main-title">Daily Log</h2>
                                <span class="date-badge">{{ todayDate }}</span>
                            </div>
                            <button @click="showAddTaskModal = true" class="btn-add-task-compact">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>New Task</span>
                            </button>
                        </div>
                        
                        <!-- Task Search -->
                        <div class="search-container-compact">
                            <div class="search-input-wrapper">
                                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input
                                    type="text"
                        v-model="taskSearchQuery"
                                    @focus="showDropdown = true"
                                    @input="showDropdown = taskSearchQuery.trim().length > 0"
                                    placeholder="Search and add tasks to your log..."
                                    class="search-input"
                                />
                            </div>

                            <!-- Task Dropdown -->
                            <transition name="dropdown-fade">
                                <div v-if="showDropdown && filteredTasks.length > 0" class="task-dropdown" @click.stop>
                                    <div
                                        v-for="task in filteredTasks"
                                        :key="task.id"
                                        @click.stop="selectTask(task)"
                                        class="dropdown-item"
                                    >
                                        <div class="dropdown-item-content">
                                            <span class="dropdown-task-title">{{ task.title }}</span>
                                            <span class="dropdown-task-board">{{ task.board?.title }}</span>
                                        </div>
                                        <span class="weight-badge">{{ task.weight }} pts</span>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="unified-content">
                        <!-- Log Submitted State -->
                        <div v-if="logSubmitted" class="log-submitted-state">
                            <div class="submitted-content">
                                <div class="submitted-icon-wrapper">
                                    <svg class="submitted-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="submitted-text">
                                    <h3 class="submitted-title">Log submitted for today</h3>
                                    <p class="submitted-message">Your daily log has been saved successfully.</p>
                                </div>
                                <button @click="logSubmitted = false" class="btn-edit-log">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <span>Edit Today's Log</span>
                                </button>
                            </div>
                        </div>

                        <!-- Daily Log Form (shown when not submitted) -->
                        <template v-else>
                            <!-- Selected Task Card (Inline) -->
                            <div v-if="selectedTask" class="selected-task-inline">
                                <div class="selected-task-header-compact">
                                    <div class="task-title-compact">
                                        <span class="task-title-text">{{ selectedTask.title }}</span>
                                        <div class="task-badges-compact">
                                            <span class="badge-small">{{ selectedTask.board?.title }}</span>
                                            <span class="badge-small weight">{{ selectedTask.weight }} pts</span>
                                        </div>
                                    </div>
                                    <button @click="clearSelectedTask" class="btn-clear-small">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                    <SelectedTaskSection
                        :task="selectedTask"
                        :show-add-subtask-input="showAddSubtaskInput"
                        @clear="clearSelectedTask"
                        @toggle-add-form="showAddSubtaskInput = !showAddSubtaskInput"
                        @add-subtask="handleAddSubtask"
                        @mark-completed="markSubtaskCompleted"
                        @add-to-log="addSubtaskToTodayLog"
                    />
                </div>

                <!-- Daily Log Form -->
                <DailyLogForm
                    :tasks="todayLog.tasks"
                    :notes="todayLog.notes"
                    @update:notes="todayLog.notes = $event"
                    @toggle-task="toggleTask"
                    @delete-task="deleteTaskFromLog"
                    @task-update="handleTaskUpdate"
                    @submit="handleCreateLog"
                />
                        </template>
                    </div>
                </div>
            </div>

            <!-- History View -->
            <div v-if="currentView === 'history'" class="history-view">
                <LogHistory />
            </div>
        </div>

        <!-- Add Task Modal -->
        <AddTaskModal
            :show="showAddTaskModal"
            @close="showAddTaskModal = false"
            @submit="addNewTask"
        />
    </div>
</template>

<script>
import SelectedTaskSection from './components/SelectedTaskSection.vue';
import DailyLogForm from './components/DailyLogForm.vue';
import AddTaskModal from './components/AddTaskModal.vue';
import LogHistory from './components/LogHistory.vue';

export default {
    name: 'DailyReportApp',
    components: {
        SelectedTaskSection,
        DailyLogForm,
        AddTaskModal,
        LogHistory
    },
    data() {
        return {
            tasks: [],
            todayLog: {
                tasks: [],
                notes: '',
            },
            selectedTask: null,
            currentView: 'create',
            taskSearchQuery: '',
            showAddTaskModal: false,
            showAddSubtaskInput: false,
            showDropdown: false,
            weight: 1,
            logSubmitted: false
        };
    },
    computed: {
        todayDate() {
            return new Date().toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }
    },
    computed: {
        filteredTasks() {
            if (!this.taskSearchQuery.trim()) return this.tasks;
            const query = this.taskSearchQuery.toLowerCase();
            return this.tasks?.filter(task =>
                task?.title?.toLowerCase()?.includes(query) ||
                task?.board?.title?.toLowerCase()?.includes(query)
            );
        },
        totalHoursToday() {
            return this.todayLog.tasks.reduce((sum, task) => sum + (parseFloat(task.hours) || 0), 0);
        },
        totalStoryPoints() {
            return this.todayLog.tasks.reduce((sum, task) => sum + (parseInt(task.complete_weight) || 0), 0);
        }
    },
    methods: {
        selectTask(task) {
            this.selectedTask = task;
            this.taskSearchQuery = '';
            this.showDropdown = false;
            // Focus management
            this.$nextTick(() => {
                const input = this.$el?.querySelector('.search-input');
                if (input) {
                    input.blur();
                }
            });
        },
        clearSelectedTask() {
            this.selectedTask = null;
            this.taskSearchQuery = '';
            this.showDropdown = false;
        },
        handleAddSubtask(data) {
            if (!this.selectedTask) return;

            this.$post('subtasks', {
                task_id: this.selectedTask.id,
                title: data.title,
                weight: data.weight,
                group_id: this.selectedTask.group_id,
                board_id: this.selectedTask.board_id,
            }).then(res => {
                if (!this.selectedTask.subtasks) {
                    this.$set(this.selectedTask, 'subtasks', []);
                }
                this.selectedTask.subtasks.push(res);
                this.showAddSubtaskInput = false;
                this.$notify({
                    type: 'success',
                    text: 'Subtask added successfully'
                });
            }).catch(err => {
                this.$notify({
                    type: 'error',
                    text: 'Failed to add subtask'
                });
            });
        },
        markSubtaskCompleted(subtask) {
            this.$patch(`subtasks/completed/${subtask.id}`).then(res => {
                subtask.status = 'closed';
                this.$notify({
                    type: 'success',
                    text: 'Subtask marked as completed'
                });
            }).catch(err => {
                this.$notify({
                    type: 'error',
                    text: 'Failed to update subtask'
                });
            });
        },
        addSubtaskToTodayLog(subtask) {
            // Check if subtask already exists in today's log
            const exists = this.todayLog.tasks.some(t => t.id === subtask.id);
            if (exists) {
                this.$notify({
                    type: 'warning',
                    text: 'This subtask is already in today\'s log'
                });
                return;
            }

            this.todayLog.tasks.push({
                id: subtask.id,
                title: subtask.title,
                weight: subtask.weight,
                complete_weight: 0,
                hours: 0,
                status: 'in-progress',
            });

            this.handleCreateLog();
        },
        toggleTask(task) {
            // Status change is handled in TaskList component
            // This method is kept for backward compatibility
            if (!task.status) {
                task.status = 'in-progress';
            }
        },
        handleTaskUpdate(task) {
            // Auto-save task updates (optional - can be debounced)
            // For now, just ensure data is in sync
            if (task.status === 'blocked' && !task.blocker_reason) {
                // Warn user if blocker reason is missing
                this.$notify({
                    type: 'warning',
                    text: 'Please provide a reason why this task is blocked'
                });
            }
        },
        getTasks() {
            this.$get('tasks').then(res => {
                this.tasks = res.all();
            }).catch(err => {
                this.$notify({
                    type: 'error',
                    text: 'Failed to load tasks'
                });
            });
        },
        getTodayLogs() {
            this.$get('today-logs').then(res => {
                const data = res.all();
                const tasks = (data?.log_items || []).map(task => {
                    // Ensure status defaults to in-progress
                    if (!task.status) {
                        task.status = 'in-progress';
                    }
                    // Initialize blocker_reason if status is blocked
                    if (task.status === 'blocked' && !task.blocker_reason) {
                        task.blocker_reason = task.note || '';
                    }
                    return task;
                });
                
                this.todayLog = {
                    notes: data?.additional_notes || '',
                    tasks: tasks,
                };
            }).catch(err => {
                // this.$notify({
                //     type: 'error',
                //     text: 'Failed to load today\'s log'
                // });
            });
        },
        addNewTask(taskData) {
            // This is a placeholder - implement actual API call when backend is ready
            const task = {
                id: this.tasks.length + 1,
                title: taskData.title,
                board: { title: taskData.board },
                weight: parseInt(taskData.weight),
                status: taskData.status,
                subtasks: []
            };

            this.tasks.push(task);
            this.$notify({
                type: 'success',
                text: 'Task added successfully!'
            });
        },
        handleCreateLog() {
            // Validate blocked tasks have blocker reasons
            const blockedTasksWithoutReason = this.todayLog.tasks.filter(
                task => task.status === 'blocked' && (!task.blocker_reason || task.blocker_reason.trim() === '')
            );

            if (blockedTasksWithoutReason.length > 0) {
                this.$notify({
                    type: 'warning',
                    text: `Please provide a reason for ${blockedTasksWithoutReason.length} blocked task(s) before submitting`
                });
                return;
            }

            this.$post('logs', this.todayLog).then(res => {
                this.$notify({
                    type: 'success',
                    text: 'Log saved successfully'
                });
                // Set submitted state
                this.logSubmitted = true;
                // Refresh today's log to get updated data
                this.getTodayLogs();
            }).catch(err => {
                this.$notify({
                    type: 'error',
                    text: 'Log save failed'
                });
            });
        },
        deleteTaskFromLog(task) {
            // Remove from local array first for immediate UI update
            const index = this.todayLog.tasks.findIndex(t => 
                (t.id && task.id && t.id === task.id) || 
                (t.task_id && task.task_id && t.task_id === task.task_id) ||
                (t.title === task.title && t.weight === task.weight)
            );
            
                if (index > -1) {
                    this.todayLog.tasks.splice(index, 1);
            }

            // If task has a log item ID, delete from server
            if (task.id) {
            this.$delete(`logs/items/${task.id}`).then(res => {
                this.$notify({
                    type: 'success',
                    text: 'Task removed from log'
                });
                // Refresh to get updated data
                this.getTodayLogs();
            }).catch(err => {
                this.$notify({
                    type: 'error',
                    text: 'Failed to remove task from log'
                });
                    // Revert local change on error
                    this.getTodayLogs();
                });
            } else {
                // For tasks without ID, just save the updated log
                this.handleCreateLog();
                this.$notify({
                    type: 'success',
                    text: 'Task removed from log'
                });
            }
        }
    },
    mounted() {
        this.getTasks();
        this.getTodayLogs();
        
        // Auto-create log if it doesn't exist
        if (!window.taskLedgerAdmin?.hasLogForToday) {
            this.handleCreateLog();
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-container-compact')) {
                this.showDropdown = false;
            }
        });
    }
};
</script>

<style lang="scss" scoped>
.task-ledger-app {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f8fafc 0%, #f1f5f9 100%);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

// Top Navigation Bar
.app-navbar {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;

    .brand-icon {
        width: 2.5rem;
        height: 2.5rem;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border-radius: 0.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);

        svg {
            width: 1.5rem;
            height: 1.5rem;
        }
    }

    .brand-text {
        .brand-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 0.75rem;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }
    }
}

.navbar-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.view-switcher {
    display: flex;
    gap: 0.5rem;
    background: #f3f4f6;
    padding: 0.25rem;
    border-radius: 0.5rem;
}

.view-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    background: transparent;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        color: #6366f1;
        background: rgba(99, 102, 241, 0.1);
    }

    &.active {
    background: white;
        color: #6366f1;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        font-weight: 600;
    }
}

// Main Content
.app-content {
    max-width: 1800px;
    margin: 0 auto;
    padding: 1.5rem;
}

// Stats Dashboard
.stats-dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid #e5e7eb;
    transition: all 0.3s;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);

    &:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .stat-icon-wrapper {
        width: 3rem;
        height: 3rem;
        border-radius: 0.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;

        svg {
            width: 1.5rem;
            height: 1.5rem;
        }
    }

    .stat-content {
        flex: 1;
        min-width: 0;

        .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
            line-height: 1.2;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.7;
        }
    }

    &.stat-primary {
        border-left: 4px solid #6366f1;
        .stat-icon-wrapper {
            background: #eef2ff;
            color: #6366f1;
        }
        .stat-value {
            color: #6366f1;
        }
    }

    &.stat-success {
        border-left: 4px solid #10b981;
        .stat-icon-wrapper {
            background: #ecfdf5;
            color: #10b981;
        }
        .stat-value {
            color: #10b981;
        }
    }

    &.stat-warning {
        border-left: 4px solid #f59e0b;
        .stat-icon-wrapper {
            background: #fffbeb;
            color: #f59e0b;
        }
        .stat-value {
            color: #f59e0b;
        }
    }

    &.stat-info {
        border-left: 4px solid #3b82f6;
        .stat-icon-wrapper {
            background: #eff6ff;
            color: #3b82f6;
        }
        .stat-value {
            color: #3b82f6;
        }
    }
}

// Create View Layout - Unified panel styles below

// Unified Panel
.unified-panel {
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.unified-header {
    padding: 0.625rem 0.875rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    gap: 0.75rem;
}

.header-title-section {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
}

.main-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
    line-height: 1.2;
}

.date-badge {
    padding: 0.125rem 0.5rem;
    background: #eef2ff;
    color: #6366f1;
    border-radius: 0.25rem;
    font-size: 0.6875rem;
    font-weight: 600;
    line-height: 1.4;
}

.btn-add-task-compact {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.625rem;
    background: #6366f1;
    color: white;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    line-height: 1.2;

    svg {
        width: 0.75rem;
        height: 0.75rem;
    }

    &:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 1px 3px rgba(99, 102, 241, 0.3);
    }
}

.search-container-compact {
    position: relative;
}

.unified-content {
    padding: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

// Log Submitted State
.log-submitted-state {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    min-height: 200px;
}

.submitted-content {
    text-align: center;
    max-width: 400px;
}

.submitted-icon-wrapper {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
}

.submitted-icon {
    width: 2rem;
    height: 2rem;
    color: white;
}

.submitted-text {
    margin-bottom: 1.5rem;
}

.submitted-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.submitted-message {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.btn-edit-log {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: #6366f1;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(99, 102, 241, 0.3);
    }

    &:active {
        transform: translateY(0);
    }
}

// Selected Task Inline
.selected-task-inline {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 0.625rem;
    margin-bottom: 0.375rem;
}

.selected-task-header-compact {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
    gap: 0.5rem;
}

.task-title-compact {
    flex: 1;
    min-width: 0;
}

.task-title-text {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.task-badges-compact {
    display: flex;
    gap: 0.25rem;
    flex-wrap: wrap;
}

.badge-small {
    padding: 0.125rem 0.375rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.25rem;
    font-size: 0.625rem;
    font-weight: 600;
    color: #6b7280;
    line-height: 1.3;

    &.weight {
        background: #f3e8ff;
        border-color: #e9d5ff;
        color: #6b21a8;
    }
}

.btn-clear-small {
    width: 1.25rem;
    height: 1.25rem;
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
    padding: 0;

    svg {
        width: 0.75rem;
        height: 0.75rem;
    }

    &:hover {
        background: #fecaca;
        transform: scale(1.05);
    }
}

// Search Container
.search-container {
    position: relative;
    margin-bottom: 1rem;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;

    .search-icon {
        position: absolute;
        left: 0.625rem;
        width: 1rem;
        height: 1rem;
        color: #9ca3af;
        pointer-events: none;
        z-index: 1;
    }

    .search-input {
        width: 100%;
        padding: 0.5rem 0.75rem 0.5rem 2rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.8125rem;
        background: #f9fafb;
        transition: all 0.2s;
        line-height: 1.4;

        &:focus {
            outline: none;
            border-color: #6366f1;
            background: white;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }
    }
}

.task-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    margin-top: 0.375rem;
    max-height: 250px;
    overflow-y: auto;
    z-index: 50;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);

    &::-webkit-scrollbar {
        width: 6px;
    }

    &::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    &::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;

        &:hover {
            background: #94a3b8;
        }
    }
}

// Dropdown transition
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}

.dropdown-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.15s;
    border-bottom: 1px solid #f3f4f6;

    &:last-child {
        border-bottom: none;
    }

    &:hover {
        background: #f9fafb;
    }
}

.dropdown-item-content {
    flex: 1;
    min-width: 0;

    .dropdown-task-title {
        display: block;
        font-weight: 600;
        color: #111827;
        font-size: 0.8125rem;
        margin-bottom: 0.125rem;
        line-height: 1.3;
    }

    .dropdown-task-board {
        display: block;
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.3;
    }
}

.weight-badge {
    background: #f3e8ff;
    color: #6b21a8;
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.6875rem;
    font-weight: 600;
    line-height: 1.3;
    font-weight: 600;
    white-space: nowrap;
}

// Selected Task Card
.selected-task-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
}

.selected-task-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    gap: 1rem;
}

.task-title-section {
    flex: 1;
    min-width: 0;

    .task-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.5rem 0;
        line-height: 1.4;
    }
}

.task-meta-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.meta-badge {
    padding: 0.25rem 0.625rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;

    &.weight {
        background: #f3e8ff;
        border-color: #e9d5ff;
        color: #6b21a8;
    }
}

.btn-clear {
    width: 2rem;
    height: 2rem;
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        background: #fecaca;
        transform: scale(1.05);
    }
}

// Empty State
    .empty-state {
        text-align: center;
    padding: 3rem 1.5rem;
        color: #6b7280;

    .empty-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 1rem;
        opacity: 0.4;
        color: #9ca3af;
    }

    .empty-text {
            margin: 0;
        font-size: 0.875rem;
    }
}

// History View
.history-view {
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    padding: 0.875rem;
}

// Responsive Design
@media (max-width: 768px) {
    .unified-header {
        padding: 0.5rem 0.75rem;
    }

    .header-top {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .btn-add-task-compact {
        width: 100%;
        justify-content: center;
    }

    .unified-content {
        padding: 0.625rem;
        gap: 0.5rem;
    }
}

@media (max-width: 768px) {
    .app-content {
        padding: 1rem;
    }

    .app-navbar {
        padding: 0.75rem 1rem;
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .navbar-brand {
        justify-content: center;
    }

    .view-switcher {
        width: 100%;
        justify-content: stretch;
    }

    .view-btn {
        flex: 1;
        justify-content: center;
    }

    .stats-dashboard {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .panel-header {
        padding: 1rem;
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }

    .panel-body {
        padding: 1rem;
    }
}
</style>
