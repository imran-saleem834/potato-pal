<script setup>
import { getCategoriesByType } from '@/helper.js';

defineProps({
  type: String,
  categories: Array,
});
</script>

<template>
  <ul
    class="p-0"
    v-if="getCategoriesByType(categories, type).length"
  >
    <li
      v-for="category in getCategoriesByType(categories, type)"
      :key="category.id"
    >
      <a>{{ category.category.name }}</a>
    </li>
  </ul>
  <template v-else>-</template>
</template>
