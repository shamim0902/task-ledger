<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">{{ role ? 'Edit Role' : 'Create Role' }}</h3>
                <button @click="$emit('close')" class="modal-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="handleSubmit" class="modal-body">
                <div class="form-group">
                    <label for="role-name">Role Name *</label>
                    <input
                        id="role-name"
                        type="text"
                        v-model="formData.name"
                        required
                        placeholder="e.g., Developer, Designer"
                        class="form-input"
                    />
                </div>

                <div class="form-group">
                    <label for="role-slug">Slug *</label>
                    <input
                        id="role-slug"
                        type="text"
                        v-model="formData.slug"
                        required
                        placeholder="e.g., developer, designer"
                        class="form-input"
                    />
                    <small class="form-hint">URL-friendly identifier (lowercase, no spaces)</small>
                </div>

                <div class="form-group">
                    <label for="role-description">Description</label>
                    <textarea
                        id="role-description"
                        v-model="formData.description"
                        rows="3"
                        placeholder="Describe the role and its purpose..."
                        class="form-textarea"
                    ></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" @click="$emit('close')" class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-primary">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: 'RoleForm',
    props: {
        role: {
            type: Object,
            default: null,
        },
    },
    emits: ['close', 'save'],
    data() {
        return {
            formData: {
                name: '',
                slug: '',
                description: '',
            },
        };
    },
    watch: {
        role: {
            immediate: true,
            handler(newRole) {
                if (newRole) {
                    this.formData = {
                        name: newRole.name || '',
                        slug: newRole.slug || '',
                        description: newRole.description || '',
                    };
                } else {
                    this.formData = {
                        name: '',
                        slug: '',
                        description: '',
                    };
                }
            },
        },
    },
    methods: {
        handleSubmit() {
            const data = {
                ...this.formData,
            };

            if (this.role) {
                data.id = this.role.id;
            }

            this.$emit('save', data);
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
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.modal-content {
    background: white;
    border-radius: 0.75rem;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

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
    background: #f9fafb;
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

.modal-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.25rem;

    label {
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

        &:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
    }

    .form-textarea {
        resize: vertical;
        font-family: inherit;
    }

    .form-hint {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: #6b7280;
    }
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.btn-cancel {
    padding: 0.625rem 1.25rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
}

.btn-primary {
    padding: 0.625rem 1.25rem;
    background: #6366f1;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    color: white;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
        background: #4f46e5;
    }
}
</style>

