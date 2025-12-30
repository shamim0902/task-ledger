<template>
    <div class="review-dashboard">
        <!-- Top Navigation Bar -->
        <div class="review-header">
            <div class="header-content">
                <div class="header-brand">
                    <div class="brand-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="brand-text">
                        <h1 class="brand-title">Submissions</h1>
                        <p class="brand-subtitle">Review submitted daily logs</p>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="action-group">
                        <button 
                            v-if="selectedSubmissions.length > 0" 
                            class="action-btn primary" 
                            @click="bulkMarkReviewed"
                            :disabled="bulkReviewing"
                        >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Mark {{ selectedSubmissions.length }} as Reviewed</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="review-content">
            <!-- Date Range Filter -->
            <div class="date-range-section">
                <div class="date-range-filter">
                    <label>Date Range:</label>
                    <input
                        type="date"
                        v-model="startDate"
                        @change="loadSubmissions"
                        class="date-input"
                    />
                    <span>to</span>
                    <input
                        type="date"
                        v-model="endDate"
                        @change="loadSubmissions"
                        class="date-input"
                    />
                </div>
            </div>

            <!-- Member List Sidebar -->
            <div class="review-layout">
                <div class="member-sidebar">
                    <MemberList
                        :members="members"
                        :selected-member-id="selectedMemberId"
                        :loading="loadingMembers"
                        @select-member="handleMemberSelect"
                    />
                </div>

                <!-- Submissions List -->
                <div class="submissions-main">
                    <SubmissionList
                        :submissions="submissions"
                        :loading="loading"
                        :selected-submissions="selectedSubmissions"
                        :pagination="pagination"
                        @select-submission="handleSubmissionSelect"
                        @select-all="handleSelectAll"
                        @toggle-selection="handleToggleSelection"
                        @load-page="handlePageChange"
                    />

                    <!-- Submission Detail Modal -->
                    <SubmissionDetail
                        v-if="selectedSubmission"
                        :submission="selectedSubmission"
                        :loading="loadingDetails"
                        @close="closeSubmissionDetail"
                        @mark-reviewed="handleMarkReviewed"
                        @mark-item-reviewed="handleMarkItemReviewed"
                        @mark-all-reviewed="handleMarkAllReviewed"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import MemberList from './components/MemberList.vue';
import SubmissionList from './components/SubmissionList.vue';
import SubmissionDetail from './components/SubmissionDetail.vue';

