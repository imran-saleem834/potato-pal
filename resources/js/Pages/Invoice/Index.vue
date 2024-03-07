<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import Details from '@/Pages/Invoice/Details.vue';
import LeftBar from '@/Components/LeftBar.vue';
import { useToast } from 'vue-toastification';
import { useWindowSize } from 'vue-window-size';

const toast = useToast();
const { width, height } = useWindowSize();

const props = defineProps({
  invoices: Object,
  single: Object,
  receivals: Object,
  grades: Object,
  cuttings: Object,
  filters: Object,
  errors: Object,
});

const invoice = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);
const details = ref(null);

watch(
  () => props?.single,
  (single) => {
    if (
      Object.values(props.errors).length === undefined ||
      Object.values(props.errors).length <= 0
    ) {
      invoice.value = single || {};
    }
  },
);

watch(search, (value) => {
  router.get(
    route('invoices.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  );
});

const filter = (keyword) => (search.value = keyword);

const getInvoice = (id) => {
  axios.get(route('invoices.show', id)).then((response) => {
    invoice.value = response.data;

    setActiveTab(response.data.id);
  });
};

const setActiveTab = (id) => {
  activeTab.value = id;
  edit.value = null;
  isNewRecord.value = false;
};

const setEdit = (id) => {
  edit.value = edit.value === id ? null : id;
  isNewRecord.value = false;
};

const setNewRecord = () => {
  isNewRecord.value = true;
  edit.value = null;
  invoice.value = {};
  activeTab.value = null;
};

const deleteInvoice = (id) => {
  const form = useForm({});
  form.delete(route('invoices.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      setActiveTab(invoice.value?.id);
      toast.success('The invoice has been deleted successfully!');
    },
  });
};

if (width.value > 991) {
  setActiveTab(invoice.value?.id);
}
</script>

<template>
  <AppLayout title="Invoices">
    <TopBar
      type="Invoices"
      :title="`Invoice`"
      :active-tab="activeTab"
      :search="search"
      @search="filter"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      @new="setNewRecord"
      @edit="() => setEdit(invoice?.id)"
      @unset="() => setActiveTab(null)"
      @store="() => details.storeRecord()"
      @update="() => details.updateRecord()"
      @delete="() => deleteInvoice(invoice?.id)"
    />

    <div class="tab-section">
      <div class="row g-0">
        <div
          class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block"
          :class="{ 'd-none': activeTab || isNewRecord }"
        >
          <LeftBar
            :items="invoices.data"
            :links="invoices.links"
            :active-tab="activeTab"
            :row-1="{ title: 'Reference Id', value: 'invoiceable_id' }"
            :row-2="{ title: 'Invoice Id', value: 'id' }"
            @click="getInvoice"
          />
        </div>
        <div
          class="col-12 col-lg-7 col-xl-8 d-lg-block"
          :class="{ 'd-none': !activeTab && !isNewRecord }"
        >
          <div class="tab-content" v-if="Object.values(invoice).length > 0 || isNewRecord">
            <Details
              ref="details"
              :invoice="invoice"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              :receivals="receivals"
              :grades="grades"
              :cuttings="cuttings"
              @update="() => getInvoice(activeTab)"
              @create="() => setActiveTab(invoice?.id)"
              @unset="() => setActiveTab(null)"
            />
          </div>
          <div v-if="Object.values(invoice).length <= 0 && !isNewRecord">
            <p class="w-100 text-center" style="margin-top: calc(50vh - 120px)">No Records Found</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
