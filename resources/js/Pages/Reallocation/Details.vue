<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from 'vue-toastification';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';
import SelectedCuttingView from '@/Pages/Reallocation/Partials/SelectedCuttingView.vue';
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
  selectedCutting: Object,
});

const emit = defineEmits(['cutting', 'create', 'delete']);

const isEdit = ref(false);
const loader = ref(false);

const form = useForm({
  buyer_id: props.reallocation.buyer_id,
  allocation_buyer_id: props.reallocation.allocation_buyer_id,
  half_tonnes: props.reallocation.item?.half_tonnes,
  one_tonnes: props.reallocation.item?.one_tonnes,
  two_tonnes: props.reallocation.item?.two_tonnes,
  comment: props.reallocation.comment,
  selected_cutting: {},
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
    form.half_tonnes = reallocation.item?.half_tonnes;
    form.one_tonnes = reallocation.item?.one_tonnes;
    form.two_tonnes = reallocation.item?.two_tonnes;
    form.comment = reallocation.comment;
  },
);

watch(
  () => props.selectedCutting,
  (cutting) => {
    if (Object.values(cutting).length) {
      form.selected_cutting = cutting;
    }
  },
);

const onChangeAllocationBuyer = () => {
  form.selected_cutting = [];
};

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
});

const setIsEdit = () => {
  isEdit.value = true;
  loader.value = true;

  axios
    .get(route('buyers.cuttings', props.reallocation.allocation_buyer_id))
    .then((response) => {
      form.selected_cutting = response.data.find((cutting) => {
        return props.reallocation.item.foreignable_id === cutting.id;
      });
      form.half_tonnes = props.reallocation.item.half_tonnes;
      form.one_tonnes = props.reallocation.item.one_tonnes;
      form.two_tonnes = props.reallocation.item.two_tonnes;
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
                'is-invalid': form.errors.allocation_buyer_id || form.errors.selected_cutting,
              }"
            />
            <button
              v-if="form.allocation_buyer_id"
              class="btn btn-red input-group-text px-1 px-sm-2"
              data-bs-toggle="modal"
              data-bs-target="#cuttings-modal"
              v-text="'Select Cutting'"
              @click="emit('cutting', form.allocation_buyer_id)"
            />
          </div>
          <div
            v-if="form.errors.allocation_buyer_id"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.allocation_buyer_id"
          />
          <div
            v-if="form.errors.selected_cutting"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.selected_cutting"
          />
        </td>
      </tr>
    </table>

    <template v-if="isForm">
      <SelectedCuttingView :loader="loader" :cutting="form.selected_cutting" />
      <div class="row">
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <TextInput type="text" v-model="form.half_tonnes" :error="form.errors.half_tonnes">
            <template #prefix-addon>
              <div class="input-group-text">Half tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <TextInput type="text" v-model="form.one_tonnes" :error="form.errors.one_tonnes">
            <template #prefix-addon>
              <div class="input-group-text">One tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <TextInput type="text" v-model="form.two_tonnes" :error="form.errors.two_tonnes">
            <template #prefix-addon>
              <div class="input-group-text">Two tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label d-none">Comments</label>
          <TextInput v-model="form.comment" :error="form.errors.comment" type="text" placeholder="Comments" />
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
