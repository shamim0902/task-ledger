<template>
    <div class="report-history">
        <div class="history-card">
            <h3 class="history-title">Report History</h3>
            <div class="history-tabs">
                <button
                    v-for="tab in tabs"
                    :key="tab.value"
                    @click="activeTab = tab.value"
                    :class="['tab-btn', { active: activeTab === tab.value }]"
                >
                    {{ tab.label }}
                </button>
            </div>

            <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
                <p>Loading history...</p>
            </div>

            <div v-else-if="filteredHistory.length === 0" class="empty-state">
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
                        @click="$emit('regenerate', item.type, item.start)"
                        class="regenerate-btn"
                    >
                        Regenerate
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ReportHistory',
    props: {
        history: {
            type: Object,
            default: () => ({}),
        },
        selectedUserId: {
            type: Number,
            default: null,
        },
    },
    data() {
        return {
            activeTab: 'weekly',
            loading: false,
            tabs: [
                { value: 'weekly', label: 'Weekly' },
                { value: 'monthly', label: 'Monthly' },
                { value: 'yearly', label: 'Yearly' },
            ],
        };
    },
    computed: {
        filteredHistory() {
            if (!this.history || !this.history[this.activeTab]) {
                return [];
            }
            return this.history[this.activeTab] || [];
        },
    },
};
</script>

<style lang="scss" scoped>
.report-history {
    width: 100%;
    margin-bottom: 1.5rem;
}

.history-card {
    background: #ffffff;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    padding: 1.5rem;
}

.history-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 1rem 0;
}

.history-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 0.5rem;
}

.tab-btn {
    padding: 0.5rem 1rem;
    border: none;
    background: transparent;
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -0.5rem;
    transition: all 0.2s;

    &:hover {
        color: #667eea;
    }

    &.active {
        color: #667eea;
        border-bottom-color: #667eea;
    }
}

.loading-state,
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.spinner {
    width: 2rem;
    height: 2rem;
    border: 3px solid #e5e7eb;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.history-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.history-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
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
    font-size: 0.875rem;
    font-weight: 500;
    color: #111827;
    margin-bottom: 0.25rem;
}

.history-item-meta {
    font-size: 0.75rem;
    color: #6b7280;
}

.submissions-count {
    color: #667eea;
}

.regenerate-btn {
    padding: 0.375rem 0.75rem;
    background: #667eea;
    color: #ffffff;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        background: #5568d3;
    }
}
</style>

