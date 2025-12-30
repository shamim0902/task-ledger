<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content">
            <div class="modal-header">
                <div class="header-info">
                    <div class="member-avatar-large">
                        <span>{{ submission.user_initials }}</span>
                    </div>
                    <div>
                        <h3 class="modal-title">{{ submission.user_name }}</h3>
                        <p class="modal-subtitle">{{ formatDate(submission.log_date) }}</p>
                    </div>
                </div>
                <div class="header-actions">
                    <span v-if="submission.reviewed" class="status-badge reviewed">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Reviewed
                    </span>
                    <button @click="$emit('close')" class="close-btn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="modal-body">
                <div v-if="loading" class="loading-state">
                    <div class="spinner"></div>
                    <p>Loading details...</p>
                </div>
                <div v-else>
                    <!-- Additional Notes -->
                    <div v-if="submission.additional_notes" class="notes-section">
                        <h4 class="section-title">Additional Notes</h4>
                        <div class="notes-content">{{ submission.additional_notes }}</div>
                    </div>

                    <!-- Tasks List -->
                    <div class="tasks-section">
                        <div class="section-header">
                            <h4 class="section-title">Tasks ({{ submission.tasks.length }})</h4>
                            <button
                                v-if="!allTasksReviewed"
                                @click="$emit('mark-all-reviewed', submission.id)"
                                class="btn-mark-all"
                            >
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Mark All as Reviewed
                            </button>
                        </div>
                        <div v-if="submission.tasks.length === 0" class="empty-tasks">
                            <p>No tasks in this submission</p>
                        </div>
                        <div v-else class="tasks-list">
                            <div
                                v-for="task in submission.tasks"
                                :key="task.id"
                                :class="['task-item', { reviewed: task.reviewed }]"
                            >
                                <div class="task-header">
                                    <div class="task-title-row">
                                        <input
                                            v-if="!task.reviewed"
                                            type="checkbox"
                                            :checked="task.reviewed"
                                            @change="$emit('mark-item-reviewed', task.id)"
                                            class="task-checkbox"
                                        />
                                        <span v-else class="reviewed-checkmark">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                        <h5 class="task-title">{{ task.task_title }}</h5>
                                    </div>
                                    <span :class="['status-badge', getStatusClass(task.activity_type)]">
                                        {{ formatStatus(task.activity_type) }}
                                    </span>
                                </div>
                                <div class="task-details">
                                    <div class="detail-item">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ task.time_spent }}h</span>
                                    </div>
                                    <div class="detail-item">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        <span>{{ task.complete_weight }} pts</span>
                                    </div>
                                </div>
                                <div v-if="task.note" class="task-note">
                                    <strong>Note:</strong> {{ task.note }}
                                </div>
                                <div v-if="task.block_reason" class="task-blocker">
                                    <strong>Blocker:</strong> {{ task.block_reason }}
                                </div>
                                <div v-if="task.reviewed && task.reviewed_at" class="task-reviewed-info">
                                    Reviewed on {{ formatDateTime(task.reviewed_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button
                    v-if="!submission.reviewed"
                    @click="$emit('mark-reviewed', submission.id)"
                    class="btn-primary"
                >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Mark Submission as Reviewed
                </button>
                <button @click="$emit('close')" class="btn-secondary">
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SubmissionDetail',
    props: {
        submission: {
            type: Object,
            default: null,
        },
        loading: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['close', 'mark-reviewed', 'mark-item-reviewed', 'mark-all-reviewed'],
    computed: {
        allTasksReviewed() {
            if (!this.submission || !this.submission.tasks) return false;
            return this.submission.tasks.every(task => task.reviewed);
        },
    },
    methods: {
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        },
        formatDateTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },
        formatStatus(status) {
            const statusMap = {
                'in-progress': 'In Progress',
                'completed': 'Completed',
                'blocked': 'Blocked',
            };
            return statusMap[status] || status;
        },
        getStatusClass(status) {
            const classMap = {
                'in-progress': 'status-in-progress',
                'completed': 'status-completed',
                'blocked': 'status-blocked',
            };
            return classMap[status] || '';
        },
    },
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
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 0.75rem;
    animation: fadeIn 0.2s ease;
    box-sizing: border-box;
    overflow: hidden;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    border-radius: 0.75rem;
    width: 100%;
    max-width: 900px;
    max-height: 90vh;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.2s ease;
    box-sizing: border-box;
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
    justify-content: space-between;
    align-items: center;
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
}

.header-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.member-avatar-large {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;

    span {
        color: white;
        font-size: 1.125rem;
        font-weight: 600;
    }
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
}

.modal-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 500;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &.reviewed {
        background: #d1fae5;
        color: #065f46;
    }
}

.close-btn {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f9fafb;
    border: none;
    border-radius: 0.375rem;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;

    svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    &:hover {
        background: #f3f4f6;
        color: #111827;
    }
}

.modal-body {
    padding: 1rem;
    overflow-y: auto;
    overflow-x: hidden;
    flex: 1;
    min-height: 0;
    box-sizing: border-box;
}

.loading-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #10b981;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    p {
        margin: 0;
        font-size: 0.875rem;
    }
}

.notes-section {
    margin-bottom: 1rem;
    padding: 0.875rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}

.section-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.75rem 0;
}

.notes-content {
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.6;
    white-space: pre-wrap;
}

.tasks-section {
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
}

.btn-mark-all {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #10b981;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: white;
    cursor: pointer;
    transition: all 0.2s;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        background: #059669;
    }
}

.empty-tasks {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.tasks-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.task-item {
    padding: 0.875rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    transition: all 0.2s;
    box-sizing: border-box;

    &:hover {
        border-color: #d1d5db;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }

    &.reviewed {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }
}

.task-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.task-title-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}

.task-checkbox {
    cursor: pointer;
    width: 1.125rem;
    height: 1.125rem;
}

.reviewed-checkmark {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.125rem;
    height: 1.125rem;
    color: #10b981;

    svg {
        width: 1rem;
        height: 1rem;
    }
}

.task-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
    flex: 1;
}

.status-badge {
    &.status-in-progress {
        background: #dbeafe;
        color: #1e40af;
    }

    &.status-completed {
        background: #d1fae5;
        color: #065f46;
    }

    &.status-blocked {
        background: #fee2e2;
        color: #991b1b;
    }
}

.task-details {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 0.75rem;
    font-size: 0.8125rem;
    color: #6b7280;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;

    svg {
        width: 1rem;
        height: 1rem;
    }
}

.task-note,
.task-blocker {
    padding: 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    margin-bottom: 0.5rem;

    strong {
        font-weight: 600;
    }
}

.task-note {
    background: #f9fafb;
    color: #374151;
}

.task-blocker {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.task-reviewed-info {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #e5e7eb;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
}

.btn-primary,
.btn-secondary {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;

    svg {
        width: 1rem;
        height: 1rem;
    }
}

.btn-primary {
    background: #10b981;
    color: white;

    &:hover {
        background: #059669;
    }
}

.btn-secondary {
    background: white;
    border: 1px solid #d1d5db;
    color: #374151;

    &:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
}
</style>

