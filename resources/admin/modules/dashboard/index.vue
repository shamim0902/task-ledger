<template>
    <div class="daily-report-app">
        <div class="container">
            <!-- Header -->
            <div class="header-card">
                <div class="header-content">
                    <div class="header-left">
                        <h1 class="title">
                            <svg class="icon-large" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Daily Report System
                        </h1>
                        <p class="subtitle">Track your daily progress and task completion</p>
                    </div>
                    <div class="header-right">
                        <div class="date-label">Today's Date</div>
                        <div class="date-value">
                            <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ todayDate }}
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card stat-indigo">
                        <div class="stat-label">Total Tasks</div>
                        <div class="stat-value">{{ tasks.length }}</div>
                    </div>
                    <div class="stat-card stat-green">
                        <div class="stat-label">Hours Today</div>
                        <div class="stat-value">{{ totalHoursToday }}h</div>
                    </div>
                    <div class="stat-card stat-purple">
                        <div class="stat-label">Story Points</div>
                        <div class="stat-value">{{ totalWeightCompleted }}</div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="nav-card">
                <div class="nav-tabs">
                    <button @click="currentView = 'create'" :class="['nav-tab', { active: currentView === 'create' }]">
                        <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Log
                    </button>
                    <button @click="currentView = 'history'"
                        :class="['nav-tab', { active: currentView === 'history' }]">
                        <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View History
                    </button>
                </div>
            </div>
            <div class="wrapper" style="display: flex; gap: 24px; align-items: flex-start;">
                <!-- Create Log View -->
                <div v-if="currentView === 'create'" class="content-card" style="width: 50%;">
                    <h2 class="section-title">Select Your Daily Tasks</h2>

                    <!-- Task Search and Add -->
                    <div class="task-search-section">
                        <div class="search-wrapper">
                            <label class="form-label">Search or Select Task</label>
                            <div class="search-input-wrapper">
                                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" v-model="taskSearchQuery" @focus="showTaskDropdown = true"
                                    placeholder="Search tasks..." class="search-input" />
                            </div>

                            <!-- Task Dropdown -->
                            <div v-if="showTaskDropdown && filteredTasks.length > 0" class="task-dropdown">
                                <div v-for="task in filteredTasks" :key="task.id" @click="selectTask(task)"
                                    class="dropdown-item">
                                    <div class="dropdown-item-content">
                                        <span class="dropdown-task-title">{{ task.title }} {{ task.subtasks.length ?
                                            `(${task.subtasks.length} subtasks)` : '' }}</span>
                                        <span class="dropdown-task-board">{{ task.board?.title }}</span>
                                    </div>
                                    <span class="weight-badge-small">{{ task.weight }} pts</span>
                                </div>
                            </div>
                        </div>

                        <button @click="showAddTaskModal = true" class="add-task-button">
                            <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New Task
                        </button>
                    </div>

                    <!-- Selected Task Details -->
                    <div v-if="selectedTask" class="selected-task-section">
                        <div class="selected-task-header">
                            <div class="selected-task-info">
                                <h3 class="selected-task-title">{{ selectedTask.title }}</h3>
                                <div class="task-meta">
                                    <span class="meta-badge">{{ selectedTask.board?.title }}</span>
                                    <span
                                        :class="['status-badge', selectedTask.status === 'In Progress' ? 'status-progress' : 'status-todo']">
                                        {{ selectedTask.status }}
                                    </span>
                                    <span class="weight-badge">{{ selectedTask.weight }} pts</span>
                                </div>
                            </div>
                            <!-- add today log button -->
                            <!-- <el-button @click="addTaskToTodayLog" class="add-today-log-button">
                                <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Add to Today's Log
                            </el-button> -->
                            <button @click="clearSelectedTask" class="clear-button">
                                <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Subtasks Section -->
                        <div class="subtasks-section">
                            <div class="subtasks-header">
                                <h4 class="subtasks-title">Subtasks</h4>
                                <button @click="showAddSubtaskInput = !showAddSubtaskInput" class="add-subtask-button">
                                    <svg class="icon-tiny" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Subtask
                                </button>
                            </div>

                            <!-- Add Subtask Input -->
                            <div v-if="showAddSubtaskInput" class="add-subtask-form">
                                <div class="form-group">
                                    <input type="text" v-model="newSubtaskTitle" @keyup.enter="addSubtask"
                                        placeholder="Enter subtask title..." class="subtask-input" />
                                    <el-input-number size="large" v-model="weight" :min="1" :max="10" @change="handleChange"
                                        placeholder="Weight" />
                                </div>
                                <div class="subtask-actions">
                                    <button @click="addSubtask(selectedTask)" class="btn-save">Save</button>
                                    <button @click="cancelAddSubtask" class="btn-cancel">Cancel</button>
                                </div>
                            </div>

                            <!-- Subtasks List -->
                            <div v-if="selectedTask.subtasks.length > 0" class="subtasks-list">
                                <div v-for="subtask in selectedTask.subtasks" :key="subtask.id"
                                    :class="['subtask-item', { completed: subtask.status === 'closed' }]">
                                    <label class="subtask-checkbox">
                                        <input disabled type="checkbox" v-model="subtask.status"
                                            @change="updateSubtaskStatus(subtask)" />
                                        <span class="checkmark"></span>
                                    </label>
                                    <span class="subtask-text">{{ subtask.title }}</span>
                                    <span class="weight-badge-small">{{ subtask.weight }} pts</span>
                                    <span v-if="subtask.status === 'closed'" class="completed-badge">✓ Completed</span>
                                    <div v-else>
                                        <el-button v-if="subtask?.weight <= 0" @click="markSubtaskCompleted(subtask)" class="btn-delete">Mark Completed</el-button>
                                        <el-button v-else @click="addSubtaskToTodayLog(subtask)" class="btn-delete">Add Today Log</el-button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="no-subtasks">
                                <p>No subtasks yet. Click "Add Subtask" to create one.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="log-entry-form">

                    <div class="daily-log-header">
                        <h2 class="daily-log-title">Daily Log</h2>
                        <p class="log-date">Today: {{ getTodayDateString }}</p>
                    </div>

                    <TaskList v-if="todayLog.tasks.length" :tasks="todayLog.tasks" @toggle-task="toggleTask" />

                    <div class="form-group">
                        <label class="form-label">Daily Log</label>
                        <textarea v-model="todayLog.notes" placeholder="Describe what you worked on today..." rows="6"
                            class="form-textarea"></textarea>
                    </div>

                    <button @click="handleCreateLog" class="submit-button">
                        <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Submit Daily Log
                    </button>
                </div>
            </div>

            <!-- History View -->
            <div v-if="currentView === 'history'" class="content-card">
                <div class="history-header">
                    <h2 class="section-title">Log History</h2>
                    <div class="filter-controls">
                        <label class="filter-label">Filter by Date:</label>
                        <input type="date" v-model="filterDate" class="filter-input" />
                        <button v-if="filterDate" @click="filterDate = ''" class="clear-filter-button">
                            Clear
                        </button>
                    </div>
                </div>

                <div class="history-list">
                    <div v-if="filteredLogs.length === 0" class="empty-state">
                        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p>No logs found for the selected criteria</p>
                    </div>

                    <div v-for="log in filteredLogs" :key="log.id" class="log-card">
                        <div class="log-header">
                            <div class="log-main-info">
                                <h3 class="log-task-title">{{ getTaskById(log.taskId).title }}</h3>
                                <div class="log-meta">
                                    <span class="meta-item">
                                        <svg class="icon-tiny" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ formatDate(log.date) }}
                                    </span>
                                    <span class="meta-item">
                                        <svg class="icon-tiny" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ log.hours }}h
                                    </span>
                                    <span class="board-badge">{{ getTaskById(log.taskId).board }}</span>
                                </div>
                            </div>
                            <span class="weight-badge">{{ getTaskById(log.taskId).weight }} pts</span>
                        </div>
                        <div class="log-content">{{ log.log }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Task Modal -->
        <div v-if="showAddTaskModal" class="modal-overlay" @click.self="showAddTaskModal = false">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Add New Task</h3>
                    <button @click="showAddTaskModal = false" class="modal-close">
                        <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Task Title</label>
                        <input type="text" v-model="newTask.title" placeholder="Enter task title..."
                            class="form-input" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Board</label>
                        <input type="text" v-model="newTask.board" placeholder="e.g., Backend, Frontend..."
                            class="form-input" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Weight (Story Points)</label>
                        <input type="number" v-model="newTask.weight" min="1" placeholder="e.g., 5"
                            class="form-input" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select v-model="newTask.status" class="form-select">
                            <option value="To Do">To Do</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Done">Done</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button @click="showAddTaskModal = false" class="btn-cancel">Cancel</button>
                    <button @click="addNewTask" class="btn-primary">Add Task</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { add } from 'lodash';
import TaskList from './TaskList.vue';
export default {
    name: 'DailyReportApp',
    components: {
        TaskList,
    },
    data() {
        return {
            tasks: [],
            logs: [
                { id: 1, taskId: 1, date: '2024-12-24', log: 'Completed JWT implementation and started working on refresh token logic', hours: 4 },
                { id: 2, taskId: 3, date: '2024-12-24', log: 'Identified the issue with webhook callbacks, working on fix', hours: 3 },
                { id: 3, taskId: 1, date: '2024-12-25', log: 'Finished refresh token implementation and added password reset functionality', hours: 5 },
            ],
            todayLog: {
                tasks: [
                ],
                notes: '',
            },
            selectedTask: null,
            currentView: 'create',
            filterDate: '',
            taskSearchQuery: '',
            showTaskDropdown: false,
            showAddTaskModal: false,
            showAddSubtaskInput: false,
            newSubtaskTitle: '',
            weight: 1,
            newTask: {
                title: '',
                board: '',
                weight: '',
                status: 'To Do'
            }
        };
    },
    computed: {
        todayDate() {
            return new Date().toLocaleDateString();
        },
        getTodayDateString() {
            return new Date().toISOString().split('T')[0];
        },
        totalHoursToday() {
            const today = this.getTodayDateString;
            return this.logs
                .filter(log => log.date === today)
                .reduce((sum, log) => sum + log.hours, 0);
        },
        totalWeightCompleted() {
            const loggedTaskIds = new Set(this.logs.map(log => log.taskId));
            return this.tasks
                .filter(task => loggedTaskIds.has(task.id))
                .reduce((sum, task) => sum + task.weight, 0);
        },
        filteredLogs() {
            if (!this.filterDate) return this.logs;
            return this.logs.filter(log => log.date === this.filterDate);
        },
        filteredTasks() {
            if (!this.taskSearchQuery.trim()) return this.tasks;
            const query = this.taskSearchQuery.toLowerCase();
            return this.tasks.filter(task =>
                task?.title?.toLowerCase()?.includes(query) ||
                task?.board?.title?.toLowerCase()?.includes(query)
            );
        },
        currentSubtasks() {
            if (!this.selectedTask) return [];
            return this.subtasks.filter(st => st.taskId === this.selectedTask.id);
        }
    },
    methods: {

        markSubtaskCompleted(subtask) {
            this.$patch(`subtasks/completed/${subtask.id}`).then(res => {
                subtask.status = 'closed';
            });
        },
        getTasks() {
            this.$get('tasks').then(res => {
                this.tasks = res.all();
            });
        },
        selectTask(task) {
            this.selectedTask = task;
            this.showTaskDropdown = false;
            this.taskSearchQuery = task.title;
        },
        clearSelectedTask() {
            this.selectedTask = null;
            this.taskSearchQuery = '';
            this.logText = '';
            this.hoursSpent = '';
        },
        addSubtask(selectedTask) {
            if (!this.newSubtaskTitle.trim()) {
                alert('Please enter a subtask title');
                return;
            }

            this.$post('subtasks', {
                task_id: selectedTask.id,
                title: this.newSubtaskTitle,
                weight: this.weight,
                group_id: selectedTask.group_id,
                board_id: selectedTask.board_id,
            }).then(res => {
                const data = res;
                console.log({ data });

                // example usage
                this.selectedTask.subtasks.push(data);
                this.newSubtaskTitle = '';
                this.showAddSubtaskInput = false;
            });

        },
        cancelAddSubtask() {
            this.newSubtaskTitle = '';
            this.showAddSubtaskInput = false;
        },

        getTodayLogs() {
            this.$get('today-logs').then(res => {
                console.log(res?.all());
                const data = res.all();
                this.todayLog = {
                    notes: data?.additional_notes || '',
                    tasks: data?.log_items || [],
                };
            })
        },

        addSubtaskToTodayLog(subtask) {
            this.todayLog.tasks.push({
                id: subtask.id,
                title: subtask.title,
                weight: subtask.weight,
                hours: 0,
                status: 'in-progress',
            });

            this.handleCreateLog();
        },
        updateSubtaskStatus(subtask) {
            // this.$patch('subtasks', subtask).then(res => {
            //     this.subtasks = res.all();
            // });
        },
        addNewTask() {
            if (!this.newTask.title.trim() || !this.newTask.board.trim() || !this.newTask.weight) {
                alert('Please fill in all fields');
                return;
            }

            const task = {
                id: this.tasks.length + 1,
                title: this.newTask.title,
                board: this.newTask.board,
                weight: parseInt(this.newTask.weight),
                status: this.newTask.status
            };

            this.tasks.push(task);
            this.showAddTaskModal = false;
            this.newTask = { title: '', board: '', weight: '', status: 'To Do' };
            alert('Task added successfully!');
        },
        handleCreateLog() {
            this.$post('logs', this.todayLog).then(res => {
                this.logs = res.all();
                this.$notify({
                    type: 'success',
                    text: 'Log created successfully'
                })
            });
        },
        getTaskById(taskId) {
            return this.tasks.find(t => t.id === taskId);
        },
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        }
    },
    mounted() {
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-wrapper')) {
                this.showTaskDropdown = false;
            }
        });
        this.getTasks();
        this.getTodayLogs();
        if(!window.taskLedgerAdmin.hasLogForToday) {
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
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.container {   
    margin: 0 auto;
}

// Icons
.icon-large {
    width: 2rem;
    height: 2rem;
}

.icon-small {
    width: 1.25rem;
    height: 1.25rem;
}

.icon-tiny {
    width: 1rem;
    height: 1rem;
}

// Header Card
.header-card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.header-left {
    .title {
        font-size: 1.875rem;
        font-weight: bold;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0 0 0.25rem 0;

        svg {
            color: #4f46e5;
        }
    }

    .subtitle {
        color: #6b7280;
        margin: 0;
    }
}

.header-right {
    text-align: right;

    .date-label {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .date-value {
        font-size: 1.25rem;
        font-weight: 600;
        color: #4f46e5;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        justify-content: flex-end;
    }
}

// Stats
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-top: 1.5rem;
}

.stat-card {
    border-radius: 0.5rem;
    padding: 1rem;

    .stat-label {
        font-size: 0.875rem;
        font-weight: 500;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
        margin-top: 0.25rem;
    }

    &.stat-indigo {
        background: #eef2ff;

        .stat-label {
            color: #4f46e5;
        }

        .stat-value {
            color: #4338ca;
        }
    }

    &.stat-green {
        background: #f0fdf4;

        .stat-label {
            color: #16a34a;
        }

        .stat-value {
            color: #15803d;
        }
    }

    &.stat-purple {
        background: #faf5ff;

        .stat-label {
            color: #9333ea;
        }

        .stat-value {
            color: #7e22ce;
        }
    }
}

// Navigation
.nav-card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.nav-tabs {
    display: flex;
    border-bottom: 1px solid #e5e7eb;
}

.nav-tab {
    flex: 1;
    padding: 1rem 1.5rem;
    font-weight: 500;
    color: #6b7280;
    background: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;

    &:hover {
        background: #f9fafb;
    }

    &.active {
        color: #4f46e5;
        background: #eef2ff;
        border-bottom: 2px solid #4f46e5;
    }
}

// Content Card
.content-card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1f2937;
    margin: 0 0 1.5rem 0;
}

