<template>
    <div class="log-history">
        <!-- Date Filter -->
        <div class="history-filters">
            <div class="date-range-picker">
                <label class="filter-label">Filter by Date</label>
                <div class="date-inputs">
                    <input
                        type="date"
                        v-model="startDate"
                        @change="loadHistory"
                        class="date-input"
                        placeholder="Start Date"
                    />
                    <span class="date-separator">to</span>
                    <input
                        type="date"
                        v-model="endDate"
                        @change="loadHistory"
                        class="date-input"
                        placeholder="End Date"
                    />
                </div>
                <button @click="resetDateFilter" class="reset-btn">Reset</button>
            </div>
            <div class="quick-filters">
                <button
                    v-for="filter in quickFilters"
                    :key="filter.value"
                    @click="applyQuickFilter(filter.value)"
                    :class="['quick-filter-btn', { active: activeQuickFilter === filter.value }]"
                >
                    {{ filter.label }}
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading log history...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="logs.length === 0" class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3>No logs found</h3>
            <p>No logs match your current filter criteria.</p>
        </div>

        <!-- Logs List -->
        <div v-else class="logs-list">
            <div
                v-for="log in logs"
                :key="log.id"
                class="log-card"
            >
                <div class="log-header">
                    <div class="log-date-section">
                        <div class="log-date">
                            <svg class="date-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ formatDate(log.log_date) }}
                        </div>
                        <div class="log-stats">
                            <span class="stat-badge">
                                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                {{ log.tasks_count || 0 }} tasks
                            </span>
                            <span class="stat-badge">
                                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ log.total_hours || 0 }}h
                            </span>
                            <span class="stat-badge">
                                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                {{ log.total_points || 0 }} pts
                            </span>
                        </div>
                    </div>
                    <div class="log-actions">
                        <button @click="toggleExpand(log.id)" class="expand-btn" :title="expandedLogs.includes(log.id) ? 'Collapse' : 'Expand'">
                            <svg
                                class="expand-icon"
                                :class="{ expanded: expandedLogs.includes(log.id) }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <button @click="exportLog(log)" class="export-btn" title="Export Log">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Expanded Content -->
                <div v-if="expandedLogs.includes(log.id)" class="log-details">
                    <div v-if="log.additional_notes" class="log-notes">
                        <h4 class="notes-title">Notes</h4>
                        <p class="notes-content">{{ log.additional_notes }}</p>
                    </div>

                    <div v-if="log.tasks && log.tasks.length > 0" class="log-tasks">
                        <h4 class="tasks-title">Tasks</h4>
                        <div class="tasks-list">
                            <div
                                v-for="task in log.tasks"
                                :key="task.id"
                                class="task-item"
                                :class="`task-${task.status}`"
                            >
                                <div class="task-header">
                                    <span class="task-title">{{ task.title }}</span>
                                    <span class="task-status-badge" :class="`badge-${task.status}`">
                                        {{ formatStatus(task.status) }}
                                    </span>
                                </div>
                                <div class="task-meta">
                                    <span v-if="task.hours" class="meta-item">
                                        <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ task.hours }}h
                                    </span>
                                    <span v-if="task.complete_weight" class="meta-item">
                                        {{ task.complete_weight }} pts
                                    </span>
                                    <span v-if="task.note" class="meta-item note">
                                        {{ task.note }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="pagination">
            <button
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="page-btn"
            >
                Previous
            </button>
            <span class="page-info">
                Page {{ currentPage }} of {{ totalPages }}
            </span>
            <button
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="page-btn"
            >
                Next
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'LogHistory',
    data() {
        const today = new Date();
        const lastWeek = new Date();
        lastWeek.setDate(today.getDate() - 7);
        const lastMonth = new Date();
        lastMonth.setMonth(today.getMonth() - 1);

        return {
            loading: false,
            logs: [],
            startDate: lastWeek.toISOString().split('T')[0],
            endDate: today.toISOString().split('T')[0],
            activeQuickFilter: 'week',
            expandedLogs: [],
            currentPage: 1,
            perPage: 10,
            totalPages: 1,
            quickFilters: [
                { label: 'Today', value: 'today' },
                { label: 'This Week', value: 'week' },
                { label: 'This Month', value: 'month' },
                { label: 'All Time', value: 'all' }
            ]
        };
    },
    mounted() {
        this.loadHistory();
    },
    methods: {
        async loadHistory() {
            this.loading = true;
            try {
                const params = {
                    start_date: this.startDate,
                    end_date: this.endDate,
                    page: this.currentPage,
                    per_page: this.perPage
                };

                const response = await this.$get('logs/history', params);
                const data = response.all ? response.all() : response;

                this.logs = data.logs || [];
                this.totalPages = data.total_pages || 1;
                this.currentPage = data.current_page || 1;
            } catch (error) {
                console.error('Error loading history:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to load log history'
                });
            } finally {
                this.loading = false;
            }
        },
        applyQuickFilter(filter) {
            this.activeQuickFilter = filter;
            const today = new Date();
            let start, end;

            switch (filter) {
                case 'today':
                    start = today;
                    end = today;
                    break;
                case 'week':
                    start = new Date();
                    start.setDate(today.getDate() - 7);
                    end = today;
                    break;
                case 'month':
                    start = new Date();
                    start.setMonth(today.getMonth() - 1);
                    end = today;
                    break;
                case 'all':
                    start = null;
                    end = null;
                    break;
            }

            if (start && end) {
                this.startDate = start.toISOString().split('T')[0];
                this.endDate = end.toISOString().split('T')[0];
            } else {
                this.startDate = '';
                this.endDate = '';
            }

            this.currentPage = 1;
            this.loadHistory();
        },
        resetDateFilter() {
            const today = new Date();
            const lastWeek = new Date();
            lastWeek.setDate(today.getDate() - 7);
            this.startDate = lastWeek.toISOString().split('T')[0];
            this.endDate = today.toISOString().split('T')[0];
            this.activeQuickFilter = 'week';
            this.currentPage = 1;
            this.loadHistory();
        },
        toggleExpand(logId) {
            const index = this.expandedLogs.indexOf(logId);
            if (index > -1) {
                this.expandedLogs.splice(index, 1);
            } else {
                this.expandedLogs.push(logId);
            }
        },
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.loadHistory();
            }
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        formatStatus(status) {
            const statusMap = {
                'in-progress': 'In Progress',
                'completed': 'Completed',
                'blocked': 'Blocked'
            };
            return statusMap[status] || status;
        },
        exportLog(log) {
            // Export functionality - can be enhanced later
            const data = {
                date: log.log_date,
                notes: log.additional_notes,
                tasks: log.tasks || []
            };
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `log-${log.log_date}.json`;
            a.click();
            URL.revokeObjectURL(url);
        }
    }
};
</script>

