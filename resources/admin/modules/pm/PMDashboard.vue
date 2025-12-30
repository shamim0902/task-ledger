<template>
    <div class="pm-dashboard">
        <!-- Top Navigation Bar -->
        <div class="pm-header">
            <div class="header-content">
                <div class="header-brand">
                    <div class="brand-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="brand-text">
                        <h1 class="brand-title">Project Manager</h1>
                        <p class="brand-subtitle">Team Dashboard</p>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="filter-group">
                        <div class="filter-wrapper">
                            <button 
                                @click.stop="showTeamFilter = !showTeamFilter"
                                :class="['filter-btn', { active: selectedTeamMembers.length > 0 && selectedTeamMembers.length < teamMembers.length }]"
                            >
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>{{ selectedTeamMembers.length === teamMembers.length ? 'All Team' : `${selectedTeamMembers.length} Selected` }}</span>
                            </button>
                            <transition name="dropdown">
                                <div v-if="showTeamFilter" class="filter-dropdown">
                                    <div class="filter-header">
                                        <span>Team Members</span>
                                        <button @click.stop="showTeamFilter = false" class="close-filter">×</button>
                                    </div>
                                    <div class="filter-options">
                                        <label class="filter-checkbox">
                                            <input type="checkbox" :checked="selectedTeamMembers.length === teamMembers.length" @change="toggleAllTeamMembers" />
                                            <span><strong>Select All</strong></span>
                                        </label>
                                        <label v-for="member in teamMembers" :key="member.id" class="filter-checkbox">
                                            <input type="checkbox" :value="member.id" v-model="selectedTeamMembers" @change="loadData" />
                                            <span>{{ member.name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </transition>
                        </div>
                        <div class="filter-wrapper">
                            <button 
                                @click.stop="showBoardFilter = !showBoardFilter"
                                :class="['filter-btn', { active: selectedBoards.length > 0 && selectedBoards.length < boards.length }]"
                            >
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span>{{ selectedBoards.length === boards.length || boards.length === 0 ? 'All Boards' : `${selectedBoards.length} Selected` }}</span>
                            </button>
                            <transition name="dropdown">
                                <div v-if="showBoardFilter" class="filter-dropdown">
                                    <div class="filter-header">
                                        <span>Boards</span>
                                        <button @click.stop="showBoardFilter = false" class="close-filter">×</button>
                                    </div>
                                    <div class="filter-options">
                                        <label v-if="boards.length > 0" class="filter-checkbox">
                                            <input type="checkbox" :checked="selectedBoards.length === boards.length" @change="toggleAllBoards" />
                                            <span><strong>Select All</strong></span>
                                        </label>
                                        <label v-for="board in boards" :key="board.id" class="filter-checkbox">
                                            <input type="checkbox" :value="board.id" v-model="selectedBoards" @change="loadData" />
                                            <span>{{ board.title || board.name }}</span>
                                        </label>
                                        <div v-if="boards.length === 0" class="no-boards">No boards available</div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                    <div class="action-group">
                        <button class="action-btn" @click="exportCSV" :disabled="loading" title="Export CSV">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </button>
                        <button class="action-btn" @click="exportPDF" :disabled="loading" title="Export PDF">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </button>
                        <button class="action-btn primary" @click="sendReminders" :disabled="loading" title="Send Reminders">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span>Reminders</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="pm-content">
            <!-- Date Filter (Top) -->
            <div class="date-filter-section">
                <div class="date-filter">
                    <button @click="goToPreviousDay" class="date-nav-btn" title="Previous Day">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div class="date-picker-wrapper">
                        <input
                            type="date"
                            v-model="currentDateString"
                            @change="handleDateChange"
                            class="date-input"
                        />
                        <div class="date-display" @click="showDatePicker = !showDatePicker">
                            {{ formattedDate }}
                            <svg class="calendar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <button @click="goToNextDay" class="date-nav-btn" title="Next Day">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <button @click="goToToday" class="today-btn">Today</button>
                </div>
            </div>

            <!-- Quick Stats Dashboard -->
            <div class="stats-dashboard">
                <div class="stat-card stat-primary">
                    <div class="stat-icon-wrapper">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ summaryStats.updates_submitted || 0 }}</div>
                        <div class="stat-label">Updates Submitted</div>
                    </div>
                </div>
                <div class="stat-card stat-success">
                    <div class="stat-icon-wrapper">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ summaryStats.tasks_completed || 0 }}</div>
                        <div class="stat-label">Tasks Completed</div>
                    </div>
                </div>
                <div class="stat-card stat-danger">
                    <div class="stat-icon-wrapper">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ summaryStats.blocked_tasks || 0 }}</div>
                        <div class="stat-label">Blocked Tasks</div>
                    </div>
                </div>
                <div class="stat-card stat-warning">
                    <div class="stat-icon-wrapper">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ summaryStats.missing_updates || 0 }}</div>
                        <div class="stat-label">Missing Updates</div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Sections -->
            <div class="dashboard-sections">
                <!-- Team Activity Section -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <div class="section-title-wrapper">
                            <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <div>
                                <h2 class="section-title">Team Activity</h2>
                                <p class="section-subtitle">Daily updates and progress</p>
                            </div>
                        </div>
                    </div>
                    <div class="section-content">
                        <TeamActivityTable 
                            :team-activity="teamActivity" 
                            :loading="loading"
                            :currentDate="currentDateString"
                            :show-date-filter="false"
                            @date-change="handleDateChange"
                        />
                    </div>
                </div>

                <!-- Task Overview Section -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <div class="section-title-wrapper">
                            <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <div>
                                <h2 class="section-title">Task Overview</h2>
                                <p class="section-subtitle">Monitor progress and activity</p>
                            </div>
                        </div>
                    </div>
                    <div class="section-content">
                        <TaskOverview 
                            :tasks="taskOverview" 
                            :loading="loading"
                            @filter-change="handleTaskFilterChange"
                        />
                    </div>
                </div>

                <!-- Analytics & Insights -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <div class="section-title-wrapper">
                            <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <div>
                                <h2 class="section-title">Analytics & Insights</h2>
                                <p class="section-subtitle">Performance metrics and trends</p>
                            </div>
                        </div>
                    </div>
                    <div class="section-content">
                        <AnalyticsSection 
                            :analytics="analytics"
                            :loading="loading"
                        />
                    </div>
                </div>

                <!-- Blocked Tasks -->
                <div class="dashboard-section" v-if="blockedTasks.length > 0">
                    <div class="section-header">
                        <div class="section-title-wrapper">
                            <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <h2 class="section-title">Blocked Tasks</h2>
                                <p class="section-subtitle">Requires immediate attention</p>
                            </div>
                        </div>
                    </div>
                    <div class="section-content">
                        <BlockedTasksList 
                            :tasks="blockedTasks"
                            :loading="loading"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import TeamActivityTable from './components/TeamActivityTable.vue';
