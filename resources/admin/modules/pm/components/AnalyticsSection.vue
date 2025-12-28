<template>
    <div class="analytics-section">
        <div v-if="loading" class="loading-state">
            <p>Loading analytics...</p>
        </div>
        <div v-else class="analytics-grid">
            <!-- Tasks Touched vs Completed Chart -->
            <div class="chart-card">
                <h3 class="chart-title">Tasks Touched vs Completed</h3>
                <div class="chart-container">
                    <div class="chart-bars">
                        <div
                            v-for="user in users"
                            :key="user.id"
                            class="bar-group"
                        >
                            <div class="bar-label">{{ user.name }}</div>
                            <div class="bars-wrapper">
                                <div
                                    class="bar bar-touched"
                                    :style="{ height: `${getBarHeight(analytics.tasks_touched_by_user?.[user.id] || 0, 'touched')}%` }"
                                    :title="`Touched: ${analytics.tasks_touched_by_user?.[user.id] || 0}`"
                                >
                                    <span class="bar-value">{{ analytics.tasks_touched_by_user?.[user.id] || 0 }}</span>
                                </div>
                                <div
                                    class="bar bar-completed"
                                    :style="{ height: `${getBarHeight(analytics.tasks_completed_by_user?.[user.id] || 0, 'completed')}%` }"
                                    :title="`Completed: ${analytics.tasks_completed_by_user?.[user.id] || 0}`"
                                >
                                    <span class="bar-value">{{ analytics.tasks_completed_by_user?.[user.id] || 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color touched"></div>
                            <span>Tasks Touched</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color completed"></div>
                            <span>Completed</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Distribution Donut -->
            <div class="chart-card">
                <h3 class="chart-title">Work Distribution</h3>
                <div class="donut-chart">
                    <div class="donut-container">
                        <svg class="donut-svg" viewBox="0 0 120 120">
                            <circle
                                class="donut-ring"
                                cx="60"
                                cy="60"
                                r="50"
                                fill="none"
                                stroke="#e5e7eb"
                                stroke-width="20"
                            />
                            <circle
                                class="donut-segment completed"
                                cx="60"
                                cy="60"
                                r="50"
                                fill="none"
                                stroke="#10b981"
                                stroke-width="20"
                                :stroke-dasharray="`${(workDistribution.completed / 100) * 314} 314`"
                                stroke-dashoffset="0"
                                transform="rotate(-90 60 60)"
                            />
                            <circle
                                class="donut-segment in-progress"
                                cx="60"
                                cy="60"
                                r="50"
                                fill="none"
                                stroke="#3b82f6"
                                stroke-width="20"
                                :stroke-dasharray="`${(workDistribution['in-progress'] / 100) * 314} 314`"
                                :stroke-dashoffset="`-${(workDistribution.completed / 100) * 314}`"
                                transform="rotate(-90 60 60)"
                            />
                            <circle
                                class="donut-segment blocked"
                                cx="60"
                                cy="60"
                                r="50"
                                fill="none"
                                stroke="#ef4444"
                                stroke-width="20"
                                :stroke-dasharray="`${(workDistribution.blocked / 100) * 314} 314`"
                                :stroke-dashoffset="`-${((workDistribution.completed + workDistribution['in-progress']) / 100) * 314}`"
                                transform="rotate(-90 60 60)"
                            />
                        </svg>
                        <div class="donut-center">
                            <div class="donut-total">{{ totalTasks }}</div>
                            <div class="donut-label">Total Tasks</div>
                        </div>
                    </div>
                    <div class="donut-legend">
                        <div class="legend-item">
                            <div class="legend-color completed"></div>
                            <span>Completed: {{ workDistribution.completed }}%</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color in-progress"></div>
                            <span>In Progress: {{ workDistribution['in-progress'] }}%</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color blocked"></div>
                            <span>Blocked: {{ workDistribution.blocked }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blocked Tasks Trend -->
            <div class="chart-card">
                <h3 class="chart-title">Blocked Tasks Trend (Last 7 Days)</h3>
                <div class="trend-chart">
                    <div class="trend-bars">
                        <div
                            v-for="day in blockedTasksTrend"
                            :key="day.date"
                            class="trend-bar-group"
                        >
                            <div
                                class="trend-bar"
                                :style="{ height: `${getTrendBarHeight(day.count)}%` }"
                                :title="`${day.label}: ${day.count} blocked`"
                            >
                                <span class="trend-value">{{ day.count }}</span>
                            </div>
                            <div class="trend-label">{{ day.label }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attention Required -->
            <div class="chart-card">
                <h3 class="chart-title">Attention Required</h3>
                <div v-if="attentionRequired.length === 0" class="no-attention">
                    <svg class="check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>All team members are up to date!</p>
                </div>
                <div v-else class="attention-list">
                    <div
                        v-for="item in attentionRequired"
                        :key="item.user_id"
                        class="attention-item"
                    >
                        <div class="attention-avatar">{{ item.user_initials }}</div>
                        <div class="attention-content">
                            <div class="attention-name">{{ item.user_name }}</div>
                            <div class="attention-issues">
                                <span
                                    v-for="(issue, index) in item.issues"
                                    :key="index"
                                    class="issue-badge"
                                >
                                    {{ issue }}
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
    name: 'AnalyticsSection',
    props: {
        analytics: {
            type: Object,
            default: () => ({})
        },
        loading: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        users() {
            return this.analytics.users || [];
        },
        workDistribution() {
            return this.analytics.work_distribution || {
                completed: 0,
                'in-progress': 0,
                blocked: 0
            };
        },
        blockedTasksTrend() {
            return this.analytics.blocked_tasks_trend || [];
        },
        attentionRequired() {
            return this.analytics.attention_required || [];
        },
        totalTasks() {
            return Object.values(this.workDistribution).reduce((sum, val) => sum + val, 0);
        }
    },
    methods: {
        getBarHeight(value, type) {
            const maxValue = Math.max(
                ...Object.values(this.analytics.tasks_touched_by_user || {}),
                ...Object.values(this.analytics.tasks_completed_by_user || {}),
                1
            );
            return maxValue > 0 ? (value / maxValue) * 100 : 0;
        },
        getTrendBarHeight(value) {
            const maxValue = Math.max(...this.blockedTasksTrend.map(d => d.count), 1);
            return maxValue > 0 ? (value / maxValue) * 100 : 0;
        }
    }
};
</script>

<style lang="scss" scoped>
.analytics-section {
    width: 100%;
}

.loading-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;
}

.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.chart-card {
    background: #f9fafb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
}

.chart-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 1.25rem 0;
}

.chart-container {
    width: 100%;
}

.chart-bars {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
    height: 200px;
    margin-bottom: 1rem;
    padding: 0 0.5rem;
}

.bar-group {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}

.bar-label {
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
    text-align: center;
    font-weight: 500;
}

.bars-wrapper {
    display: flex;
    gap: 0.25rem;
    justify-content: space-around;
    align-items: flex-end;
    height: calc(100% - 2rem);
    width: 100%;
}

.bar {
    flex: 1;
    min-height: 20px;
    border-radius: 0.25rem 0.25rem 0 0;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 0.25rem;
    position: relative;
    transition: all 0.2s;
    max-width: 30px;

    &:hover {
        opacity: 0.8;
    }

    .bar-value {
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
        position: absolute;
        top: -1.5rem;
    }

    &.bar-touched {
        background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
    }

    &.bar-completed {
        background: linear-gradient(180deg, #10b981 0%, #059669 100%);
    }
}

.chart-legend {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    margin-top: 1rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;

    .legend-color {
        width: 1rem;
        height: 1rem;
        border-radius: 0.25rem;

        &.touched {
            background: #3b82f6;
        }

        &.completed {
            background: #10b981;
        }

        &.in-progress {
            background: #3b82f6;
        }

        &.blocked {
            background: #ef4444;
        }
    }
}

.donut-chart {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

.donut-container {
    position: relative;
    width: 200px;
    height: 200px;
}

.donut-svg {
    width: 100%;
    height: 100%;
}

.donut-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;

    .donut-total {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1;
    }

    .donut-label {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }
}

.donut-legend {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    width: 100%;
}

.trend-chart {
    width: 100%;
}

.trend-bars {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
    height: 200px;
    padding: 0 0.5rem;
}

.trend-bar-group {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}

.trend-bar {
    width: 100%;
    background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
    border-radius: 0.25rem 0.25rem 0 0;
    min-height: 20px;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 0.25rem;
    position: relative;
    transition: all 0.2s;

    &:hover {
        opacity: 0.8;
    }

    .trend-value {
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
        position: absolute;
        top: -1.5rem;
    }
}

.trend-label {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.5rem;
    text-align: center;
    font-weight: 500;
}

.no-attention {
    text-align: center;
    padding: 2rem 1rem;
    color: #10b981;

    .check-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 1rem;
    }

    p {
        margin: 0;
        font-weight: 500;
    }
}

.attention-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.attention-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #fee2e2;
}

.attention-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.attention-content {
    flex: 1;
    min-width: 0;
}

.attention-name {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.attention-issues {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.issue-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
    background: #fee2e2;
    color: #991b1b;
    border-radius: 9999px;
    font-weight: 500;
}

@media (max-width: 768px) {
    .analytics-grid {
        grid-template-columns: 1fr;
    }

    .chart-bars,
    .trend-bars {
        height: 150px;
    }
}
</style>

