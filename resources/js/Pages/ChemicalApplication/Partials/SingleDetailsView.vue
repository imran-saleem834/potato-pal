<script setup>
import { Link } from '@inertiajs/vue3';
import * as bootstrap from 'bootstrap';
import { onMounted, onUpdated } from 'vue';
import { getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';

defineProps({
  chemicalApplication: Object,
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
          <Link :href="route('allocations.index', { buyerId: chemicalApplication.buyer_id })">
            {{ chemicalApplication.allocation_id }}
          </Link>
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{ chemicalApplication.allocation?.grower?.grower_name }}
        </td>
        <td class="d-none d-md-table-cell text-primary">{{ chemicalApplication.allocation.paddock }}</td>
        <td class="d-none d-xl-table-cell text-primary">
          {{
            getSingleCategoryNameByType(chemicalApplication.allocation.categories, 'seed-variety') || '-'
          }}
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{
            getSingleCategoryNameByType(chemicalApplication.allocation.categories, 'seed-generation') ||
            '-'
          }}
        </td>
        <td class="text-primary">
          {{ getSingleCategoryNameByType(chemicalApplication.allocation.categories, 'seed-type') || '-' }}
          <a
            data-bs-toggle="tooltip"
            data-bs-html="true"
            class="d-xl-none"
            :data-bs-title="`
            <div class='text-start'>
              Grower: ${chemicalApplication.allocation.grower.grower_name}<br/>
              Paddock: ${chemicalApplication.allocation.paddock}<br/>
              Variety: ${getSingleCategoryNameByType(chemicalApplication.allocation.categories, 'seed-variety') || '-'}<br/>
              Gen.: ${getSingleCategoryNameByType(chemicalApplication.allocation.categories, 'seed-generation') || '-'}<br/>
              Class: ${getSingleCategoryNameByType(chemicalApplication.allocation.categories, 'seed-class') || '-'}
            </div>
          `"
          >
            <i class="bi bi-question-circle fs-6 text-black"></i>
          </a>
        </td>
        <td class="d-none d-xl-table-cell text-primary">
          {{
            getSingleCategoryNameByType(chemicalApplication.allocation.categories, 'seed-class') || '-'
          }}
        </td>
        <td class="text-primary">{{ getBinSizesValue(chemicalApplication.allocation.item.bin_size) }}</td>
        <td class="text-primary">{{ chemicalApplication.allocation.item.no_of_bins }}</td>
      </tr>
    </tbody>
  </table>

  <h4 class="mt-0 mb-3">Grading Details:</h4>
  <div class="row allocation-items-box">
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Bins Tipped:</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Half Tonne: </span>
      <span class="text-primary">{{ chemicalApplication.bins_tipped?.half_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>One Tonne: </span>
      <span class="text-primary">{{ chemicalApplication.bins_tipped?.one_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Two Tonne: </span>
      <span class="text-primary">{{ chemicalApplication.bins_tipped?.two_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Bins Out:</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Half Tonne: </span>
      <span class="text-primary">{{ chemicalApplication.bins_out?.half_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>One Tonne: </span>
      <span class="text-primary">{{ chemicalApplication.bins_out?.one_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Two Tonne: </span>
      <span class="text-primary">{{ chemicalApplication.bins_out?.two_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Fungicide: </span>
      <span class="text-primary">{{ chemicalApplication.fungicide ? 'Yes' : 'No' }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Fungicide Used: </span>
      <span class="text-primary">{{ chemicalApplication.fungicide_used }} litres</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Start Time: </span>
      <span class="text-primary">{{ chemicalApplication.start }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>End Time: </span>
      <span class="text-primary">{{ chemicalApplication.end }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>No of crew: </span>
      <span class="text-primary">{{ chemicalApplication.no_of_crew }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Comments: </span>
      <span class="text-primary">{{ chemicalApplication.comments }}</span>
    </div>
  </div>
</template>
