<template>
    <div class="review-reports">
        <!-- Header -->
        <div class="reports-header">
            <div class="header-content">
                <div class="header-brand">
                    <div class="brand-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="brand-text">
                        <h1 class="brand-title">Generate Employee Report</h1>
                        <p class="brand-subtitle">Create comprehensive reports for employees</p>
                    </div>
                </div>
                <div class="header-actions">
                    <router-link 
                        to="/review"
                        class="action-btn"
                        title="Back to Submissions"
                    >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Back to Submissions</span>
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="reports-content">
            <div class="reports-main-layout">
                <!-- Left Column: Form and Preview -->
                <div class="reports-main-column">
                    <!-- Report Generation Form -->
                    <div class="report-form-card">
                        <h3 class="form-title">Report Settings</h3>
                        <div class="form-content">
                            <!-- Person Selector -->
                            <div class="form-group">
                                <label class="form-label">Select Employee</label>
                                <select v-model="selectedUserId" @change="onUserChange" class="form-select">
                                    <option value="">-- Select Employee --</option>
                                    <option v-for="member in members" :key="member.id" :value="member.id">
                                        {{ member.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Timeframe Selector -->
                            <div class="form-group">
                                <label class="form-label">Timeframe</label>
                                <div class="timeframe-buttons">
                                    <button
                                        v-for="tf in timeframes"
                                        :key="tf.value"
                                        @click="selectedTimeframe = tf.value"
                                        :class="['timeframe-btn', { active: selectedTimeframe === tf.value }]"
                                    >
                                        {{ tf.label }}
                                    </button>
                                    <button
                                        @click="selectedTimeframe = 'custom'"
                                        :class="['timeframe-btn', { active: selectedTimeframe === 'custom' }]"
                                    >
                                        Custom
                                    </button>
                                </div>
                            </div>

                            <!-- Date Range Picker (shown when Custom is selected) -->
                            <div v-if="selectedTimeframe === 'custom'" class="form-group">
                                <label class="form-label">Date Range</label>
                                <div class="date-range-inputs">
                                    <div class="date-input-group">
                                        <label class="date-label">Start Date</label>
                                        <input
                                            type="date"
                                            v-model="startDate"
                                            class="form-input"
                                        />
                                    </div>
                                    <div class="date-input-group">
                                        <label class="date-label">End Date</label>
                                        <input
                                            type="date"
                                            v-model="endDate"
                                            class="form-input"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Generate Button -->
                            <div class="form-actions">
                                <button
                                    @click="generateReport"
                                    :disabled="!canGenerate || generating"
                                    class="btn-primary"
                                >
                                    <span v-if="generating">Generating...</span>
                                    <span v-else>Generate Report</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Report Preview -->
                    <div v-if="reportData" class="report-preview-card">
                        <ReportPreview
                            :report-data="reportData"
                            :generating="generating"
                            @send-to-admin="openSendModal"
                            @download="downloadReport"
                        />
                    </div>
                </div>

                <!-- Right Sidebar: Submitted Reports -->
                <div class="reports-sidebar">
                    <div class="report-history-card">
                        <h3 class="history-title">Submitted Reports</h3>
                        
                        <div v-if="loadingSubmittedReports" class="empty-state">
                            <p>Loading...</p>
                        </div>
                        <div v-else-if="submittedReports.length === 0" class="empty-state">
                            <p>No submitted reports yet</p>
                        </div>
                        <div v-else class="history-list">
                            <div
                                v-for="report in submittedReports"
                                :key="report.id"
                                class="history-item"
                            >
                                <div class="history-item-content">
                                    <div class="history-item-label">{{ report.employee_name }}</div>
                                    <div class="history-item-meta">
                                        <span class="submissions-count">
                                            {{ capitalizeTimeframe(report.timeframe) }} • 
                                            {{ formatDate(report.start_date) }} to {{ formatDate(report.end_date) }}
                                        </span>
                                        <span class="submitted-date">{{ formatDateTime(report.created_at) }}</span>
                                    </div>
                                </div>
                                <button
                                    @click="viewSubmittedReport(report)"
                                    class="view-btn"
                                >
                                    View
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Send to Admin Modal -->
        <div v-if="showSendModal" class="modal-overlay" @click.self="showSendModal = false">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">
                        {{ selectedTimeframe === 'custom' ? 'Custom' : capitalizeTimeframe(selectedTimeframe) }} report for {{ reportData?.employee?.name || 'Employee' }}
                    </h3>
                    <button class="modal-close" @click="showSendModal = false">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="send-preview" v-if="editableReportData">
                        <!-- Employee Information -->
                        <div class="preview-section">
                            <h4 class="section-title">Employee Information</h4>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value">{{ editableReportData.employee?.name || 'N/A' }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Email:</span>
                                    <span class="info-value">{{ editableReportData.employee?.email || 'N/A' }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Period:</span>
                                    <span class="info-value">
                                        {{ editableReportData.date_range?.start }} to {{ editableReportData.date_range?.end }}
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Timeframe:</span>
                                    <span class="info-value">{{ editableReportData.timeframe || 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Statistics -->
                        <div class="preview-section" v-if="editableReportData.summary">
                            <h4 class="section-title">Summary Statistics</h4>
                            <div class="stats-grid">
                                <div class="stat-card editable-stat">
                                    <div class="stat-label">Total Submissions</div>
                                    <input 
                                        type="text" 
                                        v-model="editableReportData.summary.total_submissions"
                                        class="stat-value-input"
                                    />
                                </div>
                                <div class="stat-card editable-stat">
                                    <div class="stat-label">Tasks Worked On</div>
                                    <input 
                                        type="text" 
                                        v-model="editableReportData.summary.total_tasks"
                                        class="stat-value-input"
                                    />
                                </div>
                                <div class="stat-card success editable-stat">
                                    <div class="stat-label">Completed</div>
                                    <input 
                                        type="text" 
                                        v-model="editableReportData.summary.completed_tasks"
                                        class="stat-value-input"
                                    />
                                </div>
                                <div class="stat-card danger editable-stat">
                                    <div class="stat-label">Blocked</div>
                                    <input 
                                        type="text" 
                                        v-model="editableReportData.summary.blocked_tasks"
                                        class="stat-value-input"
                                    />
                                </div>
                                <div class="stat-card editable-stat">
                                    <div class="stat-label">Total Hours</div>
                                    <input 
                                        type="text" 
                                        v-model="editableReportData.summary.total_hours"
                                        class="stat-value-input"
                                    />
                                </div>
                                <div class="stat-card editable-stat">
                                    <div class="stat-label">Story Points</div>
                                    <input 
                                        type="text" 
                                        v-model="editableReportData.summary.total_points"
                                        class="stat-value-input"
                                    />
                                </div>
                                <div class="stat-card editable-stat">
                                    <div class="stat-label">Completion Rate</div>
                                    <input 
                                        type="text" 
                                        v-model="editableReportData.summary.completion_rate"
                                        class="stat-value-input"
                                    />
                                </div>
                                <div class="stat-card editable-stat">
                                    <div class="stat-label">Avg Hours/Day</div>
                                    <input 
                                        type="text" 
                                        v-model="editableReportData.summary.average_hours_per_day"
                                        class="stat-value-input"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="preview-note">
                            <p>This report will be sent to all administrators via email.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary" @click="showSendModal = false">Cancel</button>
                    <button class="btn-primary" @click="confirmSendReport" :disabled="sending">
                        <span v-if="sending">Sending...</span>
                        <span v-else>Send Report</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ReportPreview from '../reports/components/ReportPreview.vue';

export default {
    name: 'ReviewReports',
    components: {
        ReportPreview,
    },
    data() {
        return {
            members: [],
            selectedUserId: null,
            selectedTimeframe: 'weekly',
            selectedDate: new Date().toISOString().split('T')[0],
            startDate: new Date().toISOString().split('T')[0],
            endDate: new Date().toISOString().split('T')[0],
            timeframes: [
                { value: 'weekly', label: 'Weekly' },
                { value: 'monthly', label: 'Monthly' },
                { value: 'yearly', label: 'Yearly' },
            ],
            reportData: null,
            reportHistory: null,
            generating: false,
            loadingMembers: false,
            activeTab: 'weekly',
            historyTabs: [
                { value: 'weekly', label: 'Weekly' },
                { value: 'monthly', label: 'Monthly' },
                { value: 'yearly', label: 'Yearly' },
            ],
            showSendModal: false,
            sending: false,
            editableReportData: null,
        };
    },
    computed: {
        canGenerate() {
            if (!this.selectedUserId) return false;
            
            if (this.selectedTimeframe === 'custom') {
                return this.startDate && this.endDate && this.startDate <= this.endDate;
            } else {
                return this.selectedTimeframe && this.selectedDate;
            }
        },
        filteredHistory() {
            if (!this.reportHistory || !this.reportHistory[this.activeTab]) {
                return [];
            }
            return this.reportHistory[this.activeTab] || [];
        },
    },
    mounted() {
        this.loadMembers();
        this.loadSubmittedReports();
    },
    methods: {
        async loadMembers() {
            this.loadingMembers = true;
            try {
                const response = await this.$get('review/members');
                let membersData = response;
                if (response && typeof response.all === 'function') {
                    membersData = response.all();
                } else if (response && Array.isArray(response)) {
                    membersData = response;
                } else if (response && response.data && Array.isArray(response.data)) {
                    membersData = response.data;
                }
                this.members = Array.isArray(membersData) ? membersData : [];
            } catch (error) {
                console.error('Error loading members:', error);
                this.$notify('Failed to load members', 'error');
                this.members = [];
            } finally {
                this.loadingMembers = false;
            }
        },
        async onUserChange() {
            if (this.selectedUserId) {
                await this.loadReportHistory();
            } else {
                this.reportHistory = null;
            }
        },
        async loadReportHistory() {
            if (!this.selectedUserId) return;
            
            try {
                const response = await this.$get(`reports/history/${this.selectedUserId}`);
                let historyData = response;
                if (response && typeof response.all === 'function') {
                    historyData = response.all();
                } else if (response && Array.isArray(response)) {
                    historyData = response;
                } else if (response && response.data) {
                    historyData = response.data;
                }
                this.reportHistory = historyData;
            } catch (error) {
                console.error('Error loading report history:', error);
                this.reportHistory = null;
            }
        },
        async generateReport() {
            if (!this.canGenerate) return;

            this.generating = true;
            this.reportData = null;

            try {
                let requestData = {
                    user_id: this.selectedUserId,
                };

                if (this.selectedTimeframe === 'custom') {
                    requestData.start_date = this.startDate;
                    requestData.end_date = this.endDate;
                    requestData.timeframe = 'custom';
                } else {
                    requestData.timeframe = this.selectedTimeframe;
                    requestData.date = this.selectedDate;
                }

                const response = await this.$post('reports/generate', requestData);

                let reportData = response;
                if (response && typeof response.all === 'function') {
                    reportData = response.all();
                } else if (response && response.data) {
                    reportData = response.data;
                }

                this.reportData = reportData;
                this.$notify('Report generated successfully', 'success');
            } catch (error) {
                console.error('Error generating report:', error);
                const errorMessage = error?.message || error?.responseJSON?.message || 'Failed to generate report';
                this.$notify(errorMessage, 'error');
            } finally {
                this.generating = false;
            }
        },
        handleRegenerate(timeframe, startDate) {
            this.selectedTimeframe = timeframe;
            this.selectedDate = startDate;
            this.generateReport();
        },
        openSendModal() {
            // Create a deep copy of report data for editing
            this.editableReportData = JSON.parse(JSON.stringify(this.reportData));
            this.showSendModal = true;
        },
        async confirmSendReport() {
            if (!this.editableReportData) return;

            this.sending = true;
            try {
                let requestData = {
                    user_id: this.selectedUserId,
                    report_data: this.editableReportData,
                };

                if (this.selectedTimeframe === 'custom') {
                    requestData.start_date = this.startDate;
                    requestData.end_date = this.endDate;
                    requestData.timeframe = 'custom';
                } else {
                    requestData.timeframe = this.selectedTimeframe;
                    requestData.date = this.selectedDate;
                }

                const response = await this.$post('reports/send', requestData);

                let result = response;
                if (response && typeof response.all === 'function') {
                    result = response.all();
                } else if (response && response.data) {
                    result = response.data;
                }

                if (result.success) {
                    this.$notify('Report sent to admin(s) successfully', 'success');
                    this.showSendModal = false;
                    // Reload submitted reports
                    this.loadSubmittedReports();
                } else {
                    this.$notify('Failed to send report', 'error');
                }
            } catch (error) {
                console.error('Error sending report:', error);
                const errorMessage = error?.message || error?.responseJSON?.message || 'Failed to send report';
                this.$notify(errorMessage, 'error');
            } finally {
                this.sending = false;
            }
        },
        async loadSubmittedReports() {
            this.loadingSubmittedReports = true;
            try {
                const response = await this.$get('reports/submitted');
                let reportsData = response;
                
                // Handle different response formats
                if (response && typeof response.all === 'function') {
                    reportsData = response.all();
                } else if (response && Array.isArray(response)) {
                    reportsData = response;
                } else if (response && response.data && Array.isArray(response.data)) {
                    reportsData = response.data;
                } else if (response && response.data && typeof response.data === 'object') {
                    // Handle object response
                    reportsData = Object.values(response.data);
                }
                
                this.submittedReports = Array.isArray(reportsData) ? reportsData : [];
            } catch (error) {
                console.error('Error loading submitted reports:', error);
                this.submittedReports = [];
                // Show error notification
                if (error?.responseJSON?.message) {
                    this.$notify(error.responseJSON.message, 'error');
                }
            } finally {
                this.loadingSubmittedReports = false;
            }
        },
        viewSubmittedReport(report) {
            // Set the employee and regenerate the report
            this.selectedUserId = report.employee_id;
            if (report.timeframe === 'custom') {
                this.selectedTimeframe = 'custom';
                this.startDate = report.start_date;
                this.endDate = report.end_date;
            } else {
                this.selectedTimeframe = report.timeframe;
                this.selectedDate = report.start_date;
            }
            this.generateReport();
        },
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },
        formatDateTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        },
        async downloadReport() {
            if (!this.reportData) return;

            try {
                const params = new URLSearchParams({
                    user_id: this.selectedUserId,
                });

                if (this.selectedTimeframe === 'custom') {
                    params.append('start_date', this.startDate);
                    params.append('end_date', this.endDate);
                    params.append('timeframe', 'custom');
                } else {
                    params.append('timeframe', this.selectedTimeframe);
                    params.append('date', this.selectedDate);
                }

                const url = `${this.$url('reports/download')}?${params.toString()}`;
                window.open(url, '_blank');
                this.$notify('Report download started', 'success');
            } catch (error) {
                console.error('Error downloading report:', error);
                this.$notify('Failed to download report', 'error');
            }
        },
        getDateRangeHint() {
            if (!this.selectedDate || !this.selectedTimeframe) return '';

            const date = new Date(this.selectedDate);
            let start, end;

            switch (this.selectedTimeframe) {
                case 'weekly':
                    const monday = new Date(date);
                    monday.setDate(date.getDate() - date.getDay() + 1);
                    const sunday = new Date(monday);
                    sunday.setDate(monday.getDate() + 6);
                    start = monday.toISOString().split('T')[0];
                    end = sunday.toISOString().split('T')[0];
                    break;
                case 'monthly':
                    start = new Date(date.getFullYear(), date.getMonth(), 1).toISOString().split('T')[0];
                    end = new Date(date.getFullYear(), date.getMonth() + 1, 0).toISOString().split('T')[0];
                    break;
                case 'yearly':
                    start = `${date.getFullYear()}-01-01`;
                    end = `${date.getFullYear()}-12-31`;
                    break;
                default:
                    return '';
            }

            return `Report will cover: ${start} to ${end}`;
        },
        capitalizeTimeframe(timeframe) {
            if (!timeframe) return '';
            return timeframe.charAt(0).toUpperCase() + timeframe.slice(1);
        },
    },
};
</script>

<style lang="scss" scoped>
.review-reports {
    width: 100%;
    min-height: 100vh;
    background: #f8fafc;
}

.reports-header {
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.625rem 1rem;
}

.header-content {
    max-width: 1800px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.header-brand {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.brand-icon {
    width: 1.75rem;
    height: 1.75rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    flex-shrink: 0;

    svg {
        width: 1.125rem;
        height: 1.125rem;
    }
}

.brand-text {
    display: flex;
    flex-direction: column;
}

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

.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    flex: 1;
    justify-content: flex-end;
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
    text-decoration: none;

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
}

.reports-content {
    max-width: 1800px;
    margin: 0 auto;
    padding: 0.75rem 1rem;
}

.reports-main-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 0.75rem;
    align-items: start;
}

.reports-main-column {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.reports-sidebar {
    position: sticky;
    top: 0.75rem;
}

.report-form-card,
.report-history-card,
.report-preview-card {
    background: #ffffff;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;
    padding: 0.875rem 1rem;
}

.form-title,
.history-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.75rem 0;
}

.form-content {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.form-label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #374151;
}

.form-select,
.form-input {
    padding: 0.375rem 0.625rem;
    border: 1px solid #d1d5db;
    border-radius: 0.25rem;
    font-size: 0.8125rem;
    color: #111827;
    background: #ffffff;

    &:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
    }
}

.form-hint {
    font-size: 0.6875rem;
    color: #6b7280;
    margin: 0;
}

.timeframe-buttons {
    display: flex;
    gap: 0.375rem;
}

.date-range-inputs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.date-input-group {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.date-label {
    font-size: 0.6875rem;
    font-weight: 500;
    color: #6b7280;
}

.timeframe-btn {
    padding: 0.375rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.25rem;
    background: #ffffff;
    color: #374151;
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        border-color: #667eea;
        color: #667eea;
    }

    &.active {
        background: #667eea;
        border-color: #667eea;
        color: #ffffff;
    }
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 0.25rem;
}

.btn-primary {
    padding: 0.5rem 1rem;
    background: #667eea;
    color: #ffffff;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;

    &:hover:not(:disabled) {
        background: #5568d3;
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
}

.history-tabs {
    display: flex;
    gap: 0.375rem;
    margin-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 0.375rem;
}

.tab-btn {
    padding: 0.375rem 0.75rem;
    border: none;
    background: transparent;
    color: #6b7280;
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -0.375rem;
    transition: all 0.2s;

    &:hover {
        color: #667eea;
    }

    &.active {
        color: #667eea;
        border-bottom-color: #667eea;
    }
}

.empty-state {
    text-align: center;
    padding: 1rem;
    color: #6b7280;
    font-size: 0.8125rem;
}

.history-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.history-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.25rem;
    transition: all 0.2s;

    &:hover {
        border-color: #667eea;
        background: #f9fafb;
    }
}

.history-item-content {
    flex: 1;
}

.history-item-label {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #111827;
    margin-bottom: 0.125rem;
}

.history-item-meta {
    font-size: 0.6875rem;
    color: #6b7280;
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.submissions-count {
    color: #667eea;
}

.submitted-date {
    color: #9ca3af;
    font-size: 0.625rem;
}

.view-btn {
    padding: 0.25rem 0.625rem;
    background: #667eea;
    color: #ffffff;
    border: none;
    border-radius: 0.25rem;
    font-size: 0.6875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        background: #5568d3;
    }
}

// Modal Styles
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
    z-index: 1000;
    padding: 1rem;
}

.modal-content {
    background: #ffffff;
    border-radius: 0.5rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.modal-close {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;
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
    padding: 1.25rem;
    flex: 1;
    overflow-y: auto;
}

.send-preview {
    .preview-section {
        margin-bottom: 0.875rem;
    }

    .section-title {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.5rem 0;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
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
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 0.5rem;
    }

    .stat-card {
        padding: 0.5rem 0.375rem;
        background: #f9fafb;
        border-radius: 0.25rem;
        border: 1px solid #e5e7eb;
        text-align: center;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;

        &.success {
            border-color: #10b981;
            background: #f0fdf4;
        }

        &.danger {
            border-color: #ef4444;
            background: #fef2f2;
        }

        &.editable-stat {
            padding: 0.375rem 0.25rem;
        }
    }

    .stat-label {
        font-size: 0.625rem;
        color: #6b7280;
        line-height: 1.2;
    }

    .stat-value-input {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #111827;
        text-align: center;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 0.1875rem;
        padding: 0.125rem 0.25rem;
        width: 100%;
        min-height: 1.5rem;
        transition: all 0.2s;
        line-height: 1.2;

        &:hover {
            border-color: #d1d5db;
            background: rgba(255, 255, 255, 0.5);
        }

        &:focus {
            outline: none;
            border-color: #667eea;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }
    }

    .preview-note {
        padding: 0.75rem;
        background: #f3f4f6;
        border-radius: 0.375rem;
        border-left: 3px solid #667eea;

        p {
            font-size: 0.8125rem;
            color: #374151;
            margin: 0;
            line-height: 1.5;
        }
    }
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-top: 1px solid #e5e7eb;
}

.btn-secondary {
    padding: 0.5rem 1rem;
    background: #ffffff;
    color: #374151;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
}

@media (max-width: 1024px) {
    .reports-main-layout {
        grid-template-columns: 1fr;
    }

    .reports-sidebar {
        position: static;
    }
}
</style>

