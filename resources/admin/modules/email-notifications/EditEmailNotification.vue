<template>
    <div class="edit-email-notification-wrapper">
        <div class="app-content">
            <!-- Loading State -->
            <div v-if="loading" class="loading-state">
                <div class="loading-spinner"></div>
                <p>Loading notification settings...</p>
            </div>

            <template v-else>
                <!-- Header Section -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="breadcrumb-section">
                            <el-breadcrumb separator="/">
                                <el-breadcrumb-item>
                                    <router-link to="/settings">Settings</router-link>
                                </el-breadcrumb-item>
                                <el-breadcrumb-item>
                                    <router-link to="/settings/email-notifications">Email Notifications</router-link>
                                </el-breadcrumb-item>
                                <el-breadcrumb-item>
                                    <span>{{ notificationData.title }}</span>
                                </el-breadcrumb-item>
                            </el-breadcrumb>
                        </div>
                        
                        <div class="header-info">
                            <div class="notification-title">
                                <h2>{{ notificationData.title }}</h2>
                                <p class="notification-description" v-if="notificationData.description">
                                    {{ notificationData.description }}
                                </p>
                            </div>
                            <div class="toggle-section">
                                <el-switch
                                    v-model="notificationData.settings.active"
                                    active-value="yes"
                                    inactive-value="no"
                                />
                                <span class="toggle-label">Enable</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Form Card -->
                <div class="form-card">
                    <el-form label-position="top" class="notification-form">
                        <!-- Email Subject Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <label class="section-label">Email Subject</label>
                                <span class="section-help">The subject line that will appear in the email</span>
                            </div>
                            <div class="subject-input-wrapper">
                                <el-input
                                    ref="subjectInput"
                                    v-model="notificationData.settings.subject"
                                    placeholder="e.g., Daily Task Submission - {{developer.name}}"
                                    size="large"
                                    @focus="handleSubjectFocus"
                                    @blur="handleSubjectBlur"
                                >
                                    <template #append>
                                        <el-dropdown 
                                            ref="shortcodeDropdown"
                                            @command="onShortCodeSelected" 
                                            placement="bottom-end" 
                                            trigger="click"
                                            style="width:100px;"
                                            @visible-change="handleDropdownVisible"
                                        >
                                            <el-button type="primary" class="shortcode-btn">
                                                <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                            </el-button>
                                            <template #dropdown>
                                                <el-dropdown-menu class="shortcode-dropdown">
                                                    <template v-for="(group, groupKey) in shortCodes" :key="groupKey">
                                                        <el-dropdown-item 
                                                            :divided="groupKey !== Object.keys(shortCodes)[0]"
                                                            disabled
                                                            class="shortcode-header-item"
                                                        >
                                                            <div class="shortcode-group-header">
                                                                <span class="header-label">{{ group.label }}</span>
                                                            </div>
                                                        </el-dropdown-item>
                                                        <el-dropdown-item
                                                            v-for="(desc, code) in group.shortcodes"
                                                            :key="`${groupKey}-${code}`"
                                                            :command="code"
                                                            class="shortcode-menu-item"
                                                        >
                                                            <div class="shortcode-item">
                                                                <code class="shortcode-code">{{ code }}</code>
                                                                <span class="shortcode-desc">{{ desc }}</span>
                                                            </div>
                                                        </el-dropdown-item>
                                                    </template>
                                                </el-dropdown-menu>
                                            </template>
                                        </el-dropdown>
                                    </template>
                                </el-input>
                            </div>
                        </div>

                        <!-- Email Body Type Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <label class="section-label">Email Body</label>
                                <span class="section-help">Choose between default or customized email body</span>
                            </div>
                            <el-radio-group v-model="notificationData.settings.is_default_body" size="large">
                                <el-radio-button label="Default Body" value="yes"/>
                                <el-radio-button label="Custom Body" value="no"/>
                            </el-radio-group>
                        </div>

                        <!-- Custom Email Body Editor -->
                        <div v-if="notificationData.settings.is_default_body === 'no' && !loading" class="form-section">
                            <div class="section-header">
                                <label class="section-label">Custom Email Body</label>
                                <span class="section-help">Use HTML and shortcodes to customize the email content</span>
                            </div>
                            <div class="email-body-editor">
                                <el-input
                                    v-model="notificationData.settings.email_body"
                                    type="textarea"
                                    :rows="12"
                                    placeholder="Enter custom email body HTML..."
                                    class="email-textarea"
                                />
                                <div class="editor-help">
                                    <div class="help-header">
                                        <svg class="help-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="help-text">Available Shortcodes</span>
                                    </div>
                                    <div class="shortcodes-list">
                                        <div
                                            v-for="(group, groupKey) in shortCodes"
                                            :key="groupKey"
                                            class="shortcode-group"
                                        >
                                            <div class="group-label">{{ group.label }}</div>
                                            <div class="shortcode-items">
                                                <button
                                                    v-for="(desc, code) in group.shortcodes"
                                                    :key="code"
                                                    class="shortcode-tag"
                                                    @click="copyShortcode(code)"
                                                    :title="desc"
                                                >
                                                    <code>{{ code }}</code>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </el-form>
                </div>

                <!-- Action Footer -->
                <div class="action-footer">
                    <el-button
                        @click="updateNotification"
                        type="primary"
                        size="large"
                        :disabled="saving"
                        :loading="saving"
                        class="save-button"
                    >
                        <svg v-if="!saving" class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ saving ? 'Saving...' : 'Save Changes' }}
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
            subjectInputFocused: false,
            dropdownVisible: false,
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
        handleSubjectFocus() {
            this.subjectInputFocused = true;
        },
        handleSubjectBlur() {
            // Delay blur to allow dropdown clicks
            setTimeout(() => {
                if (!this.dropdownVisible) {
                    this.subjectInputFocused = false;
                }
            }, 200);
        },
        handleDropdownVisible(visible) {
            this.dropdownVisible = visible;
            if (!visible) {
                // Restore focus to input after dropdown closes
                this.$nextTick(() => {
                    if (this.$refs.subjectInput) {
                        const input = this.$refs.subjectInput.$el?.querySelector('input');
                        if (input && this.subjectInputFocused) {
                            input.focus();
                        }
                    }
                });
            }
        },
        onShortCodeSelected(code) {
            // Get the input element
            const input = this.$refs.subjectInput?.$el?.querySelector('input');
            if (!input) {
                // Fallback: just append
                this.notificationData.settings.subject += code;
                return;
            }

            // Get current cursor position
            const start = input.selectionStart || 0;
            const end = input.selectionEnd || 0;
            const currentValue = this.notificationData.settings.subject || '';

            // Insert shortcode at cursor position
            const newValue = 
                currentValue.substring(0, start) + 
                code + 
                currentValue.substring(end);

            // Update the value
            this.notificationData.settings.subject = newValue;

            // Restore cursor position after the inserted shortcode
            this.$nextTick(() => {
                const newPosition = start + code.length;
                input.setSelectionRange(newPosition, newPosition);
                input.focus();
            });
        },
        copyShortcode(code) {
            // Copy to clipboard
            navigator.clipboard.writeText(code).then(() => {
                this.$notify({
                    type: 'success',
                    text: `Copied ${code} to clipboard`
                });
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = code;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                this.$notify({
                    type: 'success',
                    text: `Copied ${code} to clipboard`
                });
            });
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
}