<style lang="scss" scoped>
.log-history {
    width: 100%;
}

.history-filters {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding: 0.875rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}

.date-range-picker {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;

    .filter-label {
        font-weight: 600;
        color: #374151;
        font-size: 0.8125rem;
        white-space: nowrap;
    }

    .date-inputs {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .date-input {
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.8125rem;
        transition: all 0.2s;
        background: white;

        &:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }
    }

    .date-separator {
        color: #6b7280;
        font-size: 0.8125rem;
    }

    .reset-btn {
        padding: 0.5rem 0.875rem;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-weight: 500;
        font-size: 0.8125rem;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s;

        &:hover {
            border-color: #6366f1;
            color: #6366f1;
            background: #f9fafb;
        }
    }
}

.quick-filters {
    display: flex;
    gap: 0.375rem;
    flex-wrap: wrap;
}

.quick-filter-btn {
    padding: 0.375rem 0.75rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.8125rem;

    &:hover {
        border-color: #6366f1;
        color: #6366f1;
        background: #f9fafb;
    }

    &.active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }
}

.loading-state {
    text-align: center;
    padding: 2rem 1rem;

    .spinner {
        width: 2rem;
        height: 2rem;
        border: 3px solid #e5e7eb;
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 0.75rem;
    }

    p {
        color: #6b7280;
        margin: 0;
        font-size: 0.875rem;
    }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 0.75rem;
        opacity: 0.4;
        color: #9ca3af;
    }

    h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.375rem 0;
    }

    p {
        margin: 0;
        font-size: 0.875rem;
    }
}

