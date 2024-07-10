<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { useToast } from 'vue-toastification';
import TextInput from '@/Components/TextInput.vue';
import UlLiButton from '@/Components/UlLiButton.vue';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';
import SingleDetailsView from '@/Pages/Sizing/Partials/SingleDetailsView.vue';
import SelectedUnloadView from '@/Pages/Sizing/Partials/SelectedUnloadView.vue';
import SelectedAllocationView from '@/Pages/Sizing/Partials/SelectedAllocationView.vue';
import { getCategoryIdsByType, getCategoriesDropDownByType } from "@/helper.js";

const toast = useToast();

const props = defineProps({
  uniqueKey: String,
  sizing: {
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

const emit = defineEmits(['toSelect', 'create', 'delete']);

const isEdit = ref(false);
const loader = ref(false);

const tonnes = {
  half_tonne: 0,
  one_tonne: 0,
  two_tonne: 0,
};

const form = useForm({
  type: props.sizing?.sizeable_type ? (props.sizing.sizeable_type === 'App\\Models\\Allocation' ? 'allocation' : 'unload') : null,
  user_id: props.sizing.user_id,
  bins_tipped: props.sizing.bins_tipped || { ...tonnes },
  fungicide: getCategoryIdsByType(props.sizing.categories, 'fungicide')[0],
  seed_type: getCategoryIdsByType(props.sizing.categories, 'seed-type')[0],
  weight: props.sizing.weight,
  start: props.sizing.start,
  end: props.sizing.end,
  no_of_crew: props.sizing.no_of_crew,
  comments: props.sizing.comments,
  selected_allocation: {},
  selected_unload: {},
});

watch(
  () => props.sizing,
  (sizing) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.type = sizing?.sizeable_type ? (sizing.sizeable_type === 'App\\Models\\Allocation' ? 'allocation' : 'unload') : null;
    form.user_id = sizing.user_id;
    form.bins_tipped = sizing.bins_tipped || { ...tonnes };
    form.fungicide = getCategoryIdsByType(sizing.categories, 'fungicide')[0];
    form.seed_type = getCategoryIdsByType(sizing.categories, 'seed-type')[0];
    form.weight = sizing.weight;
    form.start = sizing.start;
    form.end = sizing.end;
    form.no_of_crew = sizing.no_of_crew;
    form.comments = sizing.comments;
  },
);

watch(
  () => props.selectedAllocation,
  (allocation) => {
    if (Object.values(allocation).length > 0) {
      if (isAllocation.value) {
        form.selected_allocation = allocation;
      } else {
        form.selected_unload = allocation;
      }
    }
  },
);

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
});

const isAllocation = computed(() => form.type === 'allocation');

const onChangeUser = () => {
  form.selected_allocation = {};
  form.selected_unload = {};
};

const onChangeType = (value) => {
  form.type = value;
  form.user_id = null;
  onChangeUser();
};

const onChangeInnerType = (value) => {
  form.type = value;
  onChangeUser();
};

const setIsEdit = () => {
  isEdit.value = true;
  loader.value = true;

  axios
    .get(route(isAllocation.value ? 'buyers.allocations' : 'grower.unloads', props.sizing.user_id))
    .then((response) => {
      if (isAllocation.value) {
        form.selected_allocation = response.data.find(
          (item) => props.sizing.sizeable_id === item.id,
        );
      } else {
        form.selected_unload = response.data.find(
          (item) => props.sizing.sizeable_id === item.id,
        );
      }
    })
    .catch(() => {})
    .finally(() => {
      loader.value = false;
    });
};

const updateRecord = () => {
  form.patch(route(`sizing.update`, props.sizing.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
      toast.success('The grade has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route(`sizing.store`), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The grade has been created successfully!');
    },
  });
};