import TaskOverview from './components/TaskOverview.vue';
import AnalyticsSection from './components/AnalyticsSection.vue';
import BlockedTasksList from './components/BlockedTasksList.vue';

export default {
    name: 'ProjectManagerDashboard',
    components: {
        TeamActivityTable,
        TaskOverview,
        AnalyticsSection,
        BlockedTasksList
    },
    data() {
        return {
            loading: false,
            selectedDate: 'today',
            currentDateString: new Date().toISOString().split('T')[0],
            selectedTeamMembers: [],
            selectedBoards: [],
            showTeamFilter: false,
            showBoardFilter: false,
            showDatePicker: false,
            teamMembers: [],
            boards: [],
            summaryStats: {
                updates_submitted: 0,
                tasks_completed: 0,
                blocked_tasks: 0,
                missing_updates: 0
            },
            teamActivity: [],
            taskOverview: [],
            analytics: {},
            blockedTasks: [],
            taskFilter: {
                status: 'all'
            }
        };
    },
    computed: {
        formattedDate() {
            const date = new Date(this.currentDateString);
            return date.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    },
    methods: {
        async loadData() {
            this.loading = true;
            try {
                const date = this.selectedDate === 'today' ? this.currentDateString : this.selectedDate;
                const params = {
                    date: date,
                    team_members: this.selectedTeamMembers.length > 0 ? this.selectedTeamMembers : 'all',
                    boards: this.selectedBoards.length > 0 ? this.selectedBoards : 'all'
                };

                // Load all data in parallel
                const [stats, activity, overview, analytics, blocked] = await Promise.all([
                    this.$get('pm/summary-stats', { date }),
                    this.$get('pm/team-activity', params),
                    this.$get('pm/task-overview', { ...params, status: this.taskFilter.status }),
                    this.$get('pm/task-analytics', params),
                    this.$get('pm/blocked-tasks', { date })
                ]);

                this.summaryStats = stats.all ? stats.all() : stats;
                this.teamActivity = activity.all ? activity.all() : activity;
                this.taskOverview = overview.all ? overview.all() : overview;
                this.analytics = analytics.all ? analytics.all() : analytics;
                this.blockedTasks = blocked.all ? blocked.all() : blocked;
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to load dashboard data'
                });
            } finally {
                this.loading = false;
            }
        },
        async loadTeamMembers() {
            try {
                const members = await this.$get('pm/team-members');
                this.teamMembers = members.all ? members.all() : members;
                this.selectedTeamMembers = this.teamMembers.map(m => m.id);
            } catch (error) {
                console.error('Error loading team members:', error);
            }
        },
        async loadBoards() {
            try {
                const boardsData = await this.$get('pm/boards');
                this.boards = boardsData.all ? boardsData.all() : boardsData;
                this.selectedBoards = this.boards.map(b => b.id);
            } catch (error) {
                console.error('Error loading boards:', error);
            }
        },
        handleTaskFilterChange(filter) {
            this.taskFilter = { ...this.taskFilter, ...filter };
            this.loadData();
        },
        handleDateChange(event) {
            let date;
            if (typeof event === 'string') {
                date = event;
            } else if (event?.target?.value) {
                date = event.target.value;
            } else {
                date = this.currentDateString;
            }
            this.currentDateString = date;
            this.selectedDate = date;
            this.loadData();
        },
        goToPreviousDay() {
            const date = new Date(this.currentDateString);
            date.setDate(date.getDate() - 1);
            this.currentDateString = date.toISOString().split('T')[0];
            this.handleDateChange(this.currentDateString);
        },
        goToNextDay() {
            const date = new Date(this.currentDateString);
            date.setDate(date.getDate() + 1);
            this.currentDateString = date.toISOString().split('T')[0];
            this.handleDateChange(this.currentDateString);
        },
        goToToday() {
            this.currentDateString = new Date().toISOString().split('T')[0];
            this.handleDateChange(this.currentDateString);
        },
        toggleAllTeamMembers(event) {
            if (event.target.checked) {
                this.selectedTeamMembers = this.teamMembers.map(m => m.id);
            } else {
                this.selectedTeamMembers = [];
            }
            this.loadData();
        },
        toggleAllBoards(event) {
            if (event.target.checked) {
                this.selectedBoards = this.boards.map(b => b.id);
            } else {
                this.selectedBoards = [];
            }
            this.loadData();
        },
        exportCSV() {
            try {
                const date = this.selectedDate === 'today' ? this.currentDateString : this.selectedDate;
                const csvData = this.generateCSV();
                const blob = new Blob([csvData], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `pm-dashboard-${date}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                this.$notify({
                    type: 'success',
                    text: 'CSV exported successfully'
                });
            } catch (error) {
                console.error('Error exporting CSV:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to export CSV'
                });
            }
        },
        exportPDF() {
            this.$notify({
                type: 'info',
                text: 'PDF export functionality will be available soon. For now, please use the browser print function (Ctrl/Cmd + P) and save as PDF.'
            });
        },
        async sendReminders() {
            try {
                this.loading = true;
                const date = this.selectedDate === 'today' ? this.currentDateString : this.selectedDate;
                const membersNeedingReminders = this.getMembersNeedingReminders();
                
                if (membersNeedingReminders.length === 0) {
                    this.$notify({
                        type: 'info',
                        text: 'No reminders needed - all team members are up to date!'
                    });
                    return;
                }

                // Show confirmation
                if (!confirm(`Send reminders to ${membersNeedingReminders.length} team member(s) with missing updates or blockers?`)) {
                    return;
                }

                // Get member IDs
                const memberIds = membersNeedingReminders.map(m => m.id);

                // Call API endpoint
                const response = await this.$post('pm/send-reminders', {
                    date: date,
                    team_members: memberIds
                });

                if (response.success) {
                    this.$notify({
                        type: 'success',
                        text: `Reminders sent to ${response.reminders_sent} team member(s)`
                    });
                    
                    if (response.errors && response.errors.length > 0) {
                        console.warn('Some reminders failed:', response.errors);
                    }
                } else {
                    throw new Error(response.message || 'Failed to send reminders');
                }
            } catch (error) {
                console.error('Error sending reminders:', error);
                this.$notify({
                    type: 'error',
                    text: error.message || 'Failed to send reminders'
                });
            } finally {
                this.loading = false;
            }
        },
        getMembersNeedingReminders() {
            const members = [];
            
            // Members with missing updates
            this.teamActivity.forEach(member => {
                if (!member.has_update) {
                    members.push({
                        id: member.user_id,
                        name: member.user_name,
                        reason: 'Missing update'
                    });
                } else if (member.blocked_tasks > 0) {
                    members.push({
                        id: member.user_id,
                        name: member.user_name,
                        reason: `${member.blocked_tasks} blocked task(s)`
                    });
                }
            });

            return members;
        },
        generateCSV() {
            const date = this.selectedDate === 'today' ? this.currentDateString : this.selectedDate;
            const rows = [];
            
            // Header
            rows.push('Team Activity Dashboard Report');
            rows.push(`Date: ${date}`);
            rows.push('');
            
            // Summary Stats
            rows.push('Summary Statistics');
            rows.push(`Updates Submitted,${this.summaryStats.updates_submitted || 0}`);
            rows.push(`Tasks Completed,${this.summaryStats.tasks_completed || 0}`);
            rows.push(`Blocked Tasks,${this.summaryStats.blocked_tasks || 0}`);
            rows.push(`Missing Updates,${this.summaryStats.missing_updates || 0}`);
            rows.push('');
            
            // Team Activity
            rows.push('Team Activity');
            rows.push('Team Member,Tasks Worked On,Completed,Blocked,Notes');
            this.teamActivity.forEach(member => {
                rows.push([
                    member.user_name,
                    member.tasks_worked_on,
                    member.completed_tasks,
                    member.blocked_tasks,
                    `"${(member.notes || '').replace(/"/g, '""')}"`
                ].join(','));
            });
            rows.push('');
            
            // Task Overview
            rows.push('Task Overview');
            rows.push('Task Title,Status,Assignee,Board,Hours,Story Points,Blocker Reason');
            this.taskOverview.forEach(task => {
                rows.push([
                    `"${task.title.replace(/"/g, '""')}"`,
                    task.status,
                    task.assignee?.name || 'Unassigned',
                    task.board?.title || 'N/A',
                    task.hours || 0,
                    task.story_points || 0,
                    `"${(task.blocker_reason || '').replace(/"/g, '""')}"`
                ].join(','));
            });
            
            return rows.join('\n');
        }
    },
    mounted() {
        this.loadTeamMembers();
        this.loadBoards();
        this.loadData();
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.filter-wrapper')) {
                this.showTeamFilter = false;
                this.showBoardFilter = false;
            }
        });
    },
    watch: {
        selectedDate() {
            this.loadData();
        },
        selectedTeamMembers() {
            this.loadData();
        },
        selectedBoards() {
            this.loadData();
        }
    }
};
</script>

