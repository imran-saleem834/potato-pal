<script setup>
import { ref, watch } from 'vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import { toTonnes, getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';

DataTable.use(DataTablesCore);

const props = defineProps({
  growerId: {
    type: Number,
    default: null,
  },
});

const emit = defineEmits(['unload', 'close']);

const loader = ref(false);
const unloads = ref([]);

const getUnloads = () => {
  loader.value = true;
  axios
    .get(route('grower.unloads', props.growerId))
    .then((response) => {
      unloads.value = response.data;
    })
    .catch(() => {})
    .finally(() => {
      loader.value = false;
    });
};

const onCloseModal = () => {
  unloads.value = [];
  emit('close');
};

const onSelectUnload = (unload) => {
  emit('unload', unload);
  onCloseModal();
};

watch(
  () => props.growerId,
  (growerId) => {
    if (growerId) {
      getUnloads();
    }
  },
);
</script>

<template>
  <div class="modal fade" id="unloads-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="unloads-modal-Label">Select Unload</h5>
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
            v-if="!loader && unloads.length <= 0"
            class="d-flex justify-content-center text-danger fs-5 my-3"
          >
            Data not found
          </div>
          <div v-if="!loader && unloads.length" class="table-responsive">
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
                  <th>Bin size</th>
                  <th>Weight</th>
                  <th>Select</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="unload in unloads" :key="unload.id">
                  <tr>
                    <td>
                      {{
                        getSingleCategoryNameByType(unload.receival.categories, 'grower-group') || '-'
                      }}
                    </td>
                    <td>{{ unload.receival.grower?.grower_name || '-' }}</td>
                    <td>{{ unload.receival.paddock }}</td>
                    <td>
                      {{
                        getSingleCategoryNameByType(unload.receival.categories, 'seed-variety') || '-'
                      }}
                    </td>
                    <td>
                      {{
                        getSingleCategoryNameByType(unload.receival.categories, 'seed-generation') || '-'
                      }}
                    </td>
                    <td>
                      {{ getSingleCategoryNameByType(unload.categories, 'seed-type') || '-' }}
                    </td>
                    <td>
                      {{ getSingleCategoryNameByType(unload.receival.categories, 'seed-class') || '-' }}
                    </td>
                    <td>{{ getBinSizesValue(unload.bin_size) }}</td>
                    <td>{{ toTonnes(unload.weight) }}</td>
                    <td>
                      <input
                        type="checkbox"
                        @click="onSelectUnload(unload)"
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
