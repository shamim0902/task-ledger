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

                <!-- Navigation Tabs -->
            <NavigationTabs 
                :current-view="currentView" 
                @view-change="currentView = $event"
            />

                <!-- Create Log View -->
            <div v-if="currentView === 'create'" class="create-view">
                <div class="unified-panel">
                    <!-- Compact Header -->
                    <div class="unified-header">
                        <div class="header-top">
                            <div class="header-title-section">
                                <h2 class="main-title">Daily Tasks</h2>
                                <span class="date-badge">{{ todayDate }}</span>
                            </div>
                            <button @click="showAddTaskModal = true" class="btn-add-task-compact">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Add custom task</span>
                            </button>
                                <!-- Task Selection Button -->
                        <button @click="showTaskSelectModal = true" class="btn-select-tasks">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Pick from boards</span>
                        </button>
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
                                    <div class="submitted-summary">
                                        <span>{{ todayLog.tasks.length }} task{{ todayLog.tasks.length !== 1 ? 's' : '' }}</span>
                                        <span v-if="totalStoryPoints > 0">• {{ totalStoryPoints }} pts</span>
                                        <span v-if="completedTasksCount > 0">• {{ completedTasksCount }} completed</span>
                                    </div>
                                </div>
                                <div class="submitted-actions">
                                    <button @click="handleEditLog" class="btn-edit-log">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                    <button @click="handleDeleteTodayLog" class="btn-delete-log">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>Delete</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Submitted Log Details Summary -->
                            <div class="submitted-details">
                                <!-- <div v-if="todayLog.tasks.length > 0" class="submitted-tasks-list">
                                    <div 
                                        v-for="(task, index) in todayLog.tasks" 
                                        :key="task.id || task.task_id || index"
                                        class="submitted-task-item"
                                    >
                                        <div class="task-item-content">
                                            <div class="task-item-header">
                                                <span class="task-item-title">{{ task.task_title || task.title || 'Untitled Task' }}</span>
                                                <div class="task-item-badges">
                                                    <span 
                                                        :class="['status-badge', `status-${task.activity_type || task.status}`]"
                                                    >
                                                        {{ formatStatus(task.activity_type || task.status) }}
                                                    </span>
                                                    <span v-if="task.complete_weight" class="points-badge">{{ task.complete_weight }} pts</span>
                                                    <span v-if="task.time_spent || task.hours" class="hours-badge">
                                                        {{ parseFloat(task.time_spent || task.hours || 0).toFixed(1) }}h
                                                    </span>
                                                </div>
                                            </div>
                                            <div v-if="task.note" class="task-item-note">
                                                <svg class="note-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                </svg>
                                                <span>{{ task.note }}</span>
                                            </div>
                                            <div v-if="task.block_reason" class="task-item-blocker">
                                                <svg class="blocker-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <span>{{ task.block_reason }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                                
                                <div v-if="todayLog.notes && todayLog.notes.trim()" class="submitted-notes">
                                    <div class="notes-header">
                                        <svg class="notes-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="notes-title">Additional Notes</span>
                                    </div>
                                    <div class="notes-content">{{ todayLog.notes }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Daily Log Form (shown when not submitted) -->
                        <template v-if="!logSubmitted">
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
                    @open-task-select="showTaskSelectModal = true"
                />
                        </template>
                    </div>
                </div>
            </div>

            <!-- History View -->
            <div v-if="currentView === 'history'" class="history-view">
                <LogHistory :key="historyKey" />
            </div>
        </div>

        <!-- Add Task Modal -->
        <AddTaskModal
            :show="showAddTaskModal"
            @close="showAddTaskModal = false"
            @submit="addNewTask"
        />

        <!-- Task Select Modal -->
        <TaskSelectModal
            :show="showTaskSelectModal"
            :tasks="tasks"
            :selected-task-ids="selectedTaskIds"
            @close="showTaskSelectModal = false"
            @select="handleTaskSelect"
        />
    </div>
</template>

<script>
import SelectedTaskSection from './components/SelectedTaskSection.vue';
import DailyLogForm from './components/DailyLogForm.vue';
import AddTaskModal from './components/AddTaskModal.vue';
import LogHistory from './components/LogHistory.vue';
import NavigationTabs from './components/NavigationTabs.vue';
import TaskSelectModal from './components/TaskSelectModal.vue';

export default {
    name: 'DailyReportApp',
    components: {
        SelectedTaskSection,
        DailyLogForm,
        AddTaskModal,
        LogHistory,
        NavigationTabs,
        TaskSelectModal
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
            showAddTaskModal: false,
            showTaskSelectModal: false,
            showAddSubtaskInput: false,
            weight: 1,
            logSubmitted: false,
            historyKey: 0
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
        selectedTaskIds() {
            return this.todayLog.tasks.map(t => t.task_id || t.id).filter(Boolean);
        },
        totalHoursToday() {
            return this.todayLog.tasks.reduce((sum, task) => sum + (parseFloat(task.hours) || 0), 0);
        },
        totalStoryPoints() {
            return this.todayLog.tasks.reduce((sum, task) => sum + (parseInt(task.complete_weight) || 0), 0);
        },
        completedTasks() {
            return this.todayLog.tasks.filter(t => t.status === 'completed');
        },
        inProgressTasks() {
            return this.todayLog.tasks.filter(t => t.status === 'in-progress');
        },
        blockedTasks() {
            return this.todayLog.tasks.filter(t => t.status === 'blocked');
        },
        completedTasksCount() {
            return this.completedTasks.length;
        },
        inProgressTasksCount() {
            return this.inProgressTasks.length;
        },
        blockedTasksCount() {
            return this.blockedTasks.length;
        }
    },
    methods: {
        async handleTaskSelect(task) {
            // Check if task already exists in today's log
            const exists = this.todayLog.tasks.some(t => 
                (t.task_id && task.id && t.task_id === task.id) ||
                (t.id && task.id && t.id === task.id)
            );
            
            if (exists) {
                this.$notify({
                    type: 'warning',
                    text: 'This task is already in today\'s log'
                });
                return;
            }

            // Add task to today's log
            this.todayLog.tasks.push({
                task_id: task.id,
                id: task.id,
                title: task.title,
                weight: task.weight,
                complete_weight: 0,
                hours: 0,
                status: 'in-progress',
                board: task.board
            });

            // Close the modal
            this.showTaskSelectModal = false;
            
            // Auto-save the log to persist the selected task (silently, without changing submitted state)
            try {
                await this.$post('logs', this.todayLog);
                // Don't set logSubmitted = true here - just save the data
                // Don't call getTodayLogs() as it might change the submitted state
                this.$notify(
                    'Task added to log'
                );
            } catch (error) {
                console.error('Error saving task to log:', error);
                this.$notify('Failed to save task to log'
                );
            }
        },
        clearSelectedTask() {
            this.selectedTask = null;
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
        async handleTaskUpdate(task) {
            // Validate blocked tasks have blocker reasons before auto-saving
            if (task.status === 'blocked' && (!task.blocker_reason || task.blocker_reason.trim() === '')) {
                // Don't save if blocker reason is missing
                return;
            }

            // Auto-save the log when task changes (weight, hours, status, etc.)
            // But don't change the submitted state - only save the data
            try {
                await this.$post('logs', this.todayLog);
                // Silent save - no notification to avoid spam
                // Don't set logSubmitted = true here - that should only happen on explicit submission
            } catch (error) {
                console.error('Error auto-saving task update:', error);
                // Don't show error notification on every status change to avoid spam
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

                // Only mark as submitted if the log status is actually 'submitted'
                // Don't mark as submitted just because there are tasks - user might still be editing
                this.logSubmitted = data?.status === 'submitted';
            }).catch(err => {
                // this.$notify({
                //     type: 'error',
                //     text: 'Failed to load today\'s log'
                // });
            });
        },
        handleEditLog() {
            this.logSubmitted = false;
        },
        handleDeleteTodayLog() {
            if (!confirm('Are you sure you want to delete today\'s submitted log? This action cannot be undone.')) {
                return;
            }

            this.$delete('logs/today').then(res => {
                this.$notify({
                    type: 'success',
                    text: 'Today\'s log deleted successfully'
                });
                
                // Reset the log state
                this.todayLog = {
                    tasks: [],
                    notes: '',
                };
                this.logSubmitted = false;
                
                // Refresh today's log to ensure UI is updated
                this.getTodayLogs();
            }).catch(err => {
                this.$notify({
                    type: 'error',
                    text: 'Failed to delete today\'s log'
                });
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

            // Mark log as submitted when user explicitly clicks "Save Daily Log"
            const logData = {
                ...this.todayLog,
                status: 'submitted'
            };
            
            this.$post('logs', logData).then(res => {
                // Only show notification if this was an explicit user action (not auto-save)
                // Check if there are actually tasks or notes to save
                if (this.todayLog.tasks.length > 0 || (this.todayLog.notes && this.todayLog.notes.trim())) {
                    this.$notify({
                        type: 'success',
                        text: 'Log saved successfully'
                    });
                }
                // Set submitted state
                this.logSubmitted = true;
                // Refresh today's log to get updated data
                this.getTodayLogs();
                // Force refresh history component by updating key
                this.historyKey += 1;
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
        },
        formatStatus(status) {
            if (!status) return 'In Progress';
            const statusMap = {
                'completed': 'Completed',
                'in-progress': 'In Progress',
                'blocked': 'Blocked'
            };
            return statusMap[status] || status.charAt(0).toUpperCase() + status.slice(1).replace('-', ' ');
        }
    },
    mounted() {
        this.getTasks();
        this.getTodayLogs();
        
        // Auto-create log if it doesn't exist (silently, without notification)
        if (!window.taskLedgerAdmin?.hasLogForToday) {
            // Create log silently without showing notification
            this.$post('logs', this.todayLog).then(res => {
                // Silent creation - no notification
                this.getTodayLogs();
            }).catch(err => {
                // Silent error - no notification
            });
        }
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
    margin-bottom: 1rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 100;
}

.navbar-brand {
    max-width: 1800px;
    margin: 0 auto;
    padding: 0.625rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;

    .brand-icon {
        width: 2rem;
        height: 2rem;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 2px 4px -1px rgba(99, 102, 241, 0.3);
        flex-shrink: 0;

        svg {
            width: 1.125rem;
            height: 1.125rem;
        }
    }

    .brand-text {
        .brand-title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 0.6875rem;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }
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
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.875rem;
    border: 1px solid #e5e7eb;
    transition: all 0.3s;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);

    &:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .stat-icon-wrapper {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.3s;

        svg {
            width: 1.375rem;
            height: 1.375rem;
        }
    }

    .stat-content {
        flex: 1;
        min-width: 0;

        .stat-value {
            font-size: 1.375rem;
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
        background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%);
        border-color: #c7d2fe;
        
        .stat-icon-wrapper {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
        }
        .stat-value {
            color: #6366f1;
        }
        
        &:hover {
            background: linear-gradient(135deg, #e0e7ff 0%, #f5f7ff 100%);
            border-color: #a5b4fc;
            
            .stat-icon-wrapper {
                transform: scale(1.1);
                box-shadow: 0 6px 12px rgba(99, 102, 241, 0.4);
            }
        }
    }

    &.stat-success {
        background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
        border-color: #a7f3d0;
        
        .stat-icon-wrapper {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
        }
        .stat-value {
            color: #10b981;
        }
        
        &:hover {
            background: linear-gradient(135deg, #d1fae5 0%, #f0fdf4 100%);
            border-color: #6ee7b7;
            
            .stat-icon-wrapper {
                transform: scale(1.1);
                box-shadow: 0 6px 12px rgba(16, 185, 129, 0.4);
            }
        }
    }

    &.stat-warning {
        background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);
        border-color: #fde68a;
        
        .stat-icon-wrapper {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
        }
        .stat-value {
            color: #f59e0b;
        }
        
        &:hover {
            background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%);
            border-color: #fcd34d;
            
            .stat-icon-wrapper {
                transform: scale(1.1);
                box-shadow: 0 6px 12px rgba(245, 158, 11, 0.4);
            }
        }
    }

    &.stat-info {
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
        border-color: #bfdbfe;
        
        .stat-icon-wrapper {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }
        .stat-value {
            color: #3b82f6;
        }
        
        &:hover {
            background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
            border-color: #93c5fd;
            
            .stat-icon-wrapper {
                transform: scale(1.1);
                box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
            }
        }
    }
}

// Create View Layout - Unified panel styles below

// Unified Panel
.unified-panel {
    border-radius: 0.5rem;
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
    background: none;
    color: rgb(0, 0, 0);
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
        background: none;
        transform: translateY(-1px);
        // box-shadow: 0 1px 3px rgba(99, 102, 241, 0.3);
    }
}

.btn-select-tasks {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.875rem;
    background: white;
    color: #6366f1;
    border: 1.5px solid #6366f1;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    line-height: 1.2;

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

.unified-content {
    padding-top: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

// Log Submitted State
.log-submitted-state {
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.submitted-content {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.875rem 1rem;
}

.submitted-icon-wrapper {
    width: 2.5rem;
    height: 2.5rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    flex-shrink: 0;
}

.submitted-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: white;
}

.submitted-text {
    flex: 1;
    min-width: 0;
}

.submitted-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
    line-height: 1.2;
}

.submitted-summary {
    font-size: 0.8125rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.submitted-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

.btn-edit-log,
.btn-delete-log {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.875rem;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;

    svg {
        width: 0.875rem;
        height: 0.875rem;
    }

    &:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    &:active {
        transform: translateY(0);
    }
}

.btn-edit-log {
    background: #6366f1;
    color: white;

    &:hover {
        background: #4f46e5;
        box-shadow: 0 2px 4px rgba(99, 102, 241, 0.2);
    }
}

.btn-delete-log {
    background: #ef4444;
    color: white;

    &:hover {
        background: #dc2626;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
    }
}

// Submitted Log Details
.submitted-details {
    border-top: 1px solid #e5e7eb;
    padding: 0.875rem 1rem;
    background: #f9fafb;
}

.submitted-tasks-list {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
    margin-bottom: 0.875rem;
}

.submitted-task-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    padding: 0.75rem;
    transition: all 0.2s;

    &:hover {
        border-color: #d1d5db;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
}

.task-item-content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.task-item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.task-item-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    flex: 1;
    min-width: 0;
    line-height: 1.4;
}

.task-item-badges {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.status-badge {
    padding: 0.1875rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;

    &.status-completed {
        background: #d1fae5;
        color: #065f46;
    }

    &.status-in-progress {
        background: #dbeafe;
        color: #1e40af;
    }

    &.status-blocked {
        background: #fee2e2;
        color: #991b1b;
    }
}

.points-badge {
    padding: 0.1875rem 0.5rem;
    background: #f3f4f6;
    color: #374151;
    border-radius: 0.25rem;
    font-size: 0.6875rem;
    font-weight: 600;
}

.hours-badge {
    padding: 0.1875rem 0.5rem;
    background: #eff6ff;
    color: #1e40af;
    border-radius: 0.25rem;
    font-size: 0.6875rem;
    font-weight: 600;
}

.task-item-note,
.task-item-blocker {
    display: flex;
    align-items: flex-start;
    gap: 0.375rem;
    padding: 0.5rem;
    background: #f9fafb;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    color: #4b5563;
    line-height: 1.4;

    svg {
        width: 0.875rem;
        height: 0.875rem;
        flex-shrink: 0;
        margin-top: 0.125rem;
    }
}

.task-item-note {
    .note-icon {
        color: #6366f1;
    }
}

.task-item-blocker {
    background: #fef2f2;
    color: #991b1b;

    .blocker-icon {
        color: #dc2626;
    }
}

.submitted-notes {
    margin-top: 0.875rem;
    padding-top: 0.875rem;
    border-top: 1px solid #e5e7eb;
}

.notes-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;

    .notes-icon {
        width: 1rem;
        height: 1rem;
        color: #6b7280;
    }

    .notes-title {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
    }
}

.notes-content {
    padding: 0.625rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    color: #4b5563;
    line-height: 1.5;
    white-space: pre-wrap;
    word-wrap: break-word;
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
    border-radius: 0.5rem;
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

    .btn-select-tasks {
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
