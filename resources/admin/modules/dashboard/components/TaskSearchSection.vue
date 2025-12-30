<template>
    <div class="task-search-section">
        <div class="search-wrapper">
            <label class="form-label">Search or Select Task</label>
            <div class="search-input-wrapper">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input 
                    type="text" 
                    :value="searchQuery"
                    @input="handleInput"
                    @focus="showDropdown = true"
                    placeholder="Search tasks..." 
                    class="search-input" 
                />
            </div>

            <!-- Task Dropdown -->
            <div v-if="showDropdown && filteredTasks.length > 0" class="task-dropdown">
                <div 
                    v-for="task in filteredTasks" 
                    :key="task.id" 
                    @click="selectTask(task)"
                    class="dropdown-item"
                >
                    <div class="dropdown-item-content">
                        <span class="dropdown-task-title">
                            {{ task.title }} 
                            <span v-if="task.subtasks?.length" class="subtask-count">
                                ({{ task.subtasks.length }} subtasks)
                            </span>
                        </span>
                        <span class="dropdown-task-board">{{ task.board?.title }}</span>
                    </div>
                    <span class="weight-badge-small">{{ task.weight }} pts</span>
                </div>
            </div>
        </div>

        <button @click="$emit('add-task')" class="add-task-button">
            <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add New Task
        </button>
    </div>
</template>

<script>
export default {
    name: 'TaskSearchSection',
    props: {
        filteredTasks: {
            type: Array,
            default: () => []
        },
        modelValue: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            showDropdown: false
        };
    },
    computed: {
        searchQuery: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        }
    },
    emits: ['update:modelValue', 'select-task', 'add-task'],
    methods: {
        handleInput(event) {
            this.searchQuery = event.target.value;
        },
        selectTask(task) {
            this.searchQuery = task.title;
            this.showDropdown = false;
            this.$emit('select-task', task);
        }
    },
    mounted() {
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-wrapper')) {
                this.showDropdown = false;
            }
        });
    }
};
</script>

<style lang="scss" scoped>
.task-search-section {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1rem;
    align-items: flex-end;
}

.search-wrapper {
    flex: 1;
    position: relative;

    .form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.375rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
}

.search-input-wrapper {
    position: relative;

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1rem;
        height: 1rem;
        color: #9ca3af;
        z-index: 1;
    }

    .search-input {
        width: 100%;
        padding: 0.625rem 0.875rem 0.625rem 2.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
        background: #f9fafb;

        &:focus {
            outline: none;
            border-color: #6366f1;
            background: white;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        &::placeholder {
            color: #9ca3af;
        }
    }
}

.task-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    margin-top: 0.375rem;
    max-height: 280px;
    overflow-y: auto;
    width: 159%;
    z-index: 50;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);

    &::-webkit-scrollbar {
        width: 4px;
    }

    &::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 2px;
    }

    &::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;

        &:hover {
            background: #94a3b8;
        }
    }
}

.dropdown-item {
    padding: 0.75rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.15s;

    &:hover {
        background: #f9fafb;
    }

    &:not(:last-child) {
        border-bottom: 1px solid #f3f4f6;
    }
}

.dropdown-item-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    flex: 1;
    min-width: 0;

    .dropdown-task-title {
        font-weight: 600;
        color: #111827;
        font-size: 0.875rem;
        line-height: 1.3;

        .subtask-count {
            font-weight: 400;
            color: #6b7280;
            font-size: 0.8125rem;
        }
    }

    .dropdown-task-board {
        font-size: 0.8125rem;
        color: #6b7280;
    }
}

.weight-badge-small {
    background: #f3e8ff;
    color: #6b21a8;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.6875rem;
    font-weight: 600;
    white-space: nowrap;
    border: 1px solid #e9d5ff;
}

.add-task-button {
    padding: 0.625rem 1.25rem;
    background: #6366f1;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    transition: all 0.2s;
    white-space: nowrap;
    box-shadow: 0 1px 2px 0 rgba(99, 102, 241, 0.2);

    .icon-small {
        width: 1rem;
        height: 1rem;
    }

    &:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px 0 rgba(99, 102, 241, 0.3);
    }

    &:active {
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .task-search-section {
        flex-direction: column;
        align-items: stretch;
    }

    .add-task-button {
        width: 100%;
        justify-content: center;
        margin-top: 0;
    }
}
</style>

