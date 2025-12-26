import Rest from '@/utils/http/Rest';

export default function user() {
    return {
        title: 'Users',
        users: [],
        selectedUser: null,
        errors: {},
        visible: false,
        form: {
            ID: null,
            user_email: null,
            user_nicename: null,
        },
        pagination: {
            current_page: 1,
            per_page: 2,
            total: 0,
        },
        loading: false,
        saving: false,

        async get() {
            const params = {
                per_page: this.pagination.per_page,
                page: this.pagination.current_page,
            };
            try {
                this.loading = true;
                const response = await Rest.get('users', params);
                this.setResponse(response);
            } finally {
                this.loading = false;
            }
        },

        setResponse(response) {
            this.users = response.data;
            this.pagination.total = response.total;
            this.pagination.current_page = response.current_page;
        },

        async save() {
            if (!this.form.ID) {
                return await this.create();
            }
            
            return await this.update();
        },

        async create() {
            try {
                this.saving = true;
                await Rest.post('users', this.form);
                this.visible = false;
                await this.get();
            } catch (error) {
                if (error.status == 422) {
                    this.errors = error.all();
                } else {
                    throw error;
                }
            } finally {
                this.saving = false;
            }
        },

        async update() {
            try {
                this.saving = true;
                await Rest.patch(`users/${this.form.ID}`, this.form);
                await this.get();
                this.visible = false;
            } catch (error) {
                if (error.status === 422) {
                    this.errors = error.all();
                } else {
                    throw error;
                }
            } finally {
                this.saving = false;
            }
        },

        showForm(data = {}) {
            this.errors = {};
            this.title = data.ID ? 'Edit User' : 'Add User';
            this.visible = true;
            this.form = { ...data };
        },

        async load(id) {
            try {
                this.loading = true;
                return await Rest.get(`users/${id}`);
            } finally {
                this.loading = false;
            }
        },

        async remove(e, user) {
            this.loading = true;
            try {
                await Rest.delete(`users/${user.ID}`);
                await this.get();
            } finally {
                this.loading = false;
            }
        },
    };
}
