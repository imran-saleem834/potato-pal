<script setup>
import { useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import Multiselect from '@vueform/multiselect'
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import { getBinSizesValue } from "@/tonnes.js";
import TextInput from "@/Components/TextInput.vue";
import ConfirmedModal from "@/Components/ConfirmedModal.vue";
import {
  getCategoryIdsByType,
  getCategoriesDropDownByType,
  getSingleCategoryNameByType
} from "@/helper.js";
import { useToast } from "vue-toastification";

const toast = useToast();

DataTable.use(DataTablesCore);

const props = defineProps({
  uniqueKey: String,
  cutting: {
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
  categories: Object,
  allocations: Object,
  buyers: Object,
});

const emit = defineEmits(['create', 'delete']);

const isEdit = ref(false);

const form = useForm({
  buyer_id: props.cutting.buyer_id,
  cut_date: props.cutting.cut_date,
  cut_by: props.cutting.cut_by,
  fungicide: getCategoryIdsByType(props.cutting.categories, 'fungicide'),
  comment: props.cutting.comment,
  selected_allocations: props.cutting.cutting_allocations || [],
});

watch(() => props.cutting,
  (cutting) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = cutting.buyer_id
    form.cut_date = cutting.cut_date
    form.cut_by = cutting.cut_by
    form.fungicide = getCategoryIdsByType(cutting.categories, 'fungicide')
    form.comment = cutting.comment
    form.selected_allocations = cutting.cutting_allocations || []
  }
);

const onSelectAllocation = (allocation) => {
  const allocationExists = form.selected_allocations.find(alloc => alloc.allocation_id === allocation.id);
  if (allocationExists !== undefined) {
    form.selected_allocations = form.selected_allocations.filter(alloc => alloc.allocation_id !== allocation.id);
  } else {
    form.selected_allocations = [...form.selected_allocations, {
      allocation_id: allocation.id,
      no_of_bins_before_cutting: '',
      weight_before_cutting: '',
      no_of_bins_after_cutting: '',
      weight_after_cutting: '',
      allocation
    }];
  }
}

const onChangeBuyers = () => {
  form.selected_allocations = [];
}

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
})

const setIsEdit = () => {
  isEdit.value = true
}

const updateRecord = () => {
  form.patch(route('cuttings.update', props.cutting.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
      toast.success('The cutting information has been updated successfully!');
    },
  });
}

const storeRecord = () => {
  form.post(route('cuttings.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The cutting information has been saved successfully!');
    },
  });
}

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
}

defineExpose({
  storeRecord
});
</script>

