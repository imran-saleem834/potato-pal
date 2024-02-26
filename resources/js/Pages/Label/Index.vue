<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import Details from '@/Pages/Label/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import { useToast } from "vue-toastification";
import { useWindowSize } from 'vue-window-size';

const toast = useToast();
const { width, height } = useWindowSize();

const props = defineProps({
  labels: Object,
  single: Object,
  allocations: Object,
  cuttings: Object,
  filters: Object,
  errors: Object
});

const label = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);
const details = ref(null);

watch(() => props?.single,
  (single) => {
    if (Object.values(props.errors).length === undefined || Object.values(props.errors).length <= 0) {
      label.value = single || {};
    }
  }
);

watch(search, (value) => {
  router.get(
    route('labels.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  )
});

const filter = (keyword) => search.value = keyword;

const getLabel = (id) => {
  axios.get(route('labels.show', id)).then(response => {
    label.value = response.data;

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
}

const setNewRecord = () => {
  isNewRecord.value = true;
  edit.value = null;
  label.value = {};
  activeTab.value = null;
}

const deleteLabel = (id) => {
  const form = useForm({});
  form.delete(route('labels.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      setActiveTab(label.value?.id);
      toast.success('The label has been deleted successfully!');
    },
  });
}

if (width.value > 991) {
  setActiveTab(label.value?.id);
}
</script>

<template>
  <AppLayout title="Labels">
    <TopBar
      type="Labels"
      :title="label?.grower?.grower_name || 'New'"
      :active-tab="activeTab"
      :search="search"
      @search="filter"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      @new="setNewRecord"
      @edit="() => setEdit(label?.id)"
      @unset="() => setActiveTab(null)"
      @store="() => details.storeRecord()"
      @update="() => details.updateRecord()"
      @delete="() => deleteLabel(label?.id)"
    />

    <div class="tab-section">
      <div class="row g-0">
        <div class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block" :class="{'d-none' : activeTab || isNewRecord}">
          <LeftBar
            :items="labels.data"
            :links="labels.links"
            :active-tab="activeTab"
            :row-1="{title: 'Grower', value: 'unload.receival.grower.grower_name'}"
            :row-2="{title: 'Label Id', value: 'id'}"
            @click="getLabel"
          />
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{'d-none': !activeTab && !isNewRecord}">
          <div class="tab-content" v-if="Object.values(label).length > 0 || isNewRecord">
            <Details
              ref="details"
              :label="label"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              :allocations="allocations"
              :cuttings="cuttings"
              @update="() => getLabel(activeTab)"
              @create="() => setActiveTab(label?.id)"
              @unset="() => setActiveTab(null)"
              col-size="col-12 col-xl-6"
            />
          </div>
          <div v-if="Object.values(label).length <= 0 && !isNewRecord">
            <p class="w-100 text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
