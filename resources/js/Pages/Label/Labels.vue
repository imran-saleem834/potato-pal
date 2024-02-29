<script setup>
import RecIdComponent from "@/Pages/Label/Partials/RecIdComponent.vue";
import Rec3Component from "@/Pages/Label/Partials/Rec3Component.vue";
import Rec1Component from "@/Pages/Label/Partials/Rec1Component.vue";
import CutSeedComponent from "@/Pages/Label/Partials/CutSeedComponent.vue";

defineProps({
  type: String,
  label: Object,
});
</script>

<template>
  <div class="print-body-page">
    <Rec1Component
      v-if="type === 'rec-1'"
      :label="label"
    />
    <Rec3Component
      v-else-if="type === 'rec-3'"
      :label="label"
    />
    <RecIdComponent
      v-else-if="type === 'rec-id'"
      :label="label"
    />
    <CutSeedComponent
      v-else-if="type === 'cut-seed'"
      :label="label"
    />
  </div>
</template>
