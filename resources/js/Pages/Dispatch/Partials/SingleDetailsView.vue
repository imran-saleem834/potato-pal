<script setup>
import moment from 'moment';
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, onUpdated } from 'vue';
import { toTonnes, getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';
import * as bootstrap from 'bootstrap';

const props = defineProps({
  dispatch: Object,
});

const allocation = computed(() => {
  if (props.dispatch.type === 'reallocation') {
    return props.dispatch.item.foreignable.item.foreignable;
  }
  return props.dispatch.item.foreignable;
});

onMounted(() => {
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl),
  );
});

onUpdated(() => {
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl),
  );
});
</script>

<template>
  <table class="table table-sm align-middle">
    <thead>
      <tr>
        <th class="d-none d-md-table-cell">Grower</th>
        <th class="d-none d-md-table-cell">Paddock</th>
        <th class="d-none d-xl-table-cell">Variety</th>
        <th class="d-none d-md-table-cell">Gen.</th>
        <th>Seed type</th>
        <th class="d-none d-xl-table-cell">Class</th>
        <th>Bin size</th>
        <th>Dispatch Bins</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="d-none d-md-table-cell text-primary">{{ allocation?.grower?.grower_name }}</td>
        <td class="d-none d-md-table-cell text-primary">{{ allocation.paddock }}</td>
        <td class="d-none d-xl-table-cell text-primary">
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
            class="d-xl-none"
            :data-bs-title="`
            <div class='text-start'>
              Grower: ${allocation.grower?.grower_name}<br/>
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
        <td class="text-primary">{{ getBinSizesValue(dispatch.item.bin_size) }}</td>
        <td class="text-primary">{{ dispatch.item.no_of_bins }}</td>
      </tr>
    </tbody>
  </table>

  <h4 class="mt-3 mb-2">Dispatch Details:</h4>
  <div class="row allocation-items-box">
    <div class="col-12 col-sm-6 col-md-3 mb-1 pb-1">
      <span v-if="dispatch.type === 'allocation'">From Allocation: </span>
      <span v-else>From Reallocation: </span>
      <span class="text-primary">
        <Link
          :href="
            route(dispatch.type === 'allocation' ? 'allocations.index' : 'reallocations.index', {
              buyerId: dispatch.item.foreignable?.buyer_id,
            })
          "
        >
          {{ dispatch.item.foreignable.id }}
        </Link>
      </span>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-1 pb-1">
      <span>From buyer: </span>
      <span class="text-primary">{{ dispatch.allocation_buyer?.buyer_name }}</span>
    </div>
    <div class="col-12 col-sm-6 col-md-4 mb-1 pb-1">
      <span>Comments: </span>
      <span class="text-primary">{{ dispatch.comment }}</span>
    </div>
  </div>

  <template v-if="dispatch.returns.length">
    <div class="row">
      <div class="col-12 mb-1 pb-1">
        <span class="text-danger">Returns:</span>
      </div>
      <template v-for="item in dispatch.returns" :key="item.id">
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Bin size: </span>
          <span class="text-primary">{{ getBinSizesValue(item.bin_size) }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Return bins: </span>
          <span class="text-primary">{{ item.no_of_bins }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1 d-none">
          <span>Return weight: </span>
          <span class="text-primary">{{ toTonnes(item.weight) }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Return Time: </span>
          <span class="text-primary">
            {{ moment(item.created_at).format('DD/MM/YYYY hh:mm A') }}
          </span>
        </div>
      </template>
    </div>
  </template>
</template>
