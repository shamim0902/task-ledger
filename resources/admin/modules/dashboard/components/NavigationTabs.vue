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
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.nav-tabs {
    display: flex;
    border-bottom: 1px solid #e5e7eb;
}

.nav-tab {
    flex: 1;
    padding: 1rem 1.5rem;
    font-weight: 500;
    color: #6b7280;
    background: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    position: relative;

    .icon-small {
        width: 1.25rem;
        height: 1.25rem;
    }

    &:hover {
        background: #f9fafb;
        color: #4f46e5;
    }

    &.active {
        color: #4f46e5;
        background: #eef2ff;

        &::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #4f46e5;
        }
    }
}
</style>

