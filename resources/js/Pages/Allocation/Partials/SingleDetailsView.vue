<script setup>
import moment from 'moment';
import { Link } from '@inertiajs/vue3';
import {
  toTonnes,
  getBinSizesValue,
  getSingleCategoryNameByType,
} from '@/helper.js';

defineProps({
  allocation: Object,
});
</script>

<template>
  <div class="row allocation-items-box">
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Grower: </span>
      <Link :href="route('users.index', { userId: allocation.grower_id })" class="text-primary">
        {{ allocation.grower?.grower_name }}
      </Link>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Paddock: </span>
      <span class="text-primary">{{ allocation.paddock }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Variety: </span>
      <span class="text-primary">
        {{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-' }}
      </span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Gen: </span>
      <span class="text-primary">
        {{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-' }}
      </span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Bin size: </span>
      <span class="text-primary">{{ getBinSizesValue(allocation.item.bin_size) }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Allocated bins: </span>
      <span class="text-primary">{{ allocation.item.no_of_bins }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Allocated weight: </span>
      <span class="text-primary">{{ toTonnes(allocation.item.weight) }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Bins tipped for cutting: </span>
      <span class="text-primary">{{ allocation.cutting_items_sum_no_of_bins || '0' }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Class: </span>
      <span class="text-primary">
        {{ getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-' }}
      </span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Grower Group: </span>
      <span class="text-primary">
        {{ getSingleCategoryNameByType(allocation.categories, 'grower-group') || '-' }}
      </span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Seed type: </span>
      <span class="text-primary">
        {{ getSingleCategoryNameByType(allocation.categories, 'seed-type') || '-' }}
      </span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Comments: </span>
      <span class="text-primary">{{ allocation.comment }}</span>
    </div>
    <template v-if="allocation.returns.length">
      <div class="col-12 mb-1 pb-1">
        <span class="text-danger">Returns:</span>
      </div>
      <template v-for="item in allocation.returns" :key="item.id">
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
    </template>
  </div>
</template>
