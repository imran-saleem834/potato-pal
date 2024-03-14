<script setup>
import { getCategoriesByType } from '@/helper.js';
import ItemOfCategories from "@/Components/ItemOfCategories.vue";

defineProps({
  type: String,
  categories: Array,
});
</script>

<template>
  <td :class="{'pb-0' : getCategoriesByType(categories, type).length }">
    <ItemOfCategories :categories="categories" :type="type" />
  </td>
</template>
