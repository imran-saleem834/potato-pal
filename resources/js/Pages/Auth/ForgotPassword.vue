<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import LoginCard from '@/Components/LoginCard.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="login-section">
        <div class="container">
            <LoginCard
                type="forget-password"
                @submit="submit"
            >
                <p class="terms-condition" style="margin-top: 0;">
                    Forgot your password? No problem. Just let us know your email address and we will email you a
                    password reset link that will allow you to choose a new one.
                </p>
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
                <div class="form-group has-feedback">
                    <input
                        type="submit"
                        value="Email Password Reset Link"
                        class="btn btn-red-large"
                        :disabled="form.processing"
                    >
                </div>
            </LoginCard>
        </div>
    </div>
</template>
