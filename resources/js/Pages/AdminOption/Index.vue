<script setup>
import { computed, ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/AdminOption/Details.vue';
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";
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

const setNewRecord = () => {
  isNewRecord.value = true;
}

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
      v-if="activeTab"
      :type="title"
      :value="search"
      @search="filter"
      @newRecord="setNewRecord"
    />
    <MiddleBar
      v-if="activeTab"
      type="Admin Options"
      :title="title"
      :is-new-record-selected="isNewRecord"
      :access="{
        new: true,
        edit: false,
        delete: false,
      }"
      @newRecord="setNewRecord"
      @editRecord="() => {}"
      @deleteRecord="() => {}"
    />

    <!-- tab-section -->
    <div class="tab-section">
      <div class="row g-0">
        <div class="col-12 col-sm-4 nav-left">
          <a
            role="button"
            v-for="optionType in optionTypes"
            :key="optionType.slug"
            class="w-100 d-block position-relative"
            :class="{'active' : activeTab === optionType.slug}"
            :data-toggle="$windowWidth <= 767 ? 'modal' : 'tab'"
            :data-target="$windowWidth <= 767 ? '#user-details' : ''"
            @click="changeTab(optionType.slug)"
          >
            <table class="table mb-0">
              <tbody>
              <tr>
                <th class="border-0">{{ optionType.label }}</th>
              </tr>
              </tbody>
            </table>
            <i class="bi bi-chevron-right angle-right"></i>
          </a>
        </div>
        <div class="col-12 col-sm-8">
          <div class="tab-content">
            <div class="tab-pane active">
              <div class="row">
                <div v-if="isNewRecord" class="col-12 col-sm-6 col-md-4">
                  <Details
                    :category="{}"
                    :type="activeTab"
                    :is-new="true"
                    @updateRecord="() => isNewRecord = false"
                  />
                </div>
                <div v-for="category in categories" :key="category.id" class="col-12 col-sm-6 col-md-4">
                  <Details
                    :category="category"
                    :type="activeTab"
                    :is-new="false"
                    @updateRecord="() => isNewRecord = false"
                  />
                </div>
                <div class="col-12" v-if="categories.length <= 0 && !isNewRecord">
                  <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
                </div>
              </div>
            </div>
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
            title="Admin Options"
            :access="{
              new: true,
            }"
            @edit="() => {}"
            @delete="() => {}"
          />
          <div class="modal-body">
            <ModalBreadcrumb
              page="Admin Options"
              :title="title"
            />
            <div v-if="isNewRecord">
              <Details
                :category="{}"
                :type="activeTab"
                :is-new="true"
                @updateRecord="() => isNewRecord = false"
              />
            </div>
            <div v-for="category in categories" :key="category.id">
              <Details
                :category="category"
                :type="activeTab"
                :is-new="false"
                @updateRecord="() => isNewRecord = false"
              />
            </div>
            <div class="col-sm-12" v-if="categories.length <= 0 && !isNewRecord">
              <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