export default {
    name: 'ReviewDashboard',
    components: {
        MemberList,
        SubmissionList,
        SubmissionDetail,
    },
    data() {
        return {
            loading: false,
            loadingMembers: false,
            loadingDetails: false,
            bulkReviewing: false,
            members: [],
            submissions: [],
            selectedMemberId: null,
            selectedMemberName: null,
            selectedSubmission: null,
            selectedSubmissions: [],
            reviewedFilter: 'all', // 'all', 'reviewed', 'unreviewed' - Default to 'all' to show everything
            showMemberFilter: false,
            showStatusFilter: false,
            startDate: '', // No default date filter - show all submissions
            endDate: '',
            pagination: {
                current_page: 1,
                per_page: 20,
                total: 0,
                total_pages: 0,
            },
        };
    },
    mounted() {
        this.loadMembers();
        this.loadSubmissions();
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.filter-wrapper')) {
                this.showMemberFilter = false;
                this.showStatusFilter = false;
            }
        });
    },
    methods: {
        getDateDaysAgo(days) {
            const date = new Date();
            date.setDate(date.getDate() - days);
            return date.toISOString().split('T')[0];
        },
        async loadMembers() {
            this.loadingMembers = true;
            try {
                const response = await this.$get('review/members');
                this.members = response.all ? response.all() : response;
            } catch (error) {
                console.error('Error loading members:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to load members',
                });
            } finally {
                this.loadingMembers = false;
            }
        },
        async loadSubmissions(page = 1) {
            this.loading = true;
            try {
                const params = {
                    page,
                    per_page: this.pagination.per_page,
                    reviewed: this.reviewedFilter,
                };
                
                // Only add date params if they have values
                if (this.startDate && this.startDate.trim() !== '') {
                    params.start_date = this.startDate;
                }
                if (this.endDate && this.endDate.trim() !== '') {
                    params.end_date = this.endDate;
                }
                
                if (this.selectedMemberId) {
                    params.member_id = this.selectedMemberId;
                }

                const response = await this.$get('review/submissions', params);
                const data = response.all ? response.all() : response;
                
                this.submissions = data.submissions || [];
                this.pagination = {
                    current_page: data.current_page || 1,
                    per_page: data.per_page || 20,
                    total: data.total || 0,
                    total_pages: data.total_pages || 0,
                };
            } catch (error) {
                console.error('Error loading submissions:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to load submissions',
                });
            } finally {
                this.loading = false;
            }
        },
        async loadSubmissionDetails(logId) {
            this.loadingDetails = true;
            try {
                const response = await this.$get(`review/submissions/${logId}`);
                this.selectedSubmission = response.all ? response.all() : response;
            } catch (error) {
                console.error('Error loading submission details:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to load submission details',
                });
            } finally {
                this.loadingDetails = false;
            }
        },
        handleMemberSelect(memberId) {
            this.selectedMemberId = memberId;
            const member = this.members.find(m => m.id === memberId);
            this.selectedMemberName = member ? member.name : null;
            this.showMemberFilter = false;
            this.selectedSubmissions = [];
            this.loadSubmissions(1);
        },
        handleMemberChange() {
            const member = this.members.find(m => m.id === this.selectedMemberId);
            this.selectedMemberName = member ? member.name : null;
            this.selectedSubmissions = [];
            this.loadSubmissions(1);
        },
        handleSubmissionSelect(submission) {
            this.loadSubmissionDetails(submission.id);
        },
        closeSubmissionDetail() {
            this.selectedSubmission = null;
        },
        async handleMarkReviewed(logId) {
            try {
                await this.$post(`review/logs/${logId}/review`);
                this.$notify({
                    type: 'success',
                    text: 'Submission marked as reviewed',
                });
                this.closeSubmissionDetail();
                this.loadSubmissions(this.pagination.current_page);
                this.loadMembers(); // Refresh unread counts
            } catch (error) {
                console.error('Error marking as reviewed:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to mark as reviewed',
                });
            }
        },
        async handleMarkItemReviewed(logItemId) {
            try {
                await this.$post(`review/log-items/${logItemId}/review`);
                this.$notify({
                    type: 'success',
                    text: 'Task marked as reviewed',
                });
                // Reload submission details to update status
                if (this.selectedSubmission) {
                    this.loadSubmissionDetails(this.selectedSubmission.id);
                }
                this.loadSubmissions(this.pagination.current_page);
            } catch (error) {
                console.error('Error marking item as reviewed:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to mark task as reviewed',
                });
            }
        },
        async handleMarkAllReviewed(logId) {
            try {
                // Get all log items for this log and mark them as reviewed
                const submission = this.selectedSubmission;
                if (!submission || !submission.tasks) return;

                const logItemIds = submission.tasks
                    .filter(task => !task.reviewed)
                    .map(task => task.id);

                if (logItemIds.length === 0) {
                    // If all tasks are reviewed, just mark the log as reviewed
                    await this.handleMarkReviewed(logId);
                    return;
                }

                await this.$post('review/bulk-review', {
                    log_item_ids: logItemIds,
                });

                this.$notify({
                    type: 'success',
                    text: 'All tasks marked as reviewed',
                });
                this.closeSubmissionDetail();
                this.loadSubmissions(this.pagination.current_page);
                this.loadMembers();
            } catch (error) {
                console.error('Error marking all as reviewed:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to mark all as reviewed',
                });
            }
        },
        handleSelectAll(selected) {
            if (selected) {
                this.selectedSubmissions = this.submissions.map(s => s.id);
            } else {
                this.selectedSubmissions = [];
            }
        },
        handleToggleSelection(submissionId) {
            const index = this.selectedSubmissions.indexOf(submissionId);
            if (index > -1) {
                this.selectedSubmissions.splice(index, 1);
            } else {
                this.selectedSubmissions.push(submissionId);
            }
        },
        async bulkMarkReviewed() {
            if (this.selectedSubmissions.length === 0) return;

            this.bulkReviewing = true;
            try {
                await this.$post('review/bulk-review', {
                    log_ids: this.selectedSubmissions,
                });

                this.$notify({
                    type: 'success',
                    text: `${this.selectedSubmissions.length} submission(s) marked as reviewed`,
                });

                this.selectedSubmissions = [];
                this.loadSubmissions(this.pagination.current_page);
                this.loadMembers();
            } catch (error) {
                console.error('Error bulk marking as reviewed:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to mark submissions as reviewed',
                });
            } finally {
                this.bulkReviewing = false;
            }
        },
        handlePageChange(page) {
            this.loadSubmissions(page);
        },
    },
};
</script>

<style lang="scss" scoped>
.review-dashboard {
    min-height: 100vh;
    background: linear-gradient(to bottom, #f8fafc 0%, #f1f5f9 100%);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.review-header {
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
}

.brand-icon {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 0.5rem;
    color: white;

    svg {
        width: 1.5rem;
        height: 1.5rem;
    }
}

.brand-text {
    .brand-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
        letter-spacing: -0.025em;
    }

    .brand-subtitle {
        font-size: 0.8125rem;
        color: #6b7280;
        margin: 0.125rem 0 0 0;
    }
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    gap: 0.5rem;
}

.filter-wrapper {
    position: relative;
}

.filter-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    &.active {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #1e40af;
    }
}

.filter-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    left: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    min-width: 200px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 50;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
    font-size: 0.875rem;
}

.close-filter {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 1.5rem;
    height: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover {
        color: #111827;
    }
}

.filter-options {
    padding: 0.5rem;
}

.filter-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    cursor: pointer;
    border-radius: 0.375rem;
    font-size: 0.875rem;

    &:hover {
        background: #f9fafb;
    }

    input[type="checkbox"],
    input[type="radio"] {
        cursor: pointer;
    }
}

.unread-badge {
    display: inline-block;
    padding: 0.125rem 0.375rem;
    background: #ef4444;
    color: white;
    border-radius: 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 0.25rem;
}

.action-group {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;

    svg {
        width: 1rem;
        height: 1rem;
    }

    &:hover:not(:disabled) {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    &.primary {
        background: #10b981;
        border-color: #10b981;
        color: white;

        &:hover:not(:disabled) {
            background: #059669;
            border-color: #059669;
        }
    }
}

.review-content {
    max-width: 1800px;
    margin: 0 auto;
    padding: 0 1.5rem 1.5rem;
}

.date-range-section {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}

.date-range-filter {
    display: flex;
    align-items: center;
    gap: 0.75rem;

    label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
    }

    .date-input {
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }
}

.review-layout {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 1rem;
}

.member-sidebar {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    height: fit-content;
    position: sticky;
    top: 1rem;
}

.submissions-main {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
}

.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-0.5rem);
}
</style>