<style lang="scss" scoped>
.pm-dashboard {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f8fafc 0%, #f1f5f9 100%);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

// Top Header
.pm-header {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.header-content {
    max-width: 1800px;
    margin: 0 auto;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-brand {
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
        flex-shrink: 0;

        svg {
            width: 1.5rem;
            height: 1.5rem;
        }
    }

    .brand-text {
        .brand-title {
            font-size: 1.125rem;
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

.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    flex: 1;
    justify-content: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: row;
    gap: 0.5rem;
    align-items: center;
    border: none;
}

.filter-wrapper {
    position: relative;
    display: inline-block;
    vertical-align: top;
    
    // Prevent overflow issues
    &:last-child .filter-dropdown {
        right: 0;
        left: auto;
    }
}

.filter-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.875rem;
    border: 1px solid #d1d5db;
    background: #f9fafb;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.8125rem;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        border-color: #6366f1;
        background: #eef2ff;
        color: #6366f1;
    }

    &.active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }
}

.filter-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.75rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    z-index: 1000;
    min-width: 220px;
    max-width: 300px;
    max-height: 320px;
    overflow-y: auto;
    overflow-x: hidden;
}

// Dropdown transition
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: #111827;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.close-filter {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #6b7280;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 1.5rem;
    height: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;

    &:hover {
        background: #f3f4f6;
        color: #111827;
    }
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.filter-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.8125rem;

    &:hover {
        background: #f9fafb;
    }

    input[type="checkbox"] {
        width: 0.875rem;
        height: 0.875rem;
        cursor: pointer;
    }
}

