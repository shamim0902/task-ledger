<template>
    <div class="nav-card">
        <div class="nav-tabs">
            <button 
                @click="$emit('view-change', 'create')" 
                :class="['nav-tab', { active: currentView === 'create' }]"
            >
                <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Log
            </button>
            <button 
                @click="$emit('view-change', 'history')"
                :class="['nav-tab', { active: currentView === 'history' }]"
            >
                <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                View History
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'NavigationTabs',
    props: {
        currentView: {
            type: String,
            required: true
        }
    },
    emits: ['view-change']
};
</script>

<style lang="scss" scoped>
.nav-card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    margin-bottom: 0.75rem;
    overflow: hidden;
    width: 240px;
}

.nav-tabs {
    float: right;
    display: flex;
    background: #f9fafb;
    padding: 0.25rem;
    gap: 0.25rem;
}

.nav-tab {
    flex: 1;
    padding: 0.375rem 0.625rem;
    font-weight: 500;
    font-size: 0.75rem;
    color: #6b7280;
    background: transparent;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    position: relative;
    line-height: 1.2;
    white-space: nowrap;

    .icon-small {
        width: 0.75rem;
        height: 0.75rem;
        flex-shrink: 0;
    }

    &:hover {
        color: #6366f1;
        background: rgba(99, 102, 241, 0.08);
    }

    &.active {
        color: #6366f1;
        background: white;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        font-weight: 600;
    }
}

@media (max-width: 768px) {
    .nav-card {
        width: 100%;
    }
}
</style>

