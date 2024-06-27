<script setup>
import moment from 'moment';
import { Link } from '@inertiajs/vue3';
import { getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';
import { onMounted, onUpdated } from 'vue';
import * as bootstrap from 'bootstrap';
import ReturnItems from '@/Components/ReturnItems.vue';

defineProps({
  cutting: Object,
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
        <th>Bins Tipped</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <Link :href="route('allocations.index', { buyerId: cutting.item.foreignable?.buyer_id })">
            {{ cutting.item.foreignable.id }}
          </Link>
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{ cutting.item.foreignable?.grower?.grower_name }}
        </td>
        <td class="d-none d-md-table-cell text-primary">{{ cutting.item.foreignable.paddock }}</td>
        <td class="d-none d-xl-table-cell text-primary">
          {{
            getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-variety') || '-'
          }}
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{
            getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-generation') ||
            '-'
          }}
        </td>
        <td class="text-primary">
          {{ getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-type') || '-' }}
          <a
            data-bs-toggle="tooltip"
            data-bs-html="true"
            class="d-xl-none"
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
        <td class="d-none d-xl-table-cell text-primary">
          {{
            getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-class') || '-'
          }}
        </td>
        <td class="text-primary">{{ getBinSizesValue(cutting.item.bin_size) }}</td>
        <td class="text-primary">{{ cutting.item.no_of_bins }}</td>
      </tr>
    </tbody>
  </table>

  <h4 class="mt-0 mb-3">Cutting Details:</h4>
  <div class="row allocation-items-box">
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Bins Cut into:</span>
    </div>
    <div
      v-if="parseInt(cutting.item.half_tonnes) > 0"
      class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1"
    >
      <span>Half Tonne: </span>
      <span class="text-primary">{{ cutting.item.half_tonnes || '0' }} Bins</span>
    </div>
    <div
      v-if="parseInt(cutting.item.one_tonnes) > 0"
      class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1"
    >
      <span>One Tonne: </span>
      <span class="text-primary">{{ cutting.item.one_tonnes || '0' }} Bins</span>
    </div>
    <div
      v-if="parseInt(cutting.item.two_tonnes) > 0"
      class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1"
    >
      <span>Two Tonne: </span>
      <span class="text-primary">{{ cutting.item.two_tonnes || '0' }} Bins</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Date cut: </span>
      <span v-if="cutting.cut_date" class="text-primary">
        {{ moment(cutting.cut_date).format('DD MMM YYYY') }}
      </span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Cut By: </span>
      <span class="text-primary">
        {{ getSingleCategoryNameByType(cutting.categories, 'cool-store') || '-' }}
      </span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Fungicide: </span>
      <span class="text-primary">
        {{ getSingleCategoryNameByType(cutting.categories, 'fungicide') || '-' }}
      </span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Comments: </span>
      <span class="text-primary">{{ cutting.comment }}</span>
    </div>
  </div>

  <ReturnItems :items="cutting.return_items" />
</template>
