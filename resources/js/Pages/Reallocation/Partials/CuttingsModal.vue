<script setup>
import { ref, watch } from 'vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import { getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';

DataTable.use(DataTablesCore);

const props = defineProps({
  buyerId: {
    type: Number,
    default: null,
  },
});

const emit = defineEmits(['cutting', 'close']);

const loader = ref(false);
const cuttings = ref([]);

const getCuttings = () => {
  loader.value = true;
  axios
    .get(route('buyers.cuttings', props.buyerId))
    .then((response) => {
      cuttings.value = response.data;
    })
    .catch(() => {})
    .finally(() => {
      loader.value = false;
    });
};

const onCloseModal = () => {
  cuttings.value = [];
  emit('close');
};

const onSelectCutting = (cutting) => {
  emit('cutting', cutting);
  onCloseModal();
};

const getAllocation = (cutting) => {
  if (cutting.type === 'sizing') {
    return cutting.item.foreignable.allocatable.sizeable;
  }
  return cutting.item.foreignable;
};

watch(
  () => props.buyerId,
  (buyerId) => {
    if (buyerId) {
      getCuttings();
    }
  },
);
</script>

<template>
  <div class="modal fade" id="cuttings-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cuttings-modal-Label">Select Cuttings</h5>
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
          <div v-if="!loader && cuttings.length <= 0" class="d-flex justify-content-center text-danger fs-5 my-3">
            Data not found
          </div>
          <div v-if="!loader && cuttings.length" class="table-responsive">
            <DataTable class="table mb-0">
              <thead>
                <tr>
                  <th>Grower Group</th>
                  <th>Grower</th>
                  <th>Paddock</th>
                  <th>Variety</th>
                  <th>Gen</th>
                  <th>Seed type</th>
                  <th>Class</th>
                  <th>Tipped Bins</th>
                  <th>Half tonnes</th>
                  <th>One tonnes</th>
                  <th>Two tonnes</th>
                  <th>Select</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="cutting in cuttings" :key="cutting.id">
                  <tr>
                    <td>
                      {{ getSingleCategoryNameByType(getAllocation(cutting).categories, 'grower-group') || '-' }}
                    </td>
                    <td>{{ getAllocation(cutting).grower?.grower_name || '-' }}</td>
                    <td>{{ getAllocation(cutting).paddock }}</td>
                    <td>
                      {{ getSingleCategoryNameByType(getAllocation(cutting).categories, 'seed-variety') || '-' }}
                    </td>
                    <td>
                      {{ getSingleCategoryNameByType(getAllocation(cutting).categories, 'seed-generation') || '-' }}
                    </td>
                    <td v-if="cutting.type === 'sizing'">
                      {{ getSingleCategoryNameByType(cutting.item.foreignable.categories, 'seed-type') || '-' }}
                    </td>
                    <td v-else>
                      {{ getSingleCategoryNameByType(getAllocation(cutting).categories, 'seed-type') || '-' }}
                    </td>
                    <td>
                      {{ getSingleCategoryNameByType(getAllocation(cutting).categories, 'seed-class') || '-' }}
                    </td>
                    <td>
                      <template v-if="cutting.type === 'allocation'">
                        {{ getBinSizesValue(cutting.item.bin_size) }} X {{ cutting.item.no_of_bins }}
                      </template>
                      <template v-else>-</template>
                    </td>
                    <td>{{ `${cutting.available_half_tonnes} Bins` }}</td>
                    <td>{{ `${cutting.available_one_tonnes} Bins` }}</td>
                    <td>{{ `${cutting.available_two_tonnes} Bins` }}</td>
                    <td>
                      <input type="checkbox" @click="onSelectCutting(cutting)" data-bs-dismiss="modal" />
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
