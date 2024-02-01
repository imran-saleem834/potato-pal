<script setup>
import { computed, ref, watch } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import Multiselect from '@vueform/multiselect'
import TextInput from "@/Components/TextInput.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import { binSizes } from "@/tonnes.js";
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

defineExpose({
  storeRecord
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
            :options="buyers"
            :class="{'is-invalid' : form.errors.buyer_id}"
          />
          <div v-if="form.errors.buyer_id" class="invalid-feedback p-0 m-0" v-text="form.errors.buyer_id"/>
        </td>
      </tr>
    </table>
  </div>

  <h4 v-if="isNew">Dispatches Details</h4>
  <div class="user-boxes position-relative" :class="{'pe-5': !isForm}">
    <table v-if="isForm" class="table input-table">
      <tr>
        <th class="d-none d-sm-table-cell">Allocation Buyer Name</th>
        <td>
          <div class="p-0" :class="{'input-group': form.allocation_buyer_id}">
            <Multiselect
              v-model="form.allocation_buyer_id"
              @change="onChangeAllocationBuyer"
              mode="single"
              placeholder="Choose a buyer"
              :searchable="true"
              :options="buyers"
              :class="{'is-invalid' : form.errors.allocation_buyer_id || form.errors.allocation_id || form.errors.reallocation_id}"
            />
            <button
              v-if="form.allocation_buyer_id"
              class="btn btn-red input-group-text px-1 px-sm-2"
              data-bs-toggle="modal"
              :data-bs-target="`#allocations-${uniqueKey}`"
              v-text="'Select Allocation'"
            />
          </div>
          <div
            v-if="form.errors.allocation_buyer_id"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.allocation_buyer_id"
          />
          <div v-if="form.errors.allocation_id" class="invalid-feedback p-0 m-0" v-text="form.errors.allocation_id"/>
          <div v-if="form.errors.reallocation_id" class="invalid-feedback p-0 m-0" v-text="form.errors.reallocation_id"/>
        </td>
      </tr>
    </table>

    <div v-if="isForm && Object.values(form.selected_allocation).length" class="table-responsive">
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
          <td>{{ getSingleCategoryNameByType(form.selected_allocation.categories, 'seed-type') || '-' }}</td>
          <td>{{ (form.selected_allocation.bin_size / 1000) }} tonnes</td>
          <td>{{ form.selected_allocation.no_of_bins }}</td>
          <td>{{ form.selected_allocation.weight }} Kg</td>
        </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isForm && Object.values(form.selected_reallocation).length" class="table-responsive">
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
          <td>{{ getSingleCategoryNameByType(form.selected_reallocation.allocation.categories, 'seed-type') }}</td>
          <td>{{ (form.selected_reallocation.allocation.bin_size / 1000) }} tonnes</td>
          <td>{{ form.selected_reallocation.no_of_bins }}</td>
          <td>{{ form.selected_reallocation.weight }} Kg</td>
        </tr>
        </tbody>
      </table>
    </div>
    
    <template v-if="isForm">
      <div class="row">
        <div class="col-6 col-sm-3 mb-3">
          <label class="form-label">Dispatch Bins</label>
          <TextInput v-model="form.no_of_bins" :error="form.errors.no_of_bins" type="text"/>
        </div>
        <div class="col-6 col-sm-3 mb-3">
          <label class="form-label">Dispatch Kg</label>
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
        <button @click="deleteDispatch" class="btn btn-red p-1">
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else><i class="bi bi-trash"></i></template>
        </button>
      </div>
      <button 
        data-bs-toggle="modal"
        style="bottom: 0;right: 0;"
        :data-bs-target="`#return-${uniqueKey}`"
        @click="openModalReturnAllocation(dispatch)"
        class="position-absolute btn btn-black p-1"
        v-text="'Return'"
      />
      <div class="row allocation-items-box">
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Allocation Buyer Name: </span>
          <Link :href="route('users.index', {userId: dispatch.allocation_buyer_id})">
            {{ dispatch?.allocation_buyer?.name }}
          </Link>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Seed Type:</span>
          <template v-if="dispatch.allocation_id">
            {{ getSingleCategoryNameByType(dispatch.allocation.categories, 'seed-type') }}
          </template>
          <template v-else-if="dispatch.reallocation_id">
            {{ getSingleCategoryNameByType(dispatch.reallocation.allocation.categories, 'seed-type') }}
          </template>
          <template v-else>-</template>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Bin Size:</span>
          <template v-if="dispatch.allocation_id">
            {{ (dispatch.allocation.bin_size / 1000) }} tonnes
          </template>
          <template v-else-if="dispatch.reallocation_id">
            {{ (dispatch.reallocation.allocation.bin_size / 1000) }} tonnes
          </template>
          <template v-else>-</template>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Dispatch Bins:</span> {{ dispatch.no_of_bins }}
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Dispatch Weight:</span> {{ dispatch.weight.toFixed(2) }} Kg
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Receival Group:</span>
          <template v-if="dispatch.allocation_id">
            {{ getSingleCategoryNameByType(dispatch.allocation.categories, 'grower-group') }}
          </template>
          <template v-else-if="dispatch.reallocation_id">
            {{ getSingleCategoryNameByType(dispatch.reallocation.allocation.categories, 'grower-group') }}
          </template>
          <template v-else>-</template>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Seed Variety:</span>
          <template v-if="dispatch.allocation_id">
            {{ getSingleCategoryNameByType(dispatch.allocation.categories, 'seed-variety') }}
          </template>
          <template v-else-if="dispatch.reallocation_id">
            {{ getSingleCategoryNameByType(dispatch.reallocation.allocation.categories, 'seed-variety') }}
          </template>
          <template v-else>-</template>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Seed Generation:</span>
          <template v-if="dispatch.allocation_id">
            {{ getSingleCategoryNameByType(dispatch.allocation.categories, 'seed-generation') }}
          </template>
          <template v-else-if="dispatch.reallocation_id">
            {{ getSingleCategoryNameByType(dispatch.reallocation.allocation.categories, 'seed-generation') }}
          </template>
          <template v-else>-</template>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Seed Class:</span>
          <template v-if="dispatch.allocation_id">
            {{ getSingleCategoryNameByType(dispatch.allocation.categories, 'seed-class') }}
          </template>
          <template v-else-if="dispatch.reallocation_id">
            {{ getSingleCategoryNameByType(dispatch.reallocation.allocation.categories, 'seed-class') }}
          </template>
          <template v-else>-</template>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Paddock:</span>
          <template v-if="dispatch.allocation_id">{{ dispatch.allocation.paddock }}</template>
          <template v-else-if="dispatch.reallocation_id">{{ dispatch.reallocation.allocation.paddock }}</template>
          <template v-else>-</template>
        </div>
      </div>
    </template>
  </div>

  <div class="modal fade" :id="`allocations-${uniqueKey}`" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Select Re\Allocation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table mb-0">
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
                  data-bs-dismiss="modal"
                >
                  <td>Allocation</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-type') }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-class') }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'grower-group') }}</td>
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
                  data-bs-dismiss="modal"
                >
                  <td>Reallocation</td>
                  <td>{{ getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-type') }}</td>
                  <td>{{ getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-variety') }}</td>
                  <td>{{ getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-class') }}</td>
                  <td>{{ getSingleCategoryNameByType(reallocation.allocation.categories, 'seed-generation') }}</td>
                  <td>{{ getSingleCategoryNameByType(reallocation.allocation.categories, 'grower-group') }}</td>
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
  </div>

  <div
    
    class="modal fade"
    :id="`return-${uniqueKey}`"
    tabindex="-1"
    role="dialog"
  >
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Return Re\Allocation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" v-if="Number.isInteger(parseInt(uniqueKey)) && returnForm.dispatch?.allocation">
          <div class="table-responsive">
            <table class="table">
              <thead>
              <tr>
                <th>From</th>
                <th>Seed Type</th>
                <th>Seed Variety</th>
                <th>Seed Class</th>
                <th>Seed Gen.</th>
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
                <td>{{ getSingleCategoryNameByType(returnForm.dispatch.allocation.categories, 'grower-group') }}</td>
                <td>{{ returnForm.dispatch.allocation.paddock }}</td>
                <td>{{ (returnForm.dispatch.allocation.bin_size / 1000) }} tonnes</td>
              </tr>
              </tbody>
            </table>
          </div>
          <div class="row">
            <div class="col-12 col-xl-4 mb-2">
              <div class="user-boxes p-0 m-0 shadow-none">
                <label class="form-label">Return Bin Size</label>
                <UlLiButton
                  :is-form="true"
                  :value="returnForm.bin_size"
                  :error="returnForm.errors.bin_size"
                  :items="binSizes"
                  @click="(key) => returnForm.bin_size = key"
                />
              </div>
            </div>
            <div class="col-12 col-xl-2 mb-2">
              <label class="form-label">Return no of bins</label>
              <TextInput v-model="returnForm.no_of_bins" :error="returnForm.errors.no_of_bins" type="text"/>
            </div>
            <div class="col-12 col-xl-3 mb-2">
              <label class="form-label">Return weight kg</label>
              <TextInput v-model="returnForm.weight" :error="returnForm.errors.weight" type="text"/>
            </div>
            <div class="col-12 col-xl-3 mb-2">
              <label class="form-label">Comment</label>
              <TextInput v-model="returnForm.comment" :error="returnForm.errors.comment" type="text"/>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-red" @click="storeReturnRecord">Save Return</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@import 'datatables.net-dt';
</style>
