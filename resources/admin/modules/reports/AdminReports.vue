<template>
    <div class="admin-reports">
        <!-- Header -->
        <div class="admin-reports-header">
            <div class="header-content">
                <div class="header-brand">
                    <div class="brand-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="brand-text">
                        <h1 class="brand-title">Admin Reports</h1>
                        <p class="brand-subtitle">View and manage employee reports</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="admin-reports-content">
            <!-- Report Generation Form -->
            <div class="report-form-card">
                <h3 class="form-title">Generate Report for Any Employee</h3>
                <div class="form-content">
                    <!-- Employee Selector -->
                    <div class="form-group">
                        <label class="form-label">Select Employee</label>
                        <select v-model="selectedUserId" @change="onUserChange" class="form-select">
                            <option value="">-- Select Employee --</option>
                            <option v-for="user in allUsers" :key="user.id" :value="user.id">
                                {{ user.name }}
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

            <!-- Report Preview -->
            <ReportPreview
                v-if="reportData"
                :report-data="reportData"
                :generating="generating"
                @send-to-admin="sendReportToAdmin"
                @download="downloadReport"
            />
        </div>
    </div>
</template>

<script>
import ReportPreview from './components/ReportPreview.vue';

export default {
    name: 'AdminReports',
    components: {
        ReportPreview,
    },
    data() {
        return {
            allUsers: [],
            selectedUserId: null,
            selectedTimeframe: 'weekly',
            selectedDate: new Date().toISOString().split('T')[0],
            timeframes: [
                { value: 'weekly', label: 'Weekly' },
                { value: 'monthly', label: 'Monthly' },
                { value: 'yearly', label: 'Yearly' },
            ],
            reportData: null,
            generating: false,
            loadingUsers: false,
        };
    },
    computed: {
        canGenerate() {
            return this.selectedUserId && this.selectedTimeframe && this.selectedDate;
        },
    },
    mounted() {
        this.loadAllUsers();
    },
    methods: {
        async loadAllUsers() {
            this.loadingUsers = true;
            try {
                // Get all users who have submitted logs
                const response = await this.$get('pm/team-members');
                let usersData = response;
                if (response && typeof response.all === 'function') {
                    usersData = response.all();
                } else if (response && Array.isArray(response)) {
                    usersData = response;
                } else if (response && response.data && Array.isArray(response.data)) {
                    usersData = response.data;
                }
                this.allUsers = Array.isArray(usersData) ? usersData : [];
            } catch (error) {
                console.error('Error loading users:', error);
                this.$notify('Failed to load users', 'error');
                this.allUsers = [];
            } finally {
                this.loadingUsers = false;
            }
        },
        onUserChange() {
            // Can load history if needed
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
.admin-reports {
    width: 100%;
    min-height: 100vh;
    background: #f8fafc;
}

.admin-reports-header {
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    padding: 1.5rem 1rem;
}

.header-content {
    max-width: 1800px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.brand-icon {
    width: 2.5rem;
    height: 2.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    flex-shrink: 0;

    svg {
        width: 1.25rem;
        height: 1.25rem;
    }
}

.brand-text {
    display: flex;
    flex-direction: column;
}

.brand-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
    line-height: 1.2;
}

.brand-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.2;
}

.admin-reports-content {
    max-width: 1800px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
}

.report-form-card {
    background: #ffffff;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 1.5rem 0;
}

.form-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.form-select,
.form-input {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    color: #111827;
    background: #ffffff;

    &:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
}

.form-hint {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0;
}

.timeframe-buttons {
    display: flex;
    gap: 0.5rem;
}

.timeframe-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background: #ffffff;
    color: #374151;
    font-size: 0.875rem;
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
}

.btn-primary {
    padding: 0.625rem 1.25rem;
    background: #667eea;
    color: #ffffff;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.875rem;
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
</style>

