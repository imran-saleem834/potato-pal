<script setup>
import print from 'vue3-print-nb';
import { computed, ref, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { useToast } from 'vue-toastification';
import TextInput from '@/Components/TextInput.vue';
import UlLiButton from '@/Components/UlLiButton.vue';
import Labels from '@/Pages/Label/Labels.vue';
import { toTonnes, getBinSizesValue } from '@/helper.js';

const toast = useToast();
const vPrint = print;

const props = defineProps({
  label: Object,
  colSize: String,
  isEdit: Boolean,
  isNew: Boolean,
  allocations: Array,
  cuttings: Array,
  reallocations: Array,
  receivals: Array,
});

const emit = defineEmits(['update', 'create', 'unset']);
const btnPrintLabel = ref(null);

const form = useForm({
  labelable_type: props.label.labelable_type,
  labelable_id: props.label.labelable_id,
  buyer_id: props.label.buyer_id,
  grower_id: props.label.grower_id,
  paddock: props.label.paddock,
  receival_id: props.label.receival_id,
  type: props.label.type,
  comments: props.label.comments,
});

watch(
  () => props.label,
  (label) => {
    form.clearErrors();
    form.labelable_type = label.labelable_type;
    form.labelable_id = label.labelable_id;
    form.buyer_id = label.buyer_id;
    form.grower_id = label.grower_id;
    form.paddock = label.paddock;
    form.receival_id = label.receival_id;
    form.type = label.type;
    form.comments = label.comments;
  },
);

const isForm = computed(() => props.isEdit || props.isNew);

const updateRecord = () => {
  form.patch(route('labels.update', props.label.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The label has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('labels.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The label has been created successfully!');
    },
  });
};

const onChangeLabelType = (value) => {
  if (form.labelable_type !== value) {
    form.labelable_type = value;
    form.buyer_id = null;
    form.grower_id = null;
    form.paddock = null;
    form.labelable_id = null;
  }
};

const onChangeType = (value) => {
  if (form.type !== value) {
    form.type = value;
    // Todo: Will display label below
  }
};

const printLabels = () => {
  btnPrintLabel.value.click();
};

const buyers = computed(() => {
  if (form.labelable_type === 'App\\Models\\CuttingAllocation') {
    return props.cuttings
      .filter(
        (cutting, index, self) =>
          index === self.findIndex((t) => t.allocation.buyer_id === cutting.allocation.buyer_id),
      )
      .map((cutting) => ({
        value: cutting.allocation.buyer_id,
        label: cutting.allocation.buyer.buyer_name,
      }));
  } else if (form.labelable_type === 'App\\Models\\Reallocation') {
    return props.reallocations
      .filter((reallocation, index, self) => index === self.findIndex((t) => t.buyer_id === reallocation.buyer_id))
      .map((reallocation) => ({
        value: reallocation.buyer_id,
        label: reallocation.buyer.buyer_name,
      }));
  }

  return props.allocations
    .filter((allocation, index, self) => index === self.findIndex((t) => t.buyer_id === allocation.buyer_id))
    .map((allocation) => ({ value: allocation.buyer_id, label: allocation.buyer.buyer_name }));
});

const exGrowers = computed(() => {
  if (form.labelable_type === 'App\\Models\\CuttingAllocation') {
    return props.cuttings
      .filter((cutting) => cutting.allocation.buyer_id === form.buyer_id)
      .filter(
        (cutting, index, self) =>
          index === self.findIndex((t) => t.allocation.grower_id === cutting.allocation.grower_id),
      )
      .map((cutting) => ({
        value: cutting.allocation.grower_id,
        label: cutting.allocation.grower.grower_name,
      }));
  } else if (form.labelable_type === 'App\\Models\\Reallocation') {
    return props.reallocations
      .filter((reallocation) => reallocation.buyer_id === form.buyer_id)
      .filter(
        (reallocation, index, self) =>
          index === self.findIndex((t) => t.allocation.grower_id === reallocation.allocation.grower_id),
      )
      .map((reallocation) => ({
        value: reallocation.allocation.grower_id,
        label: reallocation.allocation.grower.grower_name,
      }));
  }

  return props.allocations
    .filter((allocation) => allocation.buyer_id === form.buyer_id)
    .filter((allocation, index, self) => index === self.findIndex((t) => t.grower_id === allocation.grower_id))
    .map((allocation) => ({ value: allocation.grower_id, label: allocation.grower.grower_name }));
});

