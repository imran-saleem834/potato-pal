<script setup>
import { router, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
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
});

const allocations = ref(props.single || []);
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);
const searchAllocations = ref('');

watch(() => props?.single,
  (single) => {
    allocations.value = single || [];
    setActiveTab(allocations.value[0]?.buyer_id);
  }
);

watch(search, (value) => {
  router.get(
    route('allocations.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  )
});

const isEditing = (value) => {
  edit.value = value
}

const filter = (keyword) => search.value = keyword;

const getAllocations = (id) => {
  axios.get(route('allocations.show', id)).then(response => {
    allocations.value = response.data;

    setActiveTab(response.data[0]?.buyer_id);
  });
};

const setActiveTab = (id) => {
  activeTab.value = id;
  edit.value = null;
  isNewRecord.value = false;
  // const newUrl = window.location.href.split('?')[0];
  // history.pushState({}, null, newUrl);
};

const setNewRecord = () => {
  isNewRecord.value = true;
  edit.value = null;
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
    for(let i = 0; i < allocation.categories.length; i++) {
      if (allocation.categories[i].category.name.toLowerCase().includes(keyword)){
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
      :is-edit-record-selected="!!edit"
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
              :is-new="true"
              :growers="growers"
              :buyers="buyers"
              @isEditing="isEditing"
              @update="() => {}"
              @create="() => getAllocations(allocationBuyers[0]?.buyer_id)"
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
                  <a role="button" class="btn btn-red">Add new allocation</a>
                </div>
              </div>
              <template v-for="allocation in filterRecord" :key="allocation.id">
                <Details
                  :allocation="allocation"
                  :growers="growers"
                  :buyers="buyers"
                  @isEditing="isEditing"
                  @update="() => {}"
                  @create="() => {}"
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
    <!--    <div class="modal right fade user-details" id="user-details" tabindex="-1" role="dialog"-->
    <!--         aria-labelledby="myModalLabel3">-->
    <!--      <div class="modal-dialog" role="document">-->
    <!--        <div class="modal-content">-->
    <!--          <ModalHeader-->
    <!--            title="Allocations"-->
    <!--            :is-new="isNewRecord"-->
    <!--            @edit="() => setEdit(allocation?.id)"-->
    <!--            @delete="() => deleteAllocation(allocation?.id)"-->
    <!--          />-->
    <!--          <div class="modal-body" v-if="allocation">-->
    <!--            <ModalBreadcrumb-->
    <!--              page="Allocations"-->
    <!--              :title="allocation?.name || 'Allocations'"-->
    <!--            />-->
    <!--            <Details-->
    <!--              :allocation="allocation"-->
    <!--              :is-edit="!!edit"-->
    <!--              :is-new="isNewRecord"-->
    <!--              :growers="growers"-->
    <!--              :buyers="buyers"-->
    <!--              @update="() => getAllocation(activeTab)"-->
    <!--              @create="() => setActiveTab(allocation?.id)"-->
    <!--            />-->
    <!--          </div>-->
    <!--        </div>-->
    <!--      </div>-->
    <!--    </div>-->
  </AppLayout>
</template>
