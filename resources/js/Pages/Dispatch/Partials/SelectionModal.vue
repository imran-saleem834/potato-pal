<script setup>
import { computed, ref, watch } from 'vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import UlLiButton from '@/Components/UlLiButton.vue';
import { getSingleCategoryNameByType } from '@/helper.js';

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
    .get(route(`dispatch.buyers.${type.value}`, props.buyerId))
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
  row.dispatch_type = type.value;
  emit('select', row);
  onCloseModal();
};

const onChangeType = (value) => {
  type.value = value;
  loadAvailableSelections();
};

const isCutting = computed(() => type.value === 'cutting');
const isSizing = computed(() => type.value === 'sizing');
const isAllocation = computed(() => type.value === 'allocation');
const isReallocation = computed(() => type.value === 'reallocation');

const getAllocation = (row) => {
  if (isReallocation.value) {
    if (row.item.foreignable.type === 'sizing') {
      return row.item.foreignable.item.foreignable.allocatable.sizeable;
    }
    return row.item.foreignable.item.foreignable;
  } else if (isCutting.value) {
    if (row.type === 'sizing') {
      return row.item.foreignable.allocatable.sizeable;
    }
    return row.item.foreignable;
  } else if (isSizing.value) {
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
  <div class="modal fade" id="dispatch-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dispatch-modal-Label">Select for dispatch</h5>
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
                  { value: 'reallocation', label: 'Reallocation' },
                  { value: 'cutting', label: 'Cutting' },
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
                  <th>Grower</th>
                  <th>Paddock</th>
                  <th>Variety</th>
                  <th>Gen</th>
                  <th>Seed type</th>
                  <th>Class</th>
                  <th>Half tonnes</th>
                  <th>One tonnes</th>
                  <th>Two tonnes</th>
                  <th v-if="isAllocation">Bulk Bags</th>
                  <th>Select</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="row in data" :key="row.id">
                  <tr
                    v-if="row.available_half_tonnes > 0 || row.available_one_tonnes > 0 || row.available_two_tonnes > 0"
                  >
                    <td>{{ getAllocation(row).grower?.grower_name || '-' }}</td>
                    <td>{{ getAllocation(row).paddock }}</td>
                    <td>{{ getSingleCategoryNameByType(getAllocation(row).categories, 'seed-variety') || '-' }}</td>
                    <td>{{ getSingleCategoryNameByType(getAllocation(row).categories, 'seed-generation') || '-' }}</td>
                    <td>
                      <template v-if="isCutting">Cut Seed</template>
                      <template v-else-if="isSizing">
                        {{ getSingleCategoryNameByType(row.categories, 'seed-type') || '-' }}
                      </template>
                      <template v-else-if="isReallocation">
                        <template v-if="row.item.foreignable.type === 'sizing'">
                          {{
                            getSingleCategoryNameByType(
                              row.item.foreignable.item.foreignable.categories,
                              'seed-type',
                            ) || '-'
                          }}
                        </template>
                        <template v-else>
                          {{ getSingleCategoryNameByType(getAllocation(row).categories, 'seed-type') || '-' }}
                        </template>
                      </template>
                      <template v-else>
                        {{ getSingleCategoryNameByType(getAllocation(row).categories, 'seed-type') || '-' }}
                      </template>
                    </td>
                    <td>{{ getSingleCategoryNameByType(getAllocation(row).categories, 'seed-class') || '-' }}</td>
                    <td>
                      <template v-if="isCutting || isReallocation">
                        {{ `${row.available_from_half_tonnes} Tipped Bins` }} <br />
                      </template>
                      {{ `${row.available_half_tonnes} Bins` }}
                    </td>
                    <td>
                      <template v-if="isCutting || isReallocation">
                        {{ `${row.available_from_one_tonnes} Tipped Bins` }} <br />
                      </template>
                      {{ `${row.available_one_tonnes} Bins` }}
                    </td>
                    <td>
                      <template v-if="isCutting || isReallocation">
                        {{ `${row.available_from_two_tonnes} Tipped Bins` }} <br />
                      </template>
                      {{ `${row.available_two_tonnes} Bins` }}
                    </td>
                    <td v-if="isAllocation">{{ row.baggings_sum_no_of_bulk_bags_out || '0' }}</td>
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
