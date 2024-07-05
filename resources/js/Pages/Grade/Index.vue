<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import Details from '@/Pages/Grade/Details.vue';
import LeftBar from '@/Components/LeftBar.vue';
import { useToast } from 'vue-toastification';
import { useWindowSize } from 'vue-window-size';

const toast = useToast();
const { width, height } = useWindowSize();

const props = defineProps({
  grades: Object,
  single: Object,
  unloads: Object,
  routeName: String,
  filters: Object,
  errors: Object,
});

const grade = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);
const details = ref(null);

watch(
  () => props?.single,
  (single) => {
    if (
      Object.values(props.errors).length === undefined ||
      Object.values(props.errors).length <= 0
    ) {
      grade.value = single || {};
    }
  },
);

watch(search, (value) => {
  router.get(
    route(`${props.routeName}.index`),
    { search: value },
    { preserveState: true, preserveScroll: true },
  );
});

const filter = (keyword) => (search.value = keyword);

const getGrade = (id) => {
  axios.get(route(`${props.routeName}.show`, id)).then((response) => {
    grade.value = response.data;

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
  grade.value = {};
  activeTab.value = null;
};

const deleteGrade = (id) => {
  const form = useForm({});
  form.delete(route(`${props.routeName}.destroy`, id), {
    preserveState: true,
    onSuccess: () => {
      setActiveTab(grade.value?.id);
      toast.success('The grade has been deleted successfully!');
    },
  });
};

const title = computed(() => {
  if (props.routeName === 'grading') {
    return 'Grading';
  } else if (props.routeName === 'sizing') {
    return 'Sizing';
  } else if (props.routeName === 'chemical-applicant') {
    return 'Chemical Applicant';
  } else {
    return 'Bulk Bagging';
  }
});

if (width.value > 991) {
  setActiveTab(grade.value?.id);
}
</script>

<template>
  <AppLayout :title="title">
    <TopBar
      :type="title"
      :title="grade?.grower?.grower_name || 'New'"
      :active-tab="activeTab"
      :search="search"
      @search="filter"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      @new="setNewRecord"
      @edit="() => setEdit(grade?.id)"
      @unset="() => setActiveTab(null)"
      @store="() => details.storeRecord()"
      @update="() => details.updateRecord()"
      @delete="() => deleteGrade(grade?.id)"
    />

    <div class="tab-section">
      <div class="row g-0">
        <div
          class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block"
          :class="{ 'd-none': activeTab || isNewRecord }"
        >
          <LeftBar
            :items="grades.data"
            :links="grades.links"
            :active-tab="activeTab"
            :row-1="{ title: 'Grower', value: 'unload.receival.grower.grower_name' }"
            :row-2="{ title: `${title} ID`, value: 'id' }"
            @click="getGrade"
          />
        </div>
        <div
          class="col-12 col-lg-7 col-xl-8 d-lg-block"
          :class="{ 'd-none': !activeTab && !isNewRecord }"
        >
          <div class="tab-content" v-if="Object.values(grade).length > 0 || isNewRecord">
            <Details
              ref="details"
              :grade="grade"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              :unloads="unloads"
              :route-name="routeName"
              @update="() => getGrade(activeTab)"
              @create="() => setActiveTab(grade?.id)"
              @unset="() => setActiveTab(null)"
              col-size="col-12 col-xl-6"
            />
          </div>
          <div v-if="Object.values(grade).length <= 0 && !isNewRecord">
            <p class="w-100 text-center" style="margin-top: calc(50vh - 120px)">No Records Found</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
