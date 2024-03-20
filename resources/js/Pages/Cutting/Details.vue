<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUpdated, ref, watch } from 'vue';
import Multiselect from '@vueform/multiselect';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import TextInput from '@/Components/TextInput.vue';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';
import {
  toTonnes,
  getBinSizesValue,
  getCategoryIdsByType,
  getCategoryByKeyword,
  getCategoriesDropDownByType,
  getSingleCategoryNameByType,
} from '@/helper.js';
import { useToast } from 'vue-toastification';
import * as bootstrap from 'bootstrap';

const toast = useToast();

DataTable.use(DataTablesCore);

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
  categories: Object,
  allocations: Object,
  buyers: Object,
});

const emit = defineEmits(['create', 'delete']);

const isEdit = ref(false);

const getDefaultCategoryId = (categories, type, keyword) => {
  let categoriesIds = getCategoryIdsByType(categories, type);
  if (categoriesIds.length <= 0) {
    const defaultCategory = getCategoryByKeyword(props.categories, type, keyword);
    if (defaultCategory) {
      categoriesIds = [defaultCategory.id];
    }
  }
  return categoriesIds;
};

const form = useForm({
  buyer_id: props.cutting.buyer_id,
  half_tonnes: props.cutting.half_tonnes,
  one_tonnes: props.cutting.one_tonnes,
  two_tonnes: props.cutting.two_tonnes,
  cut_date: props.cutting.cut_date,
  cool_store: getDefaultCategoryId(props.cutting.categories, 'cool-store', 'Cherry Hill'),
  fungicide: getDefaultCategoryId(props.cutting.categories, 'fungicide', 'Mancozeb/Lime'),
  comment: props.cutting.comment,
  selected_allocations: props.cutting.cutting_allocations || [],
});

watch(
  () => props.cutting,
  (cutting) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = cutting.buyer_id;
    form.half_tonnes = cutting.half_tonnes;
    form.one_tonnes = cutting.one_tonnes;
    form.two_tonnes = cutting.two_tonnes;
    form.cut_date = cutting.cut_date;
    form.cool_store = getDefaultCategoryId(cutting.categories, 'cool-store', 'Cherry Hill');
    form.fungicide = getDefaultCategoryId(cutting.categories, 'fungicide', 'Mancozeb/Lime');
    form.comment = cutting.comment;
    form.selected_allocations = cutting.cutting_allocations || [];
  },
);

const onSelectAllocation = (allocation) => {
  const allocationExists = form.selected_allocations.find(
    (alloc) => alloc.allocation_id === allocation.id,
  );
  if (allocationExists !== undefined) {
    form.selected_allocations = form.selected_allocations.filter(
      (alloc) => alloc.allocation_id !== allocation.id,
    );
  } else {
    form.selected_allocations = [
      ...form.selected_allocations,
      {
        allocation_id: allocation.id,
        no_of_bins: '',
        allocation,
      },
    ];
  }
};

const onChangeBuyers = () => {
  form.selected_allocations = [];
};

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
});

const setIsEdit = () => {
  isEdit.value = true;
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
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl),
  );
});

onUpdated(() => {
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl),
  );
});

defineExpose({
  storeRecord,
});
</script>

