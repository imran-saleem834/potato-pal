<script setup>
import { useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import Multiselect from '@vueform/multiselect'
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import TextInput from "@/Components/TextInput.vue";
import { getCategoriesByType, getCategoryIdsByType, getCategoriesDropDownByType } from "@/helper.js";

DataTable.use(DataTablesCore);

const props = defineProps({
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
  const allocationExists = form.selected_allocations.find(alloc => alloc.id === allocation.id);
  if (allocationExists !== undefined) {
    form.selected_allocations = form.selected_allocations.filter(alloc => alloc.id !== allocation.id);
  } else {
    form.selected_allocations = [...form.selected_allocations, allocation];
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
    },
  });
}

const storeRecord = () => {
  form.post(route('cuttings.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
    },
  });
}

const deleteAllocation = () => {
  const form = useForm({});
  form.delete(route('cuttings.destroy', props.cutting.id), {
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
      <h4 v-if="isNew">Cutting Details</h4>
      <div class="user-boxes allocation-boxes">
        <template v-if="isForm">
          <div class="row">
            <div class="col-sm-10">
              <h6>Buyer Name</h6>
              <Multiselect
                v-if="isNew"
                v-model="form.buyer_id"
                mode="single"
                placeholder="Choose a buyer"
                :searchable="true"
                :options="buyers"
                @change="onChangeBuyers"
              />
              <TextInput v-else v-model="buyers.find(buyer => buyer.value === form.buyer_id).label" disabled />
              <div v-if="form.errors.buyer_id" class="has-error">
                <span class="help-block text-left">{{ form.errors.buyer_id }}</span>
              </div>
              <div v-if="form.errors.selected_allocations" class="has-error">
                <span class="help-block text-left">{{ form.errors.selected_allocations }}</span>
              </div>
            </div>
            <div class="col-sm-2">
              <h6>&nbsp;</h6>
              <button
                class="btn-red btn-select-receival"
                data-toggle="modal"
                data-target="#allocations"
              >Select Allocations
              </button>
            </div>
          </div>
          <div v-for="(selectedAllocation, index) in form.selected_allocations" class="row">
            <div class="col-sm-3">
              <h5>Seed Type: {{ getCategoriesByType(selectedAllocation.categories, 'seed-type')[0]?.category?.name }}</h5>
              <h5>Seed Variety: {{ getCategoriesByType(selectedAllocation.categories, 'seed-variety')[0]?.category?.name }}</h5>
              <h5>Paddock: {{ selectedAllocation.paddock }}</h5>
            </div>
            <div class="col-sm-3">
              <h5>Bin Size: {{ selectedAllocation.bin_size }} Tonnes</h5>
              <h5>Available No of Bins: {{ selectedAllocation.no_of_bins }}</h5>
              <h5>Available Weight: {{ selectedAllocation.weight }} Tonnes</h5>
            </div>
            <div class="col-sm-3">
              <h6>Cutting No of Bins</h6>
              <TextInput
                v-model="form.selected_allocations[index].no_of_bins_after_cutting"
                :error="form.errors[`selected_allocations.${index}.no_of_bins_after_cutting`]"
                type="text"
              />
            </div>
            <div class="col-sm-3">
              <h6>Cutting Tonnes</h6>
              <TextInput
                v-model="form.selected_allocations[index].weight_after_cutting"
                :error="form.errors[`selected_allocations.${index}.weight_after_cutting`]"
                type="text"
              />
            </div>
          </div>
          <div class="row">
            <div class="col-sm-3">
              <h6>Date of Cutting</h6>
              <TextInput v-model="form.cut_date" :error="form.errors.cut_date" type="date"/>
            </div>
            <div class="col-sm-3">
              <h6>Cut By</h6>
              <TextInput v-model="form.cut_by" :error="form.errors.cut_by" type="text"/>
            </div>
            <div class="col-sm-3">
              <h6>Fungicide</h6>
              <Multiselect
                v-model="form.fungicide"
                mode="tags"
                placeholder="Choose a fungicide"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'fungicide')"
              />
            </div>
            <div class="col-sm-3">
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
            <div class="col-sm-3">
              <h5>Date of Cut: {{ cutting.cut_date }}</h5>
            </div>
            <div class="col-sm-3">
              <h5>Cut By: {{ cutting.cut_by }}</h5>
            </div>
            <div class="col-sm-4">
              <h5>Comment: {{ cutting.comment }}</h5>
            </div>
            <div class="col-sm-2 text-right">
              <a role="button" @click="setIsEdit" class="btn btn-red">Edit</a>
              <a role="button" @click="deleteAllocation" class="btn btn-red">Delete</a>
            </div>
          </div>
          <template v-for="cuttingAllocation in cutting.cutting_allocations" :key="cuttingAllocation.id">
            <div class="row">
              <div class="col-sm-2">
                <h5>Seed Type: {{ getCategoriesByType(cuttingAllocation.allocation.categories, 'seed-type')[0]?.category?.name }}</h5>
              </div>
              <div class="col-sm-2">
                <h5>Size of Bin: {{ cuttingAllocation.allocation.bin_size }} Tonnes</h5>
              </div>
              <div class="col-sm-3">
                <h5>No of Bins After Cut: {{ cuttingAllocation.no_of_bins_after_cutting }}</h5>
              </div>
              <div class="col-sm-3">
                <h5>Weight After Cut: {{ cuttingAllocation.weight_after_cutting }} Tonnes</h5>
              </div>
              <div class="col-sm-2">
                <h5>Grower Group: {{ getCategoriesByType(cuttingAllocation.allocation.categories, 'grower')[0]?.category?.name }}</h5>
              </div>
            </div>
            <div class="row" style="border-bottom: 1px solid #dddcdc">
              <div class="col-sm-2">
                <h5>Seed Variety: {{ getCategoriesByType(cuttingAllocation.allocation.categories, 'seed-variety')[0]?.category?.name }}</h5>
              </div>
              <div class="col-sm-2">
                <h5>Seed Generation: {{ getCategoriesByType(cuttingAllocation.allocation.categories, 'seed-generation')[0]?.category?.name }}</h5>
              </div>
              <div class="col-sm-2">
                <h5>Seed Class: {{ getCategoriesByType(cuttingAllocation.allocation.categories, 'seed-class')[0]?.category?.name }}</h5>
              </div>
              <div class="col-sm-2">
                <h5>Paddock: {{ cuttingAllocation.allocation.paddock }}</h5>
              </div>
            </div>
          </template>
        </template>
      </div>
    </div>
  </div>

  <div class="modal fade" id="allocations" tabindex="-1" role="dialog" aria-labelledby="myModalLabel4">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%;">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 v-if="form.selected_allocations.length <= 0" class="modal-title" id="myModalLabel3">Select Allocations</h4>
          <h4 v-if="form.selected_allocations.length > 0" class="modal-title" id="myModalLabel3">
            Selected {{ form.selected_allocations.length }} Allocations
          </h4>
        </div>
        <div class="modal-body">
          <div style="margin: 20px 0;">
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
                <th>Select</th>
              </tr>
              </thead>
              <tbody>
              <template v-for="allocation in allocations" :key="allocation.id">
                <tr v-if="form.buyer_id === allocation.buyer_id && isForm">
                  <td>{{ getCategoriesByType(allocation.categories, 'seed-type')[0]?.category?.name }}</td>
                  <td>{{ getCategoriesByType(allocation.categories, 'seed-variety')[0]?.category?.name }}</td>
                  <td>{{ getCategoriesByType(allocation.categories, 'seed-class')[0]?.category?.name }}</td>
                  <td>{{ getCategoriesByType(allocation.categories, 'seed-generation')[0]?.category?.name }}</td>
                  <td>{{ getCategoriesByType(allocation.categories, 'grower')[0]?.category?.name }}</td>
                  <td>{{ allocation.paddock }}</td>
                  <td>{{ allocation.bin_size }} Tonnes</td>
                  <td>{{ allocation.no_of_bins }}</td>
                  <td>{{ allocation.weight.toFixed(2) }} Tonnes</td>
                  <td>
                    <input
                      type="checkbox"
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
</template>

<style>
@import 'datatables.net-dt';
</style>
