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
            <!-- Filters Section -->
            <div class="task-ledger-filters-section">
                <div class="filters-header">
                    <h3 class="filters-title">Filters</h3>
                </div>
                <div class="filters-content">
                    <!-- Quick Date Filters -->
                    <div class="task-ledger-filter-group">
                        <div class="quick-filters">
                            <button
                                v-for="filter in quickDateFilters"
                                :key="filter.value"
                                @click="applyQuickDateFilter(filter.value)"
                                :class="['quick-filter-btn', { active: dateFilterType === filter.value }]"
                            >
                                {{ filter.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Custom Date Range (shown when custom is selected) -->
                    <div v-if="dateFilterType === 'custom'" class="task-ledger-filter-group">
                        <div class="date-range-inputs">
                            <input
                                type="date"
                                v-model="startDate"
                                @change="applyCustomDateRange"
                                class="date-input"
                                placeholder="Start date"
                            />
                            <span class="date-separator">to</span>
                            <input
                                type="date"
                                v-model="endDate"
                                @change="applyCustomDateRange"
                                class="date-input"
                                placeholder="End date"
                            />
                        </div>
                    </div>

                    <!-- Member Filter -->
                    <div class="task-ledger-filter-group">
                        <div class="filter-wrapper">
                            <button
                                @click.stop="showMemberFilter = !showMemberFilter"
                                :class="['member-filter-btn', { active: selectedMemberId }]"
                            >
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>{{ selectedMemberName || 'All Members' }}</span>
                                <svg v-if="selectedMemberId" @click.stop="clearMemberFilter" class="clear-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <transition name="dropdown">
                                <div v-if="showMemberFilter" class="filter-dropdown">
                                    <div class="filter-header">
                                        <span>Select Member</span>
                                        <button @click.stop="showMemberFilter = false" class="close-filter">×</button>
                                    </div>
                                    <div class="filter-options">
                                        <label class="filter-checkbox" @click="handleMemberSelect(null)">
                                            <input type="radio" :checked="selectedMemberId === null" />
                                            <span><strong>All Members</strong></span>
                                        </label>
                                        <label
                                            v-for="member in members"
                                            :key="member.id"
                                            class="filter-checkbox"
                                            @click="handleMemberSelect(member.id)"
                                        >
                                            <input type="radio" :checked="selectedMemberId === member.id" />
                                            <span>{{ member.name }}</span>
                                            <span v-if="member.unread_count > 0" class="unread-badge">{{ member.unread_count }}</span>
                                        </label>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
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
            dateFilterType: 'all', // 'all', 'today', 'monthly', 'yearly', 'custom'
            startDate: '', // No default date filter - show all submissions
            endDate: '',
            quickDateFilters: [
                { label: 'All Time', value: 'all' },
                { label: 'Today', value: 'today' },
                { label: 'This Month', value: 'monthly' },
                { label: 'This Year', value: 'yearly' },
                { label: 'Custom Range', value: 'custom' },
            ],
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
        getTodayDate() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        },
        getMonthStartDate() {
            const today = new Date();
            return new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
        },
        getYearStartDate() {
            const today = new Date();
            return new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
        },
        applyQuickDateFilter(filterType) {
            this.dateFilterType = filterType;
            
            if (filterType === 'all') {
                this.startDate = '';
                this.endDate = '';
            } else if (filterType === 'today') {
                const today = this.getTodayDate();
                this.startDate = today;
                this.endDate = today;
            } else if (filterType === 'monthly') {
                this.startDate = this.getMonthStartDate();
                this.endDate = this.getTodayDate();
            } else if (filterType === 'yearly') {
                this.startDate = this.getYearStartDate();
                this.endDate = this.getTodayDate();
            } else if (filterType === 'custom') {
                // Keep existing dates or set to empty
                if (!this.startDate && !this.endDate) {
                    this.startDate = this.getMonthStartDate();
                    this.endDate = this.getTodayDate();
                }
            }
            
            this.loadSubmissions(1);
        },
        applyCustomDateRange() {
            if (this.startDate && this.endDate) {
                this.dateFilterType = 'custom';
            }
            this.loadSubmissions(1);
        },
        clearMemberFilter() {
            this.selectedMemberId = null;
            this.selectedMemberName = null;
            this.showMemberFilter = false;
            this.selectedSubmissions = [];
            this.loadSubmissions(1);
        },
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
            if (memberId === null) {
                this.clearMemberFilter();
                return;
            }
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
    margin-bottom: 1rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.header-content {
    max-width: 1800px;
    margin: 0 auto;
    padding: 0.625rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.header-brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.brand-icon {
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 0.5rem;
    color: white;
    box-shadow: 0 2px 4px -1px rgba(16, 185, 129, 0.3);
    flex-shrink: 0;

    svg {
        width: 1.125rem;
        height: 1.125rem;
    }
}

.brand-text {
    .brand-title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
        line-height: 1.2;
    }

    .brand-subtitle {
        font-size: 0.6875rem;
        color: #6b7280;
        margin: 0;
        font-weight: 500;
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

.task-ledger-filter-group {
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
    top: calc(100% + 0.375rem);
    left: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    min-width: 200px;
    max-width: 280px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 50;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
    font-size: 0.8125rem;
    background: #f9fafb;
}

.close-filter {
    background: none;
    border: none;
    font-size: 1.125rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 1.25rem;
    height: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;
    transition: all 0.2s;

    &:hover {
        background: #f3f4f6;
        color: #111827;
    }
}

.filter-options {
    padding: 0.375rem;
}

.filter-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4375rem 0.5rem;
    cursor: pointer;
    border-radius: 0.25rem;
    font-size: 0.8125rem;
    transition: all 0.2s;

    &:hover {
        background: #f9fafb;
    }

    input[type="checkbox"],
    input[type="radio"] {
        cursor: pointer;
        width: 0.875rem;
        height: 0.875rem;
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
    padding: 0 1rem 1rem;
}

.task-ledger-filters-section {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    margin-bottom: 0.75rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.filters-header {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.filters-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filters-content {
    padding: 0.75rem;
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.task-ledger-filter-group {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    flex-shrink: 0;
}

.quick-filters {
    display: flex;
    gap: 0.375rem;
    flex-wrap: nowrap;
}

.quick-filter-btn {
    padding: 0.375rem 0.75rem;
    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;

    &:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    &.active {
        background: #6366f1;
        border-color: #6366f1;
        color: white;
    }
}

.date-range-inputs {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    flex-wrap: nowrap;
}

.date-input {
    padding: 0.375rem 0.625rem;
    border: 1px solid #d1d5db;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    color: #374151;
    background: white;
    transition: all 0.2s;
    width: 140px;

    &:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
    }
}

.date-separator {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
    white-space: nowrap;
}

.member-filter-btn {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    background: #f9fafb;
    border: 1px solid #d1d5db;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    min-width: 140px;
    justify-content: space-between;

    svg {
        width: 0.875rem;
        height: 0.875rem;
        flex-shrink: 0;
    }

    .clear-icon {
        width: 0.75rem;
        height: 0.75rem;
        opacity: 0.6;
        
        &:hover {
            opacity: 1;
        }
    }

    &:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    &.active {
        background: #eef2ff;
        border-color: #6366f1;
        color: #6366f1;
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

