<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from 'vue-toastification';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';
import SelectedAllocationView from '@/Pages/Reallocation/Partials/SelectedAllocationView.vue';
import SingleDetailsView from '@/Pages/Reallocation/Partials/SingleDetailsView.vue';

const toast = useToast();

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
  selectedAllocation: Object,
});

const emit = defineEmits(['allocation', 'create', 'delete']);

const isEdit = ref(false);
const loader = ref(false);

const form = useForm({
  buyer_id: props.reallocation.buyer_id,
  allocation_buyer_id: props.reallocation.allocation_buyer_id,
  comment: props.reallocation.comment,
  selected_allocation: [],
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
    form.comment = reallocation.comment;
  },
);

watch(
  () => props.selectedAllocation,
  (allocation) => {
    if (Object.values(allocation).length) {
      form.selected_allocation = allocation;
    }
  },
);

const onChangeAllocationBuyer = () => {
  form.selected_allocation = [];
};

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
});

const setIsEdit = () => {
  isEdit.value = true;
  loader.value = true;

  axios
    .get(route('r.buyers.allocations', props.reallocation.allocation_buyer_id))
    .then((response) => {
      form.selected_allocation = response.data.find((allocation) => {
        return props.reallocation.item.foreignable_id === allocation.id;
      });
      form.selected_allocation.no_of_bins = props.reallocation.item.no_of_bins;
    })
    .catch(() => {})
    .finally(() => {
      loader.value = false;
    });
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
        <th>Reallocate to Buyer</th>
        <td>
          <Multiselect
            v-model="form.buyer_id"
            mode="single"
            placeholder="Choose a reallocation buyer"
            :searchable="true"
            :options="$page.props.buyers"
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
        <th class="d-none d-sm-table-cell">Reallocate from Buyer</th>
        <td>
          <div class="p-0" :class="{ 'input-group': form.allocation_buyer_id }">
            <Multiselect
              v-model="form.allocation_buyer_id"
              @change="onChangeAllocationBuyer"
              mode="single"
              placeholder="Choose a allocation buyer"
              :searchable="true"
              :options="$page.props.buyers"
              :class="{
                'is-invalid': form.errors.allocation_buyer_id || form.errors.selected_allocation,
              }"
            />
            <button
              v-if="form.allocation_buyer_id"
              class="btn btn-red input-group-text px-1 px-sm-2"
              data-bs-toggle="modal"
              data-bs-target="#allocations-modal"
              v-text="'Select Cutting'"
              @click="emit('allocation', form.allocation_buyer_id)"
            />
          </div>
          <div
            v-if="form.errors.allocation_buyer_id"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.allocation_buyer_id"
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
      <SelectedAllocationView :loader="loader" :allocation="form.selected_allocation" />
      <div class="row mb-3">
        <div class="col-4">
          <label class="form-label">Reallocate</label>
          <TextInput
            v-model="form.selected_allocation.no_of_bins"
            :error="form.errors[`selected_allocation.no_of_bins`]"
            type="text"
          >
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-8">
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
        <button @click="setIsEdit" class="btn btn-red p-1 z-1"><i class="bi bi-pen"></i></button>
        <button
          data-bs-toggle="modal"
          :data-bs-target="`#delete-reallocation-${uniqueKey}`"
          class="btn btn-red p-1 z-1"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else><i class="bi bi-trash"></i></template>
        </button>
      </div>

      <SingleDetailsView :reallocation="reallocation" />
    </template>
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