// Task Search Section
.task-search-section {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.search-wrapper {
    flex: 1;
    position: relative;

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }
}

.search-input-wrapper {
    position: relative;

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1.25rem;
        height: 1.25rem;
        color: #9ca3af;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 3rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: all 0.2s;

        &:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
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
    border-radius: 0.5rem;
    margin-top: 0.25rem;
    max-height: 300px;
    overflow-y: auto;
    z-index: 50;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.dropdown-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.2s;

    &:hover {
        background: #f9fafb;
    }

    &:not(:last-child) {
        border-bottom: 1px solid #f3f4f6;
    }
}

.dropdown-item-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;

    .dropdown-task-title {
        font-weight: 500;
        color: #1f2937;
    }

    .dropdown-task-board {
        font-size: 0.875rem;
        color: #6b7280;
    }
}

.weight-badge-small {
    background: #f3e8ff;
    color: #6b21a8;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.add-task-button {
    padding: 0.75rem 1.5rem;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    margin-top: 1.75rem;

    &:hover {
        background: #4338ca;
    }
}

// Selected Task Section
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
}

.selected-task-info {
    flex: 1;

    .selected-task-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 0.5rem 0;
    }
}

.task-meta {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.meta-badge {
    background: #e0e7ff;
    color: #4338ca;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;

    &.status-progress {
        background: #dbeafe;
        color: #1e40af;
    }

    &.status-todo {
        background: #fef3c7;
        color: #92400e;
    }
}

.weight-badge {
    background: #f3e8ff;
    color: #6b21a8;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
}

.clear-button {
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 0.5rem;
    padding: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        background: #fecaca;
    }
}

