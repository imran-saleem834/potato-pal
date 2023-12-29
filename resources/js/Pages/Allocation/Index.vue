<script setup>
import { computed, ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/Allocation/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
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
const searchAllocations = ref('');

watch(() => props?.single,
  (single) => {
    if (props.errors.length === undefined ||props.errors.length <= 0) {
      allocations.value = single || [];
    }
  }
);

watch(search, (value) => {
  router.get(
    route('allocations.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  )
});

const filter = (keyword) => search.value = keyword;

const getAllocations = (id) => {
  axios.get(route('allocations.show', id)).then(response => {
    allocations.value = response.data;

    // Reset URL
    const newUrl = window.location.href.split('?')[0];
    history.pushState({}, null, newUrl);

    // Set Active Tab
    setActiveTab(response.data[0]?.buyer_id);
  });
};

const setActiveTab = (id) => {
  activeTab.value = id;
  isNewRecord.value = false;
  isNewItemRecord.value = false;
};

const setNewRecord = () => {
  isNewRecord.value = true;
  isNewItemRecord.value = false;
  allocations.value = [];
  activeTab.value = null;
}

const filterRecord = computed(() => {
  if (searchAllocations.value === '') {
    return allocations.value;
  }

  const keyword = searchAllocations.value.toLowerCase();
  return allocations.value.filter(allocation => {
    if (allocation.paddock.toLowerCase().includes(keyword)) {
      return true;
    }
    if (allocation.grower?.name.toLowerCase().includes(keyword)) {
      return true;
    }
    if (`${allocation.bin_size} Tonnes`.toLowerCase().includes(keyword)) {
      return true;
    }
    if (`${allocation.no_of_bins}`.toLowerCase().includes(keyword)) {
      return true;
    }
    if (`${allocation.weight} Tonnes`.toLowerCase().includes(keyword)) {
      return true;
    }
    for (let i = 0; i < allocation.categories.length; i++) {
      if (allocation.categories[i].category.name.toLowerCase().includes(keyword)) {
        return true;
      }
    }
    return false;
  });
});

setActiveTab(allocations.value[0]?.buyer_id);
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
      :title="allocations[0]?.buyer?.name || 'New'"
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
            :active-tab="activeTab"
            :row-1="{title: 'Buyer Name', value: 'buyer.name'}"
            :row-2="{title: 'Buyer Id', value: 'id'}"
            @click="getAllocations"
          />
        </div>
        <div class="col-lg-8 col-sm-6">
          <div class="tab-content" v-if="allocations.length > 0 || isNewRecord">
            <Details
              v-if="isNewRecord"
              unique-key="newRecord"
              :is-new="true"
              :growers="growers"
              :buyers="buyers"
              @create="() => setActiveTab(allocations[0]?.buyer_id)"
            />
            <template v-else>
              <div class="user-boxes">
                <div class="row">
                  <div class="col-sm-4">
                    <h6>Buyer Name</h6>
                    <h5>{{ allocations[0]?.buyer?.name }}</h5>
                  </div>
                  <div class="col-sm-4">
                    <h6>Buyer Group</h6>
                    <ul v-if="getCategoriesByType(allocations[0]?.buyer?.categories, 'buyer').length > 0">
                      <li v-for="category in getCategoriesByType(allocations[0]?.buyer?.categories, 'buyer')"
                          :key="category.id">
                        <a>{{ category.category.name }}</a>
                      </li>
                    </ul>
                  </div>
                  <div class="col-sm-4">
                    <h6>Buyer Id</h6>
                    <h5>
                      <Link :href="route('users.index', {userId: allocations[0]?.buyer_id})">
                        {{ allocations[0]?.buyer_id }}
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
                      v-model="searchAllocations"
                      type="text"
                      class="form-control customInput"
                      placeholder="Search..."
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
                :allocation="{ buyer_id: allocations[0]?.buyer_id }"
                :is-new-item="true"
                :growers="growers"
                :buyers="buyers"
                @create="() => setActiveTab(allocations[0]?.buyer_id)"
              />
              <template v-for="allocation in filterRecord" :key="allocation.id">
                <Details
                  :unique-key="`${allocation.id}`"
                  :allocation="allocation"
                  :growers="growers"
                  :buyers="buyers"
                  @delete="() => setActiveTab(allocations[0]?.buyer_id)"
                />
              </template>
              <div class="col-sm-12" v-if="filterRecord.length <= 0">
                <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
              </div>
            </template>
          </div>
          <div class="col-sm-12" v-if="allocations.length <= 0 && !isNewRecord">
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
      aria-labelledby="myModalLabel3"
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
              :title="allocations[0]?.buyer?.name || 'New'"
            />
            <div v-if="allocations.length > 0 || isNewRecord">
              <Details
                v-if="isNewRecord"
                :is-new="true"
                :growers="growers"
                :buyers="buyers"
                @create="() => setActiveTab(allocations[0]?.buyer_id)"
              />
              <template v-else>
                <div class="user-boxes">
                  <div class="row">
                    <div class="col-sm-4">
                      <h6>Buyer Name</h6>
                      <h5>{{ allocations[0]?.buyer?.name }}</h5>
                    </div>
                    <div class="col-sm-4">
                      <h6>Buyer Group</h6>
                      <ul v-if="getCategoriesByType(allocations[0]?.buyer?.categories, 'buyer').length > 0">
                        <li v-for="category in getCategoriesByType(allocations[0]?.buyer?.categories, 'buyer')"
                            :key="category.id">
                          <a>{{ category.category.name }}</a>
                        </li>
                      </ul>
                    </div>
                    <div class="col-sm-4">
                      <h6>Buyer Id</h6>
                      <h5>
                        <Link :href="route('users.index', {userId: allocations[0]?.buyer_id})">
                          {{ allocations[0]?.buyer_id }}
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
                        v-model="searchAllocations"
                        type="text"
                        class="form-control customInput"
                        placeholder="Search..."
                      >
                    </div>
                  </div>
                  <div class="col-sm-4 text-right">
                    <a role="button" class="btn btn-red" @click="() => isNewItemRecord = true">Add new allocation</a>
                  </div>
                </div>
                <Details
                  v-if="isNewItemRecord"
                  :allocation="{ buyer_id: allocations[0]?.buyer_id }"
                  :is-new-item="true"
                  :growers="growers"
                  :buyers="buyers"
                  @create="() => setActiveTab(allocations[0]?.buyer_id)"
                />
                <template v-for="allocation in filterRecord" :key="allocation.id">
                  <Details
                    :allocation="allocation"
                    :growers="growers"
                    :buyers="buyers"
                    @delete="() => setActiveTab(allocations[0]?.buyer_id)"
                  />
                </template>
                <div class="col-sm-12" v-if="filterRecord.length <= 0">
                  <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
                </div>
              </template>
            </div>
            <div class="col-sm-12" v-if="allocations.length <= 0 && !isNewRecord">
              <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
