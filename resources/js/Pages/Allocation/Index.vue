<script setup>
import { reactive, ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { useWindowSize } from 'vue-window-size';
import { getCategoriesByType } from '@/helper.js';
import TopBar from '@/Components/TopBar.vue';
import LeftBar from '@/Components/LeftBar.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Details from '@/Pages/Allocation/Details.vue';
import Pagination from '@/Components/Pagination.vue';
import ReceivalsModal from '@/Pages/Allocation/Partials/ReceivalsModal.vue';
import ReallocationModal from '@/Pages/Allocation/Partials/ReallocationModal.vue';

const { width, height } = useWindowSize();

const props = defineProps({
  allocationBuyers: Object,
  single: Object,
  filters: Object,
  errors: Object,
});

const allocations = ref(props.single || []);
const activeTab = ref(null);
const isNewRecord = ref(false);
const isNewItemRecord = ref(false);
const search = ref(props.filters.search);
const buyer = ref(props.filters.buyer);
const details = ref(null);
const selectIdentifier = ref(null);
const selection = reactive({});
const reallocate = ref([]);

watch(
  () => props?.single,
  (single) => {
    if (props.errors.length === undefined || props.errors.length <= 0) {
      allocations.value = single || [];
    }
  },
);

watch(search, (value) => {
  router.get(
    route('allocations.index'),
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
    route('allocations.index'),
    { search: search.value, buyer: value, buyerId: activeTab.value.buyer_id },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['allocationBuyers', 'single'],
    },
  );
});

const filter = (keyword) => (buyer.value = keyword);

const getAllocations = (id) => {
  router.get(
    route('allocations.index'),
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
  activeTab.value = props.allocationBuyers.find((buyer) => buyer.id === id);
  isNewRecord.value = false;
  isNewItemRecord.value = false;
};

const setNewRecord = () => {
  isNewRecord.value = true;
  isNewItemRecord.value = false;
  allocations.value.data = [];
  activeTab.value = null;
};

const setUpdateSelection = (identifier, id) => {
  selectIdentifier.value = identifier;
  selection[identifier] = { id: id, selected: {} };
};

if (width.value > 991) {
  setActiveTab(allocations.value.data[0]?.buyer_id);
}
</script>

<template>
  <AppLayout title="Allocations">
    <TopBar
      type="Allocations"
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
            :items="allocationBuyers"
            :active-tab="activeTab?.id"
            :row-1="{ title: 'Buyer Name', value: 'buyer.buyer_name' }"
            :row-2="{ title: 'Contact Name', value: 'buyer.name' }"
            @click="getAllocations"
          />
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{ 'd-none': !activeTab && !isNewRecord }">
          <div class="tab-content">
            <Details
              v-if="isNewRecord"
              ref="details"
              unique-key="newRecord"
              :is-new="true"
              :selected-receival="selection['newRecord']?.selected || {}"
              @grower="(id) => setUpdateSelection('newRecord', id)"
              @create="() => setActiveTab(allocationBuyers[0]?.buyer_id)"
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
                        <Link :href="route('users.index', { userId: activeTab?.buyer_id })">
                          {{ activeTab?.buyer?.name }}
                        </Link>
                      </td>
                      <td class="pb-0 border-0">
                        <Link :href="route('users.index', { userId: activeTab?.buyer_id })">
                          {{ activeTab?.buyer?.buyer_name }}
                        </Link>
                      </td>
                      <td class="pb-0 border-0">
                        <ul v-if="getCategoriesByType(activeTab?.buyer?.categories, 'buyer-group').length > 0">
                          <li
                            v-for="category in getCategoriesByType(activeTab?.buyer?.categories, 'buyer-group')"
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
                  <h4 class="m-0">Allocations Details</h4>
                </div>
                <div class="col-12 col-sm-4 col-lg-5 mb-3 mb-sm-4">
                  <input v-model="search" type="text" class="form-control" placeholder="Search Allocations..." />
                </div>
                <div class="col-12 col-sm-4 col-lg-4 mb-3 mb-sm-4 text-end">
                  <button
                    v-if="reallocate.length > 0"
                    class="btn btn-black me-2"
                    data-bs-toggle="modal"
                    data-bs-target="#relocate-modal"
                    v-text="'Reallocate'"
                  />
                  <button class="btn btn-black" :disabled="isNewItemRecord" @click="isNewItemRecord = true">
                    <i class="bi bi-plus-lg"></i> Add allocation
                  </button>
                </div>
              </div>
              <Details
                v-if="isNewItemRecord"
                ref="details"
                unique-key="newItemRecord"
                :allocation="{ buyer_id: activeTab?.buyer_id }"
                :is-new-item="true"
                :selected-receival="selection['newItemRecord']?.selected || {}"
                @grower="(id) => setUpdateSelection('newItemRecord', id)"
                @create="() => setActiveTab(activeTab?.buyer_id)"
              />
              <div class="position-relative" v-for="allocation in allocations?.data" :key="allocation.id">
                <Details
                  ref="details"
                  :unique-key="`${allocation.id}`"
                  :allocation="allocation"
                  :selected-receival="selection[allocation.id]?.selected || {}"
                  @grower="(growerId) => setUpdateSelection(allocation.id, growerId)"
                  @delete="() => setActiveTab(allocations?.data[0]?.buyer_id)"
                />
                <div v-if="!allocation.is_merger" class="btn-group position-absolute bottom-0 end-0">
                  <div class="form-check">
                    <input 
                      v-model="reallocate" 
                      class="form-check-input" 
                      type="checkbox" 
                      :value="allocation.id" 
                      :id="`reallocate-${allocation.id}`"
                    >
                    <label class="form-check-label" :for="`reallocate-${allocation.id}`">Reallocate&nbsp;</label>
                  </div>
                </div>
              </div>
              <div class="float-end">
                <Pagination :links="allocations.links" />
              </div>
            </template>
            <div class="col-12" v-if="allocations?.data?.length <= 0 && !isNewRecord">
              <p class="text-center" style="margin-top: calc(50vh - 120px)">No Records Found</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ReceivalsModal
      :grower-id="selection[selectIdentifier]?.id"
      @receival="(receival) => (selection[selectIdentifier].selected = receival)"
      @close="() => (selection[selectIdentifier].id = null)"
    />

    <ReallocationModal 
      :allocations="allocations?.data.filter((all) => reallocate.includes(all.id))" 
      @close="() => (reallocate = [])" 
    />
  </AppLayout>
</template>
