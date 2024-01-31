<script setup>
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import Details from '@/Pages/AdminOption/Details.vue';
import { router } from "@inertiajs/vue3";

const props = defineProps({
  categories: Array,
  optionTypes: Array,
  filters: Object
});

const activeTab = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);

const setActiveTab = (id) => {
  activeTab.value = id;
  isNewRecord.value = false;
};

const setNewRecord = () => isNewRecord.value = true;

const changeTab = (type) => {
  setActiveTab(type);
  router.get(
    route('categories.index'),
    { search: search.value, type: activeTab.value },
    { preserveState: true, preserveScroll: true },
  )
}

watch(search, (value) => {
  router.get(
    route('categories.index'),
    { search: value, type: activeTab.value },
    { preserveState: true, preserveScroll: true },
  )
});

const filter = (keyword) => search.value = keyword;

const title = computed(() => {
  return props.optionTypes.find(option => option.slug === activeTab.value)?.label;
})

setActiveTab(props.filters.type);
</script>

<template>
  <AppLayout :title="`Admin ${title} Options`">
    <TopBar
      type="Admin Options"
      :title="title"
      :active-tab="activeTab"
      :search="search"
      @search="filter"
      :is-new-record-selected="isNewRecord"
      :access="{
        new: !!activeTab,
        edit: false,
        delete: false,
      }"
      @new="setNewRecord"
      @unset="() => setActiveTab(null)"
    />

    <div class="tab-section">
      <div class="row g-0">
        <div class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block" :class="{'d-none' : activeTab || isNewRecord}">
          <a
            role="button"
            v-for="optionType in optionTypes"
            :key="optionType.slug"
            class="w-100 d-block position-relative"
            :class="{'active' : activeTab === optionType.slug}"
            @click="changeTab(optionType.slug)"
          >
            <table class="table table-borderless mb-0">
              <tbody>
              <tr><th>{{ optionType.label }}</th></tr>
              </tbody>
            </table>
            <i class="bi bi-chevron-right angle-right"></i>
          </a>
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{'d-none': !activeTab && !isNewRecord}">
          <div class="tab-content">
            <div class="row">
              <div v-if="isNewRecord" class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4">
                <Details
                  :type="activeTab"
                  :is-new="true"
                  @updateRecord="() => isNewRecord = false"
                />
              </div>
              <div v-for="category in categories" :key="category.id" class="col-12 col-sm-6 col-md-4 col-lg-6 col-xl-4">
                <Details
                  :category="category"
                  :type="activeTab"
                  @updateRecord="() => isNewRecord = false"
                />
              </div>
              <div class="col-12" v-if="categories.length <= 0 && !isNewRecord">
                <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
              </div>
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
  </AppLayout>
</template>