.logs-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.log-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 0.875rem;
    transition: all 0.2s;

    &:hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border-color: #d1d5db;
    }
}

.log-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
}

.log-date-section {
    flex: 1;
    min-width: 0;
}

.log-date {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
    line-height: 1.3;

    .date-icon {
        width: 1rem;
        height: 1rem;
        color: #6366f1;
        flex-shrink: 0;
    }
}

.log-stats {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.stat-badge {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    background: #f3f4f6;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    line-height: 1.3;

    .stat-icon {
        width: 0.875rem;
        height: 0.875rem;
        flex-shrink: 0;
    }
}

.log-actions {
    display: flex;
    gap: 0.375rem;
    flex-shrink: 0;
}

.expand-btn,
.export-btn {
    width: 1.875rem;
    height: 1.875rem;
    border: 1px solid #d1d5db;
    background: white;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: #6b7280;
    padding: 0;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        border-color: #6366f1;
        color: #6366f1;
        background: #f9fafb;
    }
}

.expand-icon {
    transition: transform 0.2s;

    &.expanded {
        transform: rotate(180deg);
    }
}

.log-details {
    margin-top: 0.875rem;
    padding-top: 0.875rem;
    border-top: 1px solid #e5e7eb;
}

.log-notes {
    margin-bottom: 0.875rem;
}

.notes-title,
.tasks-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.notes-content {
    padding: 0.625rem 0.75rem;
    background: #f9fafb;
    border-radius: 0.375rem;
    color: #374151;
    line-height: 1.5;
    margin: 0;
    font-size: 0.8125rem;
}

.log-tasks {
    margin-top: 0.875rem;
}

.tasks-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.task-item {
    padding: 0.625rem 0.75rem;
    background: #f9fafb;
    border-radius: 0.375rem;
    border-left: 3px solid #e5e7eb;

    &.task-completed {
        border-left-color: #10b981;
        background: #f0fdf4;
    }

    &.task-in-progress {
        border-left-color: #3b82f6;
        background: #eff6ff;
    }

    &.task-blocked {
        border-left-color: #ef4444;
        background: #fef2f2;
    }
}

.task-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.375rem;
    gap: 0.5rem;
}

.task-title {
    font-weight: 600;
    color: #111827;
    flex: 1;
    font-size: 0.8125rem;
    line-height: 1.3;
    min-width: 0;
}

.task-status-badge {
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    white-space: nowrap;
    flex-shrink: 0;

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

.task-meta {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    font-size: 0.75rem;
    color: #6b7280;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    line-height: 1.3;

    .meta-icon {
        width: 0.875rem;
        height: 0.875rem;
        flex-shrink: 0;
    }

    &.note {
        font-style: italic;
    }
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.75rem;
    margin-top: 1rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 0.5rem;
}

.page-btn {
    padding: 0.5rem 0.875rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-weight: 500;
    font-size: 0.8125rem;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;

    &:hover:not(:disabled) {
        border-color: #6366f1;
        color: #6366f1;
        background: #f9fafb;
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
}

.page-info {
    font-weight: 500;
    font-size: 0.8125rem;
    color: #6b7280;
}

@media (max-width: 768px) {
    .history-filters {
        padding: 0.75rem;
    }

    .date-range-picker {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .log-header {
        flex-direction: column;
        gap: 0.5rem;
    }

    .log-actions {
        align-self: flex-end;
    }
}
</style>

