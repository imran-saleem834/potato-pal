<template>
  <div :class="'register' === type ? 'signup-box' : 'login-box'">
    <p class="text-center">
      <Link :href="route('dashboard')">
        <img src="/images/logo.png" class="logo" alt="Potato Pal" />
      </Link>
    </p>
    <form @submit.prevent="submit" class="needs-validation">
      <div v-if="status" class="alert alert-success">
        {{ status }}
      </div>

      <slot />
    </form>

    <p v-if="canResetPassword" class="text-center">
      <Link :href="route('password.request')">Forgot your password?</Link>
    </p>

    <p v-if="'login' === type" class="text-center">
      Don’t have an account yet? <Link :href="route('register')">Sign Up</Link>
    </p>
    <p v-if="'register' === type" class="text-center">
      Already have an account? <Link :href="route('login')">Login</Link>
    </p>

    <p v-if="['login', 'register'].includes(type)" class="text-center mt-4 terms-condition">
      By logging in to the Cherry Hill Coolstores platform you agree to our
      <Link :href="route('terms.show')"> Terms & Conditions</Link> &
      <Link :href="route('policy.show')">Privacy Policy</Link>
    </p>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';

defineProps({
  canResetPassword: {
    type: Boolean,
    default: false,
  },
  status: {
    type: String,
    default: '',
  },
  type: String,
});

const emit = defineEmits(['submit']);

const submit = () => {
  emit('submit');
};
</script>