.app-content {
    padding: 0;
}

// Page Header - Full Width
.page-header {
    margin-bottom: 1.5rem;
    .header-content {
        background: white;
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem 1.5rem;
        width: 100%;
    }
    
    .breadcrumb-section {
        margin-bottom: 0.75rem;
        
        :deep(.el-breadcrumb) {
            .el-breadcrumb__item {
                .el-breadcrumb__inner {
                    color: #6b7280;
                    font-size: 0.8125rem;
                    
                    &.is-link {
                        color: #6366f1;
                        text-decoration: none;
                        
                        &:hover {
                            color: #4f46e5;
                        }
                    }
                }
                
                &:last-child {
                    .el-breadcrumb__inner {
                        color: #111827;
                        font-weight: 600;
                    }
                }
            }
        }
    }
    
    .header-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
    }
    
    .notification-title {
        flex: 1;
        min-width: 0;
        
        h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin: 0 0 0.25rem 0;
            line-height: 1.3;
        }
        
        .notification-description {
            font-size: 0.8125rem;
            color: #6b7280;
            margin: 0;
            line-height: 1.4;
        }
    }
    
    .toggle-section {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
        
        .toggle-label {
            font-size: 0.8125rem;
            font-weight: 500;
            color: #374151;
            white-space: nowrap;
        }
    }
}

