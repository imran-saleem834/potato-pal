<script setup>
import { computed } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { useToast } from 'vue-toastification';
import { binSizes } from '@/const.js';
import TextInput from '@/Components/TextInput.vue';
import { getDropDownOptions, getCategoriesDropDownByType } from '@/helper.js';

const page = usePage();
const toast = useToast();

const props = defineProps({
  report: {
    type: Object,
    default: {},
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
  isNew: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update', 'create']);

const form = useForm({
  name: props.report?.name,
  type: page.props.type,
  filters: {
    start: props.report.filters?.start,
    end: props.report.filters?.end,
    grower_ids: props.report.filters?.grower_ids,
    grower_groups: props.report.filters?.grower_groups,
    paddocks: props.report.filters?.paddocks,
    seed_varieties: props.report.filters?.seed_varieties,
    seed_generations: props.report.filters?.seed_generations,
    seed_classes: props.report.filters?.seed_classes,
    transports: props.report.filters?.transports,
    delivery_types: props.report.filters?.delivery_types,
    seed_types: props.report.filters?.seed_types,
    cool_stores: props.report.filters?.cool_stores,
    fungicides: props.report.filters?.fungicides,
    channels: props.report.filters?.channels,
    bin_sizes: props.report.filters?.bin_sizes,
    systems: props.report.filters?.systems,
    buyer_ids: props.report.filters?.buyer_ids,
    allocation_buyer_ids: props.report.filters?.allocation_buyer_ids,
    labelable_type: props.report.filters?.labelable_type,
  },
});

const isReceivalForm = computed(() => page.props.type === 'receival');
const isUnloadForm = computed(() => page.props.type === 'unload');
const isTiaSampleForm = computed(() => page.props.type === 'tia-sample');
const isLabelForm = computed(() => page.props.type === 'label');
const isAllocationForm = computed(() => page.props.type === 'allocation');
const isReallocationForm = computed(() => page.props.type === 'reallocation');
const isCuttingForm = computed(() => page.props.type === 'cutting');
const isDispatchForm = computed(() => page.props.type === 'dispatch');

const updateRecord = () => {
  form.patch(route('reports.update', props.report.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The report has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('reports.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The report has been created successfully!');
    },
  });
};

defineExpose({
  updateRecord,
  storeRecord,
});
</script>

<template>
  <div class="row">
    <div class="col-12">
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Name</th>
            <td><TextInput v-model="form.name" :error="form.errors.name" type="text" /></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="col-12 col-sm-6">
      <h4>Grower Filter</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Growers</th>
            <td>
              <Multiselect
                v-model="form.filters.grower_ids"
                mode="tags"
                placeholder="Choose a growers"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.grower_ids'] }"
                :options="getDropDownOptions(page.props.growers, true)"
              />
              <div v-if="form.errors['filters.grower_ids']" class="invalid-feedback">
                {{ form.errors['filters.grower_ids'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Grower Groups</th>
            <td>
              <Multiselect
                v-model="form.filters.grower_groups"
                mode="tags"
                placeholder="Choose a grower group"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.grower_groups'] }"
                :options="getCategoriesDropDownByType(page.props.categories, 'grower-group')"
              />
              <div v-if="form.errors['filters.grower_groups']" class="invalid-feedback">
                {{ form.errors['filters.grower_groups'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Paddocks</th>
            <td>
              <Multiselect
                v-model="form.filters.paddocks"
                mode="tags"
                placeholder="Choose a paddocks"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.paddocks'] }"
                :options="
                  (page.props.growers || [])
                    .map((grower) => grower.paddocks)
                    .flat()
                    .map((item) => {
                      return { value: item.name, label: item.name };
                    })
                "
              />
              <div v-if="form.errors['filters.paddocks']" class="invalid-feedback">
                {{ form.errors['filters.paddocks'] }}
              </div>
            </td>
          </tr>
        </table>
      </div>

      <h4>Range Filter</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Start</th>
            <td>
              <TextInput type="date" v-model="form.filters.start" :error="form.errors['filters.start']" />
            </td>
          </tr>
          <tr>
            <th>End</th>
            <td>
              <TextInput type="date" v-model="form.filters.end" :error="form.errors['filters.end']" />
            </td>
          </tr>
        </table>
      </div>
    </div>
    <div class="col-12 col-sm-6">
      <h4>Seed Filter</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Seed Varieties</th>
            <td>
              <Multiselect
                v-model="form.filters.seed_varieties"
                mode="tags"
                placeholder="Choose a seed varieties"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.seed_varieties'] }"
                :options="getCategoriesDropDownByType(page.props.categories, 'seed-variety')"
              />
              <div v-if="form.errors['filters.seed_varieties']" class="invalid-feedback">
                {{ form.errors['filters.seed_varieties'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Seed Generations</th>
            <td>
              <Multiselect
                v-model="form.filters.seed_generations"
                mode="tags"
                placeholder="Choose a seed generations"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.seed_generations'] }"
                :options="getCategoriesDropDownByType(page.props.categories, 'seed-generation')"
              />
              <div v-if="form.errors['filters.seed_generations']" class="invalid-feedback">
                {{ form.errors['filters.seed_generations'] }}
              </div>
            </td>
          </tr>
          <tr v-if="isReceivalForm">
            <th>Seed Classes</th>
            <td>
              <Multiselect
                v-model="form.filters.seed_classes"
                mode="tags"
                placeholder="Choose a seed classes"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.seed_classes'] }"
                :options="getCategoriesDropDownByType(page.props.categories, 'seed-class')"
              />
              <div v-if="form.errors['filters.seed_classes']" class="invalid-feedback">
                {{ form.errors['filters.seed_classes'] }}
              </div>
            </td>
          </tr>
          <tr v-if="isReceivalForm">
            <th>Transports</th>
            <td>
              <Multiselect
                v-model="form.filters.transports"
                mode="tags"
                placeholder="Choose a transports"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.transports'] }"
                :options="getCategoriesDropDownByType(page.props.categories, 'transport')"
              />
              <div v-if="form.errors['filters.transports']" class="invalid-feedback">
                {{ form.errors['filters.transports'] }}
              </div>
            </td>
          </tr>
          <tr v-if="isReceivalForm">
            <th>Delivery Types</th>
            <td>
              <Multiselect
                v-model="form.filters.delivery_types"
                mode="tags"
                placeholder="Choose a delivery types"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.delivery_types'] }"
                :options="getCategoriesDropDownByType(page.props.categories, 'delivery-type')"
              />
              <div v-if="form.errors['filters.delivery_types']" class="invalid-feedback">
                {{ form.errors['filters.delivery_types'] }}
              </div>
            </td>
          </tr>
          <tr v-if="isReceivalForm" class="d-none">
            <th>Cool Stores</th>
            <td>
              <Multiselect
                v-model="form.filters.cool_stores"
                mode="tags"
                placeholder="Choose a cool stores"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.cool_stores'] }"
                :options="getCategoriesDropDownByType(page.props.categories, 'cool-store')"
              />
              <div v-if="form.errors['filters.cool_stores']" class="invalid-feedback">
                {{ form.errors['filters.cool_stores'] }}
              </div>
            </td>
          </tr>
        </table>
      </div>

      <h4 v-if="isUnloadForm">Unload Filter</h4>
      <div v-if="isUnloadForm" class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Seed Types</th>
            <td>
              <Multiselect
                v-model="form.filters.seed_types"
                mode="tags"
                placeholder="Choose a seed types"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.seed_types'] }"
                :options="getCategoriesDropDownByType(page.props.categories, 'seed-type')"
              />
              <div v-if="form.errors['filters.seed_types']" class="invalid-feedback">
                {{ form.errors['filters.seed_types'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Fungicides</th>
            <td>
              <Multiselect
                v-model="form.filters.fungicides"
                mode="tags"
                placeholder="Choose a fungicides"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.fungicides'] }"
                :options="getCategoriesDropDownByType(page.props.categories, 'fungicide')"
              />
              <div v-if="form.errors['filters.fungicides']" class="invalid-feedback">
                {{ form.errors['filters.fungicides'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Channels</th>
            <td>
              <Multiselect
                v-model="form.filters.channels"
                mode="tags"
                placeholder="Choose a channels"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.channels'] }"
                :options="[
                  { value: 'weighbridge', label: 'BU1' },
                  { value: 'BU2', label: 'BU2' },
                  { value: 'BU3', label: 'BU3' },
                ]"
              />
              <div v-if="form.errors['filters.channels']" class="invalid-feedback">
                {{ form.errors['filters.channels'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Bin Sizes</th>
            <td>
              <Multiselect
                v-model="form.filters.bin_sizes"
                mode="tags"
                placeholder="Choose a bin sizes"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.bin_sizes'] }"
                :options="binSizes"
              />
              <div v-if="form.errors['filters.bin_sizes']" class="invalid-feedback">
                {{ form.errors['filters.bin_sizes'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Systems</th>
            <td>
              <Multiselect
                v-model="form.filters.systems"
                mode="tags"
                placeholder="Choose a systems"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.systems'] }"
                :options="[
                  { value: 1, label: 'System 1' },
                  { value: 2, label: 'System 2' },
                ]"
              />
              <div v-if="form.errors['filters.systems']" class="invalid-feedback">
                {{ form.errors['filters.systems'] }}
              </div>
            </td>
          </tr>
        </table>
      </div>

      <h4 v-if="isTiaSampleForm">Tia Sample Filter</h4>
      <div v-if="isTiaSampleForm" class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Processor</th>
            <td>
              <Multiselect
                v-model="form.filters.processor"
                mode="tags"
                placeholder="Choose a processor"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.processor'] }"
                :options="binSizes"
              />
              <div v-if="form.errors['filters.processor']" class="invalid-feedback">
                {{ form.errors['filters.processor'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Size</th>
            <td>
              <Multiselect
                v-model="form.filters.size"
                mode="tags"
                placeholder="Choose a size"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.size'] }"
                :options="[
                  { value: '35-350g', label: '35 - 350g' },
                  { value: '90mm', label: '90mm' },
                  { value: '70mm', label: '70mm' },
                ]"
              />
              <div v-if="form.errors['filters.size']" class="invalid-feedback">
                {{ form.errors['filters.size'] }}
              </div>
            </td>
          </tr>
        </table>
      </div>

      <h4 v-if="isLabelForm">Label Filter</h4>
      <div v-if="isLabelForm" class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Label Type</th>
            <td>
              <Multiselect
                v-model="form.filters.labelable_type"
                mode="tags"
                placeholder="Choose a label type"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.labelable_type'] }"
                :options="[
                  { value: 'App\\Models\\Allocation', label: 'Allocation' },
                  { value: 'App\\Models\\Reallocation', label: 'Reallocation' },
                  { value: 'App\\Models\\CuttingAllocation', label: 'Cutting' },
                ]"
              />
              <div v-if="form.errors['filters.labelable_type']" class="invalid-feedback">
                {{ form.errors['filters.labelable_type'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Buyers</th>
            <td>
              <Multiselect
                v-model="form.filters.buyer_ids"
                mode="tags"
                placeholder="Choose a buyers"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.buyer_ids'] }"
                :options="getDropDownOptions(page.props.buyers, false, true)"
              />
              <div v-if="form.errors['filters.buyer_ids']" class="invalid-feedback">
                {{ form.errors['filters.buyer_ids'] }}
              </div>
            </td>
          </tr>
        </table>
      </div>

      <h4 v-if="isAllocationForm || isReallocationForm || isCuttingForm || isDispatchForm">Buyer Filter</h4>
      <div v-if="isAllocationForm || isReallocationForm || isCuttingForm || isDispatchForm" class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Buyers</th>
            <td>
              <Multiselect
                v-model="form.filters.buyer_ids"
                mode="tags"
                placeholder="Choose a buyers"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.buyer_ids'] }"
                :options="getDropDownOptions(page.props.buyers, false, true)"
              />
              <div v-if="form.errors['filters.buyer_ids']" class="invalid-feedback">
                {{ form.errors['filters.buyer_ids'] }}
              </div>
            </td>
          </tr>
          <tr v-if="isCuttingForm">
            <th>Fungicides</th>
            <td>
              <Multiselect
                v-model="form.filters.fungicides"
                mode="tags"
                placeholder="Choose a fungicides"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.fungicides'] }"
                :options="getCategoriesDropDownByType(page.props.categories, 'fungicide')"
              />
              <div v-if="form.errors['filters.fungicides']" class="invalid-feedback">
                {{ form.errors['filters.fungicides'] }}
              </div>
            </td>
          </tr>
          <tr v-if="isReallocationForm || isDispatchForm">
            <th>Allocation Buyers</th>
            <td>
              <Multiselect
                v-model="form.filters.allocation_buyer_ids"
                mode="tags"
                placeholder="Choose a buyers"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.allocation_buyer_ids'] }"
                :options="getDropDownOptions(page.props.buyers, false, true)"
              />
              <div v-if="form.errors['filters.allocation_buyer_ids']" class="invalid-feedback">
                {{ form.errors['filters.allocation_buyer_ids'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Seed Types</th>
            <td>
              <Multiselect
                v-model="form.filters.seed_types"
                mode="tags"
                placeholder="Choose a seed types"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.seed_types'] }"
                :options="getCategoriesDropDownByType(page.props.categories, 'seed-type')"
              />
              <div v-if="form.errors['filters.seed_types']" class="invalid-feedback">
                {{ form.errors['filters.seed_types'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Bin Sizes</th>
            <td>
              <Multiselect
                v-model="form.filters.bin_sizes"
                mode="tags"
                placeholder="Choose a bin sizes"
                :searchable="true"
                :class="{ 'is-invalid': form.errors['filters.bin_sizes'] }"
                :options="binSizes"
              />
              <div v-if="form.errors['filters.bin_sizes']" class="invalid-feedback">
                {{ form.errors['filters.bin_sizes'] }}
              </div>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>
