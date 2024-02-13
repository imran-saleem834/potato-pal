<script setup>
import { Head, router } from '@inertiajs/vue3';
import InstallConfirmedModal from "@/Components/InstallConfirmedModal.vue";

defineProps({
  title: String,
});

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <Head><title>{{ title }}</title></Head>

  <slot />

  <InstallConfirmedModal />
</template>
