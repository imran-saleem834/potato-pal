<script setup>
import { computed, ref, watch } from 'vue';
import { DatePicker } from 'v-calendar';
import { useForm } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { useToast } from 'vue-toastification';
import TextInput from '@/Components/TextInput.vue';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';
import SingleDetailsView from '@/Pages/BulkBagging/Partials/SingleDetailsView.vue';
import SelectedAllocationView from '@/Pages/BulkBagging/Partials/SelectedAllocationView.vue';

const toast = useToast();

const props = defineProps({
  uniqueKey: String,
  bulkBagging: {
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
  selectedAllocation: Object,
});

const emit = defineEmits(['allocation', 'create', 'delete']);

const isEdit = ref(false);
const loader = ref(false);

const tonnes = {
  half_tonne: 0,
  one_tonne: 0,
  two_tonne: 0,
};

const form = useForm({
  buyer_id: props.bulkBagging.buyer_id,
  allocation_id: props.bulkBagging.allocation_id,
  bins_tipped: props.bulkBagging.bins_tipped || { ...tonnes },
  weight: props.bulkBagging.weight,
  no_of_bulk_bags_out: props.bulkBagging.no_of_bulk_bags_out,
  net_weight_bags_out: props.bulkBagging.net_weight_bags_out,
  start: props.bulkBagging.start,
  end: props.bulkBagging.end,
  no_of_crew: props.bulkBagging.no_of_crew,
  comments: props.bulkBagging.comments,
  selected_allocation: {},
});

watch(
  () => props.bulkBagging,
  (bulkBagging) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = bulkBagging.buyer_id;
    form.allocation_id = bulkBagging.allocation_id;
    form.bins_tipped = bulkBagging.bins_tipped || { ...tonnes };
    form.weight = bulkBagging.weight;
    form.no_of_bulk_bags_out = bulkBagging.no_of_bulk_bags_out;
    form.net_weight_bags_out = bulkBagging.net_weight_bags_out;
    form.start = bulkBagging.start;
    form.end = bulkBagging.end;
    form.no_of_crew = bulkBagging.no_of_crew;
    form.comments = bulkBagging.comments;
  },
);

watch(
  () => props.selectedAllocation,
  (allocation) => {
    if (Object.values(allocation).length > 0) {
      form.selected_allocation = allocation;
    }
  },
);

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
});

const onChangeBuyer = () => (form.selected_allocation = {});

const setIsEdit = () => {
  isEdit.value = true;
  loader.value = true;

  axios
    .get(route('buyers.allocations', props.bulkBagging.buyer_id))
    .then((response) => {
      form.selected_allocation = response.data.find(
        (item) => props.bulkBagging.allocation_id === item.id,
      );
    })
    .catch(() => {})
    .finally(() => {
      loader.value = false;
    });
};

const updateRecord = () => {
  form.patch(route(`bulk-bagging.update`, props.bulkBagging.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
      toast.success('The bulk bagging has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route(`bulk-bagging.store`), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The bulk bagging has been created successfully!');
    },
  });
};

const deleteBulkBagging = () => {
  form.delete(route('bulk-bagging.destroy', props.bulkBagging.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('delete');
      toast.success('The bulk bagging has been deleted successfully!');
    },
  });
};

defineExpose({
  updateRecord,
  storeRecord,
});
</script>

