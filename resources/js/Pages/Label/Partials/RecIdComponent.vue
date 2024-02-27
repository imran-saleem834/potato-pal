<script setup>
import moment from 'moment';
import { computed } from "vue";
import { getSingleCategoryNameByType } from "@/helper.js";
import { getBinSizesValue } from "@/tonnes.js";

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
  <div class="rec-id-labels fw-bold">
    <div class="p-2 header mb-2" style="background: #e5e5e5">
      <div>CHERRY HILL COOLSTORES</div>
      <div class="fw-normal">Leaders in seed potato management</div>
    </div>

    <template v-for="index in [0, 1]" :key="index">
      <div class="d-flex justify-content-between align-items-center">
        <strong v-if="index === 0">DRIVERS DKT</strong>
        <strong v-if="index === 1">CHC OFFICE COPY</strong>

        <div>
          <table class="table table-borderless">
            <tr>
              <td class="text-muted" style="width: 150px;">RECEIVAL ID</td>
              <td>{{ label.receival_id }}</td>
            </tr>
            <tr>
              <td class="text-muted" style="width: 150px;">DATE</td>
              <td>{{ moment().format('DD-MM-YYYY') }}</td>
            </tr>
          </table>
        </div>
      </div>

      <div>
        <div class="text-muted d-inline-block w-25">GROWERS DKT ID</div> 
        <div class="text-decoration-underline d-inline-block">{{ label.grower.id }}</div>
      </div>

      <h4 class="my-3">Innovator G3</h4>
      
      <table class="table input-table table-borderless">
        <tr>
          <td class="text-muted">EX GROWER</td>
          <td>{{ label.grower.grower_name }}</td>
        </tr>
        <tr>
          <td class="text-muted">ISSUED TO</td>
          <td>{{ allocation.buyer.buyer_name }}</td>
        </tr>
        <tr>
          <td class="text-muted">PADDOCK</td>
          <td>{{ label.paddock }}</td>
        </tr>
        <tr>
          <td class="text-muted">SEED TYPE</td>
          <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-type') }}</td>
        </tr>
        <tr>
          <td class="text-muted">TRANSPORT CO</td>
          <td>{{ getSingleCategoryNameByType(label.receival.categories, 'transport') }}</td>
        </tr>
        <tr>
          <td class="text-muted">DRIVER</td>
          <td>{{ label.receival.driver_name }}</td>
        </tr>
        <tr>
          <td class="text-muted">{{ getBinSizesValue(allocation.bin_size) }} Bins X</td>
          <td>{{ allocation.no_of_bins }}</td>
        </tr>
        <tr>
          <td class="text-muted">COMMENTS</td>
          <td>{{ label.receival.comments || allocation.comment }}</td>
        </tr>
      </table>

      <template v-if="index === 0">
        <div class="p-2 mb-2" style="background: #e5e5e5; font-size: 12px;">
          <div>CHERRY HILL COOLSTORES P/L ABN 36 159 091 228</div>
          <div>
            32 CHERRY HILL ROAD, Latrobe Tasmania 7307 Australia |
            PO Box: 167, Latrobe Tasmania 7307 Australia
          </div>
          <div>Ph: (03) 6426 1590 | Email: admin@cherryhillcoolstores.net.au | www.cherryhillcoolstores.net.au</div>
        </div>
      </template>
    </template>
  </div>

  <div class="page-break"></div>

  <div class="rec-id-tia-sample row pt-3">
    <template v-for="index in [0, 1]" :key="index">
      <div class="col-6 fw-bold" :class="{'border border-top-0 border-bottom-0 border-start-0' : index === 0}">
        <h5 class="text-center fw-bold">TIA SAMPLE</h5>
        <h2 class="fw-bold">Innovator G3</h2>
        <table class="table input-table table-borderless">
          <tr>
            <td class="text-muted">EX GROWER</td>
            <td>{{ label.grower.grower_name }}</td>
          </tr>
          <tr>
            <td class="text-muted">ISSUED TO</td>
            <td>{{ allocation.buyer.buyer_name }}</td>
          </tr>
          <tr>
            <td class="text-muted">PADDOCK</td>
            <td>{{ label.paddock }}</td>
          </tr>
          <tr>
            <td class="text-muted">DATE RECEIVED</td>
            <td>{{ moment(label.receival.created_at).format('DD-MM-YYYY') }}</td>
          </tr>
        </table>
        <h3 class="text-center text-muted pt-3">RECEIVAL ID</h3>
        <h1 class="text-center">{{ label.receival.id }}</h1>
      </div>
    </template>
  </div>
</template>
