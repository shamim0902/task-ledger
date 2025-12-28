<template>
    <div class="team-activity-table">
        <!-- Date Filter -->
        <div class="date-filter">
            <button @click="goToPreviousDay" class="date-nav-btn" title="Previous Day">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <div class="date-picker-wrapper">
                <input
                    type="date"
                    v-model="selectedDate"
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

        <div v-if="loading" class="loading-state">
            <p>Loading team activity...</p>
        </div>
        <div v-else-if="teamActivity.length === 0" class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <p>No team activity found for the selected date</p>
        </div>
        <div v-else class="table-container">
            <table class="activity-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th>Team Member</th>
                        <th>Tasks Worked On</th>
                        <th>Completed</th>
                        <th>Blocked</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="member in teamActivity" :key="member.user_id">
                        <tr 
                            @click="toggleExpand(member.user_id)"
                            :class="{ 
                                'no-update': !member.has_update,
                                'expanded': expandedRows.includes(member.user_id),
                                'clickable': member.has_update && member.tasks && member.tasks.length > 0
                            }"
                        >
                            <td class="expand-icon">
                                <svg 
                                    v-if="member.has_update && member.tasks && member.tasks.length > 0"
                                    class="chevron"
                                    :class="{ 'expanded': expandedRows.includes(member.user_id) }"
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </td>
                            <td class="member-cell">
                                <div class="member-info">
                                    <div class="avatar" :class="{ 'no-update': !member.has_update }">
                                        {{ member.user_initials }}
                                    </div>
                                    <div class="member-details">
                                        <div class="member-name">{{ member.user_name }}</div>
                                        <div v-if="!member.has_update" class="no-update-badge">No update today</div>
                                    </div>
                                </div>
                            </td>
                            <td class="stat-cell">
                                <div class="stat-item">
                                    <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ member.tasks_worked_on }}</span>
                                </div>
                            </td>
                            <td class="stat-cell">
                                <div class="stat-item stat-completed">
                                    <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ member.completed_tasks }}</span>
                                </div>
                            </td>
                            <td class="stat-cell">
                                <div v-if="member.blocked_tasks > 0" class="stat-item stat-blocked">
                                    <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>{{ member.blocked_tasks }}</span>
                                </div>
                                <span v-else class="no-blocked">-</span>
                            </td>
                            <td class="notes-cell">
                                <div class="notes-content" :title="member.notes">
                                    {{ member.notes || '-' }}
                                </div>
                            </td>
                        </tr>
                        <!-- Expanded Task Details Row -->
                        <tr 
                            v-if="expandedRows.includes(member.user_id) && member.tasks && member.tasks.length > 0"
                            class="task-details-row"
                        >
                            <td colspan="6" class="task-details-cell">
                                <div class="task-details-container">
                                    <h4 class="task-details-title">Task Activity Log</h4>
                                    <div class="tasks-list">
                                        <div
                                            v-for="task in member.tasks"
                                            :key="task.id"
                                            class="task-item"
                                            :class="`task-${task.status}`"
                                        >
                                            <div class="task-item-header">
                                                <div class="task-item-left">
                                                    <span class="task-id">TASK-{{ task.task_id }}</span>
                                                    <span class="task-title-text">{{ task.title }}</span>
                                                </div>
                                                <div class="task-item-right">
                                                    <span class="task-status-badge" :class="`badge-${task.status}`">
                                                        {{ formatStatus(task.status) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div v-if="task.blocker_reason" class="blocker-note">
                                                <strong>Blocker:</strong> {{ task.blocker_reason }}
                                            </div>
                                            <div class="task-meta-info">
                                                <span v-if="task.hours" class="meta-item">
                                                    <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ task.hours }}h
                                                </span>
                                                <span v-if="task.story_points" class="meta-item">
                                                    {{ task.story_points }} pts
                                                </span>
                                                <span v-if="task.board" class="meta-item board-tag">
                                                    {{ task.board.title }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    name: 'TeamActivityTable',
    props: {
        teamActivity: {
            type: Array,
            default: () => []
        },
        loading: {
            type: Boolean,
            default: false
        },
        currentDate: {
            type: String,
            default: () => new Date().toISOString().split('T')[0]
        }
    },
    emits: ['date-change'],
    data() {
        return {
            expandedRows: [],
            selectedDate: this.currentDate,
            showDatePicker: false
        };
    },
    computed: {
        formattedDate() {
            const date = new Date(this.selectedDate);
            return date.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    },
    watch: {
        currentDate(newDate) {
            this.selectedDate = newDate;
        }
    },
    methods: {
        toggleExpand(userId) {
            const index = this.expandedRows.indexOf(userId);
            if (index > -1) {
                this.expandedRows.splice(index, 1);
            } else {
                this.expandedRows.push(userId);
            }
        },
        handleDateChange() {
            this.$emit('date-change', this.selectedDate);
        },
        goToPreviousDay() {
            const date = new Date(this.selectedDate);
            date.setDate(date.getDate() - 1);
            this.selectedDate = date.toISOString().split('T')[0];
            this.handleDateChange();
        },
        goToNextDay() {
            const date = new Date(this.selectedDate);
            date.setDate(date.getDate() + 1);
            this.selectedDate = date.toISOString().split('T')[0];
            this.handleDateChange();
        },
        goToToday() {
            this.selectedDate = new Date().toISOString().split('T')[0];
            this.handleDateChange();
        },
        formatStatus(status) {
            const statusMap = {
                'in-progress': 'In Progress',
                'completed': 'Completed',
                'blocked': 'Blocked'
            };
            return statusMap[status] || status;
        }
    }
};
</script>

<style lang="scss" scoped>
.team-activity-table {
    width: 100%;
}

.date-filter {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
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
        background: white;
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

.loading-state,
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        opacity: 0.4;
        color: #9ca3af;
    }

    p {
        margin: 0;
        font-size: 0.9375rem;
    }
}