<template>
  <div v-if="isNew" class="user-boxes">
    <table class="table input-table mb-0">
      <tr>
        <th>Buyer Name</th>
        <td>
          <Multiselect
            v-model="form.buyer_id"
            mode="single"
            placeholder="Choose a buyer"
            :searchable="true"
            :options="$page.props.buyers"
            @change="onChangeBuyer"
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

  <h4 v-if="isNew">Bulk Bagging Details</h4>
  <div class="user-boxes position-relative" :class="{ 'pe-5': !isForm }">
    <table v-if="isForm && form.buyer_id" class="table input-table">
      <tr>
        <td class="text-center">
          <button
            class="btn btn-red input-group-text px-1 px-sm-2"
            data-bs-toggle="modal"
            data-bs-target="#allocations-modal"
            v-text="'Select Allocation'"
            @click="emit('allocation', form.buyer_id)"
          />
          <div
            v-if="form.errors.selected_allocation"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.selected_allocation"
          />
        </td>
      </tr>
    </table>

    <template v-if="isForm">
      <SelectedAllocationView
        :form="form"
        :loader="loader"
        :allocation="form.selected_allocation"
      />
      <label class="form-label">Bins Tipped</label>
      <div class="row">
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form['bins_tipped'].half_tonne" :error="form.errors[`bins_tipped.half_tonne`]">
            <template #prefix-addon>
              <div class="input-group-text">Half tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form['bins_tipped'].one_tonne" :error="form.errors[`bins_tipped.one_tonne`]">
            <template #prefix-addon>
              <div class="input-group-text">One tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form['bins_tipped'].two_tonne" :error="form.errors[`bins_tipped.two_tonne`]">
            <template #prefix-addon>
              <div class="input-group-text">Two tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">Weight</label>
          <TextInput type="text" v-model="form.weight" :error="form.errors.weight">
            <template #addon>
              <div class="input-group-text">tonnes</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">No of bulk bags out</label>
          <TextInput type="text" v-model="form.no_of_bulk_bags_out" :error="form.errors.no_of_bulk_bags_out" />
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">Net weight of bulk bags</label>
          <TextInput type="text" v-model="form.net_weight_bags_out" :error="form.errors.net_weight_bags_out" />
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">No of crew</label>
          <TextInput v-model="form.no_of_crew" :error="form.errors.no_of_crew" type="text" />
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">Start Time</label>
          <DatePicker
            v-model.string="form.start"
            mode="dateTime"
            :masks="{
              modelValue: 'YYYY-MM-DD HH:mm:ss',
            }"
          >
            <template #default="{ togglePopover }">
              <input
                type="text"
                class="form-control"
                :class="{ 'is-invalid': form.errors.start }"
                :value="form.start"
                @click="togglePopover"
              />
              <div
                v-if="form.errors.start"
                class="invalid-feedback"
                v-text="form.errors.start"
              />
            </template>
          </DatePicker>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">End Time</label>
          <DatePicker
            v-model.string="form.end"
            mode="dateTime"
            :masks="{
              modelValue: 'YYYY-MM-DD HH:mm:ss',
            }"
          >
            <template #default="{ togglePopover }">
              <input
                type="text"
                class="form-control"
                :class="{ 'is-invalid': form.errors.end }"
                :value="form.end"
                @click="togglePopover"
              />
              <div
                v-if="form.errors.end"
                class="invalid-feedback"
                v-text="form.errors.end"
              />
            </template>
          </DatePicker>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">Comments</label>
          <TextInput v-model="form.comments" :error="form.errors.comments" type="text" />
        </div>
      </div>
      <div v-if="isEdit || isNewItem" class="w-100 text-end">
        <button
          v-if="isEdit"
          data-bs-toggle="modal"
          :data-bs-target="`#update-bulk-bagging-${uniqueKey}`"
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
          :data-bs-target="`#store-bulk-bagging-${uniqueKey}`"
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
        <button @click="setIsEdit" class="btn btn-red p-1 z-1"><i class="bi bi-pen"></i></button>
        <button
          data-bs-toggle="modal"
          :data-bs-target="`#delete-bulk-bagging-${uniqueKey}`"
          class="btn btn-red p-1 z-1"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else><i class="bi bi-trash"></i></template>
        </button>
      </div>

      <SingleDetailsView :bulk-bagging="bulkBagging" />
    </template>
  </div>

  <ConfirmedModal
    :id="`delete-bulk-bagging-${uniqueKey}`"
    cancel="No, Keep it"
    ok="Yes, Delete!"
    @ok="deleteBulkBagging"
  />

  <ConfirmedModal
    :id="`store-bulk-bagging-${uniqueKey}`"
    title="You want to store this record?"
    @ok="storeRecord"
  />

  <ConfirmedModal
    :id="`update-bulk-bagging-${uniqueKey}`"
    title="You want to update this record?"
    ok="Yes, Update!"
    @ok="updateRecord"
  />
</template>
