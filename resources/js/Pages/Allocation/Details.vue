<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { getCategoryIdsByType } from '@/helper.js';
import Multiselect from '@vueform/multiselect';
import TextInput from '@/Components/TextInput.vue';
import { useToast } from 'vue-toastification';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';
import SingleDetailsView from '@/Pages/Allocation/Partials/SingleDetailsView.vue';
import SelectedReceivalView from '@/Pages/Allocation/Partials/SelectedReceivalView.vue';
import ReallocationModal from '@/Pages/Allocation/Partials/ReallocationModal.vue';

const toast = useToast();

const props = defineProps({
  uniqueKey: String,
  allocation: {
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
  selectedReceival: Object,
});

const emit = defineEmits(['grower', 'create', 'delete']);

const isEdit = ref(false);
const loader = ref(false);
const reallocate = ref({});

const form = useForm({
  buyer_id: props.allocation.buyer_id,
  grower_id: props.allocation.grower_id,
  unique_key: props.allocation.unique_key,
  no_of_bins: props.allocation.item?.no_of_bins,
  weight: (props.allocation.item?.weight || 0) / 1000,
  bin_size: props.allocation.item?.bin_size,
  paddock: props.allocation.paddock,
  comment: props.allocation.comment,
  grower_group: getCategoryIdsByType(props.allocation.categories, 'grower-group'),
  seed_type: getCategoryIdsByType(props.allocation.categories, 'seed-type'),
  seed_variety: getCategoryIdsByType(props.allocation.categories, 'seed-variety'),
  seed_generation: getCategoryIdsByType(props.allocation.categories, 'seed-generation'),
  seed_class: getCategoryIdsByType(props.allocation.categories, 'seed-class'),
  select_receival: {},
});

watch(
  () => props.allocation,
  (allocation) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = allocation.buyer_id;
    form.grower_id = allocation.grower_id;
    form.unique_key = allocation.unique_key;
    form.no_of_bins = allocation.item?.no_of_bins;
    form.weight = (allocation.item?.weight || 0) / 1000;
    form.bin_size = allocation.item?.bin_size;
    form.paddock = allocation.paddock;
    form.comment = allocation.comment;
    form.grower_group = getCategoryIdsByType(allocation.categories, 'grower-group');
    form.seed_type = getCategoryIdsByType(allocation.categories, 'seed-type');
    form.seed_variety = getCategoryIdsByType(allocation.categories, 'seed-variety');
    form.seed_generation = getCategoryIdsByType(allocation.categories, 'seed-generation');
    form.seed_class = getCategoryIdsByType(allocation.categories, 'seed-class');
  },
);

watch(
  () => props.selectedReceival,
  (receival) => {
    if (Object.values(receival).length > 0) {
      onSelectReceival(receival);
    }
  },
);

const onSelectReceival = (receival) => {
  form.select_receival = receival;
  form.unique_key = receival.unique_key;
  form.no_of_bins = null;
  form.weight = null;
  form.bin_size = receival.bin_size;
  form.paddock = receival.paddock;
  form.grower_group = getCategoryIdsByType(receival.receival_categories, 'grower-group');
  form.seed_type = getCategoryIdsByType(receival.unload_categories, 'seed-type');
  form.seed_variety = getCategoryIdsByType(receival.receival_categories, 'seed-variety');
  form.seed_generation = getCategoryIdsByType(receival.receival_categories, 'seed-generation');
  form.seed_class = getCategoryIdsByType(receival.receival_categories, 'seed-class');
};

const onChangeGrower = () => {
  form.select_receival = {};
  form.unique_key = null;
  form.no_of_bins = null;
  form.weight = null;
};

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
});

const setIsEdit = () => {
  isEdit.value = true;
  loader.value = true;

  if (props.allocation.unique_key) {
    axios
      .get(route('growers.receivals', props.allocation.grower_id))
      .then((response) => {
        form.select_receival =
          response.data.find((receival) => receival.unique_key === props.allocation.unique_key) ||
          {};
      })
      .catch(() => {})
      .finally(() => {
        loader.value = false;
      });
  }
};

const updateRecord = () => {
  form.patch(route('allocations.update', props.allocation.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
      toast.success('The allocation has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('allocations.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The allocation has been created successfully!');
    },
  });
};

const deleteAllocation = () => {
  form.delete(route('allocations.destroy', props.allocation.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('delete');
      toast.success('The allocation has been deleted successfully!');
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
        <th>Buyer Name</th>
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

  <h4 v-if="isNew">Allocations Details</h4>
  <div class="user-boxes position-relative" :class="{ 'pe-5': !isForm }">
    <table v-if="isForm" class="table input-table">
      <tr>
        <th class="d-none d-sm-table-cell">Grower Name</th>
        <td>
          <div class="p-0" :class="{ 'input-group': form.grower_id }">
            <Multiselect
              v-model="form.grower_id"
              @change="onChangeGrower"
              mode="single"
              placeholder="Choose a grower"
              :searchable="true"
              :options="$page.props.growers"
              :class="{ 'is-invalid': form.errors.grower_id || form.errors.unique_key }"
            />
            <button
              v-if="form.grower_id"
              class="btn btn-red input-group-text px-1 px-sm-2"
              data-bs-toggle="modal"
              data-bs-target="#receival-modals"
              v-text="'Select Receival'"
              @click="emit('grower', form.grower_id)"
            />
          </div>
          <div
            v-if="form.errors.grower_id"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.grower_id"
          />
          <div
            v-if="form.errors.unique_key"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.unique_key"
          />
        </td>
      </tr>
    </table>

    <SelectedReceivalView v-if="isForm" :loader="loader" :receival="form.select_receival" />

    <template v-if="isForm">
      <div class="row">
        <div class="col-6 col-sm-3 mb-3">
          <label class="form-label">Allocated bins</label>
          <TextInput v-model="form.no_of_bins" :error="form.errors.no_of_bins" type="text" />
        </div>
        <div class="col-6 col-sm-3 mb-3">
          <label class="form-label">Allocated weight</label>
          <TextInput v-model="form.weight" :error="form.errors.weight" type="text">
            <template #addon>
              <div class="input-group-text d-none d-md-inline-block d-lg-none d-xl-inline-block">
                tonnes
              </div>
            </template>
          </TextInput>
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
          :data-bs-target="`#update-allocation-${uniqueKey}`"
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
          :data-bs-target="`#store-allocation-${uniqueKey}`"
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
          :data-bs-target="`#delete-allocation-${uniqueKey}`"
          class="btn btn-red p-1 z-1"
        >
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
          @click="() => reallocate = allocation"
        >Relocate</button>
      </div>

      <SingleDetailsView :allocation="allocation" />
    </template>
  </div>

  <ConfirmedModal
    :id="`delete-allocation-${uniqueKey}`"
    cancel="No, Keep it"
    ok="Yes, Delete!"
    @ok="deleteAllocation"
  />

  <ConfirmedModal
    :id="`store-allocation-${uniqueKey}`"
    title="You want to store this record?"
    @ok="storeRecord"
  />

  <ConfirmedModal
    :id="`update-allocation-${uniqueKey}`"
    title="You want to update this record?"
    ok="Yes, Update!"
    @ok="updateRecord"
  />

  <ReallocationModal
    :id="`relocate-${uniqueKey}`"
    :allocation="reallocate"
    @close="() => reallocate = {}"
  />
</template>
