<template>
    <div class="member-list">
        <div class="member-list-header">
            <h3 class="list-title">Members</h3>
            <span class="member-count">{{ members.length }}</span>
        </div>
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading members...</p>
        </div>
        <div v-else-if="members.length === 0" class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <p>No assigned members</p>
            <p class="empty-hint">Assign members to review their tasks</p>
        </div>
        <div v-else class="members">
            <div
                v-for="member in members"
                :key="member.id"
                :class="['member-item', { active: selectedMemberId === member.id }]"
                @click="$emit('select-member', member.id)"
            >
                <div class="member-avatar">
                    <span class="avatar-text">{{ member.initials }}</span>
                </div>
                <div class="member-info">
                    <div class="member-name">{{ member.name }}</div>
                    <div class="member-email">{{ member.email }}</div>
                </div>
                <div v-if="member.unread_count > 0" class="unread-badge">
                    {{ member.unread_count }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'MemberList',
    props: {
        members: {
            type: Array,
            default: () => [],
        },
        selectedMemberId: {
            type: Number,
            default: null,
        },
        loading: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['select-member'],
};
</script>

<style lang="scss" scoped>
.member-list {
    width: 100%;
}

.member-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

.list-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.member-count {
    font-size: 0.75rem;
    color: #6b7280;
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 0.75rem;
}

.loading-state,
.empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: #6b7280;
    font-size: 0.875rem;

    .empty-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 1rem;
        opacity: 0.4;
        color: #9ca3af;
    }

    .empty-hint {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 0.5rem;
    }

    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #10b981;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
}

.members {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.member-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;

    &:hover {
        background: #f9fafb;
    }

    &.active {
        background: #eff6ff;
        border: 1px solid #3b82f6;
    }
}

.member-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.avatar-text {
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
}

.member-info {
    flex: 1;
    min-width: 0;
}

.member-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #111827;
    margin-bottom: 0.125rem;
}

.member-email {
    font-size: 0.75rem;
    color: #6b7280;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.unread-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 1.5rem;
    height: 1.5rem;
    padding: 0 0.375rem;
    background: #ef4444;
    color: white;
    border-radius: 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
}
</style>