// Subtasks Section
.subtasks-section {
    background: #f9fafb;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.subtasks-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;

    .subtasks-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
}

.add-subtask-button {
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.2s;

    &:hover {
        background: #4338ca;
    }
}

.add-subtask-form {
    margin-bottom: 1rem;

    .el-input-number {
        width: 300px;
    }

    .form-group {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .subtask-input {
        width: 100%;
        padding: 4px 12px;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;

        &:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
    }
}

.subtask-actions {
    display: flex;
    gap: 0.5rem;

    .btn-save {
        background: #10b981;
        color: white;
        border: none;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;

        &:hover {
            background: #059669;
        }
    }

    .btn-cancel {
        background: #6b7280;
        color: white;
        border: none;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;

        &:hover {
            background: #4b5563;
        }
    }
}

.subtasks-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.subtask-item {
    background: white;
    padding: 0.75rem;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.2s;

    &.completed {
        opacity: 0.6;

        .subtask-text {
            text-decoration: line-through;
        }
    }
}

.subtask-checkbox {
    position: relative;
    display: flex;
    align-items: center;
    cursor: pointer;
    opacity: 0.5;

    input[type="checkbox"] {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
        opacity: 0;
        position: absolute;

        &:checked+.checkmark {
            background: #4f46e5;
            border-color: #4f46e5;

            &::after {
                display: block;
            }
        }
    }

    .checkmark {
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #d1d5db;
        border-radius: 0.25rem;
        background: white;
        position: relative;
        transition: all 0.2s;

        &::after {
            content: '';
            position: absolute;
            display: none;
            left: 0.35rem;
            top: 0.1rem;
            width: 0.35rem;
            height: 0.6rem;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
    }
}

.subtask-text {
    flex: 1;
    color: #374151;
    font-size: 0.875rem;
}

.completed-badge {
    background: #d1fae5;
    color: #065f46;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.no-subtasks {
    text-align: center;
    padding: 2rem 1rem;
    color: #6b7280;
    font-size: 0.875rem;
}

// Log Entry Form
.log-entry-form {
    border-radius: 12px;
    padding: 1.5rem;
    background: white;
    width: calc(50% - 1.5rem);

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: all 0.2s;

        &:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
    }

    .form-textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 1rem;
        resize: vertical;
        font-family: inherit;
        transition: all 0.2s;

        &:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
    }
}