.no-boards {
    padding: 0.75rem;
    text-align: center;
    color: #9ca3af;
    font-size: 0.8125rem;
}

.action-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
    flex-shrink: 0;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.875rem;
    border: 1px solid #d1d5db;
    background: white;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.8125rem;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover:not(:disabled) {
        border-color: #6366f1;
        color: #6366f1;
        background: #eef2ff;
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    &.primary {
        background: #6366f1;
        color: white;
        border-color: #6366f1;

        &:hover:not(:disabled) {
            background: #4f46e5;
            border-color: #4f46e5;
        }
    }
}

// Main Content
.pm-content {
    max-width: 1800px;
    margin: 0 auto;
    padding: 0 1.5rem;
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
    padding: 1rem;
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
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;

        svg {
            width: 1.25rem;
            height: 1.25rem;
        }
    }

    .stat-content {
        flex: 1;
        min-width: 0;

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 0.125rem;
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            opacity: 0.7;
        }
    }

    &.stat-primary {
        border-left: 4px solid #3b82f6;
        .stat-icon-wrapper {
            background: #eff6ff;
            color: #3b82f6;
        }
        .stat-content .stat-value {
            color: #3b82f6;
        }
    }

    &.stat-success {
        border-left: 4px solid #10b981;
        .stat-icon-wrapper {
            background: #ecfdf5;
            color: #10b981;
        }
        .stat-content .stat-value {
            color: #10b981;
        }
    }

    &.stat-danger {
        border-left: 4px solid #ef4444;
        .stat-icon-wrapper {
            background: #fef2f2;
            color: #ef4444;
        }
        .stat-content .stat-value {
            color: #ef4444;
        }
    }

    &.stat-warning {
        border-left: 4px solid #f59e0b;
        .stat-icon-wrapper {
            background: #fffbeb;
            color: #f59e0b;
        }
        .stat-content .stat-value {
            color: #f59e0b;
        }
    }
}

