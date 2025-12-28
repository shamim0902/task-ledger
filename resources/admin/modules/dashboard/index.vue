<template>
    <div class="daily-report-app">
        <div class="top-app-menu">
            
        </div>
        <div class="container">
            <!-- Header -->
            <HeaderCard
                :total-tasks="tasks.length"
                :hours-today="totalHoursToday"
                :story-points="totalStoryPoints"
            />

            <!-- Navigation -->
            <NavigationTabs 
                :current-view="currentView" 
                @view-change="currentView = $event"
            />

            <!-- Main Content -->
            <div v-if="currentView === 'create'" class="wrapper">
                <!-- Create Log View -->
                <div class="content-card">
                    <h2 class="section-title">Select Your Daily Tasks</h2>
                    <!-- Task Search and Add -->
                    <TaskSearchSection
                        v-model="taskSearchQuery"
                        :filtered-tasks="filteredTasks"
                        @select-task="selectTask"
                        @add-task="showAddTaskModal = true"
                    />

                    <!-- Selected Task Details -->
                    <SelectedTaskSection
                        v-if="selectedTask"
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
            </div>

            <!-- History View -->
            <div v-if="currentView === 'history'" class="content-card history-view">
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
import HeaderCard from './components/HeaderCard.vue';
import NavigationTabs from './components/NavigationTabs.vue';
import TaskSearchSection from './components/TaskSearchSection.vue';
import SelectedTaskSection from './components/SelectedTaskSection.vue';
import DailyLogForm from './components/DailyLogForm.vue';
import AddTaskModal from './components/AddTaskModal.vue';
import LogHistory from './components/LogHistory.vue';

export default {
    name: 'DailyReportApp',
    components: {
        HeaderCard,
        NavigationTabs,
        TaskSearchSection,
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
            weight: 1
        };
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
        },
        clearSelectedTask() {
            this.selectedTask = null;
            this.taskSearchQuery = '';
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
                this.$notify('Log saved successfully');
                // Refresh today's log to get updated data
                this.getTodayLogs();
            }).catch(err => {
                this.$notify('Log save failed');
            });
        },
        deleteTaskFromLog(task) {
            if (!task.id) {
                // If task doesn't have a log item ID, just remove from local array
                const index = this.todayLog.tasks.findIndex(t => t.task_id === task.task_id || t.id === task.id);
                if (index > -1) {
                    this.todayLog.tasks.splice(index, 1);
                    this.handleCreateLog();
                }
                return;
            }

            this.$delete(`logs/items/${task.id}`).then(res => {
                this.$notify({
                    type: 'success',
                    text: 'Task removed from log'
                });
                // Remove from local array
                const index = this.todayLog.tasks.findIndex(t => t.id === task.id);
                if (index > -1) {
                    this.todayLog.tasks.splice(index, 1);
                }
                // Refresh to get updated data
                this.getTodayLogs();
            }).catch(err => {
                this.$notify({
                    type: 'error',
                    text: 'Failed to remove task from log'
                });
            });
        }
    },
    mounted() {
        this.getTasks();
        this.getTodayLogs();
        
        // Auto-create log if it doesn't exist
        if (!window.taskLedgerAdmin?.hasLogForToday) {
            this.handleCreateLog();
        }
    }
};
</script>

<style lang="scss" scoped>
.daily-report-app {
    min-height: 100vh;
    background: linear-gradient(135deg, #ebf4ff 0%, #e0e7ff 100%);
    padding: 1.5rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.container {
    margin: 0 auto;
    max-width: 1600px;
}

.wrapper {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

.content-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    padding: 2rem;
    flex: 1;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 1.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;

    &::before {
        content: '';
        width: 4px;
        height: 1.5rem;
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        border-radius: 2px;
    }
}

.history-view {
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;

        p {
            margin: 0;
            font-size: 1.125rem;
        }
    }
}

// Responsive Design
@media (max-width: 1024px) {
    .wrapper {
        flex-direction: column;
    }

    .content-card {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .daily-report-app {
        padding: 1rem;
    }

    .content-card {
        padding: 1.5rem;
    }

    .section-title {
        font-size: 1.25rem;
    }
}
</style>
