<script setup>
import { toTonnes, getSingleCategoryNameByType } from '@/helper.js';

defineProps({
  loader: Boolean,
  allocation: Object,
});
</script>

<template>
  <div v-if="loader" class="d-flex justify-content-center my-3">
    <div class="spinner-border" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>
  <div v-if="!loader && Object.values(allocation).length > 0" class="table-responsive">
    <table class="table table-sm align-middle mb-3">
      <thead>
        <tr>
          <th class="d-none d-md-table-cell">Grower</th>
          <th class="d-none d-md-table-cell">Paddock</th>
          <th class="d-none d-md-table-cell">Variety</th>
          <th class="d-none d-md-table-cell">Gen.</th>
          <th>Seed type</th>
          <th class="d-none d-md-table-cell">Class</th>
          <th>Half Tonnes</th>
          <th>One Tonnes</th>
          <th>Two Tonnes</th>
          <th class="d-none d-md-table-cell">Weight</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="d-none d-md-table-cell text-primary">{{ allocation.grower.grower_name }}</td>
          <td class="d-none d-md-table-cell text-primary">{{ allocation.paddock }}</td>
          <td class="d-none d-md-table-cell text-primary">
            {{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-' }}
          </td>
          <td class="d-none d-md-table-cell text-primary">
            {{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-' }}
          </td>
          <td class="text-primary">
            {{ getSingleCategoryNameByType(allocation.categories, 'seed-type') || '-' }}
            <a
              data-bs-toggle="tooltip"
              data-bs-html="true"
              class="d-md-none"
              :data-bs-title="`
              <div class='text-start'>
                Grower: ${allocation.grower.grower_name}<br/>
                Paddock: ${allocation.paddock}<br/>
                Variety: ${getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-'}<br/>
                Gen.: ${getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-'}<br/>
                Class: ${getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-'}<br/>
                Weight: ${toTonnes(allocation.item.weight)}
              </div>
            `"
            >
              <i class="bi bi-question-circle fs-6 text-black"></i>
            </a>
          </td>
          <td class="d-none d-md-table-cell text-primary">
            {{ getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-' }}
          </td>
          <td class="text-primary">{{ allocation.item.half_tonnes }}</td>
          <td class="text-primary">{{ allocation.item.one_tonnes }}</td>
          <td class="text-primary">{{ allocation.item.two_tonnes }}</td>
          <td class="d-none d-md-table-cell text-primary">
            {{ toTonnes(allocation.item.weight) }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
