<template>
    <div class="subtasks-section">
        <div class="subtasks-header">
            <h4 class="subtasks-title">Subtasks</h4>
            <button 
                @click="$emit('toggle-add-form')" 
                class="add-subtask-button"
            >
                <svg class="icon-tiny" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4" />
                </svg>
                Add Subtask
            </button>
        </div>

        <!-- Add Subtask Input -->
        <div v-if="showAddForm" class="add-subtask-form">
            <div class="form-group">
                <input 
                    type="text" 
                    v-model="localSubtaskTitle" 
                    @keyup.enter="handleAddSubtask"
                    placeholder="Enter subtask title..." 
                    class="subtask-input" 
                />
                <el-input-number 
                    size="large" 
                    v-model="localWeight" 
                    :min="1" 
                    :max="10" 
                    placeholder="Weight" 
                />
            </div>
            <div class="subtask-actions">
                <button @click="handleAddSubtask" class="btn-save">Save</button>
                <button @click="handleCancel" class="btn-cancel">Cancel</button>
            </div>
        </div>

        <!-- Subtasks List -->
        <div v-if="subtasks.length > 0" class="subtasks-list">
            <div 
                v-for="subtask in subtasks" 
                :key="subtask.id"
                :class="['subtask-item', { completed: subtask.status === 'closed' }]"
            >
                <label class="subtask-checkbox">
                    <input 
                        disabled 
                        type="checkbox" 
                        :checked="subtask.status === 'closed'"
                    />
                    <span class="checkmark"></span>
                </label>
                <span class="subtask-text">{{ subtask.title }}</span>
                <span class="weight-badge-small">{{ subtask.weight }} pts</span>
                <span v-if="subtask.status === 'closed'" class="completed-badge">
                    ✓ Completed
                </span>
                <div v-else class="subtask-actions-inline">
                    <el-button 
                        v-if="subtask?.weight <= 0" 
                        @click="$emit('mark-completed', subtask)" 
                        class="btn-action"
                    >
                        Mark Completed
                    </el-button>
                    <el-button 
                        v-else 
                        @click="$emit('add-to-log', subtask)" 
                        class="btn-action btn-primary"
                    >
                        Add Today Log
                    </el-button>
                </div>
            </div>
        </div>
        <div v-else class="no-subtasks">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p>No subtasks yet. Click "Add Subtask" to create one.</p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SubtasksList',
    props: {
        subtasks: {
            type: Array,
            default: () => []
        },
        showAddForm: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            localSubtaskTitle: '',
            localWeight: 1
        };
    },
    emits: ['toggle-add-form', 'add-subtask', 'mark-completed', 'add-to-log'],
    methods: {
        handleAddSubtask() {
            if (!this.localSubtaskTitle.trim()) {
                this.$notify({
                    type: 'warning',
                    text: 'Please enter a subtask title'
                });
                return;
            }

            this.$emit('add-subtask', {
                title: this.localSubtaskTitle,
                weight: this.localWeight
            });

            this.localSubtaskTitle = '';
            this.localWeight = 1;
        },
        handleCancel() {
            this.localSubtaskTitle = '';
            this.localWeight = 1;
            this.$emit('toggle-add-form');
        }
    }
};
</script>

<style lang="scss" scoped>
.subtasks-section {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e5e7eb;
}

.subtasks-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;

    .subtasks-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }
}

.add-subtask-button {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    color: white;
    border: none;
    border-radius: 0.5rem;
    padding: 0.625rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);

    .icon-tiny {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);
    }
}

.add-subtask-form {
    margin-bottom: 1.25rem;
    padding: 1rem;
    background: white;
    border-radius: 0.5rem;
    border: 2px solid #e5e7eb;

    .el-input-number {
        width: 120px;
    }

    .form-group {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .subtask-input {
        flex: 1;
        padding: 0.625rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;

        &:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
    }
}

.subtask-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;

    .btn-save {
        background: #10b981;
        color: white;
        border: none;
        border-radius: 0.5rem;
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;

        &:hover {
            background: #059669;
            transform: translateY(-1px);
        }
    }

    .btn-cancel {
        background: #6b7280;
        color: white;
        border: none;
        border-radius: 0.5rem;
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;

        &:hover {
            background: #4b5563;
        }
    }
}

.subtasks-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.subtask-item {
    background: white;
    padding: 1rem;
    border-radius: 0.625rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s;
    border: 2px solid #e5e7eb;

    &:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    &.completed {
        opacity: 0.7;
        background: #f0fdf4;
        border-color: #bbf7d0;

        .subtask-text {
            text-decoration: line-through;
        }
    }
}

.subtask-checkbox {
    position: relative;
    display: flex;
    align-items: center;
    cursor: pointer;
    opacity: 0.5;

    input[type="checkbox"] {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
        opacity: 0;
        position: absolute;

        &:checked + .checkmark {
            background: #4f46e5;
            border-color: #4f46e5;

            &::after {
                display: block;
            }
        }
    }

    .checkmark {
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #d1d5db;
        border-radius: 0.375rem;
        background: white;
        position: relative;
        transition: all 0.2s;

        &::after {
            content: '';
            position: absolute;
            display: none;
            left: 0.35rem;
            top: 0.1rem;
            width: 0.35rem;
            height: 0.6rem;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
    }
}

.subtask-text {
    flex: 1;
    color: #374151;
    font-size: 0.9375rem;
    font-weight: 500;
}

.weight-badge-small {
    background: #f3e8ff;
    color: #6b21a8;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.completed-badge {
    background: #d1fae5;
    color: #065f46;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.subtask-actions-inline {
    display: flex;
    gap: 0.5rem;

    .btn-action {
        font-size: 0.75rem;
        padding: 0.5rem 1rem;
    }
}

.no-subtasks {
    text-align: center;
    padding: 2.5rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 1rem;
        opacity: 0.4;
        color: #9ca3af;
    }

    p {
        margin: 0;
        font-size: 0.9375rem;
    }
}
</style>

