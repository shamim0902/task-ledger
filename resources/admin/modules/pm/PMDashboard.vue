<template>
    <div class="pm-dashboard">
        <div class="dashboard-header">
            <div class="header-content">
                <div class="header-left">
                    <h1 class="dashboard-title">
                        <svg class="icon-large" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Project Manager Dashboard
                    </h1>
                    <p class="dashboard-subtitle">Track team updates, tasks, and blockers in real-time</p>
                </div>
                <div class="header-actions">
                    <button 
                        @click="showTeamFilter = !showTeamFilter"
                        :class="['filter-btn', { active: selectedTeamMembers.length > 0 && selectedTeamMembers.length < teamMembers.length }]"
                    >
                        {{ selectedTeamMembers.length === teamMembers.length ? 'All Team Members' : `${selectedTeamMembers.length} Selected` }}
                    </button>
                    <button 
                        @click="showBoardFilter = !showBoardFilter"
                        :class="['filter-btn', { active: selectedBoards.length > 0 && selectedBoards.length < boards.length }]"
                    >
                        {{ selectedBoards.length === boards.length || boards.length === 0 ? 'All Boards' : `${selectedBoards.length} Selected` }}
                    </button>
                    <div class="action-buttons">
                        <button class="action-btn" @click="exportCSV" :disabled="loading">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export CSV
                        </button>
                        <button class="action-btn" @click="exportPDF" :disabled="loading">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Export PDF
                        </button>
                        <button class="action-btn primary" @click="sendReminders" :disabled="loading">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Send Reminders
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Dropdowns -->
            <div v-if="showTeamFilter" class="filter-dropdown">
                <div class="filter-header">
                    <span>Select Team Members</span>
                    <button @click="showTeamFilter = false" class="close-filter">×</button>
                </div>
                <div class="filter-options">
                    <label class="filter-checkbox">
                        <input 
                            type="checkbox" 
                            :checked="selectedTeamMembers.length === teamMembers.length"
                            @change="toggleAllTeamMembers"
                        />
                        <span><strong>Select All</strong></span>
                    </label>
                    <label v-for="member in teamMembers" :key="member.id" class="filter-checkbox">
                        <input 
                            type="checkbox" 
                            :value="member.id" 
                            v-model="selectedTeamMembers"
                            @change="loadData"
                        />
                        <span>{{ member.name }}</span>
                    </label>
                </div>
            </div>
            <div v-if="showBoardFilter" class="filter-dropdown">
                <div class="filter-header">
                    <span>Select Boards</span>
                    <button @click="showBoardFilter = false" class="close-filter">×</button>
                </div>
                <div class="filter-options">
                    <label v-if="boards.length > 0" class="filter-checkbox">
                        <input 
                            type="checkbox" 
                            :checked="selectedBoards.length === boards.length"
                            @change="toggleAllBoards"
                        />
                        <span><strong>Select All</strong></span>
                    </label>
                    <label v-for="board in boards" :key="board.id" class="filter-checkbox">
                        <input 
                            type="checkbox" 
                            :value="board.id" 
                            v-model="selectedBoards"
                            @change="loadData"
                        />
                        <span>{{ board.title || board.name }}</span>
                    </label>
                    <div v-if="boards.length === 0" class="no-boards">
                        <p>No boards available</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card stat-blue">
                <div class="stat-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Updates Submitted</div>
                    <div class="stat-value">{{ summaryStats.updates_submitted || 0 }}</div>
                </div>
            </div>
            <div class="stat-card stat-green">
                <div class="stat-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Tasks Completed</div>
                    <div class="stat-value">{{ summaryStats.tasks_completed || 0 }}</div>
                </div>
            </div>
            <div class="stat-card stat-red">
                <div class="stat-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Blocked Tasks</div>
                    <div class="stat-value">{{ summaryStats.blocked_tasks || 0 }}</div>
                </div>
            </div>
            <div class="stat-card stat-orange">
                <div class="stat-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Missing Updates</div>
                    <div class="stat-value">{{ summaryStats.missing_updates || 0 }}</div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="dashboard-content">
            <!-- Team Activity Section -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Team Activity</h2>
                    <p class="section-subtitle">View detailed activity for each team member</p>
                </div>
                <TeamActivityTable 
                    :team-activity="teamActivity" 
                    :loading="loading"
                    :currentDate="currentDateString"
                    @date-change="handleDateChange"
                />
            </div>

            <!-- Task Overview Section -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Task Overview</h2>
                    <p class="section-subtitle">Monitor task progress and activity history</p>
                </div>
                <TaskOverview 
                    :tasks="taskOverview" 
                    :loading="loading"
                    @filter-change="handleTaskFilterChange"
                />
            </div>

            <!-- Analytics & Insights -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Analytics & Insights</h2>
                    <p class="section-subtitle">Visualize team performance and identify bottlenecks</p>
                </div>
                <AnalyticsSection 
                    :analytics="analytics"
                    :loading="loading"
                />
            </div>

            <!-- Blocked Tasks -->
            <div class="content-section" v-if="blockedTasks.length > 0">
                <div class="section-header">
                    <h2 class="section-title">Blocked Tasks</h2>
                    <p class="section-subtitle">Tasks that need immediate attention</p>
                </div>
                <BlockedTasksList 
                    :tasks="blockedTasks"
                    :loading="loading"
                />
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
        handleDateChange(date) {
            this.currentDateString = date;
            this.selectedDate = date;
            this.loadData();
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
    background: #f5f7fa;
    padding: 1.5rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.dashboard-header {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    position: relative;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-left {
    .dashboard-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;

        .icon-large {
            width: 2rem;
            height: 2rem;
            color: #4f46e5;
        }
    }

    .dashboard-subtitle {
        color: #6b7280;
        margin: 0;
        font-size: 0.9375rem;
    }
}

.header-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-left: auto;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 0.5rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
    white-space: nowrap;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover:not(:disabled) {
        border-color: #4f46e5;
        color: #4f46e5;
        background: #eef2ff;
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    &.primary {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;

        &:hover:not(:disabled) {
            background: #4338ca;
            border-color: #4338ca;
        }
    }
}

.filter-btn {
    padding: 0.625rem 1.25rem;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 0.5rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;

    &:hover {
        border-color: #4f46e5;
        color: #4f46e5;
    }

    &.active {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
    }
}

.filter-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 0.5rem;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    z-index: 50;
    max-height: 300px;
    overflow-y: auto;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    font-weight: 600;
    color: #1f2937;
}