const paddockOptions = computed(() => {
  if (form.labelable_type === 'App\\Models\\CuttingAllocation') {
    return props.cuttings
      .filter((cutting) => cutting.allocation.buyer_id === form.buyer_id)
      .filter((cutting) => cutting.allocation.grower_id === form.grower_id)
      .filter(
        (cutting, index, self) => index === self.findIndex((t) => t.allocation.paddock === cutting.allocation.paddock),
      )
      .map((cutting) => ({ value: cutting.allocation.paddock, label: cutting.allocation.paddock }));
  } else if (form.labelable_type === 'App\\Models\\Reallocation') {
    return props.reallocations
      .filter((reallocation) => reallocation.buyer_id === form.buyer_id)
      .filter((reallocation) => reallocation.allocation.grower_id === form.grower_id)
      .filter(
        (reallocation, index, self) =>
          index === self.findIndex((t) => t.allocation.paddock === reallocation.allocation.paddock),
      )
      .map((reallocation) => ({
        value: reallocation.allocation.paddock,
        label: reallocation.allocation.paddock,
      }));
  }

  return props.allocations
    .filter((allocation) => allocation.buyer_id === form.buyer_id)
    .filter((allocation) => allocation.grower_id === form.grower_id)
    .filter((allocation, index, self) => index === self.findIndex((t) => t.paddock === allocation.paddock))
    .map((allocation) => ({ value: allocation.paddock, label: allocation.paddock }));
});

const allocationOption = computed(() => {
  if (form.labelable_type === 'App\\Models\\CuttingAllocation') {
    return props.cuttings
      .filter((cutting) => cutting.allocation.buyer_id === form.buyer_id)
      .filter((cutting) => cutting.allocation.grower_id === form.grower_id)
      .filter((cutting) => cutting.allocation.paddock === form.paddock)
      .map((cutting) => ({
        value: cutting.id,
        label: `${cutting.id}; Size: ${getBinSizesValue(cutting.allocation.bin_size)}; Bins to cut: ${cutting.no_of_bins}}`,
      }));
  } else if (form.labelable_type === 'App\\Models\\Reallocation') {
    return props.reallocations
      .filter((reallocation) => reallocation.buyer_id === form.buyer_id)
      .filter((reallocation) => reallocation.allocation.grower_id === form.grower_id)
      .filter((reallocation) => reallocation.allocation.paddock === form.paddock)
      .map((reallocation) => ({
        value: reallocation.id,
        label: `${reallocation.id}; Size: ${getBinSizesValue(reallocation.allocation.bin_size)}; Bins: ${reallocation.no_of_bins}; W: ${toTonnes(reallocation.weight)}`,
      }));
  }

  return props.allocations
    .filter((allocation) => allocation.buyer_id === form.buyer_id)
    .filter((allocation) => allocation.grower_id === form.grower_id)
    .filter((allocation) => allocation.paddock === form.paddock)
    .map((allocation) => ({
      value: allocation.id,
      label: `${allocation.id}; Size: ${getBinSizesValue(allocation.bin_size)}; Bins: ${allocation.no_of_bins}; W: ${toTonnes(allocation.weight)}`,
    }));
});

const idSelectionLabel = computed(() => {
  const labeled = form.labelable_type.split('\\').at(-1);
  if (labeled === 'CuttingAllocation') {
    return 'Cutting';
  }
  return labeled;
});

const idSelectionRoute = computed(() => {
  const labeled = form.labelable_type.split('\\').at(-1);
  if (labeled === 'CuttingAllocation') {
    return route('cuttings.index', { buyerId: props.label.buyer_id });
  } else if (labeled === 'Reallocation') {
    return route('reallocations.index', { buyerId: props.label.buyer_id });
  }
  return route('allocations.index', { buyerId: props.label.buyer_id });
});

defineExpose({
  updateRecord,
  storeRecord,
  printLabels,
});

const printObj = {
  id: '---',
  // url: 'http://localhost:8000/users',
  // extraCss: "http://localhost:8000/build/assets/app-b0f4edb8.css",
  // extraHead: '<meta http-equiv="Content-Language" content="en-gb" />',
  preview: true,
  previewTitle: 'PRINT CHERRY HILL COOLSTORES LABELS',
  previewPrintBtnLabel: 'Print',
  asyncUrl(reslove, vue) {
    setTimeout(() => {
      reslove(route('labels.print', [props.label.id, form.type]));
    }, 1000);
  },
  // previewBeforeOpenCallback () {
  //   console.log('previewBeforeOpenCallback')
  // },
  // previewOpenCallback () {
  //   console.log('previewOpenCallback')
  // },
  beforeOpenCallback(vue) {
    // vue.printLoading = true
    console.log('beforeOpenCallback');
  },
  openCallback(vue) {
    // vue.printLoading = false
    console.log('openCallback');
  },
  closeCallback(vue) {
    console.log('closeCallback');
  },
};
</script>

