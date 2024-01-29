<script setup>
import { computed, ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/Cutting/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";
import { getCategoriesByType } from "@/helper.js";

const props = defineProps({
  cuttingBuyers: Object,
  single: Object,
  allocations: Object,
  categories: Object,
  buyers: Object,
  filters: Object,
  errors: Object
});

const cuttings = ref(props.single || []);
const activeTab = ref(null);
const isNewRecord = ref(false);
const isNewItemRecord = ref(false);
const search = ref(props.filters.search);
const searchCuttings = ref('');

watch(() => props?.single,
  (single) => {
    if (props.errors.length === undefined ||props.errors.length <= 0) {
      cuttings.value = single || [];
    }
  }
);

watch(search, (value) => {
  router.get(
    route('cuttings.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  )
});

const filter = (keyword) => search.value = keyword;

const getCuttings = (id) => {
  axios.get(route('cuttings.show', id)).then(response => {
    cuttings.value = response.data;

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
  cuttings.value = [];
  activeTab.value = null;
}

const filterRecord = computed(() => {
  if (searchCuttings.value === '') {
    return cuttings.value;
  }

  const keyword = searchCuttings.value.toLowerCase();
  return cuttings.value.filter(cutting => {
    if (`${cutting.cut_date}`.toLowerCase().includes(keyword)) {
      return true;
    }
    if (`${cutting.cut_by}`.toLowerCase().includes(keyword)) {
      return true;
    }
    if (`${cutting.comment}`.toLowerCase().includes(keyword)) {
      return true;
    }
    for (let i = 0; i < cutting.cutting_allocations.length; i++) {
      if (`${cutting.cutting_allocations[i].allocation.bin_size} Tonnes`.toLowerCase().includes(keyword)) {
        return true;
      }

      if (`${cutting.cutting_allocations[i].weight_after_cutting} Tonnes`.toLowerCase().includes(keyword)) {
        return true;
      }

      if (`${cutting.cutting_allocations[i].no_of_bins_after_cutting}`.toLowerCase().includes(keyword)) {
        return true;
      }

      if (`${cutting.cutting_allocations[i].allocation.paddock}`.toLowerCase().includes(keyword)) {
        return true;
      }

      for (let j = 0; j < cutting.cutting_allocations[i].allocation.categories.length; j++) {
        if (cutting.cutting_allocations[i].allocation.categories[j].category.name.toLowerCase().includes(keyword)) {
          return true;
        }
      }
    }
    return false;
  });
});

setActiveTab(cuttings.value[0]?.buyer_id);
</script>

<template>
  <AppLayout title="Cuttings">
    <TopBar
      type="Cuttings"
      :value="search"
      @search="filter"
      @newRecord="setNewRecord"
    />
    <MiddleBar
      type="Cuttings"
      :title="cuttings[0]?.buyer?.name || 'New'"
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
            :items="cuttingBuyers"
            :active-tab="activeTab"
            :row-1="{title: 'Buyer Name', value: 'buyer.name'}"
            :row-2="{title: 'Buyer Id', value: 'id'}"
            @click="getCuttings"
          />
        </div>
        <div class="col-lg-8 col-sm-6">
          <div class="tab-content" v-if="cuttings.length > 0 || isNewRecord">
            <Details
              v-if="isNewRecord"
              unique-key="newRecord"
              :is-new="true"
              :allocations="allocations"
              :categories="categories"
              :buyers="buyers"
              @create="() => setActiveTab(cuttings[0]?.buyer_id)"
            />
            <template v-else>
              <div class="user-boxes">
                <div class="row">
                  <div class="col-sm-4">
                    <h6>Buyer Name</h6>
                    <h5>{{ cuttings[0]?.buyer?.name }}</h5>
                  </div>
                  <div class="col-sm-4">
                    <h6>Buyer Group</h6>
                    <ul v-if="getCategoriesByType(cuttings[0]?.buyer?.categories, 'buyer-group').length > 0">
                      <li v-for="category in getCategoriesByType(cuttings[0]?.buyer?.categories, 'buyer-group')"
                          :key="category.id">
                        <a>{{ category.category.name }}</a>
                      </li>
                    </ul>
                  </div>
                  <div class="col-sm-4">
                    <h6>Buyer Id</h6>
                    <h5>
                      <Link :href="route('users.index', {userId: cuttings[0]?.buyer_id})">
                        {{ cuttings[0]?.buyer_id }}
                      </Link>
                    </h5>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-4">
                  <h4>Cutting Details</h4>
                </div>
                <div class="col-sm-4">
                  <div class="form-group has-feedback">
                    <input
                      v-model="searchCuttings"
                      type="text"
                      class="form-control custom-input"
                      placeholder="Search..."
                    >
                  </div>
                </div>
                <div class="col-sm-4 text-right">
                  <a role="button" class="btn btn-red" @click="() => isNewItemRecord = true">Add new cutting</a>
                </div>
              </div>
              <Details
                v-if="isNewItemRecord"
                unique-key="newItemRecord"
                :cutting="{ buyer_id: cuttings[0]?.buyer_id }"
                :is-new-item="true"
                :allocations="allocations"
                :categories="categories"
                :buyers="buyers"
                @create="() => setActiveTab(cuttings[0]?.buyer_id)"
              />
              <template v-for="cutting in filterRecord" :key="cutting.id">
                <Details
                  :unique-key="`${cutting.id}`"
                  :cutting="cutting"
                  :allocations="allocations"
                  :categories="categories"
                  :buyers="buyers"
                  @delete="() => setActiveTab(cuttings[0]?.buyer_id)"
                />
              </template>
              <div class="col-sm-12" v-if="filterRecord.length <= 0">
                <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
              </div>
            </template>
          </div>
          <div class="col-sm-12" v-if="cuttings.length <= 0 && !isNewRecord">
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
            title="Cuttings"
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
              page="Cuttings"
              :title="cuttings[0]?.buyer?.name || 'New'"
            />
            <div v-if="cuttings.length > 0 || isNewRecord">
              <Details
                v-if="isNewRecord"
                :is-new="true"
                :allocations="allocations"
                :categories="categories"
                :buyers="buyers"
                @create="() => setActiveTab(cuttings[0]?.buyer_id)"
              />
              <template v-else>
                <div class="user-boxes">
                  <div class="row">
                    <div class="col-sm-4">
                      <h6>Buyer Name</h6>
                      <h5>{{ cuttings[0]?.buyer?.name }}</h5>
                    </div>
                    <div class="col-sm-4">
                      <h6>Buyer Group</h6>
                      <ul v-if="getCategoriesByType(cuttings[0]?.buyer?.categories, 'buyer-group').length > 0">
                        <li v-for="category in getCategoriesByType(cuttings[0]?.buyer?.categories, 'buyer-group')"
                            :key="category.id">
                          <a>{{ category.category.name }}</a>
                        </li>
                      </ul>
                    </div>
                    <div class="col-sm-4">
                      <h6>Buyer Id</h6>
                      <h5>
                        <Link :href="route('users.index', {userId: cuttings[0]?.buyer_id})">
                          {{ cuttings[0]?.buyer_id }}
                        </Link>
                      </h5>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-4">
                    <h4>Cutting Details</h4>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group has-feedback">
                      <input
                        v-model="searchCuttings"
                        type="text"
                        class="form-control custom-input"
                        placeholder="Search..."
                      >
                    </div>
                  </div>
                  <div class="col-sm-4 text-right">
                    <a role="button" class="btn btn-red" @click="() => isNewItemRecord = true">Add new cutting</a>
                  </div>
                </div>
                <Details
                  v-if="isNewItemRecord"
                  :cutting="{ buyer_id: cuttings[0]?.buyer_id }"
                  :is-new-item="true"
                  :allocations="allocations"
                  :categories="categories"
                  :buyers="buyers"
                  @create="() => setActiveTab(cuttings[0]?.buyer_id)"
                />
                <template v-for="cutting in filterRecord" :key="cutting.id">
                  <Details
                    :cutting="cutting"
                    :allocations="allocations"
                    :categories="categories"
                    :buyers="buyers"
                    @delete="() => setActiveTab(cuttings[0]?.buyer_id)"
                  />
                </template>
                <div class="col-sm-12" v-if="filterRecord.length <= 0">
                  <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
                </div>
              </template>
            </div>
            <div class="col-sm-12" v-if="cuttings.length <= 0 && !isNewRecord">
              <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
