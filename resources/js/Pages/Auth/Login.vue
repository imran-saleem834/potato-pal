<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import LoginCard from '@/Components/LoginCard.vue';

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  username: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.transform(data => ({
    ...data,
    remember: form.remember ? 'on' : '',
  })).post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <Head><title>Log in</title></Head>

  <div class="login-section">
    <div class="container-fluid">
      <LoginCard
        type="login"
        :canResetPassword="canResetPassword"
        :status="status"
        @submit="submit"
      >
        <div class="mb-3 position-relative">
          <i class="bi bi-envelope form-control-feedback"></i>
          <input
            id="username"
            v-model="form.username"
            type="text"
            class="form-control custom-input"
            :class="{'is-invalid' : form.errors.username}"
            placeholder="sheharyar"
            required
            autocomplete="username"
          >
          <div v-if="form.errors.username" class="invalid-feedback">{{ form.errors.username }}</div>
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
            autocomplete="current-password"
          >
          <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
        </div>
        <div class="mb-3 checkbox">
          <label>
            <input
              v-model="form.remember"
              type="checkbox"
              name="remember"
              :value="1"
            > Remember me
          </label>
        </div>
        <div class="mb-3">
          <input
            type="submit"
            value="Log in"
            class="btn btn-lg-red"
            :disabled="form.processing"
          >
        </div>
      </LoginCard>
    </div>
  </div>
</template>
