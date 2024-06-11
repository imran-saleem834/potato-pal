<script setup>
import { DatePicker } from 'v-calendar';
import { computed, ref, watch } from 'vue';
import Multiselect from '@vueform/multiselect';
import { useForm, usePage } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';
import SelectedView from '@/Pages/Dispatch/Partials/SelectedView.vue';
import SingleDetailsView from '@/Pages/Dispatch/Partials/SingleDetailsView.vue';
import { getCategoriesDropDownByType, getCategoryIdsByType } from '@/helper.js';

const page = usePage();

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
  half_tonnes: props.dispatch.item?.half_tonnes,
  one_tonnes: props.dispatch.item?.one_tonnes,
  two_tonnes: props.dispatch.item?.two_tonnes,
  comment: props.dispatch.comment,
  created_at: props.dispatch.created_at
    ? props.dispatch.created_at.split('.')[0].replace('T', ' ')
    : null,
  buyer_group: getCategoryIdsByType(props.dispatch.categories, 'buyer-group'),
  transport: getCategoryIdsByType(props.dispatch.categories, 'transport'),
  docket_no: props.dispatch.docket_no,
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
    form.half_tonnes = dispatch.item?.half_tonnes;
    form.one_tonnes = dispatch.item?.one_tonnes;
    form.two_tonnes = dispatch.item?.two_tonnes;
    form.comment = dispatch.comment;
    form.created_at = dispatch.created_at ? dispatch.created_at.split('.')[0].replace('T', ' ') : null;
    form.buyer_group = getCategoryIdsByType(dispatch.categories, 'buyer-group');
    form.transport = getCategoryIdsByType(dispatch.categories, 'transport');
    form.docket_no = dispatch.docket_no;
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

const onChangeBuyer = () => {
  form.selected_allocation = {};
};

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
});

const setIsEdit = () => {
  isEdit.value = true;
  loader.value = true;

  axios
    .get(route('d.buyers.allocations', props.dispatch.buyer_id))
    .then((response) => {
      form.selected_allocation = response.data.find((allocation) => {
        return (
          props.dispatch.item.foreignable_id === allocation.id &&
          props.dispatch.type === allocation.type
        );
      });
      form.half_tonnes = props.dispatch.item.half_tonnes;
      form.one_tonnes = props.dispatch.item.one_tonnes;
      form.two_tonnes = props.dispatch.item.two_tonnes;
    })
    .catch(() => {})
    .finally(() => {
      loader.value = false;
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

const buyerGroups = computed(() => {
  const categories = page.props.buyers.find(buyer => buyer.value === form.buyer_id)?.categories || [];
  return categories.map((category) => category.category);
});

defineExpose({
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

  <h4 v-if="isNew">Dispatches Details</h4>
  <div class="user-boxes position-relative" :class="{ 'pe-5': !isForm }">
    <table v-if="isForm && form.buyer_id" class="table input-table">
      <tr>
        <td class="text-center">
          <button
            class="btn btn-red input-group-text px-1 px-sm-2"
            data-bs-toggle="modal"
            data-bs-target="#allocations-modal"
            v-text="'Select Allocation / Reallocation / Cutting'"
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
      <SelectedView :loader="loader" :selected="form.selected_allocation" />
      <div class="row mb-3">
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
          <TextInput v-model="form.comment" :error="form.errors.comment" type="text" placeholder="Comments" />
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Dispatch Time</label>
          <DatePicker
            v-model.string="form.created_at"
            mode="dateTime"
            :masks="{
              modelValue: 'YYYY-MM-DD HH:mm:ss',
            }"
          >
            <template #default="{ togglePopover }">
              <input
                type="text"
                class="form-control"
                :class="{ 'is-invalid': form.errors[`.created_at`] }"
                :value="form.created_at"
                @click="togglePopover"
              />
              <div
                v-if="form.errors[`created_at`]"
                class="invalid-feedback"
                v-text="form.errors[`created_at`]"
              />
            </template>
          </DatePicker>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Group Type</label>
          <Multiselect
            v-model="form.buyer_group"
            mode="tags"
            placeholder="Choose a group type"
            :searchable="true"
            :class="{ 'is-invalid': form.errors[`buyer_group`] }"
            :options="getCategoriesDropDownByType(buyerGroups, 'buyer-group')"
          />
          <div v-if="form.errors.buyer_group" class="invalid-feedback">
            {{ form.errors.buyer_group }}
          </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Transport</label>
          <Multiselect
            v-model="form.transport"
            mode="tags"
            placeholder="Choose a transport"
            :searchable="true"
            :class="{ 'is-invalid': form.errors[`transport`] }"
            :options="getCategoriesDropDownByType($page.props.categories, 'transport')"
          />
          <div v-if="form.errors.transport" class="invalid-feedback">
            {{ form.errors.transport }}
          </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Docket No</label>
          <TextInput type="text" v-model="form.docket_no" :error="form.errors.docket_no" />
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
