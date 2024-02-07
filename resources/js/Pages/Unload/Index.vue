<script setup>
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import Details from '@/Pages/Unload/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import { router, useForm } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import { useWindowSize } from 'vue-window-size';

const toast = useToast();
const { width, height } = useWindowSize();

const props = defineProps({
  receivals: Object,
  single: Object,
  categories: Array,
  filters: Object,
  errors: Object
});

const receival = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const search = ref(props.filters.search);
const details = ref(null);

watch(() => props?.single,
  (single) => {
    if (Object.values(props.errors).length === undefined || Object.values(props.errors).length <= 0) {
      receival.value = single || {};
    }
  }
);

watch(search, (value) => {
  router.get(
    route('unloading.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  )
});

const filter = (keyword) => search.value = keyword;

const getUnload = (id) => {
  axios.get(route('unloading.show', id)).then(response => {
    receival.value = response.data;

    setActiveTab(response.data.id);
  });
};

const setActiveTab = (id) => {
  activeTab.value = id;
  edit.value = null;
};

const setEdit = (id) => {
  edit.value = edit.value === id ? null : id;
}

const deleteUnload = (id) => {
  const form = useForm({});
  form.delete(route('unloading.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      setActiveTab(receival.value?.id);
      toast.success('The unload has been deleted successfully!');
    },
  });
}

if (width.value > 991) {
  setActiveTab(receival.value?.id);
}
</script>

<template>
  <AppLayout title="Unloading">
    <TopBar
      type="Unloading"
      :title="receival?.grower?.grower_name || 'New'"
      :active-tab="activeTab"
      :search="search"
      @search="filter"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="false"
      :access="{ new: false }"
      @edit="() => setEdit(receival?.id)"
      @unset="() => setActiveTab(null)"
      @store="() => details.storeRecord()"
      @update="() => details.updateRecord()"
      @delete="() => deleteUnload(receival?.id)"
    />

    <div class="tab-section">
      <div class="row g-0">
        <div class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block" :class="{'d-none' : activeTab}">
          <LeftBar
            :items="receivals.data"
            :links="receivals.links"
            :active-tab="activeTab"
            :row-1="{title: 'Grower', value: 'grower.grower_name'}"
            :row-2="{title: 'Receival Id', value: 'id'}"
            @click="getUnload"
          />
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{'d-none': !activeTab}">
          <div class="tab-content" v-if="Object.values(receival).length > 0">
              <Details
                ref="details"
                :receival="receival"
                :categories="categories"
                :is-edit="!!edit"
                @update="() => getUnload(activeTab)"
                @create="() => setActiveTab(receival?.id)"
                col-size="col-12 col-xl-6"
              />
          </div>
          <div class="col-sm-12" v-if="Object.values(receival).length <= 0">
            <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
  </AppLayout>
</template>
