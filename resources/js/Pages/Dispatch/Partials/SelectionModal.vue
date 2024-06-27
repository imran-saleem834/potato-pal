<script setup>
import { ref, watch } from 'vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import { getSingleCategoryNameByType } from '@/helper.js';

DataTable.use(DataTablesCore);

const props = defineProps({
  buyerId: {
    type: Number,
    default: null,
  },
});

const emit = defineEmits(['allocations', 'close']);

const loader = ref(false);
const allocations = ref([]);
const selected = ref([]);

const loadAllocations = () => {
  loader.value = true;
  axios
    .get(route('d.buyers.allocations', props.buyerId))
    .then((response) => {
      allocations.value = response.data;
    })
    .catch(() => {})
    .finally(() => {
      loader.value = false;
    });
};

const onCloseModal = () => {
  selected.value = [];
  allocations.value = [];
  emit('close');
};

const onSelect = (allocation) => {
  emit('allocations', allocation);
  onCloseModal();
};

const getAllocation = (allocation) => {
  if (allocation.type === 'reallocation') {
    return allocation.item.foreignable.item.foreignable;
  } else if (allocation.type === 'cutting') {
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
  },
);
</script>

<template>
  <div class="modal fade" id="allocations-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="allocations-modal-Label">
            Select Allocation, Reallocation OR Cutting
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
          <div
            v-if="!loader && allocations.length <= 0"
            class="d-flex justify-content-center text-danger fs-5 my-3"
          >
            Data not found
          </div>
          <div v-if="!loader && allocations.length" class="table-responsive">
            <DataTable class="table mb-0">
              <thead>
                <tr>
                  <th>Grower</th>
                  <th>Paddock</th>
                  <th>Variety</th>
                  <th>Gen</th>
                  <th>Seed type</th>
                  <th>Class</th>
                  <th>Half tonnes</th>
                  <th>One tonnes</th>
                  <th>Two tonnes</th>
                  <th>Select</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="allocation in allocations" :key="allocation.id">
                  <tr
                    v-if="
                      allocation.available_half_tonnes > 0 ||
                      allocation.available_one_tonnes > 0 ||
                      allocation.available_two_tonnes > 0
                    "
                  >
                    <td>{{ getAllocation(allocation).grower?.grower_name || '-' }}</td>
                    <td>{{ getAllocation(allocation).paddock }}</td>
                    <td>
                      {{
                        getSingleCategoryNameByType(
                          getAllocation(allocation).categories,
                          'seed-variety',
                        ) || '-'
                      }}
                    </td>
                    <td>
                      {{
                        getSingleCategoryNameByType(
                          getAllocation(allocation).categories,
                          'seed-generation',
                        ) || '-'
                      }}
                    </td>
                    <td>
                      <template v-if="allocation.type === 'cutting'">Cut Seed</template>
                      <template v-else>
                        {{
                          getSingleCategoryNameByType(
                            getAllocation(allocation).categories,
                            'seed-type',
                          ) || '-'
                        }}
                      </template>
                    </td>
                    <td>
                      {{
                        getSingleCategoryNameByType(
                          getAllocation(allocation).categories,
                          'seed-class',
                        ) || '-'
                      }}
                    </td>
                    <td>{{ `${allocation.available_half_tonnes} Bins` }}</td>
                    <td>{{ `${allocation.available_one_tonnes} Bins` }}</td>
                    <td>{{ `${allocation.available_two_tonnes} Bins` }}</td>
                    <td>
                      <input
                        type="checkbox"
                        @click="onSelect(allocation)"
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