// Date Filter Section (Top)
.date-filter-section {
    margin-bottom: 1.5rem;
}

.date-filter {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
}

.date-nav-btn {
    width: 2.5rem;
    height: 2.5rem;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: #6b7280;

    svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    &:hover {
        border-color: #4f46e5;
        color: #4f46e5;
        background: #eef2ff;
    }
}

.date-picker-wrapper {
    position: relative;
    flex: 1;
    max-width: 300px;

    .date-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 10;
        font-size: 0;
    }

    .date-display {
        padding: 0.625rem 1rem;
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        font-weight: 500;
        color: #374151;
        display: flex;
        align-items: center;
        justify-content: space-between;
        pointer-events: none;
        transition: all 0.2s;

        .calendar-icon {
            width: 1.25rem;
            height: 1.25rem;
            color: #6b7280;
        }
    }

    &:hover .date-display {
        border-color: #4f46e5;
    }
}

.today-btn {
    padding: 0.625rem 1.25rem;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;

    &:hover {
        background: #4338ca;
    }
}

// Dashboard Sections
.dashboard-sections {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.dashboard-section {
    background: white;
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: box-shadow 0.2s;

    &:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
}

.section-header {
    padding: 1rem 1.25rem;
    background: #f9fafb;
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
    font-size: 1rem;
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
    padding: 1.25rem;
}

// Responsive Design
@media (max-width: 1200px) {
    .pm-content {
        padding: 1rem;
    }
}

@media (max-width: 1024px) {
    .header-content {
        flex-wrap: wrap;
    }

    .header-actions {
        width: 100%;
        justify-content: space-between;
    }

    .filter-group {
        flex: 1;
        min-width: 0;
        border: none;
    }

    .action-group {
        flex-shrink: 0;
    }
}

@media (max-width: 768px) {
    .header-content {
        padding: 0.75rem 1rem;
        flex-direction: column;
        align-items: stretch;
    }

    .header-brand {
        justify-content: center;
        width: 100%;
    }

    .header-actions {
        flex-direction: column;
        width: 100%;
        gap: 0.75rem;
    }
    
    .filter-group {
        width: 100%;
        flex-direction: row;
        gap: 0.5rem;
        justify-content: flex-start;
        border: none;
    }

    .filter-wrapper {
        flex: 1;
        min-width: 0;
    }

    .filter-btn {
        width: 100%;
        justify-content: center;
    }
    
    .filter-dropdown {
        right: 0;
        left: 0;
        width: 100%;
        max-width: 100%;
    }

    .action-group {
        width: 100%;
        justify-content: stretch;
    }

    .action-btn {
        flex: 1;
        justify-content: center;
        min-width: 0;
    }

    .stats-dashboard {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .section-header {
        padding: 0.875rem 1rem;
    }

    .section-content {
        padding: 1rem;
    }
}
</style>
