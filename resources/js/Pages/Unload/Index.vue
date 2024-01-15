<script setup>
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/Unload/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";
import { router, useForm } from "@inertiajs/vue3";

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
    },
  });
}

setActiveTab(receival.value?.id);
</script>

<template>
  <AppLayout title="Unloading">
    <TopBar
      type="Unloading"
      :value="search"
      @search="filter"
      :access="{ new: false }"
    />
    <MiddleBar
      type="Unloading"
      :title="receival?.grower?.grower_name || 'New'"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="false"
      :access="{
        new: false,
        edit: Object.values(receival).length > 0,
        delete: Object.values(receival).length > 0,
      }"
      @editRecord="() => setEdit(receival?.id)"
      @deleteRecord="() => deleteUnload(receival?.id)"
    />

    <!-- tab-section -->
    <div class="tab-section">
      <div class="row row0">
        <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
          <LeftBar
            :items="receivals"
            :active-tab="activeTab"
            :row-1="{title: 'Grower', value: 'grower.grower_name'}"
            :row-2="{title: 'Receival Id', value: 'id'}"
            @click="getUnload"
          />
        </div>
        <div class="col-lg-8 col-sm-6">
          <div class="tab-content" v-if="Object.values(receival).length > 0">
            <div class="tab-pane active">
              <Details
                :receival="receival"
                :categories="categories"
                :is-edit="!!edit"
                @update="() => getUnload(activeTab)"
                @create="() => setActiveTab(receival?.id)"
                col-size="col-md-6"
              />
            </div>
          </div>
          <div class="col-sm-12" v-if="Object.values(receival).length <= 0">
            <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
    <!-- tab-section -->

    <!-- Modal -->
    <div class="modal right fade user-details" id="user-details" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <ModalHeader
            title="Unloading"
            :access="{ new: false }"
            @edit="() => setEdit(receival?.id)"
            @delete="() => deleteUnload(receival?.id)"
          />
          <div class="modal-body" v-if="receival">
            <ModalBreadcrumb
              page="Unloading"
              :title="receival?.grower?.grower_name || 'New'"
            />
            <Details
              :receival="receival"
              :categories="categories"
              :is-edit="!!edit"
              @update="() => getUnload(activeTab)"
              @create="() => setActiveTab(receival?.id)"
              col-size="col-md-12"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
