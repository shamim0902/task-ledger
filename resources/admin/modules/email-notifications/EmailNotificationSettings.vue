<template>
    <div class="email-notifications-wrapper">

        <!-- Main Content Area -->
        <div class="app-content">
            <div class="notifications-content">
                <div v-if="loading" class="loading-state">
                    <div class="loading-spinner"></div>
                    <p>Loading notifications...</p>
                </div>

                <div v-else class="notifications-list">
                    <el-table :data="notifications" style="width: 100%">
                        <el-table-column
                            prop="title"
                            label="Notification Name"
                            min-width="300"
                        >
                            <template #default="scope">
                                <h4 class="notification-title">{{ scope.row.title }}</h4>
                                <p class="notification-description">{{ scope.row.description }}</p>
                            </template>
                        </el-table-column>

                        <el-table-column
                            prop="recipient"
                            label="Recipient"
                            width="120"
                        >
                            <template #default="scope">
                                <span class="recipient-badge">{{ formatRecipient(scope.row.recipient) }}</span>
                            </template>
                        </el-table-column>

                        <el-table-column label="Status" width="150" align="center">
                            <template #default="scope">
                                <div class="notification-actions">
                                    <el-switch
                                        @change="value => enableNotification(value, scope.row.name)"
                                        v-model="scope.row.settings.active"
                                        active-value="yes"
                                        inactive-value="no"
                                    ></el-switch>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column label="Actions" width="100" align="center">
                            <template #default="scope">
                                <el-button
                                    type="primary"
                                    size="small"
                                    @click="editNotification(scope.row.name)"
                                >
                                    <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </el-button>
                            </template>
                        </el-table-column>

                        <template #empty>
                            <div class="empty-state">
                                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <p>No email notifications available</p>
                            </div>
                        </template>
                    </el-table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'EmailNotificationSettings',
    data() {
        return {
            loading: true,
            notifications: [],
        };
    },
    methods: {
        getNotifications() {
            this.loading = true;
            this.$get('email-notifications')
                .then((response) => {
                    this.notifications = response.data || [];
                })
                .catch((error) => {
                    this.$notify({
                        type: 'error',
                        text: 'Failed to load email notifications'
                    });
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        enableNotification(active, name) {
            this.$post(`email-notifications/${name}/enable`, { active })
                .then((response) => {
                    this.$notify({
                        type: 'success',
                        text: response.message || 'Notification updated successfully'
                    });
                })
                .catch((error) => {
                    this.$notify({
                        type: 'error',
                        text: error.data?.message || 'Failed to update notification'
                    });
                });
        },
        editNotification(name) {
            this.$router.push({
                name: 'settings.email-notifications.edit',
                params: { name }
            });
        },
        formatRecipient(recipient) {
            if (recipient === 'manager') {
                return 'Manager';
            }
            return recipient ? recipient.charAt(0).toUpperCase() + recipient.slice(1) : '';
        },
    },
    mounted() {
        this.getNotifications();
    },
};
</script>

<style lang="scss" scoped>
.email-notifications-wrapper {
    min-height: 100vh;
    background: #f9fafb;
}

.app-navbar {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.75rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.brand-icon {
    width: 2rem;
    height: 2rem;
    color: #6366f1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.brand-text {
    .brand-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        line-height: 1.2;
    }

    .brand-subtitle {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
        line-height: 1.2;
    }
}

.app-content {
    padding: 1.5rem;
}

.notifications-content {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.loading-state {
    padding: 3rem;
    text-align: center;
    color: #6b7280;

    .loading-spinner {
        width: 40px;
        height: 40px;
        margin: 0 auto 1rem;
        border: 3px solid #e5e7eb;
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    p {
        margin: 0;
        font-size: 0.875rem;
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.notifications-list {
    padding: 1rem;
}

.notification-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
}

.notification-description {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0;
    line-height: 1.4;
}

.recipient-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background: #eef2ff;
    color: #4338ca;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.notification-actions {
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-small {
    width: 1rem;
    height: 1rem;
    margin-right: 0.25rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 1rem;
        color: #d1d5db;
    }

    p {
        margin: 0;
        font-size: 0.875rem;
    }
}
</style>

