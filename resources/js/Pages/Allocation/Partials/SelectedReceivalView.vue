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
        <th class="d-none d-md-table-cell">Paddock</th>
        <th class="d-none d-md-table-cell">Grower Group</th>
        <th class="d-none d-md-table-cell">Variety</th>
        <th class="d-none d-md-table-cell">Gen.</th>
        <th>Seed type</th>
        <th class="d-none d-md-table-cell">Class</th>
        <th>Bin size</th>
        <th>Bins available</th>
        <th>Weight</th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td class="d-none d-md-table-cell text-primary">{{ receival.paddock }}</td>
        <td class="d-none d-md-table-cell text-primary">
          {{ getSingleCategoryNameByType(receival.receival_categories, 'grower-group') || '-' }}
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{ getSingleCategoryNameByType(receival.receival_categories, 'seed-variety') || '-' }}
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{ getSingleCategoryNameByType(receival.receival_categories, 'seed-generation') || '-' }}
        </td>
        <td class="text-primary">
          {{ getSingleCategoryNameByType(receival.unload_categories, 'seed-type') }}
          <a
            data-bs-toggle="tooltip"
            data-bs-html="true"
            class="d-md-none"
            :data-bs-title="`
              <div class='text-start'>
                Paddock: ${receival.paddock}<br/>
                Variety: ${getSingleCategoryNameByType(receival.receival_categories, 'grower-group') || '-'}<br/>
                Variety: ${getSingleCategoryNameByType(receival.receival_categories, 'seed-variety') || '-'}<br/>
                Gen.: ${getSingleCategoryNameByType(receival.receival_categories, 'seed-generation') || '-'}<br/>
                Class: ${getSingleCategoryNameByType(receival.receival_categories, 'seed-class') || '-'}<br/>
              </div>
            `"
          >
            <i class="bi bi-question-circle fs-6 text-black"></i>
          </a>
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{ getSingleCategoryNameByType(receival.receival_categories, 'seed-class') || '-' }}
        </td>
        <td class="text-primary">{{ getBinSizesValue(receival.bin_size) }}</td>
        <td class="text-primary text-center text-md-start">{{ receival.no_of_bins }}</td>
        <td class="text-primary">{{ toTonnes(receival.weight) }}</td>
      </tr>
      </tbody>
    </table>
  </div>
</template>
