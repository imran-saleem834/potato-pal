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

const emit = defineEmits(['receival', 'close']);

const loader = ref(false);
const receivals = ref([]);

const getReceivals = () => {
  loader.value = true;
  axios
    .get(route('growers.receivals', props.growerId))
    .then((response) => {
      receivals.value = response.data;
    })
    .catch(() => {})
    .finally(() => {
      loader.value = false;
    });
};

const onSelectReceival = (receival) => {
  emit('receival', receival);
  onCloseModal();
};

const onCloseModal = () => {
  emit('close');
  receivals.value = [];
};

watch(
  () => props.growerId,
  (growerId) => {
    if (growerId) {
      getReceivals();
    }
  },
);
</script>

<template>
  <div class="modal fade" id="receival-modals" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="receival-modals-label">Select Receival</h5>
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
            v-if="!loader && receivals.length <= 0"
            class="d-flex justify-content-center text-danger fs-5 my-3"
          >
            Doesn't find any records
          </div>
          <div v-if="!loader && receivals.length" class="table-responsive">
            <DataTable class="table mb-0">
              <thead>
                <tr>
                  <th>Grower Group</th>
                  <th>Paddock</th>
                  <th>Variety</th>
                  <th>Gen</th>
                  <th>Seed type</th>
                  <th>Class</th>
                  <th>Bin size</th>
                  <th>No of bins</th>
                  <th>Weight</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="receival in receivals" :key="receival.id">
                  <tr
                    v-if="receival.no_of_bins > 0 || receival.weight > 0"
                    @click="() => onSelectReceival(receival)"
                    style="cursor: pointer"
                    data-bs-dismiss="modal"
                  >
                    <td>
                      {{
                        getSingleCategoryNameByType(receival.receival_categories, 'grower-group')
                      }}
                    </td>
                    <td>{{ receival.paddock }}</td>
                    <td>
                      {{
                        getSingleCategoryNameByType(receival.receival_categories, 'seed-variety')
                      }}
                    </td>
                    <td>
                      {{
                        getSingleCategoryNameByType(receival.receival_categories, 'seed-generation')
                      }}
                    </td>
                    <td>
                      {{ getSingleCategoryNameByType(receival.unload_categories, 'seed-type') }}
                    </td>
                    <td>
                      {{ getSingleCategoryNameByType(receival.receival_categories, 'seed-class') }}
                    </td>
                    <td>{{ getBinSizesValue(receival.bin_size) }}</td>
                    <td>{{ receival.no_of_bins }}</td>
                    <td>{{ toTonnes(receival.weight) }}</td>
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
