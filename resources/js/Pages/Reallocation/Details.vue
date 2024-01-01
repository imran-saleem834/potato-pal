<script setup>
import { computed, ref, watch } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import Multiselect from '@vueform/multiselect'
import TextInput from "@/Components/TextInput.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import { getSingleCategoryNameByType } from "@/helper.js";

DataTable.use(DataTablesCore);

const props = defineProps({
  uniqueKey: String,
  reallocation: {
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
  allocations: Object,
  buyers: Object,
});

const emit = defineEmits(['create', 'delete']);

const isEdit = ref(false);

const form = useForm({
  buyer_id: props.reallocation.buyer_id,
  allocation_buyer_id: props.reallocation.allocation_buyer_id,
  allocation_id: props.reallocation.allocation_id,
  no_of_bins: props.reallocation.no_of_bins,
  weight: props.reallocation.weight,
  comment: props.reallocation.comment,
  selected_allocation: {},
});

watch(() => props.reallocation,
  (reallocation) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = reallocation.buyer_id
    form.allocation_buyer_id = reallocation.allocation_buyer_id
    form.allocation_id = reallocation.allocation_id
    form.no_of_bins = reallocation.no_of_bins
    form.weight = reallocation.weight
    form.comment = reallocation.comment

    selectReceivalOnEdit(reallocation);
  }
);

const selectReceivalOnEdit = (reallocation) => {
  form.selected_allocation = props.allocations
    ?.find(allocation => allocation.id === reallocation.allocation_id) || {};
}

selectReceivalOnEdit(props.reallocation);

const onSelectAllocation = (allocation) => {
  form.allocation_id = allocation.id
  form.selected_allocation = allocation
  form.no_of_bins = null
  form.weight = null
}

const onChangeGrower = () => {
  form.selected_allocation = {};
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
  form.patch(route('reallocations.update', props.reallocation.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
    },
  });
}

const storeRecord = () => {
  form.post(route('reallocations.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
    },
  });
}

const deleteReallocation = () => {
  const form = useForm({});
  form.delete(route('reallocations.destroy', props.reallocation.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('delete');
    },
  });
}
</script>

