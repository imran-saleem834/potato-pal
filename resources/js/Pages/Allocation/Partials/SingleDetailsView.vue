<script setup>
import { Link } from '@inertiajs/vue3';
import ReturnItems from '@/Components/ReturnItems.vue';
import { toTonnes, getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';

defineProps({
  allocation: Object,
});
</script>

<template>
  <div class="row allocation-items-box">
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Allocation ID: </span>
      <span class="text-primary">{{ allocation.id }}</span>
    </div>
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
      <span>No of bulk bags out: </span>
      <span class="text-primary">{{ allocation.baggings_sum_no_of_bulk_bags_out || '0' }}</span>
    </div>
    <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
      <span>Net weight of bulk bags: </span>
      <span class="text-primary">{{ allocation.baggings_sum_net_weight_bags_out || '0' }}</span>
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
    <div class="col-12 mb-1 pb-1">
      <span>Comments: </span>
      <span class="text-primary">{{ allocation.comment }}</span>
    </div>

    <ReturnItems :items="allocation.return_items" />
  </div>
</template>
