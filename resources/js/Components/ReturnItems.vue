<script setup>
import moment from 'moment';

defineProps({
  items: Array,
});
</script>

<template>
  <template v-if="items.length">
    <div class="row">
      <div class="col-12 pb-1">
        <span class="text-danger">Returns:</span>
      </div>
      <template v-for="item in items" :key="item.id">
        <div
          v-if="item.half_tonnes > 0"
          class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1"
        >
          <span>Half Tonnes: </span>
          <span class="text-danger">{{ `${item.half_tonnes} Bins` }}</span>
        </div>
        <div
          v-if="item.one_tonnes > 0"
          class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1"
        >
          <span>One Tonnes: </span>
          <span class="text-danger">{{ `${item.one_tonnes} Bins` }}</span>
        </div>
        <div
          v-if="item.two_tonnes > 0"
          class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1"
        >
          <span>Two Tonnes: </span>
          <span class="text-danger">{{ `${item.two_tonnes} Bins` }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Return Time: </span>
          <span class="text-danger">
            {{ moment(item.returns.created_at).format('DD/MM/YYYY hh:mm A') }}
          </span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Docket No: </span>
          <span class="text-danger">{{ item.returns.docket_no }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Comments: </span>
          <span class="text-danger">{{ item.returns.comment }}</span>
        </div>
      </template>
    </div>
  </template>
</template>
