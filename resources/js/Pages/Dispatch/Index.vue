<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/Dispatch/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import Pagination from "@/Components/Pagination.vue";
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";
import { getCategoriesByType } from "@/helper.js";

const props = defineProps({
  dispatchBuyers: Object,
  single: Object,
  allocations: Object,
  reallocations: Object,
  buyers: Object,
  filters: Object,
  errors: Object
});

const dispatches = ref(props.single || []);
const activeTab = ref(null);
const isNewRecord = ref(false);
const isNewItemRecord = ref(false);
const search = ref(props.filters.search);

watch(() => props?.single,
  (single) => {
    if (props.errors.length === undefined || props.errors.length <= 0) {
      dispatches.value = single || [];
    }
  }
);

watch(search, (value) => {
  router.get(
    route('dispatches.index'),
    { search: value, buyerId: activeTab.value.buyer_id },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['single']
    },
  )
});

const filter = (keyword) => search.value = keyword;

const getDispatch = (id) => {
  router.get(route('dispatches.index'),
    { buyerId: id },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['single'],
      onSuccess: () => {
        const newUrl = window.location.href.split('?')[0];
        history.pushState({}, null, newUrl);

        setActiveTab(id);
      }
    }
  )
};

const setActiveTab = (id) => {
  activeTab.value = props.dispatchBuyers.find(buyer => buyer.id === id);
  isNewRecord.value = false;
  isNewItemRecord.value = false;
};

const setNewRecord = () => {
  isNewRecord.value = true;
  isNewItemRecord.value = false;
  dispatches.value.data = [];
  activeTab.value = null;
}

setActiveTab(dispatches.value.data[0]?.buyer_id);
</script>

