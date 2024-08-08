<script setup>
import { ref, watch } from 'vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import UlLiButton from '@/Components/UlLiButton.vue';
import { toTonnes, getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';

DataTable.use(DataTablesCore);

const props = defineProps({
  buyerId: {
    type: Number,
    default: null,
  },
});

const emit = defineEmits(['select', 'close']);

const loader = ref(false);
const data = ref([]);
const type = ref('allocation');

const loadAvailableSelections = () => {
  loader.value = true;
  axios
    .get(route(`cutting.buyers.${type.value}`, props.buyerId))
    .then((response) => {
      data.value = response.data;
    })
    .catch(() => {})
    .finally(() => {
      loader.value = false;
    });
};

const onCloseModal = () => {
  data.value = [];
  emit('close');
};

const onSelect = (row) => {
  row.type = type;
  emit('select', row);
  onCloseModal();
};

const onChangeType = (value) => {
  type.value = value;
  loadAvailableSelections();
};

const getAllocation = (row) => {
  if (type.value === 'sizing') {
    return row.allocatable.sizeable;
  }
  return row;
};

watch(
  () => props.buyerId,
  (buyerId) => {
    if (buyerId) {
      loadAvailableSelections();
    }
  },
);
</script>

<template>
  <div class="modal fade" id="allocations-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="allocations-modal-Label">Select for cutting</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
            @click="onCloseModal"
          ></button>
        </div>
        <div class="modal-body">
          <div class="tab-section">
            <div class="user-boxes text-center p-0 shadow-none">
              <UlLiButton
                :is-form="true"
                :value="type"
                :items="[
                  { value: 'allocation', label: 'Allocation' },
                  { value: 'sizing', label: 'Sizing' },
                ]"
                @click="onChangeType"
              />
            </div>
          </div>
          <div v-if="loader" class="d-flex justify-content-center my-3">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div v-if="!loader && data.length <= 0" class="d-flex justify-content-center text-danger fs-5 my-3">
            Data not found
          </div>
          <div v-if="!loader && data.length" class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>Grower Group</th>
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
                <template v-for="row in data" :key="row.id">
                  <tr>
                    <td>
                      {{ getSingleCategoryNameByType(getAllocation(row).categories, 'grower-group') || '-' }}
                    </td>
                    <td>{{ getAllocation(row).grower?.grower_name || '-' }}</td>
                    <td>{{ getAllocation(row).paddock }}</td>
                    <td>
                      {{ getSingleCategoryNameByType(getAllocation(row).categories, 'seed-variety') || '-' }}
                    </td>
                    <td>
                      {{ getSingleCategoryNameByType(getAllocation(row).categories, 'seed-generation') || '-' }}
                    </td>
                    <td v-if="type === 'sizing'">
                      {{ getSingleCategoryNameByType(row.categories, 'seed-type') || '-' }}
                    </td>
                    <td v-else>
                      {{ getSingleCategoryNameByType(getAllocation(row).categories, 'seed-type') || '-' }}
                    </td>
                    <td>
                      {{ getSingleCategoryNameByType(getAllocation(row).categories, 'seed-class') || '-' }}
                    </td>
                    <td>{{ `${row.available_half_tonnes} Bins` }}</td>
                    <td>{{ `${row.available_one_tonnes} Bins` }}</td>
                    <td>{{ `${row.available_two_tonnes} Bins` }}</td>
                    <td>
                      <input type="checkbox" @click="onSelect(row)" data-bs-dismiss="modal" />
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@import 'datatables.net-dt';
</style>
