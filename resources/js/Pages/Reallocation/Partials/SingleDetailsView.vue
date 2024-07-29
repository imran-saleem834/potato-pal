<script setup>
import { Link } from '@inertiajs/vue3';
import * as bootstrap from 'bootstrap';
import { computed, onMounted, onUpdated } from 'vue';
import { getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';
import ReturnItems from '@/Components/ReturnItems.vue';

const props = defineProps({
  reallocation: Object,
});

const isSizing = computed(() => props.reallocation.item.foreignable.type === 'sizing');
const allocation = computed(() => {
  if (props.reallocation.item.foreignable.type === 'sizing') {
    return props.reallocation.item.foreignable.item.foreignable.allocatable.sizeable;
  }
  return props.reallocation.item.foreignable.item.foreignable;
});

onMounted(() => {
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl));
});

onUpdated(() => {
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl));
});
</script>

<template>
  <table class="table table-sm align-middle">
    <thead>
      <tr>
        <th>Allocation ID</th>
        <th class="d-none d-md-table-cell">Grower</th>
        <th class="d-none d-md-table-cell">Paddock</th>
        <th class="d-none d-xl-table-cell">Variety</th>
        <th class="d-none d-md-table-cell">Gen.</th>
        <th>Seed type</th>
        <th class="d-none d-xl-table-cell">Class</th>
        <th>Tipped Bins</th>
        <th v-if="reallocation.item.half_tonnes > 0">Half tonnes</th>
        <th v-if="reallocation.item.one_tonnes > 0">One tonnes</th>
        <th v-if="reallocation.item.two_tonnes > 0">Two tonnes</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <Link :href="route('allocations.index', { buyerId: allocation.buyer_id })">
            {{ allocation.id }}
          </Link>
        </td>
        <td class="d-none d-md-table-cell text-primary">{{ allocation.grower?.grower_name }}</td>
        <td class="d-none d-md-table-cell text-primary">{{ allocation.paddock }}</td>
        <td class="d-none d-xl-table-cell text-primary">
          {{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-' }}
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-' }}
        </td>
        <td class="text-primary">
          <template v-if="isSizing">
            {{
              getSingleCategoryNameByType(reallocation.item.foreignable.item.foreignable.categories, 'seed-type') || '-'
            }}
          </template>
          <template v-else>
            {{ getSingleCategoryNameByType(allocation.categories, 'seed-type') || '-' }}
          </template>
          <a
            data-bs-toggle="tooltip"
            data-bs-html="true"
            class="d-xl-none"
            :data-bs-title="`
            <div class='text-start'>
              Grower: ${allocation.grower.grower_name}<br/>
              Paddock: ${allocation.paddock}<br/>
              Variety: ${getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-'}<br/>
              Gen.: ${getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-'}<br/>
              Class: ${getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-'}
            </div>
          `"
          >
            <i class="bi bi-question-circle fs-6 text-black"></i>
          </a>
        </td>
        <td class="d-none d-xl-table-cell text-primary">
          {{ getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-' }}
        </td>
        <td class="text-primary">
          <template v-if="reallocation.item.foreignable.type === 'allocation'">
            {{ getBinSizesValue(reallocation.item.foreignable.item.bin_size) }} X {{ reallocation.item.foreignable.item.no_of_bins }}
          </template>
          <template v-else>-</template>
        </td>
        <td v-if="reallocation.item.half_tonnes > 0" class="text-primary">
          {{ `${reallocation.item.half_tonnes} Bins` }}
        </td>
        <td v-if="reallocation.item.one_tonnes > 0" class="text-primary">
          {{ `${reallocation.item.one_tonnes} Bins` }}
        </td>
        <td v-if="reallocation.item.two_tonnes > 0" class="text-primary">
          {{ `${reallocation.item.two_tonnes} Bins` }}
        </td>
      </tr>
    </tbody>
  </table>

  <h4 class="mt-3 mb-2">Reallocation Details:</h4>
  <div class="row allocation-items-box">
    <div class="col-12 col-sm-6 col-md-3 mb-1 pb-1">
      <span>Reallocate ID: </span>
      <span class="text-primary">{{ reallocation.id }}</span>
    </div>
    <div class="col-12 col-sm-6 col-md-4 mb-1 pb-1">
      <span>Reallocate from buyer: </span>
      <span class="text-primary">{{ reallocation.allocation_buyer.buyer_name }}</span>
    </div>
    <div class="col-12 col-sm-6 col-md-5 mb-1 pb-1">
      <span>Comments: </span>
      <span class="text-primary">{{ reallocation.comment }}</span>
    </div>
  </div>

  <ReturnItems :items="reallocation.return_items" />
</template>
