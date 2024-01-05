<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: Array,
});
</script>

<template>
  <nav v-if="links.length > 3" aria-label="Page navigation example">
    <ul class="pagination">
      <template v-for="(link, key) in links" :key="key">
        <li v-if="link.url" class="page-item">
          <Link
            class="page-link"
            :href="link.url"
            :class="{ 'bg-info text-white': link.active }"
            v-html="link.label"
          />
        </li>
      </template>
    </ul>
  </nav>
</template>