.submit-button {
    width: 100%;
    padding: 0.75rem 1.5rem;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s;
    margin-top: 1rem;

    &:hover {
        background: #059669;
    }
}

// History View
.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.filter-controls {
    display: flex;
    align-items: center;
    gap: 0.75rem;

    .filter-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
    }

    .filter-input {
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.875rem;

        &:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
    }

    .clear-filter-button {
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;

        &:hover {
            background: #fecaca;
        }
    }
}

.history-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        opacity: 0.5;
    }

    p {
        margin: 0;
        font-size: 1rem;
    }
}

.log-card {
    background: #f9fafb;
    border-radius: 0.5rem;
    padding: 1.25rem;
    border-left: 4px solid #4f46e5;
    transition: all 0.2s;

    &:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
}

.log-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.log-main-info {
    flex: 1;

    .log-task-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 0.5rem 0;
    }
}

.log-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .board-badge {
        background: #e0e7ff;
        color: #4338ca;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
}

.log-content {
    color: #374151;
    line-height: 1.6;
    font-size: 0.9375rem;
}

// Modal
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
    z-index: 100;
    padding: 1rem;
}

.modal-content {
    background: white;
    border-radius: 0.75rem;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem;
        transition: all 0.2s;

        &:hover {
            color: #374151;
        }
    }
}

.modal-body {
    padding: 1.5rem;

    .form-group {
        margin-bottom: 1rem;

        &:last-child {
            margin-bottom: 0;
        }
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: all 0.2s;

        &:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
    }

    .form-select {
        background: white;
        cursor: pointer;
    }
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;

    .btn-cancel {
        background: #f3f4f6;
        color: #374151;
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;

        &:hover {
            background: #e5e7eb;
        }
    }

    .btn-primary {
        background: #4f46e5;
        color: white;
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;

        &:hover {
            background: #4338ca;
        }
    }
}

// Responsive Design
@media (max-width: 768px) {
    .daily-report-app {
        padding: 1rem;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-right {
        text-align: left;
        margin-top: 1rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .task-search-section {
        flex-direction: column;
    }

    .history-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .filter-controls {
        width: 100%;
        flex-wrap: wrap;
    }
}
</style>