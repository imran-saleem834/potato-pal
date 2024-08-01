<script setup>
import moment from 'moment';
import { computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { useToast } from 'vue-toastification';
import UlLiButton from '@/Components/UlLiButton.vue';

const toast = useToast();

const props = defineProps({
  invoice: Object,
  isEdit: Boolean,
  isNew: Boolean,
  receivals: Array,
  grades: Array,
  cuttings: Array,
});

const emit = defineEmits(['update', 'create', 'unset']);

const form = useForm({
  invoiceable_type: props.invoice.invoiceable_type,
  invoiceable_id: props.invoice.invoiceable_id,
});

watch(
  () => props.invoice,
  (invoice) => {
    form.clearErrors();
    form.invoiceable_type = invoice.invoiceable_type;
    form.invoiceable_id = invoice.invoiceable_id;
  },
);

const isForm = computed(() => props.isEdit || props.isNew);

const invoiceableLabel = computed(() => form.invoiceable_type.split('\\').at(-1));

const invoiceableRoute = computed(() => {
  if (invoiceableLabel.value === 'Cutting') {
    return route('cuttings.index', { buyerId: props.invoice.invoiceable.buyer_id });
  } else if (invoiceableLabel.value === 'Grading') {
    return route('grading.index', { userId: props.invoice.invoiceable.buyer_id });
  }
  return route('receivals.index', { receivalId: props.invoice.invoiceable_id });
});

const onChangeInvoiceType = (value) => {
  if (form.invoiceable_type !== value) {
    form.invoiceable_type = value;
    form.invoiceable_id = null;
  }
};

const options = computed(() => {
  if (invoiceableLabel.value === 'Cutting') {
    return props.cuttings.map((cutting) => ({
      value: cutting.id,
      label: `Cutting ID: ${cutting.id}; Buyer: ${cutting.buyer.buyer_name}`,
    }));
  } else if (invoiceableLabel.value === 'Grading') {
    return props.grades.map((grade) => ({
      value: grade.id,
      label: `Grade ID: ${grade.id}; Buyer: ${grade.user.buyer_name}`,
    }));
  }

  return props.receivals.map((receival) => ({
    value: receival.id,
    label: `Receival ID: ${receival.id}; Grower: ${receival.grower.grower_name}`,
  }));
});

const updateRecord = () => {
  form.patch(route('invoices.update', props.invoice.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The invoice has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('invoices.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The invoice has been created successfully!');
    },
  });
};

defineExpose({
  updateRecord,
  storeRecord,
});
</script>

<template>
  <h4>Invoice Information</h4>
  <div class="user-boxes">
    <table class="table input-table mb-0">
      <tr>
        <th>Invoice Type</th>
        <td class="pb-0">
          <UlLiButton
            :is-form="isForm"
            :value="form.invoiceable_type"
            :error="form.errors.invoiceable_type"
            :items="[
              { value: 'App\\Models\\Receival', label: 'Receival' },
              { value: 'App\\Models\\Grading', label: 'Grading' },
              { value: 'App\\Models\\Cutting', label: 'Cutting' },
            ]"
            @click="onChangeInvoiceType"
          />
        </td>
      </tr>
      <tr v-if="form.invoiceable_type">
        <th>{{ invoiceableLabel }}</th>
        <td>
          <Multiselect
            v-if="isForm"
            v-model="form.invoiceable_id"
            mode="single"
            placeholder="Choose a label record"
            :searchable="true"
            :class="{ 'is-invalid': form.errors.invoiceable_id }"
            :options="options"
          />
          <template v-else-if="invoice.invoiceable_id">
            <Link class="p-0" :href="invoiceableRoute">
              <template v-if="invoiceableLabel === 'Grading'">
                {{ invoiceableLabel }} Id: {{ invoice.invoiceable_id }}
              </template>
              <template v-else-if="invoiceableLabel === 'Cutting'">
                {{ invoiceableLabel }} Id: {{ invoice.invoiceable_id }}; Buyer Id:
                {{ invoice.invoiceable.buyer_id }}
              </template>
              <template v-else>
                {{ invoiceableLabel }} Id: {{ invoice.invoiceable_id }}; Grower Id:
                {{ invoice.invoiceable.grower_id }}
              </template>
            </Link>
          </template>
          <template v-else>-</template>
          <div v-if="form.errors.invoiceable_id" class="invalid-feedback">
            {{ form.errors.invoiceable_id }}
          </div>
        </td>
      </tr>
      <tr v-if="!isForm">
        <th>Time Added</th>
        <td>{{ moment(invoice.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
      </tr>
    </table>
  </div>
</template>
