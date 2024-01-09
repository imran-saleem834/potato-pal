<script setup>
import { computed, ref, watch } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import Multiselect from '@vueform/multiselect'
import TextInput from "@/Components/TextInput.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import { binSizes, getBinSizesValue } from "@/tonnes.js";
import { getSingleCategoryNameByType } from "@/helper.js";
import UlLiButton from "@/Components/UlLiButton.vue";

DataTable.use(DataTablesCore);

const props = defineProps({
  uniqueKey: String,
  dispatch: {
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
  reallocations: Object,
  growers: Object,
  buyers: Object,
});

const emit = defineEmits(['create', 'delete']);

const isEdit = ref(false);

const form = useForm({
  buyer_id: props.dispatch.buyer_id,
  allocation_buyer_id: props.dispatch.allocation_buyer_id,
  allocation_id: props.dispatch.allocation_id,
  reallocation_id: props.dispatch.reallocation_id,
  no_of_bins: props.dispatch.no_of_bins,
  weight: props.dispatch.weight,
  comment: props.dispatch.comment,
  selected_allocation: {},
  selected_reallocation: {},
});

const returnForm = useForm({
  dispatch: null,
  bin_size: '',
  no_of_bins: '',
  weight: '',
  comment: '',
});

watch(() => props.dispatch,
  (dispatch) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = dispatch.buyer_id
    form.allocation_buyer_id = dispatch.allocation_buyer_id
    form.allocation_id = dispatch.allocation_id
    form.reallocation_id = dispatch.reallocation_id
    form.no_of_bins = dispatch.no_of_bins
    form.weight = dispatch.weight
    form.comment = dispatch.comment

    if (dispatch.allocation_id) {
      selectAllocationOnEdit(dispatch);
    }

    if (dispatch.reallocation_id) {
      selectReallocationOnEdit(dispatch);
    }
  }
);

const selectAllocationOnEdit = (dispatch) => {
  form.selected_allocation = props.allocations
    ?.find(allocation => allocation.id === dispatch.allocation_id) || {};
}

const selectReallocationOnEdit = (dispatch) => {
  form.selected_reallocation = props.reallocations
    ?.find(reallocation => reallocation.id === dispatch.reallocation_id) || {};
}

selectAllocationOnEdit(props.dispatch);
selectReallocationOnEdit(props.dispatch);

const onSelectAllocation = (allocation) => {
  onChangeAllocationBuyer();
  form.allocation_id = allocation.id
  form.selected_allocation = allocation
}
const onSelectReallocation = (reallocation) => {
  onChangeAllocationBuyer();
  form.reallocation_id = reallocation.id
  form.selected_reallocation = reallocation
}

const onChangeAllocationBuyer = () => {
  form.reallocation_id = null;
  form.allocation_id = null
  form.selected_allocation = {};
  form.selected_reallocation = {};
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
  form.patch(route('dispatches.update', props.dispatch.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
    },
  });
}

const storeRecord = () => {
  form.post(route('dispatches.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
    },
  });
}

const deleteDispatch = () => {
  const form = useForm({});
  form.delete(route('dispatches.destroy', props.dispatch.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('delete');
    },
  });
}

const openModalReturnAllocation = (dispatch) => {
  returnForm.dispatch = dispatch;
  returnForm.dispatch.allocation = dispatch.allocation_id ? dispatch.allocation : dispatch.reallocation.allocation
}

