<script setup>
import moment from 'moment';
import * as bootstrap from 'bootstrap';
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, onUpdated } from 'vue';
import { getSingleCategoryNameByType } from '@/helper.js';
import ReturnItems from '@/Components/ReturnItems.vue';

const props = defineProps({
  dispatch: Object,
});

defineEmits(['editReturns']);

const isCutting = computed(() => props.dispatch.dispatch_type === 'cutting');
const isSizing = computed(() => props.dispatch.dispatch_type === 'sizing');
const isAllocation = computed(() => props.dispatch.dispatch_type === 'allocation');
const isReallocation = computed(() => props.dispatch.dispatch_type === 'reallocation');

const allocation = computed(() => {
  if (isReallocation.value) {
    if (props.dispatch.item.foreignable.item.foreignable.type === 'sizing') {
      return props.dispatch.item.foreignable.item.foreignable.item.foreignable.allocatable.sizeable;
    }
    return props.dispatch.item.foreignable.item.foreignable.item.foreignable;
  } else if (isCutting.value) {
    if (props.dispatch.item.foreignable.type === 'sizing') {
      return props.dispatch.item.foreignable.item.foreignable.allocatable.sizeable;
    }
    return props.dispatch.item.foreignable.item.foreignable;
  } else if (isSizing.value) {
    return props.dispatch.item.foreignable.allocatable.sizeable;
  }
  return props.dispatch.item.foreignable;
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
        <th class="d-none d-md-table-cell">Grower</th>
        <th class="d-none d-md-table-cell">Paddock</th>
        <th class="d-none d-xl-table-cell">Variety</th>
        <th class="d-none d-md-table-cell">Gen.</th>
        <th>Seed type</th>
        <th class="d-none d-xl-table-cell">Class</th>
        <th>Half tonnes</th>
        <th>One tonnes</th>
        <th>Two tonnes</th>
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
          <template v-if="isCutting">Cut Seed</template>
          <template v-else-if="isSizing">
            {{ getSingleCategoryNameByType(dispatch.item.foreignable.categories, 'seed-type') || '-' }}
          </template>
          <template v-else-if="isReallocation">
            <template v-if="dispatch.item.foreignable.item.foreignable.type === 'sizing'">
              {{
                getSingleCategoryNameByType(
                  dispatch.item.foreignable.item.foreignable.item.foreignable.categories,
                  'seed-type',
                ) || '-'
              }}
            </template>
            <template v-else>
              {{ getSingleCategoryNameByType(allocation.categories, 'seed-type') || '-' }}
            </template>
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
        <td class="text-primary">{{ `${dispatch.item.half_tonnes} Bins` }}</td>
        <td class="text-primary">{{ `${dispatch.item.one_tonnes} Bins` }}</td>
        <td class="text-primary">{{ `${dispatch.item.two_tonnes} Bins` }}</td>
      </tr>
    </tbody>
  </table>

  <h4 class="mt-3 mb-2">Dispatch Details:</h4>
  <div class="row allocation-items-box">
    <div class="col-12 col-sm-6 col-md-3 mb-1 pb-1">
      <span>Dispatch ID: </span>
      <span class="text-primary">{{ dispatch.id }}</span>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-1 pb-1">
      <span>Dispatch Time: </span>
      <span class="text-primary">
        {{ moment(dispatch.created_at).format('DD/MM/YYYY hh:mm A') }}
      </span>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-1 pb-1">
      <span v-if="isAllocation">From Allocation: </span>
      <span v-else-if="isCutting">From Cutting: </span>
      <span v-else-if="isSizing">From Sizing: </span>
      <span v-else>From Reallocation: </span>
      <span class="text-primary">
        <Link
          v-if="isSizing"
          :href="route('sizing.index', { userId: dispatch.item.foreignable?.allocatable?.user_id })"
        >
          {{ dispatch.item.foreignable?.allocatable.id }}
        </Link>
        <Link
          v-else
          :href="
            route(isAllocation ? 'allocations.index' : isCutting ? 'cuttings.index' : 'reallocations.index', {
              buyerId: dispatch.item.foreignable?.buyer_id,
            })
          "
        >
          {{ dispatch.item.foreignable.id }}
        </Link>
      </span>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-1 pb-1">
      <span>Group Type: </span>
      <span class="text-primary">
        {{ getSingleCategoryNameByType(dispatch.categories, 'buyer-group') || '-' }}
      </span>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-1 pb-1">
      <span>Transport: </span>
      <span class="text-primary">
        {{ getSingleCategoryNameByType(dispatch.categories, 'transport') || '-' }}
      </span>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-1 pb-1">
      <span>Docket No: </span>
      <span class="text-primary">{{ dispatch.docket_no }}</span>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-1 pb-1">
      <span>Comments: </span>
      <span class="text-primary">{{ dispatch.comment }}</span>
    </div>
  </div>

  <ReturnItems 
    :items="dispatch.return_items"
    :can-edit="true"
    @edit="(item) => $emit('editReturns', item)"
  />
</template>