<template>
  <h4 v-if="isNew">Cutting Details</h4>
  <div class="user-boxes position-relative" :class="{ 'pe-sm-5': !isForm }">
    <table v-if="isForm" class="table input-table">
      <tr>
        <th class="d-none d-sm-table-cell">Buyer Name</th>
        <td>
          <div
            class="p-0"
            :class="{
              'input-group': form.buyer_id,
              'is-invalid': form.errors.buyer_id || form.errors.selected_allocations,
            }"
          >
            <Multiselect
              v-if="isNew"
              v-model="form.buyer_id"
              @change="onChangeBuyers"
              mode="single"
              placeholder="Choose a buyer"
              :searchable="true"
              :options="buyers"
            />
            <input
              v-else
              type="text"
              class="form-control"
              :disabled="true"
              v-model="buyers.find((buyer) => buyer.value === form.buyer_id).label"
            />
            <button
              v-if="form.buyer_id"
              class="btn btn-red input-group-text px-1 px-sm-2"
              data-bs-toggle="modal"
              :data-bs-target="`#allocations-${uniqueKey}`"
              v-text="'Select Allocations'"
            />
          </div>
          <div
            v-if="form.errors.buyer_id"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.buyer_id"
          />
          <div
            v-if="form.errors.selected_allocations"
            class="invalid-feedback p-0 m-0"
            v-text="form.errors.selected_allocations"
          />
        </td>
      </tr>
    </table>

    <template v-if="isForm">
      <div v-if="form.selected_allocations.length" class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>Seed type</th>
              <th class="d-none d-md-table-cell">Variety</th>
              <th class="d-none d-md-table-cell">Paddock</th>
              <th>Bin size</th>
              <th>Available/No of bins</th>
              <th>Bins to cut</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(sa, index) in form.selected_allocations" :key="index">
              <td class="text-danger">
                {{ getSingleCategoryNameByType(sa.allocation.categories, 'seed-type') || '-' }}
                <a
                  data-bs-toggle="tooltip"
                  data-bs-html="true"
                  class="d-md-none"
                  :data-bs-title="`
                  <div class='text-start'>
                    Variety: ${getSingleCategoryNameByType(sa.allocation.categories, 'seed-variety') || '-'}<br/>
                    Paddock: ${sa.allocation.paddock}
                  </div>
                `"
                >
                  <i class="bi bi-question-circle fs-6 text-black"></i>
                </a>
              </td>
              <td class="d-none d-md-table-cell text-danger">
                {{ getSingleCategoryNameByType(sa.allocation.categories, 'seed-variety') || '-' }}
              </td>
              <td class="d-none d-md-table-cell text-danger">{{ sa.allocation.paddock }}</td>
              <td class="text-danger">{{ getBinSizesValue(sa.allocation.bin_size) }}</td>
              <td class="text-danger">
                {{ `${sa.allocation.available_no_of_bins} / ${sa.allocation.no_of_bins}` }}
              </td>
              <td style="max-width: 150px">
                <TextInput
                  v-model="form.selected_allocations[index].no_of_bins"
                  :error="form.errors[`selected_allocations.${index}.no_of_bins`]"
                  type="text"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
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
              <div class="input-group-text">Two tonnes</div>
            </template>
            <template #addon>
              <div class="input-group-text">Bins</div>
            </template>
          </TextInput>
        </div>
        <div class="w-100 d-block"></div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Date of Cutting</label>
          <TextInput v-model="form.cut_date" :error="form.errors.cut_date" type="date" />
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Cut By</label>
          <Multiselect
            v-model="form.cool_store"
            mode="tags"
            placeholder="Choose a cut by"
            :searchable="true"
            :create-option="true"
            :options="getCategoriesDropDownByType(categories, 'cool-store')"
            :class="{ 'is-invalid': form.errors.cool_store }"
          />
          <div
            v-if="form.errors.cool_store"
            class="invalid-feedback"
            v-text="form.errors.cool_store"
          />
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
            :class="{ 'is-invalid': form.errors.fungicide }"
          />
          <div
            v-if="form.errors.fungicide"
            class="invalid-feedback"
            v-text="form.errors.fungicide"
          />
        </div>
        <div class="col-12 col-sm-6 col-md-3 col-lg-6 col-xl-3 mb-3">
          <label class="form-label">Comment</label>
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
        <button @click="setIsEdit" class="btn btn-red p-1"><i class="bi bi-pen"></i></button>
        <button
          data-bs-toggle="modal"
          :data-bs-target="`#delete-cutting-${uniqueKey}`"
          class="btn btn-red p-1"
        >
          <template v-if="form.processing">
            <i class="bi bi-arrow-repeat d-inline-block spin"></i>
          </template>
          <template v-else><i class="bi bi-trash"></i></template>
        </button>
      </div>
      <div class="row allocation-items-box">
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Half Tonne: </span>
          <span class="text-danger">{{ cutting.half_tonnes || '0' }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>One Tonne: </span>
          <span class="text-danger">{{ cutting.one_tonnes || '0' }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Two Tonne: </span>
          <span class="text-danger">{{ cutting.two_tonnes || '0' }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Date of cut: </span>
          <span class="text-danger">{{ cutting.cut_date }}</span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Cut By: </span>
          <span class="text-danger">
            {{ getSingleCategoryNameByType(cutting.categories, 'cool-store') || '-' }}
          </span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Fungicide: </span>
          <span class="text-danger">
            {{ getSingleCategoryNameByType(cutting.categories, 'fungicide') || '-' }}
          </span>
        </div>
        <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
          <span>Comments: </span>
          <span class="text-danger">{{ cutting.comment }}</span>
        </div>
      </div>
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>Seed type</th>
            <th class="d-none d-md-table-cell">Variety</th>
            <th class="d-none d-xl-table-cell">Grower Group</th>
            <th class="d-none d-xl-table-cell">Gen.</th>
            <th class="d-none d-xl-table-cell">Class</th>
            <th>Paddock</th>
            <th>Bin size</th>
            <th>Cut Bins</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cuttingAllocation in cutting.cutting_allocations" :key="cuttingAllocation.id">
            <td class="text-danger">
              {{
                getSingleCategoryNameByType(cuttingAllocation.allocation.categories, 'seed-type') ||
                '-'
              }}
              <a
                data-bs-toggle="tooltip"
                data-bs-html="true"
                class="d-xl-none"
                :data-bs-title="`
                  <div class='text-start'>
                    Variety: ${getSingleCategoryNameByType(cuttingAllocation.allocation.categories, 'seed-variety') || '-'}<br/>
                    Grower Group: ${getSingleCategoryNameByType(cuttingAllocation.allocation.categories, 'grower-group') || '-'}<br/>
                    Gen.: ${getSingleCategoryNameByType(cuttingAllocation.allocation.categories, 'seed-generation') || '-'}<br/>
                    Class: ${getSingleCategoryNameByType(cuttingAllocation.allocation.categories, 'seed-class') || '-'}
                  </div>
                `"
              >
                <i class="bi bi-question-circle fs-6 text-black"></i>
              </a>
            </td>
            <td class="d-none d-md-table-cell text-danger">
              {{
                getSingleCategoryNameByType(
                  cuttingAllocation.allocation.categories,
                  'seed-variety',
                ) || '-'
              }}
            </td>
            <td class="d-none d-xl-table-cell text-danger">
              {{
                getSingleCategoryNameByType(
                  cuttingAllocation.allocation.categories,
                  'grower-group',
                ) || '-'
              }}
            </td>
            <td class="d-none d-xl-table-cell text-danger">
              {{
                getSingleCategoryNameByType(
                  cuttingAllocation.allocation.categories,
                  'seed-generation',
                ) || '-'
              }}
            </td>
            <td class="d-none d-xl-table-cell text-danger">
              {{
                getSingleCategoryNameByType(
                  cuttingAllocation.allocation.categories,
                  'seed-class',
                ) || '-'
              }}
            </td>
            <td class="text-danger">{{ cuttingAllocation.allocation.paddock }}</td>
            <td class="text-danger">
              {{ getBinSizesValue(cuttingAllocation.allocation.bin_size) }}
            </td>
            <td class="text-danger">{{ cuttingAllocation.no_of_bins }}</td>
          </tr>
        </tbody>
      </table>
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
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>Grower Group</th>
                  <th>Grower</th>
                  <th>Paddock</th>
                  <th>Variety</th>
                  <th>Gen</th>
                  <th>Seed type</th>
                  <th>Class</th>
                  <th>Bin size</th>
                  <th>Weight</th>
                  <th>Available / No of bins</th>
                  <th>Select</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="allocation in allocations" :key="allocation.id">
                  <tr v-if="form.buyer_id === allocation.buyer_id && isForm">
                    <td>
                      {{
                        getSingleCategoryNameByType(allocation.categories, 'grower-group') || '-'
                      }}
                    </td>
                    <td>{{ allocation.grower?.grower_name || '-' }}</td>
                    <td>{{ allocation.paddock }}</td>
                    <td>
                      {{
                        getSingleCategoryNameByType(allocation.categories, 'seed-variety') || '-'
                      }}
                    </td>
                    <td>
                      {{
                        getSingleCategoryNameByType(allocation.categories, 'seed-generation') || '-'
                      }}
                    </td>
                    <td>
                      {{ getSingleCategoryNameByType(allocation.categories, 'seed-type') || '-' }}
                    </td>
                    <td>
                      {{ getSingleCategoryNameByType(allocation.categories, 'seed-class') || '-' }}
                    </td>
                    <td>{{ getBinSizesValue(allocation.bin_size) }}</td>
                    <td>{{ toTonnes(allocation.weight) }}</td>
                    <td>{{ `${allocation.available_no_of_bins} / ${allocation.no_of_bins}` }}</td>
                    <td>
                      <input
                        type="checkbox"
                        :checked="
                          form.selected_allocations.find(
                            (cutting_allocation) =>
                              cutting_allocation.allocation_id === allocation.id,
                          )
                        "
                        @click="onSelectAllocation(allocation)"
                      />
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
