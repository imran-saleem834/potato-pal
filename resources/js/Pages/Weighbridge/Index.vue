<script setup>
import moment from 'moment';
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import LeftBar from '@/Components/LeftBar.vue';
import { channels } from '@/const.js';
import {
  toTonnes,
  getBinSizesValue,
  getSingleCategoryNameByType,
  getCategoriesByType,
  getLabelFromItems,
} from '@/helper.js';
import { useWindowSize } from 'vue-window-size';

const { width, height } = useWindowSize();

const props = defineProps({
  unloads: Object,
  filters: Object,
});

const activeTab = ref(null);
const search = ref(props.filters.search);

watch(search, (value) => {
  router.get(route('weighbridges.index'), { search: value }, { preserveState: true, preserveScroll: true });
});

const filter = (keyword) => (search.value = keyword);

const setActiveTab = (id) => {
  activeTab.value = props.unloads.data.find((weighbridge) => weighbridge.id === id);
};

if (width.value > 991) {
  setActiveTab(props.unloads.data[0]?.id);
}
</script>

<template>
  <AppLayout title="Weighbridges">
    <TopBar
      type="Weighbridges"
      :title="`ID - ${activeTab?.id}`"
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
        <div class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block" :class="{ 'd-none': activeTab }">
          <LeftBar
            :items="unloads.data"
            :links="unloads.links"
            :active-tab="activeTab?.id"
            :row-1="{ title: 'Unload Id', value: 'id' }"
            :row-2="{ title: 'Receival Id', value: 'receival_id' }"
            @click="setActiveTab"
          />
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{ 'd-none': !activeTab }">
          <div v-if="activeTab" class="tab-content">
            <h4 class="mt-1 mb-3">Weighbridge Details</h4>
            <div class="user-boxes">
              <div class="row allocation-items-box">
                <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
                  <span>Receival ID: </span>
                  <Link :href="route('receivals.index', { receivalId: activeTab.receival_id })" class="text-primary">
                    {{ activeTab.receival_id }}
                  </Link>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
                  <span>Unload ID: </span>
                  <Link :href="route('unloading.index', { receivalId: activeTab.receival_id })" class="text-primary">
                    {{ activeTab.id }}
                  </Link>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
                  <span>Total Bins: </span>
                  <span class="text-primary">{{ activeTab.no_of_bins }}</span>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
                  <span>Total Weight: </span>
                  <span class="text-primary">{{ toTonnes(activeTab.weight) }}</span>
                </div>
              </div>
            </div>

            <div v-for="weighbridge in activeTab?.weighbridges" :key="weighbridge.id" class="user-boxes">
              <div class="row allocation-items-box">
                <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
                  <span>Unload Time: </span>
                  <span class="text-primary">
                    {{ moment(weighbridge.created_at).format('DD/MM/YYYY hh:mm A') }}
                  </span>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
                  <span>Unload By: </span>
                  <span class="text-primary">{{ weighbridge.creator?.name }}</span>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
                  <span>Channel: </span>
                  <span class="text-primary">
                    {{ getLabelFromItems(channels, weighbridge.channel) }}
                  </span>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
                  <span>System: </span>
                  <span class="text-primary">
                    {{ `System ${weighbridge.system}` }}
                  </span>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
                  <span>Seed Type: </span>
                  <span class="text-primary">
                    <template v-if="getCategoriesByType(activeTab.categories, 'seed-type').length">
                      {{ getSingleCategoryNameByType(activeTab.categories, 'seed-type') }}
                    </template>
                    <template v-else>-</template>
                  </span>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
                  <span>No. of bins weighted at a time: </span>
                  <span class="text-primary">
                    <template v-if="weighbridge.no_of_bins">{{ weighbridge.no_of_bins }}</template>
                    <template v-else>-</template>
                  </span>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
                  <span>Bin Size: </span>
                  <span class="text-primary">{{ getBinSizesValue(weighbridge.bin_size) }}</span>
                </div>
                <div class="col-12 col-sm-4 col-md-3 col-lg-4 col-xl-3 mb-1 pb-1">
                  <span>Weight of total bins: </span>
                  <span class="text-primary">
                    <template v-if="weighbridge.weight">
                      {{ toTonnes(weighbridge.weight) }}
                    </template>
                    <template v-else>-</template>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div v-if="Object.values(unloads.data).length <= 0">
            <p class="w-100 text-center" style="margin-top: calc(50vh - 120px)">No Records Found</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