.table-container {
    overflow-x: auto;
}

.activity-table {
    width: 100%;
    border-collapse: collapse;

    thead {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;

        th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    }

    tbody {
        tr {
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s;

            &:hover {
                background: #f9fafb;
            }

            &.no-update {
                opacity: 0.7;
                background: #fef3c7;
            }

            &.clickable {
                cursor: pointer;

                &:hover {
                    background: #f3f4f6;
                }
            }

            &.expanded {
                background: #eff6ff;
                border-bottom: none;
            }
        }

        td {
            padding: 1rem;
            vertical-align: middle;
        }
    }
}

.expand-icon {
    text-align: center;
    width: 40px;

    .chevron {
        width: 1.25rem;
        height: 1.25rem;
        color: #6b7280;
        transition: transform 0.2s;

        &.expanded {
            transform: rotate(90deg);
        }
    }
}

.task-details-row {
    background: #f9fafb !important;

    &:hover {
        background: #f9fafb !important;
    }
}

.task-details-cell {
    padding: 0 !important;
    background: #f9fafb;
}

.task-details-container {
    padding: 1.5rem;
}

.task-details-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 1rem 0;
}

.tasks-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.task-item {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    transition: all 0.2s;

    &.task-completed {
        border-color: #d1fae5;
        background: #f0fdf4;
    }

    &.task-in-progress {
        border-color: #dbeafe;
        background: #eff6ff;
    }

    &.task-blocked {
        border-color: #fee2e2;
        background: #fef2f2;
    }
}

.task-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    gap: 1rem;
}

.task-item-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
    min-width: 0;
}

.task-id {
    font-weight: 700;
    color: #4f46e5;
    font-size: 0.875rem;
    white-space: nowrap;
}

.task-title-text {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.9375rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.task-item-right {
    flex-shrink: 0;
}

.task-status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;

    &.badge-completed {
        background: #d1fae5;
        color: #065f46;
    }

    &.badge-in-progress {
        background: #dbeafe;
        color: #1e40af;
    }

    &.badge-blocked {
        background: #fee2e2;
        color: #991b1b;
    }
}

.blocker-note {
    padding: 0.75rem;
    background: #fee2e2;
    border-radius: 0.375rem;
    color: #991b1b;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    border-left: 3px solid #ef4444;

    strong {
        color: #dc2626;
    }
}

.task-meta-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: 0.5rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;

    .meta-icon {
        width: 1rem;
        height: 1rem;
    }

    &.board-tag {
        background: #e0e7ff;
        color: #4338ca;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
    }
}

.member-cell {
    .member-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;

        &.no-update {
            background: #9ca3af;
        }
    }

    .member-details {
        flex: 1;
        min-width: 0;

        .member-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .no-update-badge {
            font-size: 0.75rem;
            color: #92400e;
            font-weight: 500;
        }
    }
}

.stat-cell {
    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;

        .stat-icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        &.stat-completed {
            color: #10b981;
        }

        &.stat-blocked {
            color: #ef4444;
        }
    }

    .no-blocked {
        color: #9ca3af;
    }
}

.notes-cell {
    max-width: 300px;

    .notes-content {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #6b7280;
        font-size: 0.875rem;
    }
}

@media (max-width: 768px) {
    .date-filter {
        flex-wrap: wrap;
    }

    .table-container {
        font-size: 0.875rem;
    }

    .activity-table {
        thead th,
        tbody td {
            padding: 0.75rem 0.5rem;
        }
    }

    .member-cell .avatar {
        width: 2rem;
        height: 2rem;
        font-size: 0.75rem;
    }

    .task-item-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
