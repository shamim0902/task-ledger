<template>
    <div class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content import-modal">
            <div class="modal-header">
                <h3 class="modal-title">Import Projects from Boards</h3>
                <button @click="$emit('close')" class="modal-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <div v-if="loading" class="loading-state">
                    <div class="spinner"></div>
                    <p>Loading boards...</p>
                </div>

                <div v-else-if="availableBoards.length === 0" class="empty-state">
                    <p>No boards available to import.</p>
                </div>

                <div v-else>
                    <div class="search-section">
                        <div class="search-input-wrapper">
                            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search boards..."
                                class="search-input"
                            />
                        </div>
                    </div>

                    <div class="boards-list">
                        <div
                            v-for="board in filteredBoards"
                            :key="board.id"
                            class="board-item"
                        >
                            <label class="board-checkbox">
                                <input
                                    type="checkbox"
                                    :value="board.id"
                                    v-model="selectedBoards"
                                />
                                <div class="board-info">
                                    <h4 class="board-title">{{ board.title }}</h4>
                                    <p v-if="board.description" class="board-description">
                                        {{ board.description }}
                                    </p>
                                    <div class="board-meta">
                                        <span class="meta-item">
                                            {{ board.stages?.length || 0 }} Stages
                                        </span>
                                        <span class="meta-item">
                                            {{ board.users?.length || 0 }} Members
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" @click="$emit('close')" class="btn-cancel">Cancel</button>
                <button
                    @click="handleImport"
                    class="btn-primary"
                    :disabled="selectedBoards.length === 0 || importing"
                >
                    {{ importing ? 'Importing...' : `Import ${selectedBoards.length} Project(s)` }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ImportProjectsModal',
    emits: ['close', 'imported'],
    data() {
        return {
            availableBoards: [],
            selectedBoards: [],
            loading: false,
            importing: false,
            searchQuery: '',
        };
    },
    computed: {
        filteredBoards() {
            if (!this.searchQuery) {
                return this.availableBoards;
            }
            const query = this.searchQuery.toLowerCase();
            return this.availableBoards.filter(board =>
                board.title?.toLowerCase().includes(query) ||
                board.description?.toLowerCase().includes(query)
            );
        },
    },
    mounted() {
        this.loadBoards();
    },
    methods: {
        async loadBoards() {
            this.loading = true;
            try {
                // Get boards from fluent-boards
                const response = await this.$get('projects');
                this.availableBoards = response.boards?.data || response.boards || [];
            } catch (error) {
                console.error('Error loading boards:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to load boards',
                });
            } finally {
                this.loading = false;
            }
        },
        async handleImport() {
            if (this.selectedBoards.length === 0) {
                this.$notify({
                    type: 'warning',
                    text: 'Please select at least one board to import',
                });
                return;
            }

            this.importing = true;
            try {
                const response = await this.$post('projects/import', {
                    board_ids: this.selectedBoards,
                });

                if (response.errors && response.errors.length > 0) {
                    this.$notify({
                        type: 'warning',
                        text: response.message + '. Some boards could not be imported.',
                    });
                } else {
                    this.$notify({
                        type: 'success',
                        text: response.message || 'Projects imported successfully',
                    });
                }

                this.$emit('imported', response);
            } catch (error) {
                console.error('Error importing projects:', error);
                this.$notify({
                    type: 'error',
                    text: error.message || 'Failed to import projects',
                });
            } finally {
                this.importing = false;
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

.import-modal {
    max-width: 600px;
    max-height: 90vh;
}

.modal-content {
    background: white;
    border-radius: 0.75rem;
    width: 100%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
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

.loading-state,
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 2rem;
    color: #6b7280;
    text-align: center;

    .spinner {
        width: 2rem;
        height: 2rem;
        border: 3px solid #e5e7eb;
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 1rem;
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.search-section {
    margin-bottom: 1.5rem;
}

.search-input-wrapper {
    position: relative;

    .search-icon {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1.25rem;
        height: 1.25rem;
        color: #9ca3af;
        pointer-events: none;
    }

    .search-input {
        width: 100%;
        padding: 0.625rem 0.875rem 0.625rem 2.75rem;
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
}

.boards-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-height: 400px;
    overflow-y: auto;
}

.board-item {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    transition: all 0.2s;

    &:hover {
        border-color: #6366f1;
        background: #f9fafb;
    }

    .board-checkbox {
        display: flex;
        align-items: flex-start;
        padding: 1rem;
        cursor: pointer;
        margin: 0;

        input[type="checkbox"] {
            margin-right: 0.875rem;
            margin-top: 0.25rem;
            width: 1.125rem;
            height: 1.125rem;
            cursor: pointer;
            accent-color: #6366f1;
        }

        .board-info {
            flex: 1;

            .board-title {
                font-size: 0.9375rem;
                font-weight: 600;
                color: #111827;
                margin: 0 0 0.375rem;
            }

            .board-description {
                font-size: 0.8125rem;
                color: #6b7280;
                margin: 0 0 0.5rem;
                line-height: 1.5;
            }

            .board-meta {
                display: flex;
                gap: 1rem;
                font-size: 0.75rem;
                color: #9ca3af;

                .meta-item {
                    display: flex;
                    align-items: center;
                }
            }
        }
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

