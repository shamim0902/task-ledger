<template>
    <div v-if="show" class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <svg class="modal-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Task
                </h3>
                <button @click="$emit('close')" class="modal-close" title="Close">
                    <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Task Title *</label>
                    <input 
                        type="text" 
                        v-model="localTask.title" 
                        placeholder="Enter task title..."
                        class="form-input" 
                        @keyup.enter="handleSubmit"
                    />
                </div>
                <div class="form-group">
                    <label class="form-label">Board *</label>
                    <input 
                        type="text" 
                        v-model="localTask.board" 
                        placeholder="e.g., Backend, Frontend..."
                        class="form-input" 
                    />
                </div>
                <div class="form-group">
                    <label class="form-label">Weight (Story Points) *</label>
                    <input 
                        type="number" 
                        v-model.number="localTask.weight" 
                        min="1" 
                        placeholder="e.g., 5"
                        class="form-input" 
                    />
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select v-model="localTask.status" class="form-select">
                        <option value="To Do">To Do</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Done">Done</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button @click="$emit('close')" class="btn-cancel">Cancel</button>
                <button @click="handleSubmit" class="btn-primary">Add Task</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AddTaskModal',
    props: {
        show: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            localTask: {
                title: '',
                board: '',
                weight: '',
                status: 'To Do'
            }
        };
    },
    emits: ['close', 'submit'],
    watch: {
        show(newVal) {
            if (!newVal) {
                // Reset form when modal closes
                this.localTask = {
                    title: '',
                    board: '',
                    weight: '',
                    status: 'To Do'
                };
            }
        }
    },
    methods: {
        handleSubmit() {
            if (!this.localTask.title.trim() || !this.localTask.board.trim() || !this.localTask.weight) {
                this.$notify({
                    type: 'warning',
                    text: 'Please fill in all required fields'
                });
                return;
            }

            this.$emit('submit', { ...this.localTask });
            this.$emit('close');
        }
    }
};
</script>

<style lang="scss" scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.modal-content {
    background: white;
    border-radius: 0.5rem;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    animation: slideUp 0.2s ease;
    max-height: 90vh;
    overflow-y: auto;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.875rem 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;

    .modal-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;

        .modal-icon {
            width: 1.125rem;
            height: 1.125rem;
            color: #6366f1;
        }
    }

    .modal-close {
        background: transparent;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 0.25rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;

        .icon-small {
            width: 1rem;
            height: 1rem;
        }

        &:hover {
            background: #f3f4f6;
            color: #374151;
        }
    }
}

.modal-body {
    padding: 1rem;

    .form-group {
        margin-bottom: 0.875rem;

        &:last-child {
            margin-bottom: 0;
        }
    }

    .form-label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.375rem;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        transition: all 0.2s;
        background: white;

        &:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }

        &::placeholder {
            color: #9ca3af;
        }
    }

    .form-select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        background-size: 1rem;
        padding-right: 2rem;
    }
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;

    .btn-cancel {
        background: white;
        color: #374151;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;

        &:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
    }

    .btn-primary {
        background: #6366f1;
        color: white;
        border: none;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 1px 2px 0 rgba(99, 102, 241, 0.2);

        &:hover {
            background: #4f46e5;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px 0 rgba(99, 102, 241, 0.3);
        }

        &:active {
            transform: translateY(0);
        }
    }
}
</style>