<template>
  <div class="row">
    <div :class="colSize">
      <h4>Allocation Information</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Label Type</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="form.labelable_type"
                :error="form.errors.labelable_type"
                :items="[
                  { value: 'App\\Models\\Allocation', label: 'Allocation' },
                  { value: 'App\\Models\\Reallocation', label: 'Reallocation' },
                  { value: 'App\\Models\\CuttingAllocation', label: 'Cutting' },
                ]"
                @click="onChangeLabelType"
              />
            </td>
          </tr>
          <tr v-if="form.labelable_type">
            <th>Issue to</th>
            <td>
              <Multiselect
                v-if="isForm"
                v-model="form.buyer_id"
                mode="single"
                placeholder="Choose a buyer"
                :searchable="true"
                :class="{ 'is-invalid': form.errors.buyer_id }"
                :options="buyers"
              />
              <Link v-else-if="label.buyer" class="p-0" :href="route('users.index', { userId: label.buyer_id })">
                {{ label.buyer.buyer_name }}
              </Link>
              <template v-else>-</template>
              <div v-if="form.errors.buyer_id" class="invalid-feedback">
                {{ form.errors.buyer_id }}
              </div>
            </td>
          </tr>
          <tr v-if="form.buyer_id">
            <th>Ex Grower</th>
            <td>
              <Multiselect
                v-if="isForm"
                v-model="form.grower_id"
                mode="single"
                placeholder="Choose a grower"
                :searchable="true"
                :class="{ 'is-invalid': form.errors.grower_id }"
                :options="exGrowers"
              />
              <Link v-else-if="label.grower" class="p-0" :href="route('users.index', { userId: label.grower_id })">
                {{ label.grower.grower_name }}
              </Link>
              <template v-else>-</template>
              <div v-if="form.errors.grower_id" class="invalid-feedback">
                {{ form.errors.grower_id }}
              </div>
            </td>
          </tr>
          <tr v-if="form.grower_id">
            <th>Paddock</th>
            <td>
              <Multiselect
                v-if="isForm"
                v-model="form.paddock"
                mode="single"
                placeholder="Choose a paddock"
                :searchable="true"
                :class="{ 'is-invalid': form.errors.paddock }"
                :options="paddockOptions"
              />
              <template v-else-if="label.paddock">{{ label.paddock }}</template>
              <template v-else>-</template>
              <div v-if="form.errors.paddock" class="invalid-feedback">
                {{ form.errors.paddock }}
              </div>
            </td>
          </tr>
          <tr v-if="form.paddock">
            <th>{{ idSelectionLabel }}</th>
            <td>
              <Multiselect
                v-if="isForm"
                v-model="form.labelable_id"
                mode="single"
                placeholder="Choose a label record"
                :searchable="true"
                :class="{ 'is-invalid': form.errors.labelable_id }"
                :options="allocationOption"
              />
              <template v-else-if="label.labelable_id">
                {{ idSelectionLabel }} Id: {{ label.labelable_id }};
                <Link class="p-0" :href="idSelectionRoute"> Buyer Id: {{ label.buyer_id }} </Link>
              </template>
              <template v-else>-</template>
              <div v-if="form.errors.labelable_id" class="invalid-feedback">
                {{ form.errors.labelable_id }}
              </div>
            </td>
          </tr>
        </table>
      </div>
    </div>
    <div :class="colSize">
      <h4>Other Information</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Type</th>
            <td>
              <UlLiButton
                :is-form="true"
                :value="form.type"
                :error="form.errors.type"
                :items="[
                  { value: 'rec-1', label: 'Rec X 1' },
                  { value: 'rec-3', label: 'Rec X 3' },
                  { value: 'rec-id', label: 'Rec ID' },
                  { value: 'cut-seed', label: 'Cut Seed' },
                ]"
                @click="onChangeType"
              />
            </td>
          </tr>
          <tr>
            <th>Receival Id</th>
            <td>
              <Multiselect
                v-if="isForm"
                v-model="form.receival_id"
                mode="single"
                placeholder="Choose a receival"
                :searchable="true"
                :class="{ 'is-invalid': form.errors.receival_id }"
                :options="receivals"
              />
              <Link
                v-else-if="label.receival_id"
                class="p-0"
                :href="route('receivals.index', { receivalId: label.receival_id })"
              >
                {{ label.receival_id }}
              </Link>
              <template v-else>-</template>
              <div v-if="form.errors.receival_id" class="invalid-feedback">
                {{ form.errors.receival_id }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Override Comments</th>
            <td>
              <TextInput v-if="isForm" v-model="form.comments" :error="form.errors.comments" type="text" />
              <template v-else-if="form.comments">{{ form.comments }}</template>
              <template>-</template>
            </td>
          </tr>
          <tr v-if="!isForm">
            <th>Print</th>
            <td>
              <button class="btn btn-red btn-sm" v-print="printObj" ref="btnPrintLabel">
                <i class="bi bi-printer"></i> Print Labels
              </button>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <Labels v-if="!isForm" :type="form.type" :label="label" />
</template>