const storeReturnRecord = () => {
  returnForm.post(route('dispatches.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
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
        <h6>Dispatch Buyer Name</h6>
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

      <h4 v-if="isNew">Allocations Details</h4>
      <div class="user-boxes allocation-boxes">
        <template v-if="isForm">
          <div class="row">
            <div class="col-sm-10">
              <h6>Allocation Buyer Name</h6>
              <Multiselect
                v-model="form.allocation_buyer_id"
                mode="single"
                placeholder="Choose a buyer"
                :searchable="true"
                :options="buyers"
                @change="onChangeAllocationBuyer"
              />
              <div v-if="form.errors.allocation_buyer_id" class="has-error">
                <span class="help-block text-left">{{ form.errors.allocation_buyer_id }}</span>
              </div>
              <div v-if="form.errors.allocation_id" class="has-error">
                <span class="help-block text-left">{{ form.errors.allocation_id }}</span>
              </div>
              <div v-if="form.errors.reallocation_id" class="has-error">
                <span class="help-block text-left">{{ form.errors.reallocation_id }}</span>
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

          <div v-if="Object.values(form.selected_reallocation).length" class="user-table d-none"
               style="margin: 10px 0;">
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
                <td>{{
                    getSingleCategoryNameByType(form.selected_reallocation.allocation.categories, 'seed-type')
                  }}
                </td>
                <td>{{ form.selected_reallocation.allocation.bin_size }} Tonnes</td>
                <td>{{ form.selected_reallocation.no_of_bins }}</td>
                <td>{{ form.selected_reallocation.weight }} Tonnes</td>
              </tr>
              </tbody>
            </table>
          </div>

          <div class="row">
            <div class="col-sm-3">
              <h6>Dispatch No of Bins</h6>
              <TextInput v-model="form.no_of_bins" :error="form.errors.no_of_bins" type="text"/>
            </div>
            <div class="col-sm-3">
              <h6>Dispatch Tonnes</h6>
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
                <Link :href="route('users.index', {userId: dispatch.allocation_buyer_id})">
                  {{ dispatch?.allocation_buyer?.name }}
                </Link>
              </h5>
            </div>
            <div class="col-sm-2">
              <h5 v-if="dispatch.allocation_id">
                Seed Type: {{ getSingleCategoryNameByType(dispatch.allocation.categories, 'seed-type') }}
              </h5>
              <h5 v-if="dispatch.reallocation_id">
                Seed Type: {{ getSingleCategoryNameByType(dispatch.reallocation.allocation.categories, 'seed-type') }}
              </h5>
            </div>
            <div class="col-sm-2">
              <h5 v-if="dispatch.allocation_id">Size of Bin: {{ dispatch.allocation.bin_size }} Tonnes</h5>
              <h5 v-if="dispatch.reallocation_id">Size of Bin: {{ dispatch.reallocation.allocation.bin_size }}
                Tonnes</h5>
            </div>
            <div class="col-sm-2">
              <h5>Dispatch No of Bins: {{ dispatch.no_of_bins }}</h5>
            </div>
            <div class="col-sm-2">
              <h5>Dispatch: {{ dispatch.weight.toFixed(2) }} Tonnes</h5>
            </div>
            <div class="col-sm-2 text-right">
              <a role="button" @click="setIsEdit" class="btn btn-red">Edit</a>
              <a role="button" @click="deleteDispatch" class="btn btn-red">Delete</a>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-2">
              <h5 v-if="dispatch.allocation_id">
                Receival Group: {{ getSingleCategoryNameByType(dispatch.allocation.categories, 'grower') }}
              </h5>
              <h5 v-if="dispatch.reallocation_id">
                Receival Group: {{ getSingleCategoryNameByType(dispatch.reallocation.allocation.categories, 'grower') }}
              </h5>
            </div>
            <div class="col-sm-2">
              <h5 v-if="dispatch.allocation_id">
                Seed Variety: {{ getSingleCategoryNameByType(dispatch.allocation.categories, 'seed-variety') }}
              </h5>
              <h5 v-if="dispatch.reallocation_id">
                Seed Variety:
                {{ getSingleCategoryNameByType(dispatch.reallocation.allocation.categories, 'seed-variety') }}
              </h5>
            </div>
            <div class="col-sm-2">
              <h5 v-if="dispatch.allocation_id">
                Seed Generation: {{ getSingleCategoryNameByType(dispatch.allocation.categories, 'seed-generation') }}
              </h5>
              <h5 v-if="dispatch.reallocation_id">
                Seed Generation:
                {{ getSingleCategoryNameByType(dispatch.reallocation.allocation.categories, 'seed-generation') }}
              </h5>
            </div>
            <div class="col-sm-2">
              <h5 v-if="dispatch.allocation_id">
                Seed Class: {{ getSingleCategoryNameByType(dispatch.allocation.categories, 'seed-class') }}
              </h5>
              <h5 v-if="dispatch.reallocation_id">
                Seed Class: {{ getSingleCategoryNameByType(dispatch.reallocation.allocation.categories, 'seed-class') }}
              </h5>
            </div>
            <div class="col-sm-2">
              <h5 v-if="dispatch.allocation_id">Paddock: {{ dispatch.allocation.paddock }}</h5>
              <h5 v-if="dispatch.reallocation_id">Paddock: {{ dispatch.reallocation.allocation.paddock }}</h5>
            </div>
            <div class="col-sm-2 text-right">
              <button
                class="btn btn-red"
                data-toggle="modal"
                :data-target="`#return-${uniqueKey}`"
                @click="openModalReturnAllocation(dispatch)"
              >Return
              </button>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>

  <div class="modal fade" :id="`allocations-${uniqueKey}`" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%;">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">Select Re\Allocation</h4>
        </div>
        <div class="modal-body">
          <table class="table">
            <thead>
            <tr>
              <th>From</th>
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
                <td>Allocation</td>
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
            <template v-for="reallocation in reallocations" :key="reallocation.id">
              <tr
                v-if="form.allocation_buyer_id === reallocation.buyer_id && isForm && (reallocation.no_of_bins > 0 || reallocation.weight > 0)"
                @click="() => onSelectReallocation(reallocation)"
                style="cursor: pointer"
                data-dismiss="modal"
                aria-label="Close"
              >
                <td>Reallocation</td>
                <td>{{ getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-type') }}</td>
                <td>{{ getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-variety') }}</td>
                <td>{{ getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-class') }}</td>
                <td>{{ getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-generation') }}</td>
                <td>{{ getSingleCategoryNameByType(reallocation.allocation.categories, 'grower') }}</td>
                <td>{{ reallocation.allocation.paddock }}</td>
                <td>{{ reallocation.allocation.bin_size }} Tonnes</td>
                <td>{{ reallocation.no_of_bins }}</td>
                <td>{{ reallocation.weight }} Tonnes</td>
              </tr>
            </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div
    v-if="Number.isInteger(parseInt(uniqueKey)) && returnForm.dispatch?.allocation"
    class="modal fade"
    :id="`return-${uniqueKey}`"
    tabindex="-1"
    role="dialog"
  >
    <div class="modal-dialog modal-lg" role="document" style="width: 90%;">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">Return Re\Allocation</h4>
        </div>
        <div class="modal-body">
          <table class="table">
            <thead>
            <tr>
              <th>From</th>
              <th>Seed Type</th>
              <th>Seed Variety</th>
              <th>Seed Class</th>
              <th>Seed Generation</th>
              <th>Grower Group</th>
              <th>Paddock</th>
              <th>Bin Size</th>
            </tr>
            </thead>
            <tbody>
              <tr>
                <td>{{ returnForm.dispatch.allocation_id ? 'Allocation' : 'Reallocation' }}</td>
                <td>{{ getSingleCategoryNameByType(returnForm.dispatch.allocation.categories, 'seed-type') }}</td>
                <td>{{ getSingleCategoryNameByType(returnForm.dispatch.allocation.categories, 'seed-variety') }}</td>
                <td>{{ getSingleCategoryNameByType(returnForm.dispatch.allocation.categories, 'seed-class') }}</td>
                <td>{{ getSingleCategoryNameByType(returnForm.dispatch.allocation.categories, 'seed-generation') }}</td>
                <td>{{ getSingleCategoryNameByType(returnForm.dispatch.allocation.categories, 'grower') }}</td>
                <td>{{ returnForm.dispatch.allocation.paddock }}</td>
                <td>{{ returnForm.dispatch.allocation.bin_size }} Tonnes</td>
              </tr>
            </tbody>
          </table>

          <div class="row">
            <div class="col-sm-3">
              <div class="user-boxes" style="box-shadow: none; padding: 0">
                <h6>Return Bin Size</h6>
                <UlLiButton
                  :value="returnForm.bin_size"
                  :error="returnForm.errors.bin_size"
                  :items="binSizes"
                  @click="(key) => returnForm.bin_size = key"
                />
              </div>
            </div>
            <div class="col-sm-3">
              <h6>Return No of Bins</h6>
              <TextInput v-model="returnForm.no_of_bins" :error="returnForm.errors.no_of_bins" type="text"/>
            </div>
            <div class="col-sm-3">
              <h6>Return Weight Tonnes</h6>
              <TextInput v-model="returnForm.weight" :error="returnForm.errors.weight" type="text"/>
            </div>
            <div class="col-sm-3">
              <h6>Comment</h6>
              <TextInput v-model="returnForm.comment" :error="returnForm.errors.comment" type="text"/>
            </div>
          </div>

          <div class="text-right">
            <a role="button" @click="storeReturnRecord" class="btn btn-red">Create</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@import 'datatables.net-dt';
</style>
