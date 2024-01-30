<script setup>
import moment from 'moment';
import { ref, watch } from "vue";
import { router, useForm } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import Details from "@/Pages/File/Details.vue";
import VueEasyLightbox from "vue-easy-lightbox";

const toast = useToast();

const props = defineProps({
  files: Object,
  filters: Object,
});

const search = ref(props.filters.search);
const flatFiles = ref(Object.values(props.files).flat(2));
const file = ref(flatFiles.value[0] || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const visibleRef = ref(false);
const indexRef = ref(0);

watch(() => props.files,
  (newFiles) => {
    flatFiles.value = Object.values(newFiles).flat(2);
    const selectedFileId = edit.value ? file.value.id : flatFiles.value[0]?.id;
    file.value = flatFiles.value.find(f => f.id === selectedFileId) || {};
  }
);

watch(search, (value) => {
  router.get(
    route('files.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  )
});

const filter = (keyword) => search.value = keyword;

const setActiveTab = (id) => {
  file.value = flatFiles.value.find(f => f.id === id) || {};
  edit.value = null;
  isNewRecord.value = false;
  activeTab.value = file.value?.id;
};

const setEdit = (id) => {
  edit.value = edit.value === id ? null : id;
  isNewRecord.value = false;
}

const setNewRecord = () => {
  isNewRecord.value = true;
  edit.value = null;
  file.value = {};
  activeTab.value = null;
}

const showImg = () => {
  indexRef.value = 0;
  visibleRef.value = true;
};

const onHide = () => visibleRef.value = false;

const form = useForm({
  title: isNewRecord ? '' : file.value.title,
  image: '',
});

const deleteFile = (id) => {
  edit.value = null;
  form.delete(route('files.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      toast.success('The file has been deleted successfully!');
    },
  });
}

setActiveTab(file.value?.id);
</script>

<template>
  <AppLayout title="Files">
    <TopBar
      type="Files"
      :title="file?.title || 'New'"
      :active-tab="activeTab"
      :search="search"
      @search="filter"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      @new="setNewRecord"
      @edit="() => setEdit(file?.id)"
      @unset="() => setActiveTab(null)"
      @store="() => details.storeRecord()"
      @update="() => details.updateRecord()"
      @delete="() => deleteFile(file?.id)"
    />

    <!-- tab-section -->
    <div class="tab-section files-section">
      <div class="row g-0">
        <div class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block" :class="{'d-none' : activeTab || isNewRecord}">
          <div class="files-left">
            <template v-for="(images, date) in files" :key="date">
              <h5>{{ moment(date).format('DD, MMM YYYY') }}</h5>
              <ul>
                <li v-for="image in images" :key="image.id">
                  <img
                    style="cursor: pointer"
                    :src="`storage/${image.image}`"
                    :alt="image.title"
                    :data-toggle="$windowWidth <= 767 ? 'modal' : 'tab'"
                    :data-target="$windowWidth <= 767 ? '#user-details' : ''"
                    @click="() => setActiveTab(image.id)"
                  />
                </li>
              </ul>
            </template>
            <ul v-if="files.length <= 0" class="list-unstyled text-center" style="margin-top: calc(50vh - 120px);">
              <li>No Records Found</li>
            </ul>
          </div>
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{'d-none': !activeTab && !isNewRecord}">
          <div class="tab-content slider-files" v-if="Object.values(file).length > 0 || isNewRecord">
            <Details
              :file="file"
              :flat-files="flatFiles"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              @index="(index) => file = flatFiles[index]"
              @update="() => edit = null"
              @create="() => isNewRecord = false"
              @showImg="showImg"
            />
          </div>
          <div class="col-12" v-if="Object.values(file).length <= 0 && !isNewRecord">
            <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>

    <vue-easy-lightbox
      :visible="visibleRef"
      :imgs="[`storage/${file.image}`]"
      :index="indexRef"
      @hide="onHide"
    />
  </AppLayout>
</template>
