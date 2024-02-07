<script setup>
import { computed, ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import LeftBar from "@/Components/LeftBar.vue";
import { getBinSizesValue } from "@/tonnes.js";
import {
  getSingleCategoryNameByType,
  getCategoriesByType,
} from "@/helper.js";
import { useWindowSize } from 'vue-window-size';

const { width, height } = useWindowSize();

const props = defineProps({
  weighbridges: Object,
  filters: Object,
  errors: Object
});

const user = ref(props.single || {});
const activeTab = ref(null);
const search = ref(props.filters.search);

watch(search, (value) => {
  router.get(
    route('weighbridges.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  )
});

const filter = (keyword) => search.value = keyword;

const setActiveTab = (id) => {
  activeTab.value = props.weighbridges.data.find(weighbridge => weighbridge.id === id);
}

const isOversize = computed(() => {
  return getSingleCategoryNameByType(activeTab.value.categories, 'seed-type') === 'Oversize';
})

if (width.value > 991) {
  setActiveTab(props.weighbridges.data[0]?.id);
}
</script>

<template>
  <AppLayout title="Weighbridges">
    <TopBar
      type="Weighbridges"
      :title="`${activeTab?.id}`"
      :active-tab="activeTab?.id"
      :search="search"
      @search="filter"
      :access="{
        new: false,
        edit: false,
        delete: false,
      }"
      @unset="() => setActiveTab(null)"
    />

    <div class="tab-section">
      <div class="row g-0">
        <div class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block" :class="{'d-none' : activeTab}">
          <LeftBar
            :items="weighbridges.data"
            :links="weighbridges.links"
            :active-tab="activeTab?.id"
            :row-1="{title: 'Weighbridge Id', value: 'id'}"
            :row-2="{title: 'Receival Id', value: 'receival_id'}"
            @click="setActiveTab"
          />
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{'d-none': !activeTab}">
          <div class="tab-content" v-if="activeTab">
            <div class="row">
              <div class="col-12">
                <h4>Weighbridge Details</h4>
                <div class="user-boxes">
                  <table class="table input-table mb-0">
                    <tr>
                      <th>Receival ID</th>
                      <td>
                        <Link :href="route('receivals.index', {receivalId: activeTab?.receival_id})">
                          {{ activeTab?.receival_id }}
                        </Link>
                      </td>
                    </tr>
                    <tr>
                      <th>Unload ID</th>
                      <td>
                        <Link :href="route('unloading.index', {receivalId: activeTab?.receival_id})">
                          {{ activeTab?.receival_id }}
                        </Link>
                      </td>
                    </tr>
                    <tr>
                      <th>Seed Type</th>
                      <td class="pb-0">
                        <ul class="p-0" v-if="getCategoriesByType(activeTab.categories, 'seed-type').length">
                          <li><a>{{ getSingleCategoryNameByType(activeTab.categories, 'seed-type') }}</a></li>
                        </ul>
                        <template v-else>-</template>
                      </td>
                    </tr>
                    <tr v-if="activeTab.channel && !isOversize">
                      <th>Channel</th>
                      <td class="pb-0">
                        <ul class="p-0"> 
                          <li>
                            <a>{{ activeTab.channel === 'weighbridge' ? 'BU1' : activeTab.channel.toUpperCase() }}</a>
                          </li>
                        </ul>
                      </td>
                    </tr>
                    <tr>
                      <th>Bin Size</th>
                      <td class="pb-0">
                        <ul class="p-0" v-if="activeTab.bin_size">
                          <li><a>{{ getBinSizesValue(activeTab.bin_size) }}</a></li>
                        </ul>
                        <template v-else>-</template>
                      </td>
                    </tr>
                    <tr v-if="activeTab.system && !isOversize">
                      <th>System</th>
                      <td class="pb-0">
                        <ul class="p-0"><li><a>{{ `System ${activeTab.system}` }}</a></li></ul>
                      </td>
                    </tr>
                    <tr>
                      <th>No. of total bins</th>
                      <td>
                        <template v-if="activeTab.no_of_bins">{{ activeTab.no_of_bins }}</template>
                        <template v-else>-</template>
                      </td>
                    </tr>
                    <tr>
                      <th>Weight of total bins</th>
                      <td>
                        <template v-if="activeTab.weight">{{ activeTab.weight }} Kg</template>
                        <template v-else>-</template>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div v-if="Object.values(weighbridges.data).length <= 0">
            <p class="w-100 text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