<template>
  <div class="row">
    <div v-if="isNew" class="col-md-12">
      <div class="flex-end create-update-btn">
        <a role="button" @click="storeRecord" class="btn btn-red">Create</a>
      </div>
    </div>
    <div class="col-md-12">
      <div v-if="isNew" class="user-boxes">
        <h6>Reallocation Buyer Name</h6>
        <Multiselect
          v-model="form.buyer_id"
          mode="single"
          placeholder="Choose a buyer"
          :searchable="true"
          :options="buyers"
        />
        <div v-if="form.errors.buyer_id" class="has-error">
          <span class="help-block text-left">{{ form.errors.buyer_id }}</span>
        </div>
      </div>

      <h4 v-if="isNew">Reallocations Details</h4>
      <div class="user-boxes allocation-boxes">
        <template v-if="isForm">
          <div class="row">
            <div class="col-sm-10">
              <h6>Allocation Buyer Name</h6>
              <Multiselect
                v-model="form.allocation_buyer_id"
                @change="onChangeGrower"
                mode="single"
                placeholder="Choose a buyer"
                :searchable="true"
                :options="buyers"
              />
              <div v-if="form.errors.allocation_buyer_id" class="has-error">
                <span class="help-block text-left">{{ form.errors.allocation_buyer_id }}</span>
              </div>
              <div v-if="form.errors.allocation_id" class="has-error">
                <span class="help-block text-left">{{ form.errors.allocation_id }}</span>
              </div>
            </div>
            <div class="col-sm-2">
              <h6>&nbsp;</h6>
              <button
                class="btn-red btn-select-receival"
                data-toggle="modal"
                :data-target="`#allocations-${uniqueKey}`"
              >Select Allocation
              </button>
            </div>
          </div>

          <div v-if="Object.values(form.selected_allocation).length" class="user-table d-none" style="margin: 10px 0;">
            <table class="table">
              <thead>
              <tr>
                <th>Seed Type</th>
                <th>Bin Size</th>
                <th>No of Bins Available</th>
                <th>Weight in Tonnes</th>
              </tr>
              </thead>
              <tbody>
              <tr>
                <td>{{ getSingleCategoryNameByType(form.selected_allocation.categories, 'seed-type') }}</td>
                <td>{{ form.selected_allocation.bin_size }} Tonnes</td>
                <td>{{ form.selected_allocation.no_of_bins }}</td>
                <td>{{ form.selected_allocation.weight }} Tonnes</td>
              </tr>
              </tbody>
            </table>
          </div>

          <div class="row">
            <div class="col-sm-3">
              <h6>Reallocated No of Bins</h6>
              <TextInput v-model="form.no_of_bins" :error="form.errors.no_of_bins" type="text"/>
            </div>
            <div class="col-sm-3">
              <h6>Reallocated Tonnes</h6>
              <TextInput v-model="form.weight" :error="form.errors.weight" type="text"/>
            </div>
            <div class="col-sm-6">
              <h6>Comment</h6>
              <TextInput v-model="form.comment" :error="form.errors.comment" type="text"/>
            </div>
          </div>
          <div v-if="isEdit || isNewItem" class="row">
            <div class="col-sm-12 text-right">
              <a v-if="isEdit" role="button" @click="updateRecord" class="btn btn-red">Update</a>
              <a v-if="isNewItem" role="button" @click="storeRecord" class="btn btn-red">Create</a>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="row">
            <div class="col-sm-2">
              <h5>
                <strong>Allocation Buyer Name: </strong>
                <Link :href="route('users.index', {userId: reallocation.allocation_buyer_id})">
                  {{ reallocation?.allocation_buyer?.name }}
                </Link>
              </h5>
            </div>
            <div class="col-sm-2">
              <h5>Seed Type: {{ getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-type') }}</h5>
            </div>
            <div class="col-sm-2">
              <h5>Size of Bin: {{ reallocation.allocation.bin_size }} Tonnes</h5>
            </div>
            <div class="col-sm-2">
              <h5>Reallocated No of Bins: {{ reallocation.no_of_bins }}</h5>
            </div>
            <div class="col-sm-2">
              <h5>Reallocated: {{ reallocation.weight }} Tonnes</h5>
            </div>
            <div class="col-sm-2 text-right">
              <a role="button" @click="setIsEdit" class="btn btn-red">Edit</a>
              <a role="button" @click="deleteReallocation" class="btn btn-red">Delete</a>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-2">
              <h5>Grower Group: {{ getSingleCategoryNameByType(reallocation.allocation.categories, 'grower') }}</h5>
            </div>
            <div class="col-sm-2">
              <h5>Seed Variety: {{ getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-variety') }}</h5>
            </div>
            <div class="col-sm-2">
              <h5>Seed Generation: {{ getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-generation') }}</h5>
            </div>
            <div class="col-sm-2">
              <h5>Seed Class: {{ getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-class') }}</h5>
            </div>
            <div class="col-sm-2">
              <h5>Paddock: {{ reallocation.allocation.paddock }}</h5>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>

  <div class="modal fade" :id="`allocations-${uniqueKey}`" tabindex="-1" role="dialog" aria-labelledby="myModalLabel4">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%;">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" id="myModalLabel3">Select Allocation</h4>
        </div>
        <div class="modal-body">
          <table class="table">
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
            <template v-for="allocation in allocations" :key="allocation.id">
              <tr
                v-if="form.allocation_buyer_id === allocation.buyer_id && isForm && (allocation.no_of_bins > 0 || allocation.weight > 0)"
                @click="() => onSelectAllocation(allocation)"
                style="cursor: pointer"
                data-dismiss="modal"
                aria-label="Close"
              >
                <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-type') }}</td>
                <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') }}</td>
                <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-class') }}</td>
                <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') }}</td>
                <td>{{ getSingleCategoryNameByType(allocation.categories, 'grower') }}</td>
                <td>{{ allocation.paddock }}</td>
                <td>{{ allocation.bin_size }} Tonnes</td>
                <td>{{ allocation.no_of_bins }}</td>
                <td>{{ allocation.weight }} Tonnes</td>
              </tr>
            </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@import 'datatables.net-dt';
</style>
