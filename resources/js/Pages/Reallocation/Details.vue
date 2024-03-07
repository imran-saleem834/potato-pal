<script setup>
import { computed, ref, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import TextInput from '@/Components/TextInput.vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import { toTonnes, getBinSizesValue, getSingleCategoryNameByType } from '@/helper.js';
import { useToast } from 'vue-toastification';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';

const toast = useToast();

DataTable.use(DataTablesCore);

const props = defineProps({
  uniqueKey: String,
  reallocation: {
    type: Object,
    default: {},
  },
  isNew: {
    type: Boolean,
    default: false,
  },
  isNewItem: {
    type: Boolean,
    default: false,
  },
  allocations: Object,
  buyers: Object,
});

const emit = defineEmits(['create', 'delete']);

const isEdit = ref(false);

const form = useForm({
  buyer_id: props.reallocation.buyer_id,
  allocation_buyer_id: props.reallocation.allocation_buyer_id,
  allocation_id: props.reallocation.allocation_id,
  no_of_bins: props.reallocation.no_of_bins,
  weight: props.reallocation.weight,
  comment: props.reallocation.comment,
  selected_allocation: {},
});

watch(
  () => props.reallocation,
  (reallocation) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = reallocation.buyer_id;
    form.allocation_buyer_id = reallocation.allocation_buyer_id;
    form.allocation_id = reallocation.allocation_id;
    form.no_of_bins = reallocation.no_of_bins;
    form.weight = reallocation.weight;
    form.comment = reallocation.comment;

    selectAllocationOnEdit(reallocation);
  },
);

const selectAllocationOnEdit = (reallocation) => {
  form.selected_allocation =
    props.allocations?.find((allocation) => allocation.id === reallocation.allocation_id) || {};
};

selectAllocationOnEdit(props.reallocation);

const onSelectAllocation = (allocation) => {
  form.allocation_id = allocation.id;
  form.selected_allocation = allocation;
  form.no_of_bins = null;
  form.weight = null;
};

const onChangeAllocationBuyer = () => {
  form.selected_allocation = {};
  form.no_of_bins = null;
  form.weight = null;
};

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
});

const setIsEdit = () => {
  isEdit.value = true;
};

const updateRecord = () => {
  form.patch(route('reallocations.update', props.reallocation.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
      toast.success('The reallocation has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('reallocations.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The reallocation has been created successfully!');
    },
  });
};

const deleteReallocation = () => {
  const form = useForm({});
  form.delete(route('reallocations.destroy', props.reallocation.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('delete');
      toast.success('The reallocation has been deleted successfully!');
    },
  });
};

defineExpose({
  storeRecord,
});
</script>

