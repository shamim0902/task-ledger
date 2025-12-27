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
    border-radius: 1rem;
    width: 100%;
    max-width: 540px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    animation: slideUp 0.3s ease;
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
    padding: 1.75rem;
    border-bottom: 2px solid #e5e7eb;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;

        .modal-icon {
            width: 1.5rem;
            height: 1.5rem;
            color: #4f46e5;
        }
    }

    .modal-close {
        background: #fee2e2;
        border: none;
        color: #dc2626;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 0.5rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;

        .icon-small {
            width: 1.25rem;
            height: 1.25rem;
        }

        &:hover {
            background: #fecaca;
            transform: scale(1.1);
        }
    }
}

.modal-body {
    padding: 1.75rem;

    .form-group {
        margin-bottom: 1.5rem;

        &:last-child {
            margin-bottom: 0;
        }
    }

    .form-label {
        display: block;
        font-size: 0.9375rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.625rem;
        font-size: 0.9375rem;
        transition: all 0.2s;
        background: #f9fafb;

        &:focus {
            outline: none;
            border-color: #4f46e5;
            background: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        &::placeholder {
            color: #9ca3af;
        }
    }

    .form-select {
        background: white;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1.25rem;
        padding-right: 2.5rem;
    }
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.75rem;
    border-top: 2px solid #e5e7eb;
    background: #f9fafb;

    .btn-cancel {
        background: white;
        color: #374151;
        border: 2px solid #e5e7eb;
        border-radius: 0.625rem;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;

        &:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }
    }

    .btn-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        color: white;
        border: none;
        border-radius: 0.625rem;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);

        &:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px -2px rgba(79, 70, 229, 0.4);
        }

        &:active {
            transform: translateY(0);
        }
    }
}
</style>

