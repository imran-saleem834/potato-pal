<script setup>
import moment from 'moment';
import { computed } from 'vue';
import { getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';

const props = defineProps({
  label: Object,
});

const allocation = computed(() => {
  if (props.label.labelable.allocation) {
    return props.label.labelable.allocation;
  } else {
    return props.label.labelable;
  }
});
</script>

<template>
  <div class="rec-labels rec-1-labels fw-bold">
    <div v-for="index in [0, 1]" :key="index" class="border-bottom border-bottom-dashed">
      <div class="d-flex justify-content-between align-items-center mt-3 mb-1">
        <div>
          <strong v-if="index === 0">CHC BULK UNLOAD DKT - SEED</strong>
          <strong v-else>CHC BULK UNLOAD DKT - OSIZE</strong>

          <h4 class="my-3">Innovator G3</h4>
        </div>

        <div>
          <table class="table table-borderless">
            <tr>
              <td class="text-light-emphasis" style="width: 150px">RECEIVAL ID</td>
              <td>{{ label.receival_id }}</td>
            </tr>
            <tr>
              <td class="text-light-emphasis" style="width: 150px">DATE</td>
              <td>{{ moment().format('DD-MM-YYYY') }}</td>
            </tr>
          </table>
        </div>
      </div>

      <table class="table input-table table-borderless">
        <tr>
          <td class="text-light-emphasis">EX GROWER</td>
          <td>{{ label.grower.grower_name }}</td>
        </tr>
        <tr v-if="allocation.buyer">
          <td class="text-light-emphasis">EX BUYER</td>
          <td>{{ allocation.buyer.buyer_name }}</td>
        </tr>
        <tr>
          <td class="text-light-emphasis">ISSUED TO</td>
          <td>{{ label.buyer.buyer_name }}</td>
        </tr>
        <tr>
          <td class="text-light-emphasis">PADDOCK</td>
          <td>{{ label.paddock }}</td>
        </tr>
        <tr>
          <td class="text-light-emphasis">SEED TYPE</td>
          <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-type') }}</td>
        </tr>
        <tr>
          <td class="text-light-emphasis">{{ getBinSizesValue(allocation.bin_size) }} Bins X</td>
          <td>{{ allocation.no_of_bins }}</td>
        </tr>
        <tr>
          <td class="text-light-emphasis">COMMENTS</td>
          <td>{{ label.comments || allocation.comment }}</td>
        </tr>
      </table>
    </div>
  </div>
</template>