<template>
  <div v-if="isNew" class="user-boxes">
    <table class="table input-table mb-0">
      <tr>
        <th>Reallocation Buyer Name</th>
        <td>
          <Multiselect
            v-model="form.buyer_id"
            mode="single"
            placeholder="Choose a buyer"
            :searchable="true"
            :options="buyers"
            :class="{ 'is-invalid': form.errors.buyer_id }"
          />
          <div
            v-if="form.errors.buyer_id"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.buyer_id"
          />
        </td>
      </tr>
    </table>
  </div>

  <h4 v-if="isNew">Reallocations Details</h4>
  <div class="user-boxes position-relative" :class="{ 'pe-5': !isForm }">
    <table v-if="isForm" class="table input-table">
      <tr>
        <th class="d-none d-sm-table-cell">Allocation Buyer Name</th>
        <td>
          <div class="p-0" :class="{ 'input-group': form.allocation_buyer_id }">
            <Multiselect
              v-model="form.allocation_buyer_id"
              @change="onChangeAllocationBuyer"
              mode="single"
              placeholder="Choose a grower"
              :searchable="true"
              :options="buyers"
              :class="{
                'is-invalid': form.errors.allocation_buyer_id || form.errors.allocation_id,
              }"
            />
            <button
              v-if="form.allocation_buyer_id"
              class="btn btn-red input-group-text px-1 px-sm-2"
              data-bs-toggle="modal"
              :data-bs-target="`#allocations-${uniqueKey}`"
              v-text="'Select Allocation'"
            />
          </div>
          <div
            v-if="form.errors.allocation_buyer_id"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.allocation_buyer_id"
          />
          <div
            v-if="form.errors.allocation_id"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.allocation_id"
          />
        </td>
      </tr>
    </table>

    <div v-if="isForm && Object.values(form.selected_allocation).length" class="table-responsive">
      <table class="table table-sm">
        <thead>
          <tr>
            <th>Seed type</th>
            <th>Bin size</th>
            <th>Bins available</th>
            <th>Weight</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              {{ getSingleCategoryNameByType(form.selected_allocation.categories, 'seed-type') }}
            </td>
            <td>{{ getBinSizesValue(form.selected_allocation.bin_size) }}</td>
            <td class="text-center text-md-start">{{ form.selected_allocation.no_of_bins }}</td>
            <td>{{ toTonnes(form.selected_allocation.weight) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <template v-if="isForm">
      <div class="row">
        <div class="col-6 col-sm-3 mb-3">
          <label class="form-label">Reallocated bins</label>
          <TextInput v-model="form.no_of_bins" :error="form.errors.no_of_bins" type="text" />
        </div>
        <div class="col-6 col-sm-3 mb-3">
          <label class="form-label">Reallocated kg</label>
          <TextInput v-model="form.weight" :error="form.errors.weight" type="text" />
        </div>
        <div class="col-12 col-sm-6 mb-3">
          <label class="form-label">Comments</label>
          <TextInput v-model="form.comment" :error="form.errors.comment" type="text" />
        </div>
      </div>
      <div v-if="isEdit || isNewItem" class="w-100 text-end">
        <button
          v-if="isEdit"
          data-bs-toggle="modal"
          :data-bs-target="`#update-reallocation-${uniqueKey}`"
          class="btn btn-red"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else>Update</template>
        </button>
        <button
          v-if="isNewItem"
          data-bs-toggle="modal"
          :data-bs-target="`#store-reallocation-${uniqueKey}`"
          class="btn btn-red"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else>Create</template>
        </button>
      </div>
    </template>
    <template v-else>
      <div class="btn-group position-absolute top-0 end-0">
        <button @click="setIsEdit" class="btn btn-red p-1"><i class="bi bi-pen"></i></button>
        <button
          data-bs-toggle="modal"
          :data-bs-target="`#delete-reallocation-${uniqueKey}`"
          class="btn btn-red p-1"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else><i class="bi bi-trash"></i></template>
        </button>
      </div>
      <div class="row allocation-items-box">
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Allocation Buyer Name: </span>
          <Link
            :href="route('users.index', { userId: reallocation.allocation_buyer_id })"
            class="text-danger"
          >
            {{ reallocation?.allocation_buyer?.buyer_name }}
          </Link>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Seed type: </span>
          <span class="text-danger">
            {{
              getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-type') || '-'
            }}
          </span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Bin size: </span>
          <span class="text-danger">{{ getBinSizesValue(reallocation.allocation.bin_size) }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Reallocated bins: </span>
          <span class="text-danger">{{ reallocation.no_of_bins }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Reallocated weight: </span>
          <span class="text-danger">{{ toTonnes(reallocation.weight) }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Grower Group: </span>
          <span class="text-danger">
            {{
              getSingleCategoryNameByType(reallocation.allocation.categories, 'grower-group') || '-'
            }}
          </span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Variety: </span>
          <span class="text-danger">
            {{
              getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-variety') || '-'
            }}
          </span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Gen: </span>
          <span class="text-danger">
            {{
              getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-generation') ||
              '-'
            }}
          </span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Class: </span>
          <span class="text-danger">
            {{
              getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-class') || '-'
            }}
          </span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 pb-1">
          <span>Paddock: </span>
          <span class="text-danger">{{ reallocation.allocation.paddock }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 pb-1">
          <span>Comments: </span>
          <span class="text-danger">{{ reallocation.comment }}</span>
        </div>
      </div>
    </template>
  </div>

  <div class="modal fade" :id="`allocations-${uniqueKey}`" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Select Allocation</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <div v-if="form.allocation_buyer_id" class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>Seed type</th>
                  <th>Variety</th>
                  <th>Class</th>
                  <th>Gen</th>
                  <th>Grower Group</th>
                  <th>Paddock</th>
                  <th>Bin size</th>
                  <th>No of bins</th>
                  <th>Weight</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="allocation in allocations" :key="`allocations-${allocation.id}`">
                  <tr
                    v-if="
                      form.allocation_buyer_id === allocation.buyer_id &&
                      isForm &&
                      (allocation.no_of_bins > 0 || allocation.weight > 0)
                    "
                    @click="() => onSelectAllocation(allocation)"
                    style="cursor: pointer"
                    data-bs-dismiss="modal"
                  >
                    <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-type') }}</td>
                    <td>
                      {{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') }}
                    </td>
                    <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-class') }}</td>
                    <td>
                      {{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') }}
                    </td>
                    <td>
                      {{ getSingleCategoryNameByType(allocation.categories, 'grower-group') }}
                    </td>
                    <td>{{ allocation.paddock }}</td>
                    <td>{{ getBinSizesValue(allocation.bin_size) }}</td>
                    <td>{{ allocation.no_of_bins }}</td>
                    <td>{{ toTonnes(allocation.weight) }}</td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <ConfirmedModal
    :id="`delete-reallocation-${uniqueKey}`"
    cancel="No, Keep it"
    ok="Yes, Delete!"
    @ok="deleteReallocation"
  />

  <ConfirmedModal
    :id="`store-reallocation-${uniqueKey}`"
    title="You want to store this record?"
    @ok="storeRecord"
  />

  <ConfirmedModal
    :id="`update-reallocation-${uniqueKey}`"
    title="You want to update this record?"
    ok="Yes, Update!"
    @ok="updateRecord"
  />
</template>

<style>
@import 'datatables.net-dt';
</style>