const deleteSizing = () => {
  form.delete(route('sizing.destroy', props.sizing.id), {
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
        <th>Sizing Type</th>
        <td>
          <UlLiButton
            :is-form="true"
            :value="form.type"
            :error="form.errors.type"
            :items="[
              { value: 'unload', label: 'Unload' }, 
              { value: 'allocation', label: 'Allocation' } 
            ]"
            @click="onChangeType"
          />
        </td>
      </tr>
      <tr v-if="form.type">
        <th>{{ isAllocation ? 'Buyer Name' : 'Grower Name' }}</th>
        <td>
          <Multiselect
            v-model="form.user_id"
            mode="single"
            :placeholder="`Choose a ${isAllocation ? 'buyer' : 'grower'}`"
            :searchable="true"
            :options="isAllocation ? $page.props.buyers : $page.props.growers"
            @change="onChangeUser"
            :class="{ 'is-invalid': form.errors.user_id }"
          />
          <div
            v-if="form.errors.user_id"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.user_id"
          />
        </td>
      </tr>
    </table>
  </div>

  <h4 v-if="isNew">Sizing Details</h4>
  <div class="user-boxes position-relative" :class="{ 'pe-5': !isForm }">
    <div  v-if="isForm && form.user_id" class="d-flex justify-content-between mb-2">
      <div>
        <template v-if="!isNew">
          <UlLiButton
            :is-form="true"
            :value="form.type"
            :error="form.errors.type"
            :items="[
                { value: 'unload', label: 'Unload' }, 
                { value: 'allocation', label: 'Allocation' } 
              ]"
            @click="onChangeInnerType"
          />
        </template>
      </div>
      <div v-if="form.type">
        <button
          class="btn btn-red input-group-text px-1 px-sm-2"
          data-bs-toggle="modal"
          :data-bs-target="isAllocation ? '#allocations-modal' : '#unloads-modal'"
          v-text="isAllocation ? 'Select Allocation' : 'Select Unload'"
          @click="emit('toSelect', isAllocation ? 'allocation' : 'unload', form.user_id)"
        />
      </div>
    </div>
    <template v-if="isForm">
      <template v-if="form.errors.selected_allocation">
        <div class="is-invalid"></div>
        <div class="invalid-feedback p-0 m-0" v-text="form.errors.selected_allocation" />
      </template>
      <template v-if="form.errors.selected_unload">
        <div class="is-invalid"></div>
        <div class="invalid-feedback p-0 m-0" v-text="form.errors.selected_unload" />
      </template>
      <SelectedAllocationView
        v-if="isAllocation"
        :form="form"
        :loader="loader"
        :allocation="form.selected_allocation"
      />
      <SelectedUnloadView
        v-else
        :form="form"
        :loader="loader"
        :unload="form.selected_unload"
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
          <label class="form-label">Seed Type</label>
          <Multiselect
            v-model="form.seed_type"
            mode="single"
            placeholder="Choose a seed type"
            :searchable="true"
            :options="getCategoriesDropDownByType($page.props.categories, 'seed-type')"
            :class="{ 'is-invalid': form.errors.seed_type }"
          />
          <div
            v-if="form.errors.seed_type"
            class="invalid-feedback"
            v-text="form.errors.seed_type"
          />
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">Fungicide</label>
          <Multiselect
            v-model="form.fungicide"
            mode="single"
            placeholder="Choose a fungicide"
            :searchable="true"
            :options="getCategoriesDropDownByType($page.props.categories, 'fungicide')"
            :class="{ 'is-invalid': form.errors.fungicide }"
          />
          <div
            v-if="form.errors.fungicide"
            class="invalid-feedback"
            v-text="form.errors.fungicide"
          />
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <label class="form-label">No of crew</label>
          <TextInput v-model="form.no_of_crew" :error="form.errors.no_of_crew" type="text" />
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-2 mb-3">
          <label class="form-label">Start Time</label>
          <TextInput type="time" v-model="form.start" :error="form.errors.start" />
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-2 mb-3">
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
          :data-bs-target="`#update-sizing-${uniqueKey}`"
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
          :data-bs-target="`#store-sizing-${uniqueKey}`"
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
          :data-bs-target="`#delete-sizing-${uniqueKey}`"
          class="btn btn-red p-1 z-1"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else><i class="bi bi-trash"></i></template>
        </button>
      </div>

      <SingleDetailsView :sizing="sizing" />
    </template>
  </div>

  <ConfirmedModal
    :id="`delete-sizing-${uniqueKey}`"
    cancel="No, Keep it"
    ok="Yes, Delete!"
    @ok="deleteSizing"
  />

  <ConfirmedModal
    :id="`store-sizing-${uniqueKey}`"
    title="You want to store this record?"
    @ok="storeRecord"
  />

  <ConfirmedModal
    :id="`update-sizing-${uniqueKey}`"
    title="You want to update this record?"
    ok="Yes, Update!"
    @ok="updateRecord"
  />
</template>
