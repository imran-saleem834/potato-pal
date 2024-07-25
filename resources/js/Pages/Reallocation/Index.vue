<script setup>
import { reactive, ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import Details from '@/Pages/Reallocation/Details.vue';
import LeftBar from '@/Components/LeftBar.vue';
import Pagination from '@/Components/Pagination.vue';
import { getCategoriesByType } from '@/helper.js';
import { useWindowSize } from 'vue-window-size';
import CuttingsModal from '@/Pages/Reallocation/Partials/CuttingsModal.vue';

const { width, height } = useWindowSize();

const props = defineProps({
  reallocationBuyers: Array,
  single: Object,
  filters: Object,
  errors: Object,
});

const reallocations = ref(props.single || []);
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
      reallocations.value = single || [];
    }
  },
);

watch(search, (value) => {
  router.get(
    route('reallocations.index'),
    { search: value, buyer: buyer.value, buyerId: activeTab.value.buyer_id },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['single'],
    },
  );
});

watch(buyer, (value) => {
  router.get(
    route('reallocations.index'),
    { search: search.value, buyer: value, buyerId: activeTab.value.buyer_id },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['reallocationBuyers', 'single'],
    },
  );
});

const filter = (keyword) => (buyer.value = keyword);

const getReallocations = (id) => {
  router.get(
    route('reallocations.index'),
    { buyerId: id },
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
  activeTab.value = props.reallocationBuyers.find((buyer) => buyer.id === id);
  isNewRecord.value = false;
  isNewItemRecord.value = false;
};

const setNewRecord = () => {
  isNewRecord.value = true;
  isNewItemRecord.value = false;
  reallocations.value.data = [];
  activeTab.value = null;
};

const setUpdateSelection = (identifier, id) => {
  selectIdentifier.value = identifier;
  selection[identifier] = { id: id, selected: {} };
};

if (width.value > 991) {
  setActiveTab(reallocations.value.data[0]?.buyer_id);
}
</script>

<template>
  <AppLayout title="Reallocations">
    <TopBar
      type="Reallocations"
      :title="activeTab?.buyer?.buyer_name || 'New'"
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
            :items="reallocationBuyers"
            :active-tab="activeTab?.id"
            :row-1="{ title: 'Buyer Name', value: 'buyer.buyer_name' }"
            :row-2="{ title: 'Contact Name', value: 'buyer.name' }"
            @click="getReallocations"
          />
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{ 'd-none': !activeTab && !isNewRecord }">
          <div class="tab-content">
            <Details
              v-if="isNewRecord"
              ref="details"
              unique-key="newRecord"
              :is-new="true"
              :selected-cutting="selection['newRecord']?.selected || {}"
              @cutting="(id) => setUpdateSelection('newRecord', id)"
              @create="() => setActiveTab(reallocations[0]?.buyer_id)"
            />
            <template v-else>
              <div v-if="activeTab" class="user-boxes">
                <table class="table input-table mb-0">
                  <thead>
                    <tr>
                      <th class="d-none d-sm-table-cell">Contact Name</th>
                      <th>Buyer Name</th>
                      <th>Buyer Group</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="align-middle border-0">
                      <td class="pb-0 d-none d-sm-table-cell border-0">
                        <Link :href="route('users.index', { userId: activeTab.buyer_id })">
                          {{ activeTab?.buyer?.name }}
                        </Link>
                      </td>
                      <td class="pb-0 border-0">
                        <Link :href="route('users.index', { userId: activeTab.buyer_id })">
                          {{ activeTab.buyer?.buyer_name }}
                        </Link>
                      </td>
                      <td class="pb-0 border-0">
                        <ul v-if="getCategoriesByType(activeTab.buyer?.categories, 'buyer-group').length > 0">
                          <li
                            v-for="category in getCategoriesByType(activeTab.buyer.categories, 'buyer-group')"
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
                <div class="col-12 col-sm-4 col-lg-3 mb-3 mb-sm-4">
                  <h4 class="m-0">Reallocations Details</h4>
                </div>
                <div class="col-12 col-sm-4 col-lg-5 mb-3 mb-sm-4">
                  <input v-model="search" type="text" class="form-control" placeholder="Search Reallocations..." />
                </div>
                <div class="col-12 col-sm-4 col-lg-4 mb-3 mb-sm-4 text-end">
                  <button class="btn btn-black" :disabled="isNewItemRecord" @click="isNewItemRecord = true">
                    <i class="bi bi-plus-lg"></i> Add reallocation
                  </button>
                </div>
              </div>
              <Details
                v-if="isNewItemRecord"
                ref="details"
                unique-key="newItemRecord"
                :reallocation="{ buyer_id: activeTab?.buyer_id }"
                :is-new-item="true"
                :selected-cutting="selection['newItemRecord']?.selected || {}"
                @cutting="(id) => setUpdateSelection('newItemRecord', id)"
                @create="() => setActiveTab(activeTab?.buyer_id)"
              />
              <template v-for="reallocation in reallocations?.data" :key="reallocation.id">
                <Details
                  ref="details"
                  :unique-key="`${reallocation.id}`"
                  :reallocation="reallocation"
                  :selected-cutting="selection[reallocation.id]?.selected || {}"
                  @cutting="(buyerId) => setUpdateSelection(reallocation.id, buyerId)"
                  @delete="() => setActiveTab(reallocations?.data[0]?.buyer_id)"
                />
              </template>
              <div class="float-end">
                <Pagination :links="reallocations.links" />
              </div>
            </template>
          </div>
          <div class="col-12" v-if="reallocations?.data.length <= 0 && !isNewRecord">
            <p class="text-center" style="margin-top: calc(50vh - 120px)">No Records Found</p>
          </div>
        </div>
      </div>
    </div>

    <CuttingsModal
      :buyer-id="selection[selectIdentifier]?.id"
      @cutting="(cutting) => (selection[selectIdentifier].selected = cutting)"
      @close="() => (selection[selectIdentifier].id = null)"
    />
  </AppLayout>
</template>
