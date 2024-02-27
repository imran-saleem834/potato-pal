<script setup>
import RecIdComponent from "@/Pages/Label/Partials/RecIdComponent.vue";

defineProps({
  type: String,
  label: Object,
});
</script>

<template>
  <div class="print-body-page">
    <template v-if="type === 'rec-1'">
      Work In Progress
    </template>
    <template v-else-if="type === 'rec-3'">
      Work In Progress
    </template>
    <template v-else-if="type === 'rec-id'">
      <RecIdComponent :label="label" />
    </template>
    <template v-else-if="type === 'cut-seed'">
      Work In Progress
    </template>
  </div>
</template>
