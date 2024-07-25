<script setup>
import { reactive, ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import Details from '@/Pages/Sizing/Details.vue';
import LeftBar from '@/Components/LeftBar.vue';
import Pagination from '@/Components/Pagination.vue';
import { getCategoriesByType } from '@/helper.js';
import { useWindowSize } from 'vue-window-size';
import UnloadsModal from '@/Pages/Sizing/Partials/UnloadsModal.vue';
import AllocationsModal from '@/Pages/Sizing/Partials/AllocationsModal.vue';

const { width, height } = useWindowSize();

const props = defineProps({
  navBuyers: Object,
  allocations: Object,
  single: Object,
  filters: Object,
  errors: Object,
});

const sizings = ref(props.single || {});
const activeTab = ref(null);
const isNewRecord = ref(false);
const isNewItemRecord = ref(false);
const search = ref(props.filters.search);
const buyer = ref(props.filters.buyer);
const details = ref(null);
const selectIdentifier = ref(null);
const selection = reactive({});

watch(
  () => props?.single,
  (single) => {
    if (props.errors.length === undefined || props.errors.length <= 0) {
      sizings.value = single || [];
    }
  },
);

watch(search, (value) => {
  router.get(
    route('sizing.index'),
    { search: value, buyer: buyer.value, userId: activeTab.value.user_id },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['single'],
    },
  );
});

watch(buyer, (value) => {
  router.get(
    route('sizing.index'),
    { search: search.value, buyer: value, userId: activeTab.value.user_id },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['navBuyers', 'single'],
    },
  );
});

const filter = (keyword) => (buyer.value = keyword);

const getSizings = (id) => {
  router.get(
    route('sizing.index'),
    { userId: id },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['single'],
      onSuccess: () => {
        const newUrl = window.location.href.split('?')[0];
        history.pushState({}, null, newUrl);

        setActiveTab(id);
      },
    },
  );
};

const setActiveTab = (id) => {
  activeTab.value = props.navBuyers.find((buyer) => buyer.id === id);
  isNewRecord.value = false;
  isNewItemRecord.value = false;
};

const setNewRecord = () => {
  isNewRecord.value = true;
  isNewItemRecord.value = false;
  sizings.value.data = [];
  activeTab.value = null;
};

const setUpdateSelection = (identifier, type, id) => {
  selectIdentifier.value = identifier;
  selection[identifier] = { id, type, selected: {} };
};

if (width.value > 991) {
  setActiveTab(sizings.value.data[0]?.user_id);
}
</script>

<template>
  <AppLayout title="Sizing">
    <TopBar
      type="Sizing"
      :title="activeTab?.user?.buyer_name || 'New'"
      :active-tab="activeTab?.id"
      :search="search"
      @search="filter"
      :is-new-record-selected="isNewRecord"
      :access="{
        edit: false,
        delete: false,
      }"
      @new="setNewRecord"
      @unset="() => setActiveTab(null)"
      @store="() => details.storeRecord()"
    />

    <div class="tab-section">
      <div class="row g-0">
        <div class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block" :class="{ 'd-none': activeTab || isNewRecord }">
          <LeftBar
            :items="navBuyers"
            :active-tab="activeTab?.id"
            :row-1="{ title: 'Buyer Name', value: 'user.buyer_name' }"
            :row-2="{ title: 'Grower Name', value: 'user.grower_name' }"
            @click="getSizings"
          />
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{ 'd-none': !activeTab && !isNewRecord }">
          <div class="tab-content">
            <Details
              v-if="isNewRecord"
              ref="details"
              unique-key="newRecord"
              :is-new="true"
              :selected-allocation="selection['newRecord']?.selected || {}"
              @toSelect="(type, id) => setUpdateSelection('newRecord', type, id)"
              @create="() => setActiveTab(navBuyers[0]?.user_id)"
            />
            <template v-else>
              <div v-if="activeTab" class="user-boxes">
                <table class="table input-table mb-0">
                  <thead>
                    <tr>
                      <th class="d-none d-sm-table-cell">Grower Name</th>
                      <th>Buyer Name</th>
                      <th>Buyer Group</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="align-middle border-0">
                      <td class="pb-0 d-none d-sm-table-cell border-0">
                        <Link :href="route('users.index', { userId: activeTab?.user_id })">
                          {{ activeTab?.user?.grower_name }}
                        </Link>
                      </td>
                      <td class="pb-0 border-0">
                        <Link :href="route('users.index', { userId: activeTab?.user_id })">
                          {{ activeTab?.user?.buyer_name }}
                        </Link>
                      </td>
                      <td class="pb-0 border-0">
                        <ul v-if="getCategoriesByType(activeTab?.user?.categories, 'buyer-group').length > 0">
                          <li
                            v-for="category in getCategoriesByType(activeTab?.user?.categories, 'buyer-group')"
                            :key="category.id"
                          >
                            <a>{{ category.category.name }}</a>
                          </li>
                        </ul>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-if="activeTab" class="row align-items-center">
                <div class="col-12 col-sm-8 col-lg-7 mb-3 mb-sm-4">
                  <input v-model="search" type="text" class="form-control" placeholder="Search Sizings..." />
                </div>
                <div class="col-12 col-sm-4 col-lg-5 mb-3 mb-sm-4 text-end">
                  <button class="btn btn-black" :disabled="isNewItemRecord" @click="isNewItemRecord = true">
                    <i class="bi bi-plus-lg"></i> Add sizing
                  </button>
                </div>
              </div>
              <Details
                v-if="isNewItemRecord"
                ref="details"
                unique-key="newItemRecord"
                :sizing="{ user_id: activeTab?.user_id }"
                :is-new-item="true"
                :selected-allocation="selection['newItemRecord']?.selected || {}"
                @toSelect="(type, id) => setUpdateSelection('newItemRecord', type, id)"
                @create="() => setActiveTab(activeTab?.user_id)"
              />
              <template v-for="sizing in sizings?.data" :key="sizing.id">
                <Details
                  ref="details"
                  :unique-key="`${sizing.id}`"
                  :sizing="sizing"
                  :selected-allocation="selection[sizing.id]?.selected || {}"
                  @toSelect="(type, userId) => setUpdateSelection(sizing.id, type, userId)"
                  @delete="() => setActiveTab(sizing?.data[0]?.user_id)"
                />
              </template>
              <div class="float-end">
                <Pagination :links="sizings.links" />
              </div>
            </template>
          </div>
          <div class="col-12" v-if="sizings?.data?.length <= 0 && !isNewRecord">
            <p class="text-center" style="margin-top: calc(50vh - 120px)">No Records Found</p>
          </div>
        </div>
      </div>
    </div>

    <AllocationsModal
      :buyer-id="selection[selectIdentifier]?.type === 'allocation' ? selection[selectIdentifier]?.id : null"
      @allocation="(allocation) => (selection[selectIdentifier].selected = allocation)"
      @close="() => (selection[selectIdentifier].id = null)"
    />

    <UnloadsModal
      :grower-id="selection[selectIdentifier]?.type === 'unload' ? selection[selectIdentifier]?.id : null"
      @unload="(unload) => (selection[selectIdentifier].selected = unload)"
      @close="() => (selection[selectIdentifier].id = null)"
    />
  </AppLayout>
</template>
