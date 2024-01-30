<script setup>
import { ref, watch } from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import Details from '@/Pages/Note/Details.vue';
import LeftBar from "@/Pages/Note/LeftBar.vue";
import { router, useForm } from "@inertiajs/vue3";

const props = defineProps({
  notes: Object,
  single: Object,
  filters: Object,
  errors: Object,
});

const note = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);
const details = ref(null);

watch(() => props?.single,
  (single) => {
    if (Object.values(props.errors).length === undefined || Object.values(props.errors).length <= 0) {
      note.value = single || {};
    }
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
      :title="note?.title || 'New'"
      :active-tab="activeTab"
      :search="search"
      @search="filter"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      @new="setNewRecord"
      @edit="() => setEdit(note?.id)"
      @unset="() => setActiveTab(null)"
      @store="() => details.storeRecord()"
      @update="() => details.updateRecord()"
      @delete="() => deleteNote(note?.id)"
    />

    <div class="tab-section">
      <div class="row g-0">
        <div class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block" :class="{'d-none' : activeTab || isNewRecord}">
          <LeftBar
            :items="notes"
            :active-tab="activeTab"
            :row-1="{title: 'Title', value: 'title'}"
            :row-2="{title: 'Note Id', value: 'id'}"
            @click="getNote"
          />
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{'d-none': !activeTab && !isNewRecord}">
          <div class="tab-content" v-if="Object.values(note).length > 0 || isNewRecord">
            <Details
              ref="details"
              :note="note"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              @update="() => getNote(activeTab)"
              @create="() => setActiveTab(note?.id)"
            />
          </div>
          <div class="col-12" v-if="Object.values(note).length <= 0 && !isNewRecord">
            <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
