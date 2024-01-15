<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/Allocation/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import Pagination from "@/Components/Pagination.vue";
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";
import { getCategoriesByType } from "@/helper.js";

const props = defineProps({
  allocationBuyers: Object,
  single: Object,
  growers: Object,
  buyers: Object,
  filters: Object,
  errors: Object
});

const allocations = ref(props.single || []);
const activeTab = ref(null);
const isNewRecord = ref(false);
const isNewItemRecord = ref(false);
const search = ref(props.filters.search);

watch(() => props?.single,
  (single) => {
    if (props.errors.length === undefined || props.errors.length <= 0) {
      allocations.value = single || [];
    }
  }
);

watch(search, (value) => {
  router.get(
    route('allocations.index'),
    { search: value, buyerId: activeTab.value.buyer_id },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['single']
    },
  )
});

const filter = (keyword) => search.value = keyword;

const getAllocations = (id) => {
  router.get(route('allocations.index'),
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
  activeTab.value = props.allocationBuyers.find(buyer => buyer.id === id);
  isNewRecord.value = false;
  isNewItemRecord.value = false;
};

const setNewRecord = () => {
  isNewRecord.value = true;
  isNewItemRecord.value = false;
  allocations.value.data = [];
  activeTab.value = null;
}

setActiveTab(allocations.value.data[0]?.buyer_id);
</script>

<template>
  <AppLayout title="Allocations">
    <TopBar
      type="Allocations"
      :value="search"
      @search="filter"
      @newRecord="setNewRecord"
    />
    <MiddleBar
      type="Allocations"
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
            :items="allocationBuyers"
            :active-tab="activeTab?.id"
            :row-1="{title: 'Buyer Name', value: 'buyer.name'}"
            :row-2="{title: 'Buyer Id', value: 'id'}"
            @click="getAllocations"
          />
        </div>
        <div class="col-lg-8 col-sm-6 tab-content">
          <Details
            v-if="isNewRecord"
            unique-key="newRecord"
            :is-new="true"
            :growers="growers"
            :buyers="buyers"
            @create="() => setActiveTab(allocationBuyers[0]?.buyer_id)"
          />
          <template v-else>
            <div v-if="activeTab" class="user-boxes">
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
            <div v-if="activeTab" class="row">
              <div class="col-sm-4">
                <h4>Allocations Details</h4>
              </div>
              <div class="col-sm-4">
                <div class="form-group has-feedback">
                  <input
                    v-model="search"
                    type="text"
                    class="form-control customInput"
                    placeholder="Search Allocations..."
                  >
                </div>
              </div>
              <div class="col-sm-4 text-right">
                <a role="button" class="btn btn-red" @click="() => isNewItemRecord = true">Add new allocation</a>
              </div>
            </div>
            <Details
              v-if="isNewItemRecord"
              unique-key="newItemRecord"
              :allocation="{ buyer_id: activeTab?.buyer_id }"
              :is-new-item="true"
              :growers="growers"
              :buyers="buyers"
              @create="() => setActiveTab(activeTab?.buyer_id)"
            />
            <template v-for="allocation in allocations?.data" :key="allocation.id">
              <Details
                :unique-key="`${allocation.id}`"
                :allocation="allocation"
                :growers="growers"
                :buyers="buyers"
                @delete="() => setActiveTab(allocations?.data[0]?.buyer_id)"
              />
            </template>
            <div class="col-sm-12 text-right">
              <Pagination :links="allocations.links"/>
            </div>
          </template>
          <div class="col-sm-12" v-if="allocations?.data?.length <= 0 && !isNewRecord">
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
            title="Allocations"
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
              page="Allocations"
              :title="allocations?.data[0]?.buyer?.name || 'New'"
            />
            <div>
              <Details
                v-if="isNewRecord"
                unique-key="mobileNewRecord"
                :is-new="true"
                :growers="growers"
                :buyers="buyers"
                @create="() => setActiveTab(allocationBuyers[0]?.buyer_id)"
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
                    <h4>Allocations Details</h4>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group has-feedback">
                      <input
                        v-model="search"
                        type="text"
                        class="form-control customInput"
                        placeholder="Search Allocations..."
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
                  :allocation="{ buyer_id: activeTab?.buyer_id }"
                  :is-new-item="true"
                  :growers="growers"
                  :buyers="buyers"
                  @create="() => setActiveTab(activeTab?.buyer_id)"
                />
                <template v-for="allocation in allocations?.data" :key="allocation.id">
                  <Details
                    :unique-key="`mobile-${allocation.id}`"
                    :allocation="allocation"
                    :growers="growers"
                    :buyers="buyers"
                    @delete="() => setActiveTab(allocations?.data[0]?.buyer_id)"
                  />
                </template>
                <div class="col-sm-12 text-right">
                  <Pagination :links="allocations.links"/>
                </div>
              </template>
            </div>
            <div class="col-sm-12" v-if="allocations?.data?.length <= 0 && !isNewRecord">
              <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
