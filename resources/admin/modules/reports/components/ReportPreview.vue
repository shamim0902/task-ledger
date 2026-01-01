<template>
    <div class="report-preview">
        <div class="preview-card">
            <div class="preview-header">
                <h3 class="preview-title">Report Preview</h3>
                <div class="preview-actions">
                    <button
                        @click="$emit('send-to-admin')"
                        :disabled="sending"
                        class="action-btn"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>{{ sending ? 'Sending...' : 'Send to Admin' }}</span>
                    </button>
                    <button
                        @click="$emit('download')"
                        class="action-btn"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Download CSV</span>
                    </button>
                </div>
            </div>

            <div v-if="generating" class="loading-state">
                <div class="spinner"></div>
                <p>Generating report...</p>
            </div>

            <div v-else-if="reportData" class="preview-content">
                <!-- Employee Info -->
                <div class="preview-section">
                    <h4 class="section-title">Employee Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span class="info-value">{{ reportData.employee?.name || 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value">{{ reportData.employee?.email || 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Period:</span>
                            <span class="info-value">
                                {{ reportData.date_range?.start }} to {{ reportData.date_range?.end }}
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Timeframe:</span>
                            <span class="info-value">{{ reportData.timeframe || 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="preview-section">
                    <h4 class="section-title">Summary Statistics</h4>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value">{{ reportData.summary?.total_submissions || 0 }}</div>
                            <div class="stat-label">Total Submissions</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">{{ reportData.summary?.total_tasks || 0 }}</div>
                            <div class="stat-label">Tasks Worked On</div>
                        </div>
                        <div class="stat-card success">
                            <div class="stat-value">{{ reportData.summary?.completed_tasks || 0 }}</div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <div class="stat-card danger">
                            <div class="stat-value">{{ reportData.summary?.blocked_tasks || 0 }}</div>
                            <div class="stat-label">Blocked</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">{{ reportData.summary?.total_hours || 0 }}h</div>
                            <div class="stat-label">Total Hours</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">{{ reportData.summary?.total_points || 0 }}</div>
                            <div class="stat-label">Story Points</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">{{ reportData.summary?.completion_rate || 0 }}%</div>
                            <div class="stat-label">Completion Rate</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">{{ reportData.summary?.average_hours_per_day || 0 }}h</div>
                            <div class="stat-label">Avg Hours/Day</div>
                        </div>
                    </div>
                </div>

                <!-- Task Details -->
                <div v-if="reportData.tasks && reportData.tasks.length > 0" class="preview-section task-details-section">
                    <h4 class="section-title task-details-title">Task Details ({{ reportData.tasks.length }})</h4>
                    <div class="tasks-list">
                        <div
                            v-for="(task, index) in reportData.tasks"
                            :key="index"
                            class="task-item"
                        >
                            <div class="task-single-row">
                                <span class="task-title">{{ task.title }}</span>
                                <div class="task-meta">
                                    <span v-if="task.hours > 0" class="meta-badge">{{ task.hours }}h</span>
                                    <span v-if="task.points > 0" class="meta-badge">{{ task.points }}pts</span>
                                    <span v-if="task.date" class="meta-date">{{ task.date }}</span>
                                </div>
                                <span :class="['task-status', `status-${task.status}`]">
                                    {{ task.status }}
                                </span>
                                <span v-if="task.note" class="task-note-inline" :title="task.note">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="12" y1="18" x2="12" y2="12"></line>
                                        <line x1="9" y1="15" x2="15" y2="15"></line>
                                    </svg>
                                </span>
                                <span v-if="task.blocker_reason" class="task-blocker-inline" :title="task.blocker_reason">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ReportPreview',
    props: {
        reportData: {
            type: Object,
            default: null,
        },
        generating: {
            type: Boolean,
            default: false,
        },
        sending: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['send-to-admin', 'download'],
};
</script>

<style lang="scss" scoped>
.report-preview {
    width: 100%;
}

.preview-card {
    background: #ffffff;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;
    padding: 0.875rem 1rem;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    padding-bottom: 0.625rem;
    border-bottom: 1px solid #e5e7eb;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.preview-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.preview-actions {
    display: flex;
    gap: 0.375rem;
    flex-wrap: wrap;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    background: #667eea;
    color: #ffffff;
    border: none;
    border-radius: 0.25rem;
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;

    svg {
        width: 0.875rem;
        height: 0.875rem;
    }

    &:hover:not(:disabled) {
        background: #5568d3;
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
}

.loading-state {
    text-align: center;
    padding: 1.5rem;
    color: #6b7280;
    font-size: 0.8125rem;
}

.spinner {
    width: 1.5rem;
    height: 1.5rem;
    border: 2px solid #e5e7eb;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 0.75rem;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.preview-content {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

.preview-section {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

.section-title {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 0.625rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.info-label {
    font-size: 0.6875rem;
    color: #6b7280;
    font-weight: 500;
}

.info-value {
    font-size: 0.8125rem;
    color: #111827;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.625rem;
}

.stat-card {
    padding: 0.625rem 0.5rem;
    background: #f9fafb;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;
    text-align: center;

    &.success {
        border-color: #10b981;
        background: #f0fdf4;
    }

    &.danger {
        border-color: #ef4444;
        background: #fef2f2;
    }
}

.stat-value {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.125rem;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.6875rem;
    color: #6b7280;
}

.task-details-section {
    gap: 0.5rem;
}

.task-details-title {
    margin-bottom: 0.5rem;
}

.tasks-list {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.task-item {
    padding: 0.5rem 0.625rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.25rem;
    background: #f9fafb;
}

.task-single-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
}

.task-title {
    font-size: 0.75rem;
    font-weight: 500;
    color: #111827;
    flex: 1;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}

.task-meta {
    display: flex;
    gap: 0.375rem;
    align-items: center;
    flex-shrink: 0;
}

.meta-badge {
    font-size: 0.625rem;
    color: #6b7280;
    background: #ffffff;
    padding: 0.125rem 0.375rem;
    border-radius: 0.1875rem;
    border: 1px solid #e5e7eb;
    font-weight: 500;
    white-space: nowrap;
}

.meta-date {
    font-size: 0.625rem;
    color: #9ca3af;
    white-space: nowrap;
}

.task-status {
    padding: 0.125rem 0.375rem;
    border-radius: 0.1875rem;
    font-size: 0.625rem;
    font-weight: 500;
    text-transform: capitalize;
    white-space: nowrap;
    flex-shrink: 0;
    line-height: 1.4;

    &.status-completed {
        background: #d1fae5;
        color: #065f46;
    }

    &.status-blocked {
        background: #fee2e2;
        color: #991b1b;
    }

    &.status-in-progress {
        background: #dbeafe;
        color: #1e40af;
    }
}

.task-note-inline {
    display: flex;
    align-items: center;
    color: #6b7280;
    cursor: help;
    flex-shrink: 0;

    svg {
        width: 0.75rem;
        height: 0.75rem;
    }
}

.task-blocker-inline {
    display: flex;
    align-items: center;
    color: #ef4444;
    cursor: help;
    flex-shrink: 0;

    svg {
        width: 0.75rem;
        height: 0.75rem;
    }
}
</style>

