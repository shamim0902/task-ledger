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
                        </div>
                    </div>

                    <!-- Date Picker -->
                    <div class="form-group">
                        <label class="form-label">Select Date</label>
                        <input
                            type="date"
                            v-model="selectedDate"
                            class="form-input"
                        />
                        <p class="form-hint">
                            {{ getDateRangeHint() }}
                        </p>
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

            <!-- Report History -->
            <div v-if="selectedUserId && reportHistory" class="report-history-card">
                <h3 class="history-title">Report History</h3>
                <div class="history-tabs">
                    <button
                        v-for="tab in historyTabs"
                        :key="tab.value"
                        @click="activeTab = tab.value"
                        :class="['tab-btn', { active: activeTab === tab.value }]"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div v-if="filteredHistory.length === 0" class="empty-state">
                    <p>No reports available for this period</p>
                </div>

                <div v-else class="history-list">
                    <div
                        v-for="item in filteredHistory"
                        :key="`${item.start}_${item.end}`"
                        class="history-item"
                    >
                        <div class="history-item-content">
                            <div class="history-item-label">{{ item.label }}</div>
                            <div class="history-item-meta">
                                <span class="submissions-count">{{ item.submissions_count }} submissions</span>
                            </div>
                        </div>
                        <button
                            @click="handleRegenerate(item.type, item.start)"
                            class="regenerate-btn"
                        >
                            Regenerate
                        </button>
                    </div>
                </div>
            </div>

            <!-- Report Preview -->
            <div v-if="reportData" class="report-preview-card">
                <ReportPreview
                    :report-data="reportData"
                    :generating="generating"
                    @send-to-admin="sendReportToAdmin"
                    @download="downloadReport"
                />
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
        };
    },
    computed: {
        canGenerate() {
            return this.selectedUserId && this.selectedTimeframe && this.selectedDate;
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
                const response = await this.$post('reports/generate', {
                    user_id: this.selectedUserId,
                    timeframe: this.selectedTimeframe,
                    date: this.selectedDate,
                });

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
        async sendReportToAdmin() {
            if (!this.reportData) return;

            try {
                const response = await this.$post('reports/send', {
                    user_id: this.selectedUserId,
                    timeframe: this.selectedTimeframe,
                    date: this.selectedDate,
                });

                let result = response;
                if (response && typeof response.all === 'function') {
                    result = response.all();
                } else if (response && response.data) {
                    result = response.data;
                }

                if (result.success) {
                    this.$notify('Report sent to admin(s) successfully', 'success');
                } else {
                    this.$notify('Failed to send report', 'error');
                }
            } catch (error) {
                console.error('Error sending report:', error);
                const errorMessage = error?.message || error?.responseJSON?.message || 'Failed to send report';
                this.$notify(errorMessage, 'error');
            }
        },
        async downloadReport() {
            if (!this.reportData) return;

            try {
                const params = new URLSearchParams({
                    user_id: this.selectedUserId,
                    timeframe: this.selectedTimeframe,
                    date: this.selectedDate,
                });

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
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
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
}

.submissions-count {
    color: #667eea;
}

.regenerate-btn {
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
</style>

