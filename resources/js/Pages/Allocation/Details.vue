<script setup>
import { computed, ref, watch } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import { getCategoryIdsByType, getSingleCategoryNameByType } from "@/helper.js";
import Multiselect from '@vueform/multiselect'
import TextInput from "@/Components/TextInput.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import { useToast } from "vue-toastification";

const toast = useToast();

DataTable.use(DataTablesCore);

const props = defineProps({
  uniqueKey: String,
  allocation: {
    type: Object,
    default: {}
  },
  isNew: {
    type: Boolean,
    default: false,
  },
  isNewItem: {
    type: Boolean,
    default: false,
  },
  growers: Object,
  buyers: Object,
});

const emit = defineEmits(['create', 'delete']);

const isEdit = ref(false);

const form = useForm({
  buyer_id: props.allocation.buyer_id,
  grower_id: props.allocation.grower_id,
  unique_key: props.allocation.unique_key,
  no_of_bins: props.allocation.no_of_bins,
  weight: props.allocation.weight,
  bin_size: props.allocation.bin_size,
  paddock: props.allocation.paddock,
  comment: props.allocation.comment,
  grower_group: getCategoryIdsByType(props.allocation.categories, 'grower-group'),
  seed_type: getCategoryIdsByType(props.allocation.categories, 'seed-type'),
  seed_variety: getCategoryIdsByType(props.allocation.categories, 'seed-variety'),
  seed_generation: getCategoryIdsByType(props.allocation.categories, 'seed-generation'),
  seed_class: getCategoryIdsByType(props.allocation.categories, 'seed-class'),
  select_receival: {},
});

watch(() => props.allocation,
  (allocation) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = allocation.buyer_id
    form.grower_id = allocation.grower_id
    form.unique_key = allocation.unique_key
    form.no_of_bins = allocation.no_of_bins
    form.weight = allocation.weight
    form.bin_size = allocation.bin_size
    form.paddock = allocation.paddock
    form.comment = allocation.comment
    form.grower_group = getCategoryIdsByType(allocation.categories, 'grower-group')
    form.seed_type = getCategoryIdsByType(allocation.categories, 'seed-type')
    form.seed_variety = getCategoryIdsByType(allocation.categories, 'seed-variety')
    form.seed_generation = getCategoryIdsByType(allocation.categories, 'seed-generation')
    form.seed_class = getCategoryIdsByType(allocation.categories, 'seed-class')

    if (allocation.unique_key) {
      selectReceivalOnEdit(allocation);
    }
  }
);

const selectReceivalOnEdit = (allocation) => {
  form.select_receival = props.growers
    ?.find(grower => grower.value === allocation.grower_id)
    ?.receivals
    ?.find(receival => receival.unique_key === allocation.unique_key) || {};
}

selectReceivalOnEdit(props.allocation);

const onSelectReceival = (receival) => {
  form.select_receival = receival
  form.unique_key = receival.unique_key
  form.no_of_bins = null
  form.weight = null
  form.bin_size = receival.bin_size
  form.paddock = receival.paddock
  form.grower_group = getCategoryIdsByType(receival.receival_categories, 'grower-group')
  form.seed_type = getCategoryIdsByType(receival.unload_categories, 'seed-type')
  form.seed_variety = getCategoryIdsByType(receival.receival_categories, 'seed-variety')
  form.seed_generation = getCategoryIdsByType(receival.receival_categories, 'seed-generation')
  form.seed_class = getCategoryIdsByType(receival.receival_categories, 'seed-class')
}

const onChangeGrower = () => {
  form.select_receival = {};
  form.unique_key = null;
  form.no_of_bins = null
  form.weight = null
}

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
})

const setIsEdit = () => {
  isEdit.value = true
}

const updateRecord = () => {
  form.patch(route('allocations.update', props.allocation.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
      toast.success('The allocation has been updated successfully!');
    },
  });
}

const storeRecord = () => {
  form.post(route('allocations.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The allocation has been created successfully!');
    },
  });
}

const deleteAllocation = () => {
  form.delete(route('allocations.destroy', props.allocation.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('delete');
      toast.success('The allocation has been deleted successfully!');
    },
  });
}

defineExpose({
  storeRecord
});
</script>

