<script setup>
import { useToast } from 'vue-toastification';
import Multiselect from '@vueform/multiselect';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUpdated, ref, watch } from 'vue';
import * as bootstrap from 'bootstrap';
import TextInput from '@/Components/TextInput.vue';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';
import CustomDatePicker from '@/Components/CustomDatePicker.vue';
import SelectedView from '@/Pages/Cutting/Partials/SelectedView.vue';
import SingleDetailsView from '@/Pages/Cutting/Partials/SingleDetailsView.vue';
import ReallocationModal from '@/Pages/Cutting/Partials/ReallocationModal.vue';
import { getCategoryIdsByType, getCategoryByKeyword, getCategoriesDropDownByType } from '@/helper.js';

const page = usePage();
const toast = useToast();

const props = defineProps({
  uniqueKey: String,
  cutting: {
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
  selected: Object,
});

const emit = defineEmits(['allocation', 'create', 'delete']);

const isEdit = ref(false);
const loader = ref(false);
const reallocate = ref({});

const getDefaultCategoryId = (categories, type, keyword) => {
  let categoriesIds = getCategoryIdsByType(categories, type);
  if (categoriesIds.length <= 0) {
    const defaultCategory = getCategoryByKeyword(page.props.categories, type, keyword);
    if (defaultCategory) {
      categoriesIds = [defaultCategory.id];
    }
  }
  return categoriesIds;
};

const form = useForm({
  buyer_id: props.cutting.buyer_id,
  type: props.cutting.type,
  from_half_tonnes: props.cutting.item?.from_half_tonnes,
  from_one_tonnes: props.cutting.item?.from_one_tonnes,
  from_two_tonnes: props.cutting.item?.from_two_tonnes,
  half_tonnes: props.cutting.item?.half_tonnes,
  one_tonnes: props.cutting.item?.one_tonnes,
  two_tonnes: props.cutting.item?.two_tonnes,
  cut_date: props.cutting.cut_date ? props.cutting.cut_date.split('T')[0] : null,
  cool_store: getDefaultCategoryId(props.cutting.categories, 'cool-store', 'Cherry Hill'),
  fungicide: getDefaultCategoryId(props.cutting.categories, 'fungicide', 'Mancozeb/Lime'),
  comment: props.cutting.comment,
  selected_allocation: {},
});

watch(
  () => props.cutting,
  (cutting) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = cutting.buyer_id;
    form.type = cutting.type;
    form.from_half_tonnes = cutting.item?.from_half_tonnes;
    form.from_one_tonnes = cutting.item?.from_one_tonnes;
    form.from_two_tonnes = cutting.item?.from_two_tonnes;
    form.half_tonnes = cutting.item?.half_tonnes;
    form.one_tonnes = cutting.item?.one_tonnes;
    form.two_tonnes = cutting.item?.two_tonnes;
    form.cut_date = cutting.cut_date ? cutting.cut_date.split('T')[0] : null;
    form.cool_store = getDefaultCategoryId(cutting.categories, 'cool-store', 'Cherry Hill');
    form.fungicide = getDefaultCategoryId(cutting.categories, 'fungicide', 'Mancozeb/Lime');
    form.comment = cutting.comment;
  },
);

watch(
  () => props.selected,
  (selected) => {
    if (Object.values(selected).length > 0) {
      form.selected_allocation = selected;
      form.type = selected.type;
    }
  },
);

const onChangeBuyer = () => (form.selected_allocation = {});

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
});

const setIsEdit = () => {
  isEdit.value = true;
  loader.value = true;

  axios
    .get(route(`cutting.buyers.${form.type}`, props.cutting.buyer_id))
    .then((response) => {
      form.selected_allocation = response.data.find((row) => props.cutting.item.foreignable_id === row.id);
      form.selected_allocation.bin_size = props.cutting.item.bin_size;
      form.selected_allocation.no_of_bins = props.cutting.item.no_of_bins;
      form.selected_allocation.type = form.type;
      form.from_half_tonnes = props.cutting.item.from_half_tonnes;
      form.from_one_tonnes = props.cutting.item.from_one_tonnes;
      form.from_two_tonnes = props.cutting.item.from_two_tonnes;
      form.half_tonnes = props.cutting.item.half_tonnes;
      form.one_tonnes = props.cutting.item.one_tonnes;
      form.two_tonnes = props.cutting.item.two_tonnes;
    })
    .catch(() => {})
    .finally(() => {
      loader.value = false;
    });
};

const updateRecord = () => {
  form.patch(route('cuttings.update', props.cutting.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
      toast.success('The cutting information has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('cuttings.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The cutting information has been saved successfully!');
    },
  });
};

const deleteCutting = () => {
  const form = useForm({});
  form.delete(route('cuttings.destroy', props.cutting.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('delete');
      toast.success('The cutting record has been deleted successfully!');
    },
  });
};

onMounted(() => {
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl));
});

onUpdated(() => {
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl));
});

defineExpose({
  storeRecord,
});
</script>

