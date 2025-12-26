<template>
    <div>
        <UserHeader />
        <UserBody />
        <UserForm />
        <router-view />
    </div>
</template>

<script>
import UserHeader from './components/header';
import UserBody from './components/body';
import UserForm from './components/form';
import user from './user';

export default {
    name: 'UserPage',
    props: {
        user: Object,
        siteName: String,
        isAdmin: Boolean
    },
    components: {
        UserHeader,
        UserBody,
        UserForm,
    },

    provide() {
        return {
            user: this.model,
        };
    },

    data() {
        return {
            model: user(),
        };
    },

    mounted() {
        this.model.get();
    },
};
</script>
