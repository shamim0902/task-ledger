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
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.search-wrapper {
    flex: 1;
    position: relative;

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }
}

.search-input-wrapper {
    position: relative;

    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1.25rem;
        height: 1.25rem;
        color: #9ca3af;
        z-index: 1;
    }

    .search-input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 3rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.625rem;
        font-size: 1rem;
        transition: all 0.2s;
        background: #f9fafb;

        &:focus {
            outline: none;
            border-color: #4f46e5;
            background: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
    }
}

.task-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 0.625rem;
    margin-top: 0.5rem;
    max-height: 320px;
    overflow-y: auto;
    z-index: 50;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);

    &::-webkit-scrollbar {
        width: 6px;
    }

    &::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    &::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;

        &:hover {
            background: #94a3b8;
        }
    }
}

.dropdown-item {
    padding: 1rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.2s;

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
    gap: 0.375rem;
    flex: 1;

    .dropdown-task-title {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9375rem;

        .subtask-count {
            font-weight: 400;
            color: #6b7280;
            font-size: 0.875rem;
        }
    }

    .dropdown-task-board {
        font-size: 0.875rem;
        color: #6b7280;
    }
}

.weight-badge-small {
    background: #f3e8ff;
    color: #6b21a8;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.add-task-button {
    padding: 0.875rem 1.75rem;
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    color: white;
    border: none;
    border-radius: 0.625rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    margin-top: 1.75rem;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3);

    .icon-small {
        width: 1.25rem;
        height: 1.25rem;
    }

    &:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px -2px rgba(79, 70, 229, 0.4);
    }

    &:active {
        transform: translateY(0);
    }
}
</style>

