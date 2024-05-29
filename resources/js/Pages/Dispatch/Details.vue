<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import TextInput from '@/Components/TextInput.vue';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';
import SelectedAllocationView from "@/Pages/Dispatch/Partials/SelectedAllocationView.vue";
import SingleDetailsView from "@/Pages/Dispatch/Partials/SingleDetailsView.vue";

const props = defineProps({
  uniqueKey: String,
  dispatch: {
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

const emit = defineEmits(['allocation', 'setReturnDispatch', 'create', 'delete']);

const isEdit = ref(false);
const loader = ref(false);

const form = useForm({
  buyer_id: props.dispatch.buyer_id,
  type: props.dispatch.type,
  allocation_buyer_id: props.dispatch.allocation_buyer_id,
  comment: props.dispatch.comment,
  selected_allocation: {},
});

watch(
  () => props.dispatch,
  (dispatch) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = dispatch.buyer_id;
    form.type = dispatch.type;
    form.allocation_buyer_id = dispatch.allocation_buyer_id;
    form.comment = dispatch.comment;
  },
);

watch(
  () => props.selectedAllocation,
  (allocation) => {
    if (Object.values(allocation).length) {
      form.selected_allocation = allocation;
      form.type = allocation.type;
    }
  },
);

const onChangeAllocationBuyer = () => {
  form.selected_allocation = {};
};

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
});

const setIsEdit = () => {
  isEdit.value = true;
  loader.value = true;

  axios.get(route('d.buyers.allocations', props.dispatch.allocation_buyer_id))
    .then((response) => {
      form.selected_allocation = response.data.find((allocation) => {
        return props.dispatch.item.foreignable_id === allocation.id && props.dispatch.type === allocation.type;
      });
      form.selected_allocation.no_of_bins = props.dispatch.item.no_of_bins;
    })
    .catch(() => {

    })
    .finally(() => {
      loader.value = false
    });
};

const updateRecord = () => {
  form.patch(route('dispatches.update', props.dispatch.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
    },
  });
};

const storeRecord = () => {
  form.post(route('dispatches.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
    },
  });
};

const deleteDispatch = () => {
  const form = useForm({});
  form.delete(route('dispatches.destroy', props.dispatch.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('delete');
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
        <th>Dispatch Buyer Name</th>
        <td>
          <Multiselect
            v-model="form.buyer_id"
            mode="single"
            placeholder="Choose a buyer"
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

  <h4 v-if="isNew">Dispatches Details</h4>
  <div class="user-boxes position-relative" :class="{ 'pe-5': !isForm }">
    <table v-if="isForm" class="table input-table">
      <tr>
        <th class="d-none d-sm-table-cell">Re\Allocation Buyer Name</th>
        <td>
          <div class="p-0" :class="{ 'input-group': form.allocation_buyer_id }">
            <Multiselect
              v-model="form.allocation_buyer_id"
              @change="onChangeAllocationBuyer"
              mode="single"
              placeholder="Choose a buyer"
              :searchable="true"
              :options="$page.props.buyers"
              :class="{
                'is-invalid':
                  form.errors.allocation_buyer_id ||
                  form.errors.selected_allocation,
              }"
            />
            <button
              v-if="form.allocation_buyer_id"
              class="btn btn-red input-group-text px-1 px-sm-2"
              data-bs-toggle="modal"
              data-bs-target="#allocations-modal"
              v-text="'Select Allocation'"
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
      <SelectedAllocationView
        :loader="loader"
        :selected-allocation="form.selected_allocation"
      />
      <div class="row mb-3">
        <div class="col-4">
          <label class="form-label">Dispatch</label>
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
          <TextInput v-model="form.comment" :error="form.errors.comment" type="text"/>
        </div>
      </div>
      <div v-if="isEdit || isNewItem" class="w-100 text-end">
        <button
          v-if="isEdit"
          data-bs-toggle="modal"
          :data-bs-target="`#update-dispatch-${uniqueKey}`"
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
          :data-bs-target="`#store-dispatch-${uniqueKey}`"
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
          :data-bs-target="`#delete-dispatch-${uniqueKey}`"
          class="btn btn-red p-1 z-1"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else><i class="bi bi-trash"></i></template>
        </button>
      </div>
      <button
        data-bs-toggle="modal"
        data-bs-target="#returns-modal"
        @click="$emit('setReturnDispatch', dispatch)"
        class="btn btn-black p-1 position-absolute bottom-0 end-0"
        v-text="'Return'"
      />
      <SingleDetailsView :dispatch="dispatch" />
    </template>
  </div>

  <ConfirmedModal
    :id="`delete-dispatch-${uniqueKey}`"
    cancel="No, Keep it"
    ok="Yes, Delete!"
    @ok="deleteDispatch"
  />

  <ConfirmedModal
    :id="`store-dispatch-${uniqueKey}`"
    title="You want to store this record?"
    @ok="storeRecord"
  />

  <ConfirmedModal
    :id="`update-dispatch-${uniqueKey}`"
    title="You want to update this record?"
    ok="Yes, Update!"
    @ok="updateRecord"
  />
</template>
