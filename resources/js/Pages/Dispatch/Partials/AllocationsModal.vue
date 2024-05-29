<script setup>
import { ref, watch } from 'vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import {
  toTonnes,
  getBinSizesValue,
  getSingleCategoryNameByType,
} from '@/helper.js';

DataTable.use(DataTablesCore);

const props = defineProps({
  buyerId: {
    type: Number,
    default: null
  },
});

const emit = defineEmits(['allocations', 'close']);

const loader = ref(false);
const allocations = ref([]);
const selected = ref([]);

const loadAllocations = () => {
  loader.value = true
  axios.get(route('d.buyers.allocations', props.buyerId))
    .then((response) => {
      allocations.value = response.data;
    })
    .catch(() => {

    })
    .finally(() => {
      loader.value = false
    });
};

const onCloseModal = () => {
  selected.value = [];
  allocations.value = [];
  emit('close');
}

const onSelectAllocation = (allocation) => {
  emit('allocations', allocation);
  onCloseModal();
}

const getAllocation = (allocation) => {
  if (allocation.type === 'reallocation') {
    return allocation.item.foreignable;
  }
  return allocation;
};

watch(
  () => props.buyerId,
  (buyerId) => {
    if (buyerId) {
      loadAllocations();
    }
  });
</script>

<template>
  <div class="modal fade" id="allocations-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="allocations-modal-Label">
            <template v-if="selected.length > 0">
              Selected {{ selected.length }} Allocations
            </template>
            <template v-else>Select Allocations</template>
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
            @click="onCloseModal"
          ></button>
        </div>
        <div class="modal-body">
          <div v-if="loader" class="d-flex justify-content-center my-3">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div v-if="!loader && allocations.length <= 0" class="d-flex justify-content-center text-danger fs-5 my-3">
            Doesn't find any records
          </div>
          <div v-if="!loader && allocations.length" class="table-responsive">
            <DataTable class="table mb-0">
              <thead>
              <tr>
                <th>From</th>
                <th>Grower Group</th>
                <th>Grower</th>
                <th>Paddock</th>
                <th>Variety</th>
                <th>Gen</th>
                <th>Seed type</th>
                <th>Class</th>
                <th>Bin size</th>
                <th>Weight</th>
                <th>Available / Total bins</th>
                <th>Select</th>
              </tr>
              </thead>
              <tbody>
              <template v-for="allocation in allocations" :key="allocation.id">
                <tr>
                  <td>{{ allocation.type.toUpperCase() }}</td>
                  <td>{{ getSingleCategoryNameByType(getAllocation(allocation).categories, 'grower-group') || '-' }}</td>
                  <td>{{ getAllocation(allocation).grower?.grower_name || '-' }}</td>
                  <td>{{ getAllocation(allocation).paddock }}</td>
                  <td>{{ getSingleCategoryNameByType(getAllocation(allocation).categories, 'seed-variety') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(getAllocation(allocation).categories, 'seed-generation') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(getAllocation(allocation).categories, 'seed-type') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(getAllocation(allocation).categories, 'seed-class') || '-' }}</td>
                  <td>{{ getBinSizesValue(allocation.item.bin_size) }}</td>
                  <td>{{ toTonnes(allocation.item.weight) }}</td>
                  <td>{{ `${allocation.available_no_of_bins} / ${allocation.total_no_of_bins}` }}</td>
                  <td>
                    <input
                      type="checkbox"
                      @click="onSelectAllocation(allocation)"
                      data-bs-dismiss="modal"
                    />
                  </td>
                </tr>
              </template>
              </tbody>
            </DataTable>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@import 'datatables.net-dt';
</style>
