<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create New Project</h3>
                <button @click="$emit('close')" class="modal-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Project Title *</label>
                    <input
                        v-model="formData.title"
                        type="text"
                        placeholder="Enter project title..."
                        class="form-input"
                        @keyup.enter="handleSubmit"
                    />
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea
                        v-model="formData.description"
                        placeholder="Enter project description (optional)..."
                        class="form-textarea"
                        rows="4"
                    ></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Currency</label>
                    <input
                        v-model="formData.currency"
                        type="text"
                        placeholder="USD (optional)"
                        class="form-input"
                    />
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" @click="$emit('close')" class="btn-cancel">Cancel</button>
                <button @click="handleSubmit" class="btn-primary" :disabled="!formData.title || submitting">
                    {{ submitting ? 'Creating...' : 'Create Project' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CreateProjectModal',
    emits: ['close', 'created'],
    data() {
        return {
            formData: {
                title: '',
                description: '',
                currency: '',
            },
            submitting: false,
        };
    },
    methods: {
        async handleSubmit() {
            if (!this.formData.title.trim()) {
                this.$notify({
                    type: 'warning',
                    text: 'Please enter a project title',
                });
                return;
            }

            this.submitting = true;
            try {
                const response = await this.$post('projects', {
                    board: this.formData,
                });

                this.$notify({
                    type: 'success',
                    text: response.message || 'Project created successfully',
                });

                this.$emit('created', response.board);
            } catch (error) {
                console.error('Error creating project:', error);
                this.$notify({
                    type: 'error',
                    text: error.message || 'Failed to create project',
                });
            } finally {
                this.submitting = false;
            }
        },
    },
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
    border-radius: 0.75rem;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    overflow: hidden;
    animation: slideUp 0.2s ease;
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
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .modal-close {
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        border-radius: 0.375rem;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s;

        svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        &:hover {
            background: #f3f4f6;
            color: #111827;
        }
    }
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

.form-group {
    margin-bottom: 1.25rem;

    &:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
        background: white;
        font-family: inherit;

        &:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        &::placeholder {
            color: #9ca3af;
        }
    }

    .form-textarea {
        resize: vertical;
    }
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
}

.btn-cancel,
.btn-primary {
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-cancel {
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;

    &:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
}

.btn-primary {
    background: #6366f1;
    color: white;

    &:hover:not(:disabled) {
        background: #4f46e5;
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
}
</style>

