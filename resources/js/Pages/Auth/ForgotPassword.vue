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
  <Head><title>Forgot Password</title></Head>

  <div class="login-section">
    <div class="container-fluid">
      <LoginCard
        type="forget-password"
        @submit="submit"
      >
        <p class="mb-3 terms-condition">
          Forgot your password? No problem. Just let us know your email address and we will email you a
          password reset link that will allow you to choose a new one.
        </p>
        <div class="mb-3 position-relative">
          <i class="bi bi-envelope form-control-feedback"></i>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="form-control custom-input"
            placeholder="shehar@next-x.com.au"
            :class="{'is-invalid' : form.errors.email}"
            required
            autocomplete="username"
          >
          <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
        </div>
        <div class="mb-3 position-relative">
          <input
            type="submit"
            value="Email Password Reset Link"
            class="btn btn-lg-red"
            :disabled="form.processing"
          >
        </div>
      </LoginCard>
    </div>
  </div>
</template>
