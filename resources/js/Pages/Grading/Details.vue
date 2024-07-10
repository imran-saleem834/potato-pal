<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { useToast } from 'vue-toastification';
import TextInput from '@/Components/TextInput.vue';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';
import SingleDetailsView from '@/Pages/Grading/Partials/SingleDetailsView.vue';
import SelectedAllocationView from '@/Pages/Grading/Partials/SelectedAllocationView.vue';

const toast = useToast();

const props = defineProps({
  uniqueKey: String,
  grading: {
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
  buyer_id: props.grading.buyer_id,
  allocation_id: props.grading.allocation_id,
  bins_tipped: props.grading.bins_tipped || { ...tonnes },
  bins_graded: props.grading.bins_graded || { ...tonnes },
  weight: props.grading.weight,
  waste: props.grading.waste,
  start: props.grading.start,
  end: props.grading.end,
  no_of_crew: props.grading.no_of_crew,
  comments: props.grading.comments,
  selected_allocation: {},
});

watch(
  () => props.grading,
  (grading) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = grading.buyer_id;
    form.allocation_id = grading.allocation_id;
    form.bins_tipped = grading.bins_tipped || { ...tonnes };
    form.bins_graded = grading.bins_graded || { ...tonnes };
    form.weight = grading.weight;
    form.waste = grading.waste;
    form.start = grading.start;
    form.end = grading.end;
    form.no_of_crew = grading.no_of_crew;
    form.comments = grading.comments;
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
    .get(route('buyers.allocations', props.grading.buyer_id))
    .then((response) => {
      form.selected_allocation = response.data.find(
        (item) => props.grading.allocation_id === item.id,
      );
    })
    .catch(() => {})
    .finally(() => {
      loader.value = false;
    });
};

const updateRecord = () => {
  form.patch(route(`grading.update`, props.grading.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
      toast.success('The grade has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route(`grading.store`), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The grade has been created successfully!');
    },
  });
};

const deleteGrading = () => {
  form.delete(route('grading.destroy', props.grading.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('delete');
      toast.success('The grade has been deleted successfully!');
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

  <h4 v-if="isNew">Grading Details</h4>
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
      <label class="form-label">Graded into</label>
      <div class="row">
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form['bins_graded'].half_tonne" :error="form.errors[`bins_graded.half_tonne`]">
            <template #prefix-addon>
              <div class="input-group-text">Half tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form['bins_graded'].one_tonne" :error="form.errors[`bins_graded.one_tonne`]">
            <template #prefix-addon>
              <div class="input-group-text">One tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form['bins_graded'].two_tonne" :error="form.errors[`bins_graded.two_tonne`]">
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
          <label class="form-label">Waste</label>
          <TextInput type="text" v-model="form.waste" :error="form.errors.waste">
            <template #addon>
              <div class="input-group-text">tonnes</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">No of crew</label>
          <TextInput v-model="form.no_of_crew" :error="form.errors.no_of_crew" type="text" />
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">Start Time</label>
          <TextInput type="time" v-model="form.start" :error="form.errors.start" />
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">End Time</label>
          <TextInput type="time" v-model="form.end" :error="form.errors.end" />
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
          :data-bs-target="`#update-grading-${uniqueKey}`"
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
          :data-bs-target="`#store-grading-${uniqueKey}`"
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
          :data-bs-target="`#delete-grading-${uniqueKey}`"
          class="btn btn-red p-1 z-1"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else><i class="bi bi-trash"></i></template>
        </button>
      </div>

      <SingleDetailsView :grading="grading" />
    </template>
  </div>

  <ConfirmedModal
    :id="`delete-grading-${uniqueKey}`"
    cancel="No, Keep it"
    ok="Yes, Delete!"
    @ok="deleteGrading"
  />

  <ConfirmedModal
    :id="`store-grading-${uniqueKey}`"
    title="You want to store this record?"
    @ok="storeRecord"
  />

  <ConfirmedModal
    :id="`update-grading-${uniqueKey}`"
    title="You want to update this record?"
    ok="Yes, Update!"
    @ok="updateRecord"
  />
</template>