<template>
  <div class="row">
    <div class="col-md-12">
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
                :options="buyers"
                :class="{'is-invalid' : form.errors.buyer_id}"
              />
              <div v-if="form.errors.buyer_id" class="invalid-feedback p-0 m-0" v-text="form.errors.buyer_id"/>
            </td>
          </tr>
        </table>
      </div>

      <h4 v-if="isNew">Allocations Details</h4>
      <div class="user-boxes position-relative" :class="{'pe-5': !isForm}">
        <table v-if="isForm" class="table input-table">
          <tr>
            <th class="d-none d-sm-table-cell">Grower Name</th>
            <td>
              <div class="p-0" :class="{'input-group': form.grower_id}">
                <Multiselect
                  v-model="form.grower_id"
                  @change="onChangeGrower"
                  mode="single"
                  placeholder="Choose a grower"
                  :searchable="true"
                  :options="growers"
                  :class="{'is-invalid' : form.errors.grower_id || form.errors.unique_key}"
                />
                <button
                  v-if="form.grower_id"
                  class="btn btn-red input-group-text px-1 px-sm-2"
                  data-bs-toggle="modal"
                  :data-bs-target="`#receivals-${uniqueKey}`"
                >Select Receival
                </button>
              </div>
              <div v-if="form.errors.grower_id" class="invalid-feedback p-0 m-0" v-text="form.errors.grower_id"/>
              <div v-if="form.errors.unique_key" class="invalid-feedback p-0 m-0" v-text="form.errors.unique_key"/>
            </td>
          </tr>
        </table>

        <div v-if="isForm && Object.values(form.select_receival).length" class="table-responsive">
          <table class="table table-sm">
            <thead>
            <tr>
              <th>Seed Type</th>
              <th>Bin Size</th>
              <th>Bins Available</th>
              <th>Weight</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td>{{ getSingleCategoryNameByType(form.select_receival.unload_categories, 'seed-type') }}</td>
              <td>{{ (form.select_receival.bin_size / 1000) }} tonnes</td>
              <td class="text-center text-md-start">{{ form.select_receival.no_of_bins }}</td>
              <td>{{ form.select_receival.weight }} kg</td>
            </tr>
            </tbody>
          </table>
        </div>
        
        <template v-if="isForm">
          <div class="row">
            <div class="col-6 col-sm-3 mb-3">
              <label class="form-label">Allocated Bins</label>
              <TextInput v-model="form.no_of_bins" :error="form.errors.no_of_bins" type="text"/>
            </div>
            <div class="col-6 col-sm-3 mb-3">
              <label class="form-label">Allocated Kg</label>
              <TextInput v-model="form.weight" :error="form.errors.weight" type="text"/>
            </div>
            <div class="col-12 col-sm-6 mb-3">
              <label class="form-label">Comment</label>
              <TextInput v-model="form.comment" :error="form.errors.comment" type="text"/>
            </div>
          </div>
          <div v-if="isEdit || isNewItem" class="w-100 text-end">
            <button v-if="isEdit" @click="updateRecord" class="btn btn-red">
              <template v-if="form.processing"><i class="bi bi-arrow-repeat d-inline-block spin"></i></template>
              <template v-else>Update</template>
            </button>
            <button v-if="isNewItem" @click="storeRecord" class="btn btn-red">
              <template v-if="form.processing"><i class="bi bi-arrow-repeat d-inline-block spin"></i></template>
              <template v-else>Create</template>
            </button>
          </div>
        </template>
        <template v-else>
          <div class="btn-group position-absolute" style="top: 0; right: 0;">
            <button @click="setIsEdit" class="btn btn-red p-1"><i class="bi bi-pen"></i></button>
            <button @click="deleteAllocation" class="btn btn-red p-1">
              <template v-if="form.processing">
                <i class="bi bi-arrow-repeat d-inline-block spin"></i>
              </template>
              <template v-else><i class="bi bi-trash"></i></template>
            </button>
          </div>
          <div class="row">
            <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-2">
              Grower:
              <Link :href="route('users.index', {userId: allocation.grower_id})">
                {{ allocation.grower?.name }} {{ allocation.grower?.grower_name ? ' (' + allocation.grower?.grower_name + ')' : '' }}
              </Link>
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-2">
              Seed Type: {{ getSingleCategoryNameByType(allocation.categories, 'seed-type') || '-' }}
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-2">
              Bin Size: {{ (allocation.bin_size / 1000) }} tonnes
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-2">
              Allocated Bins: {{ allocation.no_of_bins }}
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-2">
              Allocated: {{ allocation.weight.toFixed(2) }} kg
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-2">
              Rec. Group: {{ getSingleCategoryNameByType(allocation.categories, 'grower-group') || '-' }}
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-2">
              Seed Type: {{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-' }}
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-2">
              Seed Type: {{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-' }}
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-2">
              Seed Type: {{ getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-' }}
            </div>
            <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-2">Paddock: {{ allocation.paddock }}</div>
          </div>
        </template>
      </div>
    </div>
  </div>

  <div class="modal fade" :id="`receivals-${uniqueKey}`" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Select Receival</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <template v-for="grower in growers" :key="grower.value">
            <div v-if="form.grower_id === grower.value && isForm" class="table-responsive">
              <table class="table mb-0">
                <thead>
                <tr>
                  <th>Seed Type</th>
                  <th>Seed Variety</th>
                  <th>Seed Class</th>
                  <th>Seed Generation</th>
                  <th>Grower Group</th>
                  <th>Paddock</th>
                  <th>Bin Size</th>
                  <th>No Of Bins</th>
                  <th>Weight</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="receival in grower?.receivals" :key="receival.id">
                  <tr
                    v-if="receival.no_of_bins > 0 || receival.weight > 0"
                    @click="() => onSelectReceival(receival)"
                    style="cursor: pointer"
                    data-bs-dismiss="modal"
                  >
                    <td>{{ getSingleCategoryNameByType(receival.unload_categories, 'seed-type') }}</td>
                    <td>{{ getSingleCategoryNameByType(receival.receival_categories, 'seed-variety') }}</td>
                    <td>{{ getSingleCategoryNameByType(receival.receival_categories, 'seed-class') }}</td>
                    <td>{{ getSingleCategoryNameByType(receival.receival_categories, 'seed-generation') }}</td>
                    <td>{{ getSingleCategoryNameByType(receival.receival_categories, 'grower-group') }}</td>
                    <td>{{ receival.paddock }}</td>
                    <td>{{ (receival.bin_size / 1000) }} tonnes</td>
                    <td>{{ receival.no_of_bins }}</td>
                    <td>{{ receival.weight }} kg</td>
                  </tr>
                </template>
                </tbody>
              </table>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@import 'datatables.net-dt';
</style>
