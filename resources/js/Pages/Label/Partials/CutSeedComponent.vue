<script setup>
import { computed } from 'vue';
import { getSingleCategoryNameByType } from '@/helper.js';

const props = defineProps({
  label: Object,
});

const allocation = computed(() => {
  if (props.label.labelable_type === 'App\\Models\\Cutting') {
    const cutting = props.label.labelable;
    return cutting.type === 'sizing' ? cutting.item.foreignable.allocatable.sizeable : cutting.item.foreignable;
  } else if (props.label.labelable_type === 'App\\Models\\Reallocation') {
    const reallocation = props.label.labelable;
    const cutting = reallocation.item.foreignable;
    return cutting.type === 'sizing' ? cutting.item.foreignable.allocatable.sizeable : cutting.item.foreignable;
  } else {
    return props.label.labelable;
  }
});

const seedTypeCategories = computed(() => {
  if (props.label.labelable_type === 'App\\Models\\Cutting') {
    const cutting = props.label.labelable;
    return cutting.item.foreignable.categories;
  } else if (props.label.labelable_type === 'App\\Models\\Reallocation') {
    const reallocation = props.label.labelable;
    const cutting = reallocation.item.foreignable;
    return cutting.item.foreignable.categories;
  }

  return props.label.labelable.categories;
});
</script>

<template>
  <div class="rec-labels cut-seed-labels fw-bold">
    <div v-for="index in [0, 1, 2]" :key="index" class="border-bottom border-bottom-dashed">
      <div class="mt-3 mb-1">
        <table class="table input-table table-borderless mb-0">
          <tr>
            <td class="text-light-emphasis">ISSUED TO</td>
            <td>{{ label.buyer.buyer_name }}</td>
          </tr>
          <tr v-if="allocation.buyer">
            <td class="text-light-emphasis">EX BUYER</td>
            <td>{{ allocation.buyer.buyer_name }}</td>
          </tr>
          <tr>
            <td class="text-light-emphasis">EX GROWER</td>
            <td>{{ label.grower.grower_name }}</td>
          </tr>
          <tr>
            <td class="text-light-emphasis">PADDOCK</td>
            <td>{{ label.paddock }}</td>
          </tr>
          <tr>
            <td class="text-light-emphasis">SEED TYPE</td>
            <td>{{ getSingleCategoryNameByType(seedTypeCategories, 'seed-type') }}</td>
          </tr>
        </table>
        <div class="d-flex justify-content-end mb-3" style="margin-top: -1rem">
          <img src="/images/black-white-logo.png" alt="logo" style="width: 100px" />
        </div>
      </div>
    </div>
  </div>
</template>
