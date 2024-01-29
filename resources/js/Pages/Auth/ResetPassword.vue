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
  <Head><title>Reset Password</title></Head>

  <div class="login-section">
    <div class="container-fluid">
      <LoginCard
        type="reset-password"
        @submit="submit"
      >
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
          <i class="bi bi-globe form-control-feedback"></i>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="form-control custom-input"
            :class="{'is-invalid' : form.errors.password}"
            placeholder="***************"
            required
            autocomplete="new-password"
          >
          <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
        </div>
        <div class="mb-3 position-relative">
          <i class="bi bi-globe form-control-feedback"></i>
          <input
            id="password"
            v-model="form.password_confirmation"
            type="password"
            :class="{'is-invalid' : form.errors.password_confirmation}"
            class="form-control custom-input"
            placeholder="***************"
            required
            autocomplete="new-password"
          >
          <div v-if="form.errors.password_confirmation" class="invalid-feedback">
            {{ form.errors.password_confirmation }}
          </div>
        </div>
        <div class="mb-3 position-relative">
          <input
            type="submit"
            value="Reset Password"
            class="btn btn-lg-red"
            :disabled="form.processing"
          >
        </div>
      </LoginCard>
    </div>
  </div>
</template>
