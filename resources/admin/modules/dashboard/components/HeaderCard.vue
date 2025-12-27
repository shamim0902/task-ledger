<template>
    <div class="header-card">
        <div class="header-content">
            <div class="header-left">
                <h1 class="title">
                    <svg class="icon-large" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Daily Report System
                </h1>
                <p class="subtitle">Track your daily progress and task completion</p>
            </div>
            <div class="header-right">
                <div class="date-label">Today's Date</div>
                <div class="date-value">
                    <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ todayDate }}
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card stat-indigo">
                <div class="stat-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Tasks</div>
                    <div class="stat-value">{{ totalTasks }}</div>
                </div>
            </div>
            <div class="stat-card stat-green">
                <div class="stat-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Hours Today</div>
                    <div class="stat-value">{{ hoursToday }}h</div>
                </div>
            </div>
            <div class="stat-card stat-purple">
                <div class="stat-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Story Points</div>
                    <div class="stat-value">{{ storyPoints }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HeaderCard',
    props: {
        totalTasks: {
            type: Number,
            default: 0
        },
        hoursToday: {
            type: Number,
            default: 0
        },
        storyPoints: {
            type: Number,
            default: 0
        }
    },
    computed: {
        todayDate() {
            return new Date().toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    }
};
</script>

<style scoped lang="scss">
    .header-card {
      background: #f9fafb;
      border-radius: 0.75rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
      padding: 1.25rem;
      margin-bottom: 1rem;
    }
    
    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 1.25rem;
      gap: 1rem;
    }
    
    .header-left {
      .title {
        font-size: 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #1f2937;
    
        svg {
          width: 1.5rem;
          height: 1.5rem;
          color: #6366f1;
        }
      }
    
      .subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.25rem;
      }
    }
    
    .header-right {
      text-align: right;
    
      .date-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #6b7280;
        margin-bottom: 0.125rem;
      }
    
      .date-value {
        font-size: 1rem;
        font-weight: 600;
        color: #6366f1;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        justify-content: flex-end;
    
        svg {
          width: 1rem;
          height: 1rem;
        }
      }
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 1rem;
    }
    
    .stat-card {
      background: white;
      border-radius: 0.75rem;
      padding: 1rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      min-height: 70px;
    
      .stat-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    
        svg {
          width: 1.2rem;
          height: 1.2rem;
        }
      }
    
      .stat-content {
        .stat-label {
          font-size: 0.75rem;
          font-weight: 500;
          color: #6b7280;
          margin-bottom: 0.125rem;
        }
    
        .stat-value {
          font-size: 1.25rem;
          font-weight: 700;
          color: #111827;
        }
      }
    
      &.stat-indigo {
        background: #eef2ff;
        .stat-icon {
          background: #dbeafe;
          color: #4f46e5;
        }
        .stat-label {
          color: #4f46e5;
        }
        .stat-value {
          color: #4338ca;
        }
      }
    
      &.stat-green {
        background: #edfaf4;
        .stat-icon {
          background: #d1fae5;
          color: #10b981;
        }
        .stat-label {
          color: #10b981;
        }
        .stat-value {
          color: #065f46;
        }
      }
    
      &.stat-purple {
        background: #f5f3ff;
        .stat-icon {
          background: #ede9fe;
          color: #9333ea;
        }
        .stat-label {
          color: #9333ea;
        }
        .stat-value {
          color: #7e22ce;
        }
      }
    }
    
    .icon-large {
      width: 1.5rem;
      height: 1.5rem;
    }
    
    .icon-small {
      width: 1rem;
      height: 1rem;
    }
    
    @media (max-width: 768px) {
      .header-content {
        flex-direction: column;
        align-items: flex-start;
      }
    
      .header-right {
        text-align: left;
        margin-top: 0.5rem;
      }
    
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
    </style>

