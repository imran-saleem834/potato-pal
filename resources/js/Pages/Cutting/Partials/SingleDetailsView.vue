<script setup>
import moment from 'moment';
import { Link } from '@inertiajs/vue3';
import * as bootstrap from 'bootstrap';
import { computed, onMounted, onUpdated } from 'vue';
import ReturnItems from '@/Components/ReturnItems.vue';
import { getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';

const props = defineProps({
  cutting: Object,
});

const isSizing = computed(() => props.cutting.type === 'sizing');
const allocation = computed(() => {
  if (props.cutting.type === 'sizing') {
    return props.cutting.item.foreignable.allocatable.sizeable;
  }
  return props.cutting.item.foreignable;
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
        <th>{{ isSizing ? 'Sizing ID' : 'Allocation ID' }}</th>
        <th class="d-none d-md-table-cell">Grower</th>
        <th class="d-none d-md-table-cell">Paddock</th>
        <th class="d-none d-xl-table-cell">Variety</th>
        <th class="d-none d-md-table-cell">Gen.</th>
        <th>Seed type</th>
        <th class="d-none d-xl-table-cell">Class</th>
        <template v-if="!isSizing">
          <th>Bin size</th>
          <th>Bins Tipped</th>
        </template>
        <template v-else>
          <th>Half Tonnes</th>
          <th>One Tonnes</th>
          <th>Two Tonnes</th>
        </template>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <template v-if="isSizing">
            <Link :href="route('sizing.index', { userId: cutting.item.foreignable.allocatable?.user_id })">
              {{ cutting.item.foreignable.allocatable.id }}
            </Link>
          </template>
          <template v-else>
            <Link :href="route('allocations.index', { buyerId: allocation?.buyer_id })">
              {{ allocation.id }}
            </Link>
          </template>
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{ allocation?.grower?.grower_name }}
        </td>
        <td class="d-none d-md-table-cell text-primary">{{ allocation.paddock }}</td>
        <td class="d-none d-xl-table-cell text-primary">
          {{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-' }}
        </td>
        <td class="d-none d-md-table-cell text-primary">
          {{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-' }}
        </td>
        <td class="text-primary">
          <template v-if="cutting.type === 'sizing'">
            {{ getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-type') || '-' }}
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
        <template v-if="!isSizing">
          <td class="text-primary">{{ getBinSizesValue(cutting.item.bin_size) }}</td>
          <td class="text-primary">{{ cutting.item.no_of_bins }}</td>
        </template>
        <template v-else>
          <td class="text-primary">{{ cutting.item.foreignable.half_tonnes || '0' }} Bins</td>
          <td class="text-primary">{{ cutting.item.foreignable.one_tonnes || '0' }} Bins</td>
          <td class="text-primary">{{ cutting.item.foreignable.two_tonnes || '0' }} Bins</td>
        </template>
      </tr>
    </tbody>
  </table>

  <h4 class="mt-0 mb-3">Cutting Details:</h4>
  <div class="row allocation-items-box">
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Bins Cut into:</span>
    </div>
    <div v-if="parseInt(cutting.item.half_tonnes) > 0" class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Half Tonne: </span>
      <span class="text-primary">{{ cutting.item.half_tonnes || '0' }} Bins</span>
    </div>
    <div v-if="parseInt(cutting.item.one_tonnes) > 0" class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>One Tonne: </span>
      <span class="text-primary">{{ cutting.item.one_tonnes || '0' }} Bins</span>
    </div>
    <div v-if="parseInt(cutting.item.two_tonnes) > 0" class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
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
