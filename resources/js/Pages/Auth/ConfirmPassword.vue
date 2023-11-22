<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import LoginCard from '@/Components/LoginCard.vue';

const form = useForm({
    password: '',
});

const passwordInput = ref(null);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();

            passwordInput.value.focus();
        },
    });
};
</script>

<template>
    <Head title="Secure Area" />

    <div class="login-section">
        <div class="container">
            <LoginCard
                type="confirm-password"
                @submit="submit"
            >
                <p class="terms-condition" style="margin-top: 0;">
                    This is a secure area of the application. Please confirm your password before continuing.
                </p>
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
                <div class="form-group has-feedback">
                    <input
                        type="submit"
                        value="Confirm"
                        class="btn btn-red"
                        :disabled="form.processing"
                    >
                </div>
            </LoginCard>
        </div>
    </div>
</template>
