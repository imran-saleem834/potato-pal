<script setup>
import { getSingleCategoryNameByType } from '@/helper.js';

const props = defineProps({
  loader: Boolean,
  cutting: Object,
});
</script>

<template>
  <div v-if="loader" class="d-flex justify-content-center my-3">
    <div class="spinner-border" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>
  <div v-if="!loader && Object.values(cutting).length > 0" class="table-responsive">
    <table class="table table-sm align-middle mb-3">
      <thead>
        <tr>
          <th class="d-none d-md-table-cell">Grower</th>
          <th class="d-none d-md-table-cell">Paddock</th>
          <th class="d-none d-md-table-cell">Variety</th>
          <th class="d-none d-md-table-cell">Gen.</th>
          <th>Seed type</th>
          <th class="d-none d-md-table-cell">Class</th>
          <th>Half tonnes</th>
          <th>One tonnes</th>
          <th>Two tonnes</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="d-none d-md-table-cell text-primary">
            {{ cutting.item.foreignable.grower.grower_name }}
          </td>
          <td class="d-none d-md-table-cell text-primary">
            {{ cutting.item.foreignable.paddock }}
          </td>
          <td class="d-none d-md-table-cell text-primary">
            {{
              getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-variety') ||
              '-'
            }}
          </td>
          <td class="d-none d-md-table-cell text-primary">
            {{
              getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-generation') ||
              '-'
            }}
          </td>
          <td class="text-primary">
            <template v-if="cutting.item.foreignable.sizing">
              {{ getSingleCategoryNameByType(cutting.item.foreignable.sizing.categories, 'seed-type') || '-' }}
            </template>
            <template v-else>{{ getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-type') || '-' }}</template>
            <a
              data-bs-toggle="tooltip"
              data-bs-html="true"
              class="d-md-none"
              :data-bs-title="`
              <div class='text-start'>
                Grower: ${cutting.item.foreignable.grower.grower_name}<br/>
                Paddock: ${cutting.item.foreignable.paddock}<br/>
                Variety: ${getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-variety') || '-'}<br/>
                Gen.: ${getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-generation') || '-'}<br/>
                Class: ${getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-class') || '-'}
              </div>
            `"
            >
              <i class="bi bi-question-circle fs-6 text-black"></i>
            </a>
          </td>
          <td class="d-none d-md-table-cell text-primary">
            {{
              getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-class') || '-'
            }}
          </td>
          <td class="text-primary">{{ `${cutting.available_half_tonnes} Bins` }}</td>
          <td class="text-primary">{{ `${cutting.available_one_tonnes} Bins` }}</td>
          <td class="text-primary">{{ `${cutting.available_two_tonnes} Bins` }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
