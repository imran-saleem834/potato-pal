<script setup>
import { watchEffect } from 'vue';
import { toTonnes, getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';

const props = defineProps({
  loader: Boolean,
  unload: Object,
  form: {
    required: true,
  },
});

const emit = defineEmits(['update:modelValue']);

watchEffect(() => {
  emit('update:modelValue', props.form);
});
</script>

<template>
  <div v-if="loader" class="d-flex justify-content-center my-3">
    <div class="spinner-border" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>
  <div v-if="!loader && Object.values(unload).length > 0" class="table-responsive">
    <table class="table table-sm align-middle mb-3">
      <thead>
        <tr>
          <th class="d-none d-md-table-cell">Grower</th>
          <th class="d-none d-md-table-cell">Paddock</th>
          <th class="d-none d-md-table-cell">Variety</th>
          <th class="d-none d-md-table-cell">Gen.</th>
          <th>Seed type</th>
          <th class="d-none d-md-table-cell">Class</th>
          <th>Bin size</th>
          <th class="d-none d-md-table-cell">Weight</th>
          <th>No of bins</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="d-none d-md-table-cell text-primary">{{ unload.receival.grower.grower_name }}</td>
          <td class="d-none d-md-table-cell text-primary">{{ unload.receival.paddocks[0] }}</td>
          <td class="d-none d-md-table-cell text-primary">
            {{ getSingleCategoryNameByType(unload.receival.categories, 'seed-variety') || '-' }}
          </td>
          <td class="d-none d-md-table-cell text-primary">
            {{ getSingleCategoryNameByType(unload.receival.categories, 'seed-generation') || '-' }}
          </td>
          <td class="text-primary">
            {{ getSingleCategoryNameByType(unload.categories, 'seed-type') || '-' }}
            <a
              data-bs-toggle="tooltip"
              data-bs-html="true"
              class="d-md-none"
              :data-bs-title="`
              <div class='text-start'>
                Grower: ${unload.receival.grower.grower_name}<br/>
                Paddock: ${unload.receival.paddock}<br/>
                Variety: ${getSingleCategoryNameByType(unload.receival.categories, 'seed-variety') || '-'}<br/>
                Gen.: ${getSingleCategoryNameByType(unload.receival.categories, 'seed-generation') || '-'}<br/>
                Class: ${getSingleCategoryNameByType(unload.receival.categories, 'seed-class') || '-'}<br/>
                Weight: ${toTonnes(unload.weight)}
              </div>
            `"
            >
              <i class="bi bi-question-circle fs-6 text-black"></i>
            </a>
          </td>
          <td class="d-none d-md-table-cell text-primary">
            {{ getSingleCategoryNameByType(unload.receival.categories, 'seed-class') || '-' }}
          </td>
          <td class="text-primary">{{ getBinSizesValue(unload.bin_size) }}</td>
          <td class="d-none d-md-table-cell text-primary">
            {{ toTonnes(unload.weight) }}
          </td>
          <td class="text-primary">{{ unload.no_of_bins }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