<template>
  <h4 v-if="isNew">Cutting Details</h4>
  <div class="user-boxes position-relative" :class="{'pe-5': !isForm}">
    <table v-if="isForm" class="table input-table">
      <tr>
        <th class="d-none d-sm-table-cell">Buyer Name</th>
        <td>
          <div class="p-0" :class="{'input-group': form.buyer_id}">
            <Multiselect
              v-if="isNew"
              v-model="form.buyer_id"
              @change="onChangeBuyers"
              mode="single"
              placeholder="Choose a buyer"
              :searchable="true"
              :options="buyers"
              :class="{'is-invalid' : form.errors.buyer_id || form.errors.selected_allocations}"
            />
            <input
              v-else
              type="text"
              class="form-control"
              :disabled="true"
              v-model="buyers.find(buyer => buyer.value === form.buyer_id).label"
            >
            <button
              v-if="form.buyer_id"
              class="btn btn-red input-group-text px-1 px-sm-2"
              data-bs-toggle="modal"
              :data-bs-target="`#allocations-${uniqueKey}`"
              v-text="'Select Allocations'"
            />
          </div>
          <div v-if="form.errors.buyer_id" class="invalid-feedback p-0 m-0" v-text="form.errors.buyer_id"/>
          <div
            v-if="form.errors.selected_allocations"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.selected_allocations"
          />
        </td>
      </tr>
    </table>

    <template v-if="isForm">
      <div v-for="(selectedAllocation, index) in form.selected_allocations" class="row allocation-items-box">
        <div class="col-sm-6 col-md-3 col-lg-6 col-xl-3 mt-md-4">
          <div class="col-12 mb-1 pb-1 mb-md-3 mb-lg-1 mb-xl-3">
            <span>Seed Type: </span>{{
              getSingleCategoryNameByType(selectedAllocation.allocation.categories, 'seed-type') || '-'
            }}
          </div>
          <div class="col-12 mb-1 pb-1 mb-md-3 mb-lg-1 mb-xl-3">
            <span>Seed Variety: </span>{{
              getSingleCategoryNameByType(selectedAllocation.allocation.categories, 'seed-variety') || '-'
            }}
          </div>
          <div class="col-12 mb-1 pb-1 mb-md-3 mb-lg-1 mb-xl-3">
            <span>Paddock: </span>{{ selectedAllocation.allocation.paddock }}
          </div>
        </div>
        <div class="col-sm-6 col-md-3 col-lg-6 col-xl-3 mt-md-4">
          <div class="col-12 mb-1 pb-1 mb-md-3 mb-lg-1 mb-xl-3">
            <span>Bin Size: </span>{{ getBinSizesValue(selectedAllocation.allocation.bin_size) }}
          </div>
          <div class="col-12 mb-1 pb-1 mb-md-3 mb-lg-1 mb-xl-3">
            <span>Available No of Bins: </span>{{ selectedAllocation.allocation.no_of_bins }}
          </div>
          <div class="col-12 mb-1 pb-1 mb-md-3 mb-lg-1 mb-xl-3">
            <span>Available Weight: </span>{{ selectedAllocation.allocation.weight }} kg
          </div>
        </div>
        <div class="col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <div class="row">
            <div class="col-6 col-sm-12">
              <label class="form-label">Bins to cut</label>
              <TextInput
                v-model="form.selected_allocations[index].no_of_bins_before_cutting"
                :error="form.errors[`selected_allocations.${index}.no_of_bins_before_cutting`]"
                type="text"
              />
            </div>
            <div class="col-6 col-sm-12 mt-0 mt-sm-3">
              <label class="form-label">Bins after cut</label>
              <TextInput
                v-model="form.selected_allocations[index].no_of_bins_after_cutting"
                :error="form.errors[`selected_allocations.${index}.no_of_bins_after_cutting`]"
                type="text"
              />
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <div class="row">
            <div class="col-6 col-sm-12">
              <label class="form-label">Kg to cut</label>
              <TextInput
                v-model="form.selected_allocations[index].weight_before_cutting"
                :error="form.errors[`selected_allocations.${index}.weight_before_cutting`]"
                type="text"
              />
            </div>
            <div class="col-6 col-sm-12 mt-0 mt-sm-3">
              <label class="form-label">Kg after cut</label>
              <TextInput
                v-model="form.selected_allocations[index].weight_after_cutting"
                :error="form.errors[`selected_allocations.${index}.weight_after_cutting`]"
                type="text"
              />
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Date of Cutting</label>
          <TextInput v-model="form.cut_date" :error="form.errors.cut_date" type="date"/>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Cut By</label>
          <TextInput v-model="form.cut_by" :error="form.errors.cut_by" type="text"/>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Fungicide</label>
          <Multiselect
            v-model="form.fungicide"
            mode="tags"
            placeholder="Choose a fungicide"
            :searchable="true"
            :create-option="true"
            :options="getCategoriesDropDownByType(categories, 'fungicide')"
            :class="{'is-invalid' : form.errors.fungicide}"
          />
          <div v-if="form.errors.fungicide" class="invalid-feedback" v-text="form.errors.fungicide"/>
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Comment</label>
          <TextInput v-model="form.comment" :error="form.errors.comment" type="text"/>
        </div>
      </div>
      <div v-if="isEdit || isNewItem" class="w-100 text-end">
        <button
          v-if="isEdit"
          data-bs-toggle="modal"
          :data-bs-target="`#update-cutting-${uniqueKey}`"
          class="btn btn-red"
        >
          <template v-if="form.processing"><i class="bi bi-arrow-repeat d-inline-block spin"></i></template>
          <template v-else>Update</template>
        </button>
        <button
          v-if="isNewItem"
          data-bs-toggle="modal"
          :data-bs-target="`#store-cutting-${uniqueKey}`"
          class="btn btn-red"
        >
          <template v-if="form.processing"><i class="bi bi-arrow-repeat d-inline-block spin"></i></template>
          <template v-else>Create</template>
        </button>
      </div>
    </template>
    <template v-else>
      <div class="btn-group position-absolute top-0 end-0">
        <button @click="setIsEdit" class="btn btn-red p-1"><i class="bi bi-pen"></i></button>
        <button data-bs-toggle="modal" :data-bs-target="`#delete-cutting-${uniqueKey}`" class="btn btn-red p-1">
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else><i class="bi bi-trash"></i></template>
        </button>
      </div>
      <div class="row allocation-items-box">
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Date of cut:</span> {{ cutting.cut_date }}
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Cut By:</span> {{ cutting.cut_by }}
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Fungicide:</span> {{ getSingleCategoryNameByType(cutting.categories, 'fungicide') || '-' }}
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Comment:</span> {{ cutting.comment }}
        </div>
        <template v-for="cuttingAllocation in cutting.cutting_allocations" :key="cuttingAllocation.id">
          <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
            <span>Seed Type:</span>
            {{ getSingleCategoryNameByType(cuttingAllocation.allocation.categories, 'seed-type') || '-' }}
          </div>
          <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
            <span>Bin Size:</span> {{ getBinSizesValue(cuttingAllocation.allocation.bin_size) }}
          </div>
          <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
            <span>Bins before cut:</span> {{ cuttingAllocation.no_of_bins_before_cutting }}
          </div>
          <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
            <span>Weight before cut:</span> {{ cuttingAllocation.weight_before_cutting }} kg
          </div>
          <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
            <span>Bins after cut:</span> {{ cuttingAllocation.no_of_bins_after_cutting }}
          </div>
          <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
            <span>Weight after cut:</span> {{ cuttingAllocation.weight_after_cutting }} kg
          </div>
          <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
            <span>Grower. Group:</span>
            {{ getSingleCategoryNameByType(cuttingAllocation.allocation.categories, 'grower-group') || '-' }}
          </div>
          <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
            <span>Seed Variety:</span>
            {{ getSingleCategoryNameByType(cuttingAllocation.allocation.categories, 'seed-variety') || '-' }}
          </div>
          <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
            <span>Seed Gen.:</span>
            {{ getSingleCategoryNameByType(cuttingAllocation.allocation.categories, 'seed-generation') || '-' }}
          </div>
          <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
            <span>Seed Class:</span>
            {{ getSingleCategoryNameByType(cuttingAllocation.allocation.categories, 'seed-class') || '-' }}
          </div>
          <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 pb-1">
            <span>Paddock:</span> {{ cuttingAllocation.allocation.paddock }}
          </div>
        </template>
      </div>
    </template>
  </div>

  <div class="modal fade" :id="`allocations-${uniqueKey}`" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">
            <template v-if="form.selected_allocations.length > 0">
              Selected {{ form.selected_allocations.length }} Allocations
            </template>
            <template v-else>Select Allocations</template>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
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
                <th>Select</th>
              </tr>
              </thead>
              <tbody>
              <template v-for="allocation in allocations" :key="allocation.id">
                <tr v-if="form.buyer_id === allocation.buyer_id && isForm">
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-type') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-' }}</td>
                  <td>{{ getSingleCategoryNameByType(allocation.categories, 'grower-group') || '-' }}</td>
                  <td>{{ allocation.paddock }}</td>
                  <td>{{ getBinSizesValue(allocation.bin_size) }}</td>
                  <td>{{ allocation.no_of_bins }}</td>
                  <td>{{ allocation.weight.toFixed(2) }} kg</td>
                  <td>
                    <input
                      type="checkbox"
                      :checked="form.selected_allocations.find(cutting_allocation => cutting_allocation.allocation_id === allocation.id)"
                      @click="onSelectAllocation(allocation)"
                    >
                  </td>
                </tr>
              </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <ConfirmedModal
    :id="`delete-cutting-${uniqueKey}`"
    cancel="No, Keep it"
    ok="Yes, Delete!"
    @ok="deleteCutting"
  />

  <ConfirmedModal
    :id="`store-cutting-${uniqueKey}`"
    title="You want to store this record?"
    @ok="storeRecord"
  />

  <ConfirmedModal
    :id="`update-cutting-${uniqueKey}`"
    title="You want to update this record?"
    ok="Yes, Update!"
    @ok="updateRecord"
  />
</template>

<style>
@import 'datatables.net-dt';
</style>
