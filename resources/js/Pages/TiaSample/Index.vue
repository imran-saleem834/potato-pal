<script setup>
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/TiaSample/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";

const props = defineProps({
  tiaSamples: Object,
  single: Object,
  receivals: Array,
  filters: Object,
});

const tiaSample = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);

watch(() => props?.single,
  (single) => {
    tiaSample.value = single || {};
  }
);

watch(search, (value) => {
  router.get(
    route('users.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  )
});

const filter = (keyword) => search.value = keyword;

const getTiaSample = (id) => {
  axios.get(route('tia-samples.show', id)).then(response => {
    tiaSample.value = response.data;

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
  tiaSample.value = {};
  activeTab.value = null;
}

const deleteTiaSample = (id) => {
  axios.delete(route('tia-samples.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      setActiveTab(tiaSample.value?.id);
    },
  });
}

setActiveTab(tiaSample.value?.id);
</script>

<template>
  <AppLayout title="Tia Sampling">
    <TopBar
      type="Tia Sampling"
      :value="search"
      @search="filter"
      @newRecord="setNewRecord"
    />
    <MiddleBar
      type="Tia Sampling"
      :title="tiaSample?.receival?.grower?.name || 'New'"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      :access="{
        new: true,
        edit: Object.values(tiaSample).length > 0,
        delete: Object.values(tiaSample).length > 0,
      }"
      @newRecord="setNewRecord"
      @editRecord="() => setEdit(tiaSample?.id)"
      @deleteRecord="() => deleteTiaSample(tiaSample?.id)"
    />

    <!-- tab-section -->
    <div class="tab-section tia-sample-tab-section">
      <div class="row row0">
        <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
          <LeftBar
            :items="tiaSamples"
            :active-tab="activeTab"
            :row-1="{title: 'Grower', value: 'receival.grower.name'}"
            :row-2="{title: 'Tia Sample Id', value: 'id'}"
            @click="getTiaSample"
          />
        </div>
        <div class="col-lg-9 col-sm-6">
          <div class="tab-content" v-if="Object.values(tiaSample).length > 0 || isNewRecord">
            <div class="tab-pane active">
              <Details
                :tia-sample="tiaSample"
                :is-edit="!!edit"
                :is-new="isNewRecord"
                :receivals="receivals"
                @update="() => getTiaSample(activeTab)"
                @create="() => setActiveTab(tiaSample?.id)"
                col-size="col-md-6"
              />
            </div>
          </div>
          <div class="col-sm-12" v-if="Object.values(tiaSample).length <= 0 && !isNewRecord">
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
            title="Tia Sampling"
            :access="{
              new: isNewRecord
            }"
            @edit="() => setEdit(tiaSample?.id)"
            @delete="() => deleteTiaSample(tiaSample?.id)"
          />
          <div class="modal-body" v-if="tiaSample">
            <ModalBreadcrumb
              page="Tia Sampling"
              :title="tiaSample?.receival?.grower?.name || 'New'"
            />
            <Details
              :tia-sample="tiaSample"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              :receivals="receivals"
              @update="() => getTiaSample(activeTab)"
              @create="() => setActiveTab(tiaSample?.id)"
              col-size="col-md-12"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
