<script setup>
import { ref, watch } from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/Note/Details.vue';
import LeftBar from "@/Pages/Note/LeftBar.vue";
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";
import { router, useForm } from "@inertiajs/vue3";

const props = defineProps({
  notes: Object,
  single: Object,
  filters: Object,
});

const note = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);

watch(() => props?.single,
  (single) => {
    note.value = single || {};
  }
);

watch(search, (value) => {
  router.get(
    route('notes.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  )
});

const filter = (keyword) => search.value = keyword;

const getNote = (id) => {
  axios.get(route('notes.show', id)).then(response => {
    note.value = response.data;

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
  note.value = {};
  activeTab.value = null;
}

const deleteNote = (id) => {
  const form = useForm({});
  form.delete(route('notes.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      setActiveTab(note.value?.id);
    },
  });
}

setActiveTab(note.value?.id);
</script>

<template>
  <AppLayout title="Notes">
    <TopBar
      type="Notes"
      :value="search"
      @search="filter"
      @newRecord="setNewRecord"
    />
    <MiddleBar
      type="Notes"
      :title="note?.title || 'New'"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      :access="{
        new: true,
        edit: Object.values(note).length > 0,
        delete: Object.values(note).length > 0,
      }"
      @newRecord="setNewRecord"
      @editRecord="() => setEdit(note?.id)"
      @deleteRecord="() => deleteNote(note?.id)"
    />

    <!-- tab-section -->
    <div class="tab-section">
      <div class="row row0">
        <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
          <LeftBar
            :items="notes"
            :active-tab="activeTab"
            :row-1="{title: 'Title', value: 'title'}"
            :row-2="{title: 'Note Id', value: 'id'}"
            @click="getNote"
          />
        </div>
        <div class="col-lg-8 col-sm-6">
          <div class="tab-content" v-if="Object.values(note).length > 0 || isNewRecord">
            <div class="tab-pane active">
              <Details
                :note="note"
                :is-edit="!!edit"
                :is-new="isNewRecord"
                @update="() => getNote(activeTab)"
                @create="() => setActiveTab(note?.id)"
              />
            </div>
          </div>
          <div class="col-sm-12" v-if="Object.values(note).length <= 0 && !isNewRecord">
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
            title="Notes"
            :access="{
              new: isNewRecord
            }"
            @edit="() => setEdit(note?.id)"
            @delete="() => deleteNote(note?.id)"
          />
          <div class="modal-body">
            <ModalBreadcrumb
              page="Notes"
              :title="note?.title || 'New'"
            />
            <Details
              :note="note"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              @update="() => getNote(activeTab)"
              @create="() => setActiveTab(note?.id)"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