<template>
  <AppLayout title="Dispatches">
    <TopBar
      type="Dispatches"
      :value="search"
      @search="filter"
      @newRecord="setNewRecord"
    />
    <MiddleBar
      type="Dispatches"
      :title="activeTab?.buyer?.name || 'New'"
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
      <div class="row row0">
        <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
          <LeftBar
            :items="dispatchBuyers"
            :active-tab="activeTab?.id"
            :row-1="{title: 'Buyer Name', value: 'buyer.name'}"
            :row-2="{title: 'Buyer Id', value: 'id'}"
            @click="getDispatch"
          />
        </div>
        <div class="col-lg-8 col-sm-6 tab-content">
          <Details
            v-if="isNewRecord"
            unique-key="newRecord"
            :is-new="true"
            :allocations="allocations"
            :reallocations="reallocations"
            :buyers="buyers"
            @create="() => setActiveTab(dispatchBuyers[0]?.buyer_id)"
          />
          <template v-else>
            <div class="user-boxes">
              <div class="row">
                <div class="col-sm-4">
                  <h6>Buyer Name</h6>
                  <h5>{{ activeTab?.buyer?.name }}</h5>
                </div>
                <div class="col-sm-4">
                  <h6>Buyer Group</h6>
                  <ul v-if="getCategoriesByType(activeTab?.buyer?.categories, 'buyer-group').length > 0">
                    <li v-for="category in getCategoriesByType(activeTab?.buyer?.categories, 'buyer-group')"
                        :key="category.id">
                      <a>{{ category.category.name }}</a>
                    </li>
                  </ul>
                </div>
                <div class="col-sm-4">
                  <h6>Buyer Id</h6>
                  <h5>
                    <Link :href="route('users.index', {userId: activeTab?.buyer_id})">
                      {{ activeTab?.buyer_id }}
                    </Link>
                  </h5>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-4">
                <h4>Dispatches Details</h4>
              </div>
              <div class="col-sm-4">
                <div class="form-group has-feedback">
                  <input
                    v-model="search"
                    type="text"
                    class="form-control customInput"
                    placeholder="Search dispatches..."
                  >
                </div>
              </div>
              <div class="col-sm-4 text-right">
                <a role="button" class="btn btn-red" @click="() => isNewItemRecord = true">Add new dispatch</a>
              </div>
            </div>
            <Details
              v-if="isNewItemRecord"
              unique-key="newItemRecord"
              :dispatch="{ buyer_id: activeTab?.buyer_id }"
              :is-new-item="true"
              :allocations="allocations"
              :reallocations="reallocations"
              :buyers="buyers"
              @create="() => setActiveTab(activeTab?.buyer_id)"
            />
            <template v-for="dispatch in dispatches?.data" :key="dispatch.id">
              <Details
                :unique-key="`${dispatch.id}`"
                :dispatch="dispatch"
                :allocations="allocations"
                :reallocations="reallocations"
                :buyers="buyers"
                @delete="() => setActiveTab(dispatches?.data[0]?.buyer_id)"
              />
            </template>
            <div class="col-sm-12 text-right">
              <Pagination :links="dispatches.links"/>
            </div>
          </template>
          <div class="col-sm-12" v-if="dispatches?.data?.length <= 0 && !isNewRecord">
            <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
    <!-- tab-section -->

    <!-- Modal -->
    <div
      tabindex="-1"
      role="dialog"
      id="user-details"
      class="modal right fade user-details"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <ModalHeader
            title="Dispatches"
            :access="{
              new: isNewRecord,
              edit: false,
              delete: false,
            }"
            @edit="() => {}"
            @delete="() => {}"
          />
          <div class="modal-body">
            <ModalBreadcrumb
              page="Dispatches"
              :title="dispatches?.data[0]?.buyer?.name || 'New'"
            />
            <div>
              <Details
                v-if="isNewRecord"
                unique-key="mobileNewRecord"
                :is-new="true"
                :allocations="allocations"
                :reallocations="reallocations"
                :buyers="buyers"
                @create="() => setActiveTab(dispatchBuyers[0]?.buyer_id)"
              />
              <template v-else>
                <div class="user-boxes">
                  <div class="row">
                    <div class="col-sm-4">
                      <h6>Buyer Name</h6>
                      <h5>{{ activeTab?.buyer?.name }}</h5>
                    </div>
                    <div class="col-sm-4">
                      <h6>Buyer Group</h6>
                      <ul v-if="getCategoriesByType(activeTab?.buyer?.categories, 'buyer-group').length > 0">
                        <li v-for="category in getCategoriesByType(activeTab?.buyer?.categories, 'buyer-group')"
                            :key="category.id">
                          <a>{{ category.category.name }}</a>
                        </li>
                      </ul>
                    </div>
                    <div class="col-sm-4">
                      <h6>Buyer Id</h6>
                      <h5>
                        <Link :href="route('users.index', {userId: activeTab?.buyer_id})">
                          {{ activeTab?.buyer_id }}
                        </Link>
                      </h5>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-4">
                    <h4>Dispatches Details</h4>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group has-feedback">
                      <input
                        v-model="search"
                        type="text"
                        class="form-control customInput"
                        placeholder="Search dispatches..."
                      >
                    </div>
                  </div>
                  <div class="col-sm-4 text-right">
                    <a role="button" class="btn btn-red" @click="() => isNewItemRecord = true">Add new allocation</a>
                  </div>
                </div>
                <Details
                  v-if="isNewItemRecord"
                  unique-key="mobileNewItemRecord"
                  :dispatch="{ buyer_id: activeTab?.buyer_id }"
                  :is-new-item="true"
                  :allocations="allocations"
                  :reallocations="reallocations"
                  :buyers="buyers"
                  @create="() => setActiveTab(activeTab?.buyer_id)"
                />
                <template v-for="dispatch in dispatches?.data" :key="dispatch.id">
                  <Details
                    :unique-key="`mobile-${dispatch.id}`"
                    :dispatch="dispatch"
                    :allocations="allocations"
                    :reallocations="reallocations"
                    :buyers="buyers"
                    @delete="() => setActiveTab(dispatches?.data[0]?.buyer_id)"
                  />
                </template>
                <div class="col-sm-12 text-right">
                  <Pagination :links="dispatches.links"/>
                </div>
              </template>
            </div>
            <div class="col-sm-12" v-if="dispatches?.data?.length <= 0 && !isNewRecord">
              <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