.close-filter {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #6b7280;
    cursor: pointer;
    line-height: 1;

    &:hover {
        color: #1f2937;
    }
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.filter-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.375rem;

    &:hover {
        background: #f9fafb;
    }

    input[type="checkbox"] {
        width: 1rem;
        height: 1rem;
        cursor: pointer;
    }
}

.no-boards {
    padding: 1rem;
    text-align: center;
    color: #9ca3af;
    font-size: 0.875rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s, box-shadow 0.2s;

    &:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
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

        .stat-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
        }
    }

    &.stat-blue {
        .stat-icon {
            background: #dbeafe;
            color: #2563eb;
        }
        .stat-content .stat-value {
            color: #2563eb;
        }
    }

    &.stat-green {
        .stat-icon {
            background: #d1fae5;
            color: #10b981;
        }
        .stat-content .stat-value {
            color: #10b981;
        }
    }

    &.stat-red {
        .stat-icon {
            background: #fee2e2;
            color: #ef4444;
        }
        .stat-content .stat-value {
            color: #ef4444;
        }
    }

    &.stat-orange {
        .stat-icon {
            background: #fed7aa;
            color: #f97316;
        }
        .stat-content .stat-value {
            color: #f97316;
        }
    }
}

.dashboard-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.content-section {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.section-header {
    margin-bottom: 1.5rem;

    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 0.25rem 0;
    }

    .section-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }
}

@media (max-width: 768px) {
    .pm-dashboard {
        padding: 1rem;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
