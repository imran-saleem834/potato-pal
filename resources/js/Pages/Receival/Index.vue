<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import Details from '@/Pages/Receival/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import { getCategoriesByType } from "@/helper.js";
import { useToast } from "vue-toastification";
import { useWindowSize } from 'vue-window-size';

const toast = useToast();
const { width, height } = useWindowSize();

const props = defineProps({
  receivals: Object,
  single: Object,
  users: Object,
  categories: Object,
  filters: Object,
  errors: Object
});

const duplicateForm = useForm({
  receival_id: null,
  inputs: {
    grower_id: true,
    grower_group: true,
    paddocks: true,
    seed_variety: true,
    seed_generation: true,
    seed_class: true,
    delivery_type: true,
    transport: true,
    grower_docket_no: true,
    chc_receival_docket_no: true,
    driver_name: true,
    comments: true,
  }
});

const receival = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);
const duplicateReceival = ref(null);
const details = ref(null);

watch(() => props?.single,
  (single) => {
    if (Object.values(props.errors).length === undefined || Object.values(props.errors).length <= 0) {
      receival.value = single || {};
    }
  }
);

watch(search, (value) => {
  router.get(
    route('receivals.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  )
});

const filter = (keyword) => search.value = keyword;

const getReceival = (id) => {
  axios.get(route('receivals.show', id)).then(response => {
    receival.value = response.data;

    setActiveTab(response.data.id);
  });
};

const setActiveTab = (id) => {
  activeTab.value = id;
  edit.value = null;
  isNewRecord.value = false;
};

const setEdit = (id) => {
  edit.value = edit.value === id ? null : id;
  isNewRecord.value = false;
}

const setNewRecord = () => {
  isNewRecord.value = true;
  edit.value = null;
  receival.value = {};
  activeTab.value = null;
}

const showDuplicateModal = (receival) => {
  duplicateReceival.value = receival;
  duplicateForm.receival_id = receival.id;
}

const duplicateRecord = () => {
  duplicateForm.post(route('receivals.duplicate', duplicateForm.receival_id), {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      duplicateForm.reset();
      setActiveTab(receival.value?.id);
      toast.success('The receival has been duplicated successfully!');
    },
  });
}

const deleteReceival = (id) => {
  const form = useForm({});
  form.delete(route('receivals.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      setActiveTab(receival.value?.id);
      toast.success('The receival has been deleted successfully!');
    },
  });
}

if (width.value > 991) {
  setActiveTab(receival.value?.id);
}
</script>

<template>
  <AppLayout title="Receivals">
    <TopBar
      type="Receivals"
      :title="receival?.grower?.grower_name || 'New'"
      :active-tab="activeTab"
      :search="search"
      @search="filter"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      :access="{ duplicate: true }"
      @new="setNewRecord"
      @edit="() => setEdit(receival?.id)"
      @unset="() => setActiveTab(null)"
      @store="() => details.storeRecord()"
      @update="() => details.updateRecord()"
      @delete="() => deleteReceival(receival?.id)"
      @duplicate="() => showDuplicateModal(receival)"
    />

    <!-- tab-section -->
    <div class="tab-section">
      <div class="row g-0">
        <div class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block" :class="{'d-none' : activeTab || isNewRecord}">
          <LeftBar
            :items="receivals.data"
            :links="receivals.links"
            :active-tab="activeTab"
            :row-1="{title: 'Grower', value: 'grower.grower_name'}"
            :row-2="{title: 'Receival Id', value: 'id'}"
            @click="getReceival"
          />
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{'d-none': !activeTab && !isNewRecord}">
          <div class="tab-content" v-if="Object.values(receival).length > 0 || isNewRecord">
            <Details
              ref="details"
              :receival="receival"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              :users="users"
              :categories="categories"
              @update="() => getReceival(activeTab)"
              @create="() => setActiveTab(receival?.id)"
              @unset="() => setActiveTab(null)"
              col-size="col-12 col-xl-6"
            />
          </div>
          <div v-if="Object.values(receival).length <= 0 && !isNewRecord">
            <p class="w-100 text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
          </div>
        </div>
      </div>
    </div>
    <!-- tab-section -->

    <div class="modal fade" id="duplicate-details" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Duplicate</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="tab-section">
              <div class="user-boxes m-0 p-0 shadow-none">
                <ul v-if="duplicateReceival">
                  <template v-for="(value, input) in duplicateForm.inputs" :key="input">
                    <template v-if="input !== 'grower_id'">
                      <template v-if="duplicateReceival[input]">
                        <li class="mb-2">
                          <button
                            @click="() => duplicateForm.inputs[input] = !duplicateForm.inputs[input]"
                            :class="duplicateForm.inputs[input] ? 'btn btn-black' : 'btn btn-dark'"
                          >
                            <template v-if="input === 'paddocks'">{{ duplicateReceival[input][0] }}</template>
                            <template v-else>{{ duplicateReceival[input] }}</template>
                          </button>
                        </li>
                      </template>
                      <template
                        v-else-if="getCategoriesByType(duplicateReceival.categories, input.replaceAll('_', '-')).length"
                      >
                        <li class="mb-2">
                          <button
                            @click="() => duplicateForm.inputs[input] = !duplicateForm.inputs[input]"
                            :class="duplicateForm.inputs[input] ? 'btn btn-black' : 'btn btn-dark'"
                          >
                            <template
                              v-for="(category, key) in getCategoriesByType(duplicateReceival.categories, input.replaceAll('_', '-'))"
                              :key="category.id"
                            >
                              <template v-if="key > 0 && ['delivery_type', 'transport'].includes(input)">,</template>
                              {{ category.category.name }}
                            </template>
                          </button>
                        </li>
                      </template>
                    </template>
                  </template>
                </ul>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-red" data-bs-dismiss="modal" @click="duplicateRecord">Create</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