// Form Card
.form-card {
    background: white;
    border-radius: 0;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.notification-form {
    .form-section {
        margin-bottom: 2rem;
        
        &:last-child {
            margin-bottom: 0;
        }
    }
    
    .section-header {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        margin-bottom: 1rem;
        
        .section-label {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #111827;
        }
        
        .section-help {
            font-size: 0.8125rem;
            color: #6b7280;
        }
    }
}

.subject-input-wrapper {
    position: relative;
    
    :deep(.el-input-group__append) {
        padding: 0;
        border: none;
        background: transparent;
    }
}

.icon-small {
    width: 1rem;
    height: 1rem;
}

.shortcode-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    padding: 0.625rem 1rem;
    height: 100%;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.shortcode-dropdown {
    max-width: 320px;
    max-height: 400px;
    overflow-y: auto;
}

.shortcode-header-item {
    padding: 0.5rem 0.75rem !important;
    background: #f9fafb !important;
    cursor: default !important;
    
    .shortcode-group-header {
        .header-label {
            font-weight: 600;
            font-size: 0.75rem;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    }
}

.shortcode-menu-item {
    padding: 0.5rem 0.75rem !important;
    cursor: pointer;
    transition: background-color 0.15s ease;
    
    &:hover {
        background: #f3f4f6 !important;
    }
}

.shortcode-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    width: 100%;

    .shortcode-code {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Courier New', monospace;
        color: #4338ca;
        font-weight: 600;
        font-size: 0.8125rem;
        line-height: 1.4;
    }

    .shortcode-desc {
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.3;
    }
}

.email-body-editor {
    .email-textarea {
        margin-bottom: 1rem;
    }
    
    .editor-help {
        padding: 1rem;
        background: #f9fafb;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;

        .help-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            
            .help-icon {
                width: 1.125rem;
                height: 1.125rem;
                color: #6366f1;
                flex-shrink: 0;
            }
            
            .help-text {
                margin: 0;
                font-size: 0.875rem;
                font-weight: 600;
                color: #374151;
            }
        }

        .shortcodes-list {
            .shortcode-group {
                margin-bottom: 1rem;

                &:last-child {
                    margin-bottom: 0;
                }

                .group-label {
                    display: block;
                    margin-bottom: 0.625rem;
                    font-size: 0.75rem;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    color: #6366f1;
                }

                .shortcode-items {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.5rem;

                    .shortcode-tag {
                        padding: 0;
                        margin: 0;
                        border: none;
                        background: transparent;
                        cursor: pointer;
                        transition: all 0.15s ease;
                        border-radius: 0.375rem;
                        
                        &:hover {
                            transform: translateY(-1px);
                            
                            code {
                                background: #6366f1;
                                color: white;
                                box-shadow: 0 2px 6px rgba(99, 102, 241, 0.3);
                            }
                        }
                        
                        &:active {
                            transform: translateY(0);
                        }
                        
                        code {
                            display: inline-block;
                            padding: 0.5rem 0.75rem;
                            background: #eef2ff;
                            color: #4338ca;
                            border-radius: 0.375rem;
                            font-size: 0.8125rem;
                            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Courier New', monospace;
                            font-weight: 500;
                            transition: all 0.15s ease;
                            border: 1px solid transparent;
                        }
                    }
                }
            }
        }
    }
}

// Action Footer
.action-footer {
    display: flex;
    justify-content: flex-end;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    
    .save-button {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.5rem;
        font-weight: 600;
    }
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