<template>
  <h4 v-if="isNew">Cutting Details</h4>
  <div class="user-boxes position-relative" :class="{ 'pe-sm-5': !isForm }">
    <table v-if="isForm" class="table input-table mb-2">
      <tr>
        <th class="d-none d-sm-table-cell">Buyer</th>
        <td>
          <div
            class="p-0"
            :class="{
              'input-group': form.buyer_id,
              'is-invalid': form.errors.buyer_id || form.errors.selected_allocation,
            }"
          >
            <Multiselect
              v-if="isNew"
              v-model="form.buyer_id"
              @change="onChangeBuyer"
              mode="single"
              placeholder="Choose a buyer"
              :searchable="true"
              :options="$page.props.buyers"
            />
            <input
              v-else
              type="text"
              class="form-control"
              :disabled="true"
              v-model="$page.props.buyers.find((buyer) => buyer.value === form.buyer_id).label"
            />
            <button
              v-if="form.buyer_id"
              class="btn btn-red input-group-text px-1 px-sm-2"
              data-bs-toggle="modal"
              data-bs-target="#allocations-modal"
              @click="emit('allocation', form.buyer_id)"
              v-text="'Select for cutting'"
            />
          </div>
          <div v-if="form.errors.buyer_id" class="invalid-feedback p-0 m-0" v-text="form.errors.buyer_id" />
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
      <h4 class="mt-0 mb-3">Original bins tipped:</h4>
      <div class="row">
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form.from_half_tonnes" :error="form.errors.from_half_tonnes">
            <template #prefix-addon>
              <div class="input-group-text">Half tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form.from_one_tonnes" :error="form.errors.from_one_tonnes">
            <template #prefix-addon>
              <div class="input-group-text">One tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form.from_two_tonnes" :error="form.errors.from_two_tonnes">
            <template #prefix-addon>
              <div class="input-group-text">Two tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
      </div>
      <h4 class="mt-0 mb-3">Cut into no. of bins:</h4>
      <div class="row">
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form.half_tonnes" :error="form.errors.half_tonnes">
            <template #prefix-addon>
              <div class="input-group-text">Half tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form.one_tonnes" :error="form.errors.one_tonnes">
            <template #prefix-addon>
              <div class="input-group-text">One tonne</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4 mb-3">
          <TextInput type="text" v-model="form.two_tonnes" :error="form.errors.two_tonnes">
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
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Date Cut</label>
          <CustomDatePicker :form="form" field="cut_date" mode="date" format="YYYY-MM-DD" />
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Cut By</label>
          <Multiselect
            v-model="form.cool_store"
            mode="tags"
            placeholder="Choose a cut by"
            :searchable="true"
            :create-option="true"
            :options="getCategoriesDropDownByType($page.props.categories, 'cool-store')"
            :class="{ 'is-invalid': form.errors.cool_store }"
          />
          <div v-if="form.errors.cool_store" class="invalid-feedback" v-text="form.errors.cool_store" />
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Fungicide</label>
          <Multiselect
            v-model="form.fungicide"
            mode="tags"
            placeholder="Choose a fungicide"
            :searchable="true"
            :create-option="true"
            :options="getCategoriesDropDownByType($page.props.categories, 'fungicide')"
            :class="{ 'is-invalid': form.errors.fungicide }"
          />
          <div v-if="form.errors.fungicide" class="invalid-feedback" v-text="form.errors.fungicide" />
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Comments</label>
          <TextInput v-model="form.comment" :error="form.errors.comment" type="text" />
        </div>
      </div>
      <div v-if="isEdit || isNewItem" class="w-100 text-end">
        <button
          v-if="isEdit"
          data-bs-toggle="modal"
          :data-bs-target="`#update-cutting-${uniqueKey}`"
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
          :data-bs-target="`#store-cutting-${uniqueKey}`"
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
        <button data-bs-toggle="modal" :data-bs-target="`#delete-cutting-${uniqueKey}`" class="btn btn-red p-1 z-1">
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else><i class="bi bi-trash"></i></template>
        </button>
      </div>
      <div class="btn-group position-absolute bottom-0 end-0">
        <button
          class="btn btn-black p-1 z-1"
          data-bs-toggle="modal"
          :data-bs-target="`#relocate-${uniqueKey}`"
          @click="() => (reallocate = cutting)"
        >
          Reallocate
        </button>
      </div>

      <SingleDetailsView :cutting="cutting" />
    </template>
  </div>

  <ConfirmedModal :id="`delete-cutting-${uniqueKey}`" cancel="No, Keep it" ok="Yes, Delete!" @ok="deleteCutting" />

  <ConfirmedModal :id="`store-cutting-${uniqueKey}`" title="You want to store this record?" @ok="storeRecord" />

  <ConfirmedModal
    :id="`update-cutting-${uniqueKey}`"
    title="You want to update this record?"
    ok="Yes, Update!"
    @ok="updateRecord"
  />

  <ReallocationModal :id="`relocate-${uniqueKey}`" :cutting="reallocate" @close="() => (reallocate = {})" />
</template>
