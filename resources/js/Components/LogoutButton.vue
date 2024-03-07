<template>
  <a href="javascript:;" @click.prevent="logout">Logout</a>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
const logout = () => {
  router.post(route('logout'));
};
</script>
