<script setup>
import { Link } from '@inertiajs/vue3';
import { getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';
import { onMounted, onUpdated } from 'vue';
import * as bootstrap from 'bootstrap';

defineProps({
  chemicalApplicant: Object,
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
          <Link :href="route('allocations.index', { buyerId: chemicalApplicant.buyer_id })">
            {{ chemicalApplicant.allocation_id }}
          </Link>
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{ chemicalApplicant.allocation?.grower?.grower_name }}
        </td>
        <td class="d-none d-md-table-cell text-primary">{{ chemicalApplicant.allocation.paddock }}</td>
        <td class="d-none d-xl-table-cell text-primary">
          {{
            getSingleCategoryNameByType(chemicalApplicant.allocation.categories, 'seed-variety') || '-'
          }}
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{
            getSingleCategoryNameByType(chemicalApplicant.allocation.categories, 'seed-generation') ||
            '-'
          }}
        </td>
        <td class="text-primary">
          {{ getSingleCategoryNameByType(chemicalApplicant.allocation.categories, 'seed-type') || '-' }}
          <a
            data-bs-toggle="tooltip"
            data-bs-html="true"
            class="d-xl-none"
            :data-bs-title="`
            <div class='text-start'>
              Grower: ${chemicalApplicant.allocation.grower.grower_name}<br/>
              Paddock: ${chemicalApplicant.allocation.paddock}<br/>
              Variety: ${getSingleCategoryNameByType(chemicalApplicant.allocation.categories, 'seed-variety') || '-'}<br/>
              Gen.: ${getSingleCategoryNameByType(chemicalApplicant.allocation.categories, 'seed-generation') || '-'}<br/>
              Class: ${getSingleCategoryNameByType(chemicalApplicant.allocation.categories, 'seed-class') || '-'}
            </div>
          `"
          >
            <i class="bi bi-question-circle fs-6 text-black"></i>
          </a>
        </td>
        <td class="d-none d-xl-table-cell text-primary">
          {{
            getSingleCategoryNameByType(chemicalApplicant.allocation.categories, 'seed-class') || '-'
          }}
        </td>
        <td class="text-primary">{{ getBinSizesValue(chemicalApplicant.allocation.item.bin_size) }}</td>
        <td class="text-primary">{{ chemicalApplicant.allocation.item.no_of_bins }}</td>
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
      <span class="text-primary">{{ chemicalApplicant.bins_tipped?.half_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>One Tonne: </span>
      <span class="text-primary">{{ chemicalApplicant.bins_tipped?.one_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Two Tonne: </span>
      <span class="text-primary">{{ chemicalApplicant.bins_tipped?.two_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Bins Out:</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Half Tonne: </span>
      <span class="text-primary">{{ chemicalApplicant.bins_out?.half_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>One Tonne: </span>
      <span class="text-primary">{{ chemicalApplicant.bins_out?.one_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Two Tonne: </span>
      <span class="text-primary">{{ chemicalApplicant.bins_out?.two_tonne || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Fungicide: </span>
      <span class="text-primary">{{ chemicalApplicant.fungicide ? 'Yes' : 'No' }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Fungicide Used: </span>
      <span class="text-primary">{{ chemicalApplicant.fungicide_used }} litres</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Start Time: </span>
      <span class="text-primary">{{ chemicalApplicant.start }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>End Time: </span>
      <span class="text-primary">{{ chemicalApplicant.end }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>No of crew: </span>
      <span class="text-primary">{{ chemicalApplicant.no_of_crew }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Comments: </span>
      <span class="text-primary">{{ chemicalApplicant.comments }}</span>
    </div>
  </div>
</template>
