<template>
    <div class="submission-list">
        <div class="list-header">
            <div class="header-left">
                <h3 class="list-title">Submissions</h3>
                <span class="submission-count">{{ pagination.total }} total</span>
            </div>
            <div class="header-actions">
                <label class="select-all-checkbox">
                    <input
                        type="checkbox"
                        :checked="allSelected"
                        @change="$emit('select-all', $event.target.checked)"
                    />
                    <span>Select All</span>
                </label>
            </div>
        </div>

        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading submissions...</p>
        </div>
        <div v-else-if="submissions.length === 0" class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p>No submissions found</p>
        </div>
        <div v-else class="submissions">
            <div
                v-for="submission in submissions"
                :key="submission.id"
                :class="['submission-item', { reviewed: submission.reviewed }]"
            >
                <div class="item-checkbox">
                    <input
                        type="checkbox"
                        :checked="selectedSubmissions.includes(submission.id)"
                        @change="$emit('toggle-selection', submission.id)"
                        @click.stop
                    />
                </div>
                <div class="item-content" @click="$emit('select-submission', submission)">
                    <div class="item-header">
                        <div class="member-info">
                            <div class="member-avatar-small">
                                <span>{{ submission.user_initials }}</span>
                            </div>
                            <div>
                                <div class="member-name">{{ submission.user_name }}</div>
                                <div class="submission-date">{{ formatDate(submission.log_date) }}</div>
                            </div>
                        </div>
                        <div class="item-status">
                            <span v-if="submission.reviewed" class="status-badge reviewed">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Reviewed
                            </span>
                            <span v-else class="status-badge pending">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Pending
                            </span>
                        </div>
                    </div>
                    <div class="item-stats">
                        <div class="stat">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span>{{ submission.task_count }} task{{ submission.task_count !== 1 ? 's' : '' }}</span>
                        </div>
                        <div v-if="!submission.reviewed" class="stat">
                            <span>{{ submission.reviewed_task_count }}/{{ submission.task_count }} reviewed</span>
                        </div>
                    </div>
                    <div v-if="submission.additional_notes" class="item-notes">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10m-7 4h7" />
                        </svg>
                        <span>{{ truncateText(submission.additional_notes, 100) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total_pages > 1" class="pagination">
            <button
                @click="$emit('load-page', pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="page-btn"
            >
                Previous
            </button>
            <span class="page-info">
                Page {{ pagination.current_page }} of {{ pagination.total_pages }}
            </span>
            <button
                @click="$emit('load-page', pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.total_pages"
                class="page-btn"
            >
                Next
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SubmissionList',
    props: {
        submissions: {
            type: Array,
            default: () => [],
        },
        loading: {
            type: Boolean,
            default: false,
        },
        selectedSubmissions: {
            type: Array,
            default: () => [],
        },
        pagination: {
            type: Object,
            default: () => ({
                current_page: 1,
                per_page: 20,
                total: 0,
                total_pages: 0,
            }),
        },
    },
    emits: ['select-submission', 'select-all', 'toggle-selection', 'load-page'],
    computed: {
        allSelected() {
            return this.submissions.length > 0 && 
                   this.submissions.every(s => this.selectedSubmissions.includes(s.id));
        },
    },
    methods: {
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        },
        truncateText(text, maxLength) {
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        },
    },
};
</script>

<style lang="scss" scoped>
.submission-list {
    width: 100%;
}

.list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.list-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.submission-count {
    font-size: 0.75rem;
    color: #6b7280;
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 0.75rem;
}

.select-all-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;

    input[type="checkbox"] {
        cursor: pointer;
    }
}

.loading-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;
    font-size: 0.875rem;

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
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 1rem;
        opacity: 0.4;
        color: #9ca3af;
    }

    p {
        margin: 0;
        font-size: 0.875rem;
    }
}

.submissions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.submission-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    transition: all 0.2s;

    &:hover {
        border-color: #d1d5db;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }

    &.reviewed {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }
}

.item-checkbox {
    padding-top: 0.25rem;
    flex-shrink: 0;

    input[type="checkbox"] {
        cursor: pointer;
    }
}

.item-content {
    flex: 1;
    cursor: pointer;
}

.item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.member-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.member-avatar-small {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;

    span {
        color: white;
        font-size: 0.75rem;
        font-weight: 600;
    }
}

.member-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.125rem;
}

.submission-date {
    font-size: 0.75rem;
    color: #6b7280;
}

.item-status {
    flex-shrink: 0;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;

    svg {
        width: 0.875rem;
        height: 0.875rem;
    }

    &.reviewed {
        background: #d1fae5;
        color: #065f46;
    }

    &.pending {
        background: #fed7aa;
        color: #9a3412;
    }
}

.item-stats {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
    font-size: 0.8125rem;
    color: #6b7280;

    .stat {
        display: flex;
        align-items: center;
        gap: 0.375rem;

        svg {
            width: 1rem;
            height: 1rem;
        }
    }
}

.item-notes {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.5rem;
    background: #f9fafb;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    color: #6b7280;

    svg {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
        margin-top: 0.125rem;
    }
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.page-btn {
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;

    &:hover:not(:disabled) {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
}

.page-info {
    font-size: 0.875rem;
    color: #6b7280;
}
</style>

