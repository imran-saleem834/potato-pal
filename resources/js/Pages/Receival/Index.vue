<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/Receival/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";

const props = defineProps({
  receivals: Object,
  single: Object,
  users: Object,
  categories: Object,
  filters: Object,
});

const receival = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);

watch(() => props?.single,
  (single) => {
    receival.value = single || {};
  }
);

watch(search, (value) => {
  router.get(
    route('receivals.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  )
});

const filter = (keyword) => search.value = keyword;

const getReceival = (id) => {
  axios.get(route('receivals.show', id)).then(response => {
    receival.value = response.data;

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
  receival.value = {};
  activeTab.value = null;
}

const deleteReceival = (id) => {
  const form = useForm({});
  form.delete(route('receivals.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      setActiveTab(receival.value?.id);
    },
  });
}

setActiveTab(receival.value?.id);
</script>

<template>
  <AppLayout title="Receivals">
    <TopBar
      type="Receivals"
      :value="search"
      @search="filter"
      @newRecord="setNewRecord"
    />
    <MiddleBar
      type="Receivals"
      :title="receival?.grower?.name || 'New'"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      :access="{
        new: true,
        edit: Object.values(receival).length > 0,
        delete: Object.values(receival).length > 0,
      }"
      @newRecord="setNewRecord"
      @editRecord="() => setEdit(receival?.id)"
      @deleteRecord="() => deleteReceival(receival?.id)"
    />

    <!-- tab-section -->
    <div class="tab-section">
      <div class="row row0">
        <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
          <LeftBar
            :items="receivals"
            :active-tab="activeTab"
            :row-1="{title: 'Grower\'s Name', value: 'grower.name'}"
            :row-2="{title: 'Receival Id', value: 'id'}"
            @click="getReceival"
          />
        </div>
        <div class="col-lg-8 col-sm-6">
          <div class="tab-content" v-if="Object.values(receival).length > 0 || isNewRecord">
            <div class="tab-pane active">
              <Details
                :receival="receival"
                :is-edit="!!edit"
                :is-new="isNewRecord"
                :users="users"
                :categories="categories"
                @update="() => getReceival(activeTab)"
                @create="() => setActiveTab(receival?.id)"
                col-size="col-md-6"
              />
            </div>
          </div>
          <div class="col-sm-12" v-if="Object.values(receival).length <= 0 && !isNewRecord">
            <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
    <!-- tab-section -->

    <!-- Modal -->
    <div class="modal right fade user-details" id="user-details" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel3">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <ModalHeader
            title="Receivals"
            :access="{
              new: isNewRecord
            }"
            @edit="() => setEdit(receival?.id)"
            @delete="() => deleteReceival(receival?.id)"
          />
          <div class="modal-body" v-if="receival">
            <ModalBreadcrumb
              page="Receivals"
              :title="receival?.grower?.name || 'New'"
            />
            <Details
              :receival="receival"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              :users="users"
              :categories="categories"
              @update="() => getReceival(activeTab)"
              @create="() => setActiveTab(receival?.id)"
              col-size="col-md-12"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
