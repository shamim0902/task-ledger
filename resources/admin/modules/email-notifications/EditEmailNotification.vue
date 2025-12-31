<template>
    <div class="edit-email-notification-wrapper">

        <!-- Main Content Area -->
        <div class="app-content">
            <div v-if="loading" class="loading-state">
                <div class="loading-spinner"></div>
                <p>Loading notification settings...</p>
            </div>

            <template v-else>
                <div class="edit-header">
                    <el-breadcrumb separator="/">
                        <el-breadcrumb-item>
                            <router-link to="/settings">
                                Settings
                            </router-link>
                        </el-breadcrumb-item>
                        <el-breadcrumb-item>
                            <router-link to="/settings/email-notifications">
                                Email Notifications
                            </router-link>
                        </el-breadcrumb-item>
                        <el-breadcrumb-item>
                            <span class="capitalize">{{ notificationData.title }}</span>
                        </el-breadcrumb-item>
                    </el-breadcrumb>

                    <div class="setting-switcher">
                        <el-switch
                            v-model="notificationData.settings.active"
                            active-value="yes"
                            inactive-value="no"
                            active-text="Enable this email notification!"
                        ></el-switch>
                    </div>
                </div>

                <div class="notification-form">
                    <el-form label-position="top">
                        <el-form-item label="Email Subject">
                            <div class="subject-input-wrapper">
                                <el-input
                                    v-model="notificationData.settings.subject"
                                    :class="{ 'is-focus': focusSubjectInput }"
                                    @focus="focusSubjectInput = true"
                                    @blur="focusSubjectInput = false"
                                    placeholder="Email Subject"
                                >
                                    <template #append>
                                        <el-dropdown @command="onShortCodeSelected" placement="bottom-end">
                                            <el-button type="info" size="small" plain>
                                                Add ShortCodes
                                                <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </el-button>
                                            <template #dropdown>
                                                <el-dropdown-menu>
                                                    <el-dropdown-item
                                                        v-for="(group, groupKey) in shortCodes"
                                                        :key="groupKey"
                                                        :divided="groupKey !== Object.keys(shortCodes)[0]"
                                                    >
                                                        <div class="shortcode-group-header">{{ group.label }}</div>
                                                    </el-dropdown-item>
                                                    <el-dropdown-item
                                                        v-for="(group, groupKey) in shortCodes"
                                                        :key="groupKey"
                                                    >
                                                        <template v-for="(desc, code) in group.shortcodes" :key="code">
                                                            <el-dropdown-item :command="code" divided>
                                                                <div class="shortcode-item">
                                                                    <code>{{ code }}</code>
                                                                    <span>{{ desc }}</span>
                                                                </div>
                                                            </el-dropdown-item>
                                                        </template>
                                                    </el-dropdown-item>
                                                </el-dropdown-menu>
                                            </template>
                                        </el-dropdown>
                                    </template>
                                </el-input>
                            </div>
                        </el-form-item>

                        <el-form-item label="Email Body Type">
                            <el-radio-group v-model="notificationData.settings.is_default_body">
                                <el-radio-button label="Default Body" value="yes"/>
                                <el-radio-button label="Customized Body" value="no"/>
                            </el-radio-group>
                        </el-form-item>

                        <el-form-item
                            v-if="notificationData.settings.is_default_body === 'no' && !loading"
                            label="Email Body"
                        >
                            <div class="email-body-editor">
                                <el-input
                                    v-model="notificationData.settings.email_body"
                                    type="textarea"
                                    :rows="15"
                                    placeholder="Enter custom email body HTML"
                                ></el-input>
                                <div class="editor-help">
                                    <p class="help-text">You can use HTML and shortcodes in the email body. Available shortcodes:</p>
                                    <div class="shortcodes-list">
                                        <div
                                            v-for="(group, groupKey) in shortCodes"
                                            :key="groupKey"
                                            class="shortcode-group"
                                        >
                                            <strong>{{ group.label }}:</strong>
                                            <div class="shortcode-items">
                                                <span
                                                    v-for="(desc, code) in group.shortcodes"
                                                    :key="code"
                                                    class="shortcode-tag"
                                                >
                                                    <code>{{ code }}</code>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-form-item>
                    </el-form>
                </div>

                <div class="setting-save-action">
                    <el-button
                        @click="updateNotification"
                        type="primary"
                        :disabled="saving"
                        :loading="saving"
                    >
                        {{ saving ? 'Updating' : 'Update' }}
                    </el-button>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    name: 'EditEmailNotification',
    data() {
        return {
            loading: false,
            saving: false,
            name: '',
            notificationData: {
                title: '',
                description: '',
                event: '',
                name: '',
                recipient: '',
                settings: {
                    active: '',
                    is_default_body: 'yes',
                    email_body: '',
                    subject: '',
                }
            },
            shortCodes: {},
            focusSubjectInput: false,
        };
    },
    methods: {
        getNotification() {
            this.loading = true;
            this.name = this.$route.params.name;
            
            this.$get(`email-notifications/${this.name}`)
                .then((response) => {
                    this.notificationData = response.data || this.notificationData;
                    this.shortCodes = response.shortcodes || {};
                })
                .catch((error) => {
                    this.$notify({
                        type: 'error',
                        text: error.data?.message || 'Failed to load notification'
                    });
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        updateNotification() {
            this.saving = true;
            
            this.$put(`email-notifications/${this.name}`, {
                settings: this.notificationData.settings
            })
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
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        onShortCodeSelected(code) {
            this.notificationData.settings.subject += code;
        },
    },
    mounted() {
        this.getNotification();
    },
};
</script>

<style lang="scss" scoped>
.edit-email-notification-wrapper {
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

.edit-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.setting-switcher {
    display: flex;
    align-items: center;
}

.notification-form {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.subject-input-wrapper {
    position: relative;
}

.icon-small {
    width: 1rem;
    height: 1rem;
    margin-left: 0.25rem;
}

.email-body-editor {
    .editor-help {
        margin-top: 1rem;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;

        .help-text {
            margin: 0 0 0.75rem 0;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .shortcodes-list {
            .shortcode-group {
                margin-bottom: 1rem;

                &:last-child {
                    margin-bottom: 0;
                }

                strong {
                    display: block;
                    margin-bottom: 0.5rem;
                    color: #111827;
                    font-size: 0.875rem;
                }

                .shortcode-items {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.5rem;

                    .shortcode-tag {
                        code {
                            padding: 0.25rem 0.5rem;
                            background: #eef2ff;
                            color: #4338ca;
                            border-radius: 0.25rem;
                            font-size: 0.75rem;
                            font-family: 'Courier New', monospace;
                        }
                    }
                }
            }
        }
    }
}

.shortcode-group-header {
    font-weight: 600;
    color: #111827;
    padding: 0.5rem 0;
}

.shortcode-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;

    code {
        font-family: 'Courier New', monospace;
        color: #4338ca;
        font-weight: 600;
    }

    span {
        font-size: 0.75rem;
        color: #6b7280;
    }
}

.setting-save-action {
    display: flex;
    justify-content: flex-end;
    padding: 1rem;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
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
</style>

