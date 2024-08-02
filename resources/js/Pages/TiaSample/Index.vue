<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import Details from '@/Pages/TiaSample/Details.vue';
import LeftBar from '@/Components/LeftBar.vue';
import { useToast } from 'vue-toastification';
import { useWindowSize } from 'vue-window-size';
import Actions from '@/Components/Actions.vue';

const toast = useToast();
const { width, height } = useWindowSize();

const props = defineProps({
  tiaSamples: Object,
  single: Object,
  receivals: Array,
  filters: Object,
  errors: Object,
});

const tiaSample = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);
const details = ref(null);

watch(
  () => props?.single,
  (single) => {
    if (Object.values(props.errors).length === undefined || Object.values(props.errors).length <= 0) {
      tiaSample.value = single || {};
    }
  },
);

watch(search, (value) => {
  router.get(route('tia-samples.index'), { search: value }, { preserveState: true, preserveScroll: true });
});

const filter = (keyword) => (search.value = keyword);

const getTiaSample = (id) => {
  axios.get(route('tia-samples.show', id)).then((response) => {
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
};

const setNewRecord = () => {
  isNewRecord.value = true;
  edit.value = null;
  tiaSample.value = {};
  activeTab.value = null;
};

const deleteTiaSample = (id) => {
  const form = useForm({});
  form.delete(route('tia-samples.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      setActiveTab(tiaSample.value?.id);
      toast.success('The tia sample has been deleted successfully!');
    },
  });
};

if (width.value > 991) {
  setActiveTab(tiaSample.value?.id);
}
</script>

<template>
  <AppLayout title="Tia Sampling">
    <TopBar
      type="Tia Sampling"
      :title="tiaSample?.receival?.grower?.grower_name || 'New'"
      :active-tab="activeTab"
      :search="search"
      @search="filter"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      :access="{
        new: false,
      }"
      @new="setNewRecord"
      @edit="() => setEdit(tiaSample?.id)"
      @unset="() => setActiveTab(null)"
      @store="() => details.storeRecord()"
      @update="() => details.updateRecord()"
      @delete="() => deleteTiaSample(tiaSample?.id)"
    />

    <div class="tab-section tia-sample-tab-section">
      <div class="row g-0">
        <div class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block" :class="{ 'd-none': activeTab || isNewRecord }">
          <LeftBar
            :items="tiaSamples.data"
            :links="tiaSamples.links"
            :active-tab="activeTab"
            :row-1="{ title: 'Grower', value: 'receival.grower.grower_name' }"
            :row-2="{ title: 'Receival ID', value: 'receival_id' }"
            @click="getTiaSample"
          />
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{ 'd-none': !activeTab && !isNewRecord }">
          <div class="tab-content" v-if="Object.values(tiaSample).length > 0 || isNewRecord">
            <Details
              ref="details"
              :tia-sample="tiaSample"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              :receivals="receivals"
              @update="() => getTiaSample(activeTab)"
              @create="() => setActiveTab(tiaSample?.id)"
            />
            <div class="middle-section border-0">
              <Actions
                :active-tab="activeTab"
                :is-edit-record-selected="!!edit"
                :is-new-record-selected="isNewRecord"
                @new="setNewRecord"
                @edit="() => setEdit(tiaSample?.id)"
                @unset="() => setActiveTab(null)"
                @store="() => details.storeRecord()"
                @update="() => details.updateRecord()"
                @delete="() => deleteTiaSample(tiaSample?.id)"
              />
            </div>
          </div>
          <div class="col-12" v-if="Object.values(tiaSample).length <= 0 && !isNewRecord">
            <p class="text-center" style="margin-top: calc(50vh - 120px)">No Records Found</p>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>

    <div
      tabindex="-1"
      data-bs-scroll="true"
      id="offcanvasWithBothOptions"
      class="offcanvas offcanvas-end"
      aria-labelledby="offcanvasWithBothOptionsLabel"
    >
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasWithBothOptionsLabel">Tia Sampling</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <p>Try scrolling the rest of the page to see this option in action.</p>
        <ul>
          <li><a href="">Unique ID <span class="fa fa-angle-right"></span> </a></li>
          <li><a href="">Name <span class="fa fa-angle-right"></span> </a></li>
          <li><a href="">Email <span class="fa fa-angle-right"></span> </a></li>
          <li><a href="">Username <span class="fa fa-angle-right"></span> </a></li>
          <li><a href="">User Access <span class="fa fa-angle-right"></span> </a></li>
        </ul>
      </div>
    </div>
  </AppLayout>
</template>
