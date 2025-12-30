<template>
    <div class="projects-page">
        <div class="page-header">
            <h1 class="page-title">Projects</h1>
            <div class="header-actions">
                <button @click="showCreateModal = true" class="btn-primary">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Project
                </button>
                <button @click="showImportModal = true" class="btn-secondary">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Import from Boards
                </button>
            </div>
        </div>

        <div class="projects-content">
            <!-- Search and Filters -->
            <div class="search-section">
                <div class="search-input-wrapper">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="searchQuery"
                        @input="handleSearch"
                        type="text"
                        placeholder="Search projects..."
                        class="search-input"
                    />
                </div>
            </div>

            <!-- Projects List -->
            <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
                <p>Loading projects...</p>
            </div>

            <div v-else-if="projects.length === 0" class="empty-state">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3>No projects found</h3>
                <p>Create a new project or import from boards to get started.</p>
            </div>

            <div v-else class="projects-grid">
                <div
                    v-for="project in projects"
                    :key="project.id"
                    class="project-card"
                    @click="openProjectDetail(project.id)"
                >
                    <div class="project-header">
                        <h3 class="project-title">{{ project.title }}</h3>
                        <div class="project-meta">
                            <span class="meta-item">
                                <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ formatDate(project.created_at) }}
                            </span>
                        </div>
                    </div>
                    <p v-if="project.description" class="project-description">
                        {{ project.description }}
                    </p>
                    <div class="project-footer">
                        <div class="project-stats">
                            <span class="stat-item">
                                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                {{ project.stages?.length || 0 }} Stages
                            </span>
                            <span class="stat-item">
                                <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                {{ project.users?.length || 0 }} Members
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.total_pages > 1" class="pagination">
                <button
                    @click="loadPage(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    class="pagination-btn"
                >
                    Previous
                </button>
                <span class="pagination-info">
                    Page {{ pagination.current_page }} of {{ pagination.total_pages }}
                </span>
                <button
                    @click="loadPage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.total_pages"
                    class="pagination-btn"
                >
                    Next
                </button>
            </div>
        </div>

        <!-- Create Project Modal -->
        <CreateProjectModal
            v-if="showCreateModal"
            @close="showCreateModal = false"
            @created="handleProjectCreated"
        />

        <!-- Import Projects Modal -->
        <ImportProjectsModal
            v-if="showImportModal"
            @close="showImportModal = false"
            @imported="handleProjectsImported"
        />
    </div>
</template>

<script>
import CreateProjectModal from './components/CreateProjectModal.vue';
import ImportProjectsModal from './components/ImportProjectsModal.vue';

export default {
    name: 'ProjectsPage',
    components: {
        CreateProjectModal,
        ImportProjectsModal,
    },
    data() {
        return {
            projects: [],
            loading: false,
            searchQuery: '',
            searchTimeout: null,
            pagination: null,
            showCreateModal: false,
            showImportModal: false,
        };
    },
    mounted() {
        this.loadProjects();
    },
    methods: {
        async loadProjects(page = 1) {
            this.loading = true;
            try {
                const params = {
                    page,
                    per_page: 20,
                };
                if (this.searchQuery) {
                    params.search = this.searchQuery;
                }
                const response = await this.$get('projects', params);
                const boardsData = response.boards?.data || response.boards || [];
                this.projects = Array.isArray(boardsData) ? boardsData : (boardsData.data || []);
                
                // Handle pagination - Laravel paginator structure
                if (response.boards && typeof response.boards === 'object') {
                    this.pagination = {
                        current_page: response.boards.current_page || 1,
                        total_pages: response.boards.last_page || response.boards.total_pages || 1,
                        total: response.boards.total || 0,
                    };
                } else {
                    this.pagination = null;
                }
            } catch (error) {
                console.error('Error loading projects:', error);
                this.$notify({
                    type: 'error',
                    text: 'Failed to load projects',
                });
            } finally {
                this.loading = false;
            }
        },
        handleSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.loadProjects(1);
            }, 500);
        },
        loadPage(page) {
            this.loadProjects(page);
        },
        handleProjectCreated() {
            this.showCreateModal = false;
            this.loadProjects();
            this.$notify({
                type: 'success',
                text: 'Project created successfully',
            });
        },
        handleProjectsImported() {
            this.showImportModal = false;
            this.loadProjects();
        },
        openProjectDetail(projectId) {
            // Navigate to project detail route
            this.$router.push(`/projects/${projectId}`).catch(err => {
                // Ignore navigation duplicated errors
                if (err.name !== 'NavigationDuplicated') {
                    console.error('Navigation error:', err);
                }
            });
        },
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        },
    },
};
</script>

<style lang="scss" scoped>
.projects-page {
    padding: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;

    .page-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
    }
}

.btn-primary,
.btn-secondary {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    border: none;

    .icon {
        width: 1.125rem;
        height: 1.125rem;
    }
}

.btn-primary {
    background: #6366f1;
    color: white;

    &:hover {
        background: #4f46e5;
    }
}

.btn-secondary {
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;

    &:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
}

.search-section {
    margin-bottom: 1.5rem;
}

.search-input-wrapper {
    position: relative;
    max-width: 400px;

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

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    color: #6b7280;

    .spinner {
        width: 2.5rem;
        height: 2.5rem;
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

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;

    .empty-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        color: #d1d5db;
    }

    h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #374151;
        margin: 0 0 0.5rem;
    }

    p {
        margin: 0;
        font-size: 0.875rem;
    }
}

.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.project-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    transition: all 0.2s;
    cursor: pointer;
    position: relative;

    &:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        border-color: #6366f1;
    }

    &::after {
        content: '';
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 1.5rem;
        height: 1.5rem;
        opacity: 0;
        transition: opacity 0.2s;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236366f1'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5l7 7-7 7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
    }

    &:hover::after {
        opacity: 0.5;
    }

    .project-header {
        margin-bottom: 1rem;

        .project-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin: 0 0 0.5rem;
        }

        .project-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.75rem;
            color: #6b7280;

            .meta-item {
                display: flex;
                align-items: center;
                gap: 0.375rem;

                .meta-icon {
                    width: 0.875rem;
                    height: 0.875rem;
                }
            }
        }
    }

    .project-description {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0 0 1rem;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .project-footer {
        padding-top: 1rem;
        border-top: 1px solid #f3f4f6;

        .project-stats {
            display: flex;
            gap: 1.5rem;
            font-size: 0.8125rem;
            color: #6b7280;

            .stat-item {
                display: flex;
                align-items: center;
                gap: 0.375rem;

                .stat-icon {
                    width: 1rem;
                    height: 1rem;
                }
            }
        }
    }
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;

    .pagination-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        background: white;
        color: #374151;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;

        &:hover:not(:disabled) {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        &:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    }

    .pagination-info {
        font-size: 0.875rem;
        color: #6b7280;
    }
}
</style>

