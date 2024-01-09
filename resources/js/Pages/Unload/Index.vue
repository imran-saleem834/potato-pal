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
  unloads: Object,
  single: Object,
  receivals: Array,
  filters: Object,
});

const unload = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);

watch(() => props?.single,
  (single) => {
    unload.value = single || {};
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
    unload.value = response.data;

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
  unload.value = {};
  activeTab.value = null;
}

const deleteUnload = (id) => {
  const form = useForm({});
  form.delete(route('unloading.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      setActiveTab(unload.value?.id);
    },
  });
}

setActiveTab(unload.value?.id);
</script>

<template>
  <AppLayout title="Unloading">
    <TopBar
      type="Unloading"
      :value="search"
      @search="filter"
      :access="{
        new: false,
      }"
      @newRecord="setNewRecord"
    />
    <MiddleBar
      type="Unloading"
      :title="unload?.grower?.name || 'New'"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      :access="{
        new: false,
        edit: Object.values(unload).length > 0,
        delete: Object.values(unload).length > 0,
      }"
      @newRecord="setNewRecord"
      @editRecord="() => setEdit(unload?.id)"
      @deleteRecord="() => deleteUnload(unload?.id)"
    />

    <!-- tab-section -->
    <div class="tab-section">
      <div class="row row0">
        <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
          <LeftBar
            :items="unloads"
            :active-tab="activeTab"
            :row-1="{title: 'Grower', value: 'grower.name'}"
            :row-2="{title: 'Unloading Id', value: 'id'}"
            @click="getUnload"
          />
        </div>
        <div class="col-lg-8 col-sm-6">
          <div class="tab-content" v-if="Object.values(unload).length > 0 || isNewRecord">
            <div class="tab-pane active">
              <Details
                :unload="unload"
                :is-edit="!!edit"
                :is-new="isNewRecord"
                @update="() => getUnload(activeTab)"
                @create="() => setActiveTab(unload?.id)"
                col-size="col-md-6"
              />
            </div>
          </div>
          <div class="col-sm-12" v-if="Object.values(unload).length <= 0 && !isNewRecord">
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
            :access="{
              new: isNewRecord
            }"
            @edit="() => setEdit(unload?.id)"
            @delete="() => deleteUnload(unload?.id)"
          />
          <div class="modal-body" v-if="unload">
            <ModalBreadcrumb
              page="Unloading"
              :title="unload?.grower?.name || 'New'"
            />
            <Details
              :unload="unload"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              @update="() => getUnload(activeTab)"
              @create="() => setActiveTab(unload?.id)"
              col-size="col-md-12"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
