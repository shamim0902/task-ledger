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
    background: #f9fafb;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid #e5e7eb;
}

.subtasks-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;

    .subtasks-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
}

.add-subtask-button {
    background: #6366f1;
    color: white;
    border: none;
    border-radius: 0.375rem;
    padding: 0.5rem 0.875rem;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    transition: all 0.2s;
    box-shadow: 0 1px 2px rgba(99, 102, 241, 0.2);

    .icon-tiny {
        width: 0.875rem;
        height: 0.875rem;
    }

    &:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(99, 102, 241, 0.3);
    }
}

.add-subtask-form {
    margin-bottom: 0.75rem;
    padding: 0.75rem;
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;

    .el-input-number {
        width: 100px;
    }

    .form-group {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .subtask-input {
        flex: 1;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.8125rem;
        transition: all 0.2s;
        background: #f9fafb;

        &:focus {
            outline: none;
            border-color: #6366f1;
            background: white;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }
    }
}

.subtask-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;

    .btn-save {
        background: #10b981;
        color: white;
        border: none;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        font-size: 0.8125rem;
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
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        font-size: 0.8125rem;
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
    gap: 0.5rem;
}

.subtask-item {
    background: white;
    padding: 0.75rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.2s;
    border: 1px solid #e5e7eb;

    &:hover {
        border-color: #d1d5db;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    &.completed {
        opacity: 0.7;
        background: #ecfdf5;
        border-color: #a7f3d0;

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
        width: 1rem;
        height: 1rem;
        cursor: pointer;
        opacity: 0;
        position: absolute;

        &:checked + .checkmark {
            background: #6366f1;
            border-color: #6366f1;

            &::after {
                display: block;
            }
        }
    }

    .checkmark {
        width: 1rem;
        height: 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.25rem;
        background: white;
        position: relative;
        transition: all 0.2s;

        &::after {
            content: '';
            position: absolute;
            display: none;
            left: 0.3rem;
            top: 0.1rem;
            width: 0.3rem;
            height: 0.5rem;
            border: solid white;
            border-width: 0 1.5px 1.5px 0;
            transform: rotate(45deg);
        }
    }
}

.subtask-text {
    flex: 1;
    color: #374151;
    font-size: 0.8125rem;
    font-weight: 500;
    line-height: 1.4;
}

.weight-badge-small {
    background: #f3e8ff;
    color: #6b21a8;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.6875rem;
    font-weight: 600;
    border: 1px solid #e9d5ff;
}

.completed-badge {
    background: #d1fae5;
    color: #065f46;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.6875rem;
    font-weight: 600;
    border: 1px solid #a7f3d0;
}

.subtask-actions-inline {
    display: flex;
    gap: 0.375rem;

    .btn-action {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
}

.no-subtasks {
    text-align: center;
    padding: 2rem 1rem;
    color: #6b7280;

    .empty-icon {
        width: 2.5rem;
        height: 2.5rem;
        margin: 0 auto 0.75rem;
        opacity: 0.4;
        color: #9ca3af;
    }

    p {
        margin: 0;
        font-size: 0.875rem;
    }
}
</style>

