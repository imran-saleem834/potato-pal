<script setup>
import { Head, router } from '@inertiajs/vue3';

defineProps({
  title: String,
});

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <Head><title>{{ title }}</title></Head>

  <slot/>
</template>
