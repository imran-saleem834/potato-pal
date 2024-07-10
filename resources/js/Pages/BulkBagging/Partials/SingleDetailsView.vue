<script setup>
import { Link } from '@inertiajs/vue3';
import { getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';
import { onMounted, onUpdated } from 'vue';
import * as bootstrap from 'bootstrap';

defineProps({
  bulkBagging: Object,
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
        <th>Allocation ID</th>
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
          <Link :href="route('allocations.index', { buyerId: bulkBagging.buyer_id })">
            {{ bulkBagging.allocation_id }}
          </Link>
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{ bulkBagging.allocation?.grower?.grower_name }}
        </td>
        <td class="d-none d-md-table-cell text-primary">{{ bulkBagging.allocation.paddock }}</td>
        <td class="d-none d-xl-table-cell text-primary">
          {{
            getSingleCategoryNameByType(bulkBagging.allocation.categories, 'seed-variety') || '-'
          }}
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{
            getSingleCategoryNameByType(bulkBagging.allocation.categories, 'seed-generation') ||
            '-'
          }}
        </td>
        <td class="text-primary">
          {{ getSingleCategoryNameByType(bulkBagging.allocation.categories, 'seed-type') || '-' }}
          <a
            data-bs-toggle="tooltip"
            data-bs-html="true"
            class="d-xl-none"
            :data-bs-title="`
            <div class='text-start'>
              Grower: ${bulkBagging.allocation.grower.grower_name}<br/>
              Paddock: ${bulkBagging.allocation.paddock}<br/>
              Variety: ${getSingleCategoryNameByType(bulkBagging.allocation.categories, 'seed-variety') || '-'}<br/>
              Gen.: ${getSingleCategoryNameByType(bulkBagging.allocation.categories, 'seed-generation') || '-'}<br/>
              Class: ${getSingleCategoryNameByType(bulkBagging.allocation.categories, 'seed-class') || '-'}
            </div>
          `"
          >
            <i class="bi bi-question-circle fs-6 text-black"></i>
          </a>
        </td>
        <td class="d-none d-xl-table-cell text-primary">
          {{
            getSingleCategoryNameByType(bulkBagging.allocation.categories, 'seed-class') || '-'
          }}
        </td>
        <td class="text-primary">{{ getBinSizesValue(bulkBagging.allocation.item.bin_size) }}</td>
        <td class="text-primary">{{ bulkBagging.allocation.item.no_of_bins }}</td>
      </tr>
    </tbody>
  </table>

  <h4 class="mt-0 mb-3">Bulk Bagging Details:</h4>
  <div class="row allocation-items-box">
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Bins Tipped:</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Half Tonne: </span>
      <span class="text-primary">{{ bulkBagging.bins_tipped?.half_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>One Tonne: </span>
      <span class="text-primary">{{ bulkBagging.bins_tipped?.one_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Two Tonne: </span>
      <span class="text-primary">{{ bulkBagging.bins_tipped?.two_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Weight: </span>
      <span class="text-primary">{{ bulkBagging.weight }} tonnes</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>No of bulk bags out: </span>
      <span class="text-primary">{{ bulkBagging.no_of_bulk_bags_out }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Net weight of bulk bags: </span>
      <span class="text-primary">{{ bulkBagging.net_weight_bags_out }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Start Time: </span>
      <span class="text-primary">{{ bulkBagging.start }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>End Time: </span>
      <span class="text-primary">{{ bulkBagging.end }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>No of crew: </span>
      <span class="text-primary">{{ bulkBagging.no_of_crew }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Comments: </span>
      <span class="text-primary">{{ bulkBagging.comments }}</span>
    </div>
  </div>
</template>
