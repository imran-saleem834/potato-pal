<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { booleanArray } from '@/const.js';
import Multiselect from '@vueform/multiselect';
import { useToast } from 'vue-toastification';
import UlLiButton from '@/Components/UlLiButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';
import SingleDetailsView from '@/Pages/ChemicalApplicant/Partials/SingleDetailsView.vue';
import SelectedAllocationView from '@/Pages/ChemicalApplicant/Partials/SelectedAllocationView.vue';

const toast = useToast();

const props = defineProps({
  uniqueKey: String,
  chemicalApplicant: {
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
  buyer_id: props.chemicalApplicant.buyer_id,
  allocation_id: props.chemicalApplicant.allocation_id,
  bins_tipped: props.chemicalApplicant.bins_tipped || { ...tonnes },
  bins_out: props.chemicalApplicant.bins_out || { ...tonnes },
  fungicide: props.chemicalApplicant.fungicide,
  fungicide_used: props.chemicalApplicant.fungicide_used,
  start: props.chemicalApplicant.start,
  end: props.chemicalApplicant.end,
  no_of_crew: props.chemicalApplicant.no_of_crew,
  comments: props.chemicalApplicant.comments,
  selected_allocation: {},
});

watch(
  () => props.chemicalApplicant,
  (chemicalApplicant) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = chemicalApplicant.buyer_id;
    form.allocation_id = chemicalApplicant.allocation_id;
    form.bins_tipped = chemicalApplicant.bins_tipped || { ...tonnes };
    form.bins_out = chemicalApplicant.bins_out || { ...tonnes };
    form.fungicide = chemicalApplicant.fungicide;
    form.fungicide_used = chemicalApplicant.fungicide_used;
    form.start = chemicalApplicant.start;
    form.end = chemicalApplicant.end;
    form.no_of_crew = chemicalApplicant.no_of_crew;
    form.comments = chemicalApplicant.comments;
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
    .get(route('buyers.allocations', props.chemicalApplicant.buyer_id))
    .then((response) => {
      form.selected_allocation = response.data.find(
        (item) => props.chemicalApplicant.allocation_id === item.id,
      );
    })
    .catch(() => {})
    .finally(() => {
      loader.value = false;
    });
};

const updateRecord = () => {
  form.patch(route(`chemical-applicant.update`, props.chemicalApplicant.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
      toast.success('The chemical applicant has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route(`chemical-applicant.store`), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The chemical applicant has been created successfully!');
    },
  });
};

const deleteChemicalApplicant = () => {
  form.delete(route('chemical-applicant.destroy', props.chemicalApplicant.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('delete');
      toast.success('The chemical applicant has been deleted successfully!');
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

  <h4 v-if="isNew">Chemical Applicant Details</h4>
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
      <label class="form-label">Bins Out</label>
      <div class="row">
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form['bins_out'].half_tonne" :error="form.errors[`bins_out.half_tonne`]">
            <template #prefix-addon>
              <div class="input-group-text">Half tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form['bins_out'].one_tonne" :error="form.errors[`bins_out.one_tonne`]">
            <template #prefix-addon>
              <div class="input-group-text">One tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form['bins_out'].two_tonne" :error="form.errors[`bins_out.two_tonne`]">
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
          <label class="form-label">Fungicide</label>
          <UlLiButton
            :is-form="true"
            :value="!!form.fungicide"
            :error="form.errors.fungicide"
            :items="booleanArray"
            @click="(value) => (form.fungicide = value)"
          />
        </div>
        <div v-if="!!form.fungicide" class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">Fungicide Used</label>
          <TextInput type="text" v-model="form.fungicide_used" :error="form.errors.fungicide_used">
            <template #addon>
              <div class="input-group-text">litres</div>
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
          :data-bs-target="`#update-chemical-applicant-${uniqueKey}`"
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
          :data-bs-target="`#store-chemical-applicant-${uniqueKey}`"
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
          :data-bs-target="`#delete-chemical-applicant-${uniqueKey}`"
          class="btn btn-red p-1 z-1"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else><i class="bi bi-trash"></i></template>
        </button>
      </div>

      <SingleDetailsView :chemical-applicant="chemicalApplicant" />
    </template>
  </div>

  <ConfirmedModal
    :id="`delete-chemical-applicant-${uniqueKey}`"
    cancel="No, Keep it"
    ok="Yes, Delete!"
    @ok="deleteChemicalApplicant"
  />

  <ConfirmedModal
    :id="`store-chemical-applicant-${uniqueKey}`"
    title="You want to store this record?"
    @ok="storeRecord"
  />

  <ConfirmedModal
    :id="`update-chemical-applicant-${uniqueKey}`"
    title="You want to update this record?"
    ok="Yes, Update!"
    @ok="updateRecord"
  />
</template>
