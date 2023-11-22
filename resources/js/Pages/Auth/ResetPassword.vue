<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import LoginCard from '@/Components/LoginCard.vue';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Password" />

    <div class="login-section">
        <div class="container">
            <LoginCard
                type="reset-password"
                @submit="submit"
            >
                <div class="form-group has-feedback" :class="{'has-error' : form.errors.email}">
                    <span class="fa fa-envelope-o form-control-feedback"></span>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="form-control customInput"
                        placeholder="shehar@next-x.com.au"
                        required
                        autocomplete="username"
                    >
                    <span v-show="form.errors.email" class="help-block text-left">{{ form.errors.email }}</span>
                </div>
                <div class="form-group has-feedback" :class="{'has-error' : form.errors.password}">
                    <span class="fa fa-globe form-control-feedback"></span>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="form-control customInput"
                        placeholder="***************"
                        required
                        autocomplete="new-password"
                    >
                    <span v-show="form.errors.password" class="help-block text-left">{{ form.errors.password }}</span>
                </div>
                <div class="form-group has-feedback" :class="{'has-error' : form.errors.password_confirmation}">
                    <span class="fa fa-globe form-control-feedback"></span>
                    <input
                        id="password"
                        v-model="form.password_confirmation"
                        type="password"
                        class="form-control customInput"
                        placeholder="***************"
                        required
                        autocomplete="new-password"
                    >
                    <span v-show="form.errors.password_confirmation" class="help-block text-left">{{ form.errors.password_confirmation }}</span>
                </div>
                <div class="form-group has-feedback">
                    <input
                        type="submit"
                        value="Reset Password"
                        class="btn btn-red"
                        :disabled="form.processing"
                    >
                </div>
            </LoginCard>
        </div>
    </div>
</template>
