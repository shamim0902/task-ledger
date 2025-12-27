<template>
    <div class="task-panel">
        <div v-for="task in tasks" :key="task.id" class="task-card"
            :class="{ done: task.status === 'completed' }">
            <!-- Left -->
            <div class="task-left">
                <input type="checkbox" class="task-checkbox" :checked="task.status === 'completed'"
                    @change="toggleTask(task)" />

                <div class="task-info">
                    <div class="task-title">
                        {{ task.title }}
                    </div>

                    <div class="task-meta">
                        <span class="points">
                            {{ task.complete_weight }} / {{ task.weight }} pts
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right -->
            <div class="task-right">
                <input placeholder="Set Weight That You Completed" v-if="task.status !== 'completed'" type="number" min="0" :max="task.weight"
                    v-model.number="task.complete_weight" class="weight-input" />

                <span v-else class="status-pill success">
                    Completed
                </span>
            </div>
        </div>
    </div>


</template>

<script>
export default {
    props: {
        tasks: {
            type: Array,
            required: true,
        },
    },

    computed: {
        totalCompletedWeight() {
            return this.tasks.reduce(
                (sum, t) => sum + (t.complete_weight || 0),
                0
            )
        }
    },
    methods: {
        toggleTask(task) {
            task.status = task.status === 'completed' ? 'in_progress' : 'completed';
            if (task.status === 'completed') {
                task.complete_weight = task.weight;
            }
        },
    },
};
</script>

<style lang="scss" scoped>
.task-panel {
    background: #ffffff;
    border-radius: 12px;
    padding: 14px;
}

.task-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid #eef2f7;
    background: #f9fafb;
    margin-bottom: 10px;
    transition: all 0.2s ease;
}

.task-card:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.task-card.done {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.task-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.task-checkbox {
    width: 16px;
    height: 16px;
    accent-color: #22c55e;
    cursor: pointer;
}

.task-info {
    display: flex;
    flex-direction: column;
}

.task-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.task-card.done .task-title {
    text-decoration: line-through;
    color: #6b7280;
}

.task-meta {
    font-size: 12px;
    color: #6b7280;
}

.task-right {
    display: flex;
    align-items: center;
    gap: 8px;
}

.weight-input {
    width: 240px;
    padding: 4px 6px;
    font-size: 13px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    text-align: center;
}

.weight-input:focus {
    outline: none;
    border-color: #3b82f6;
}

.status-pill {
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.status-pill.success {
    background: #dcfce7;
    color: #166534;
}

.status-pill.danger {
    background: #fef3c7;
    color: #92400e;
}
</style>