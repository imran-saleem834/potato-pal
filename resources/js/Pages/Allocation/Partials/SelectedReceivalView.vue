<script setup>
import {
  toTonnes,
  getBinSizesValue,
  getSingleCategoryNameByType,
} from '@/helper.js';

defineProps({
  loader: Boolean,
  receival: Object,
});
</script>

<template>
  <div v-if="loader" class="d-flex justify-content-center my-3">
    <div class="spinner-border" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>
  <div v-if="!loader && Object.values(receival).length" class="table-responsive">
    <table class="table table-sm">
      <thead>
      <tr>
        <th>Seed type</th>
        <th>Bin size</th>
        <th>Bins available</th>
        <th>Weight</th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td class="text-primary">{{ getSingleCategoryNameByType(receival.unload_categories, 'seed-type') }}</td>
        <td class="text-primary">{{ getBinSizesValue(receival.bin_size) }}</td>
        <td class="text-primary text-center text-md-start">{{ receival.no_of_bins }}</td>
        <td class="text-primary">{{ toTonnes(receival.weight) }}</td>
      </tr>
      </tbody>
    </table>
  </div>
</template>
