<script setup>
import { Link } from '@inertiajs/vue3';
import { getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';
import { computed, onMounted, onUpdated } from 'vue';
import * as bootstrap from 'bootstrap';

const props = defineProps({
  sizing: Object,
});

const isAllocation = computed(() => props.sizing.sizeable_type === 'App\\Models\\Allocation');
const receival = computed(() => isAllocation.value ? props.sizing.sizeable : props.sizing.sizeable.receival);

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
        <th v-if="isAllocation">Allocation ID</th>
        <th v-else>Unload ID</th>
        <th class="d-none d-md-table-cell">Grower</th>
        <th class="d-none d-md-table-cell">Paddock</th>
        <th class="d-none d-xl-table-cell">Variety</th>
        <th class="d-none d-md-table-cell">Gen.</th>
        <th>Seed type</th>
        <th class="d-none d-xl-table-cell">Class</th>
        <th>Bin size</th>
        <th>No of bins</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <Link v-if="isAllocation" :href="route('allocations.index', { buyerId: sizing.user_id })">
            {{ sizing.sizeable_id }}
          </Link>
          <Link v-else :href="route('unloading.index', { receivalId: sizing.sizeable.receival_id })">
            {{ sizing.sizeable_id }}
          </Link>
        </td>
        <td class="d-none d-md-table-cell text-primary">{{ receival?.grower?.grower_name }}</td>
        <td class="d-none d-md-table-cell text-primary">
          <template v-if="isAllocation">{{ sizing.sizeable?.paddock }}</template>
          <template v-else>{{ sizing.sizeable?.receival?.paddocks[0] }}</template>
        </td>
        <td class="d-none d-xl-table-cell text-primary">
          {{ getSingleCategoryNameByType(receival.categories, 'seed-variety') || '-' }}
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{ getSingleCategoryNameByType(receival.categories, 'seed-generation') || '-' }}
        </td>
        <td class="text-primary">
          {{ getSingleCategoryNameByType(sizing.sizeable.categories, 'seed-type') || '-' }}
          <a
            data-bs-toggle="tooltip"
            data-bs-html="true"
            class="d-xl-none"
            :data-bs-title="`
            <div class='text-start'>
              Grower: ${receival.grower.grower_name}<br/>
              Paddock: ${isAllocation ? sizing.sizeable?.paddock : sizing.sizeable?.receival?.paddocks[0]}<br/>
              Variety: ${getSingleCategoryNameByType(receival.categories, 'seed-variety') || '-'}<br/>
              Gen.: ${getSingleCategoryNameByType(receival.categories, 'seed-generation') || '-'}<br/>
              Class: ${getSingleCategoryNameByType(receival.categories, 'seed-class') || '-'}
            </div>
          `"
          >
            <i class="bi bi-question-circle fs-6 text-black"></i>
          </a>
        </td>
        <td class="d-none d-xl-table-cell text-primary">
          {{ getSingleCategoryNameByType(receival.categories, 'seed-class') || '-' }}
        </td>
        <td class="text-primary">
          <template v-if="isAllocation">{{ getBinSizesValue(sizing.sizeable.item.bin_size) }}</template>
          <template v-else>{{ getBinSizesValue(sizing.sizeable?.bin_size) }}</template>
        </td>
        <td class="text-primary">
          <template v-if="isAllocation">{{ sizing.sizeable.item.no_of_bins }}</template>
          <template v-else>{{ sizing.sizeable?.no_of_bins }}</template>
        </td>
      </tr>
    </tbody>
  </table>

  <h4 class="mt-0 mb-3">Sizing Details:</h4>
  <div class="row allocation-items-box">
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Bins Tipped:</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Half Tonne: </span>
      <span class="text-primary">{{ sizing.bins_tipped?.half_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>One Tonne: </span>
      <span class="text-primary">{{ sizing.bins_tipped?.one_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Two Tonne: </span>
      <span class="text-primary">{{ sizing.bins_tipped?.two_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Weight: </span>
      <span class="text-primary">{{ sizing.weight }} tonnes</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Seed Type: </span>
      <span class="text-primary">{{ getSingleCategoryNameByType(sizing.categories, 'seed-type') || '-' }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Fungicide: </span>
      <span class="text-primary">{{ getSingleCategoryNameByType(sizing.categories, 'fungicide') || '-' }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Start Time: </span>
      <span class="text-primary">{{ sizing.start }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>End Time: </span>
      <span class="text-primary">{{ sizing.end }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>No of crew: </span>
      <span class="text-primary">{{ sizing.no_of_crew }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Comments: </span>
      <span class="text-primary">{{ sizing.comments }}</span>
    </div>
  </div>
</template>