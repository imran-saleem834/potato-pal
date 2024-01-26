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
  <Head title="Log in"></Head>

  <div class="login-section">
    <div class="container">
      <LoginCard
        type="login"
        :canResetPassword="canResetPassword"
        :status="status"
        @submit="submit"
      >
        <div class="form-group has-feedback" :class="{'has-error' : form.errors.username}">
          <span class="fa fa-envelope-o form-control-feedback"></span>
          <input
            id="username"
            v-model="form.username"
            type="text"
            class="form-control customInput"
            placeholder="sheharyar"
            required
            autocomplete="username"
          >
          <span v-show="form.errors.username" class="help-block text-left">{{ form.errors.username }}</span>
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
            autocomplete="current-password"
          >
          <span v-show="form.errors.password" class="help-block text-left">{{
              form.errors.password
            }}</span>
        </div>
        <div class="checkbox text-left">
          <label>
            <input
              v-model="form.remember"
              type="checkbox"
              name="remember"
              :value="1"
            > Remember me
          </label>
        </div>
        <div class="form-group has-feedback">
          <input
            type="submit"
            value="Log in"
            class="btn btn-red-large"
            :disabled="form.processing"
          >
        </div>
      </LoginCard>
    </div>
  </div>
</template>
