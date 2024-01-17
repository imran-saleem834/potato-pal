<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/Receival/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";
import { getCategoriesByType } from "@/helper.js";

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
    },
  });
}

const deleteReceival = (id) => {
  const form = useForm({});
  form.delete(route('receivals.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      setActiveTab(receival.value?.id);
    },
  });
}

setActiveTab(receival.value?.id);
</script>

<template>
  <AppLayout title="Receivals">
    <TopBar
      type="Receivals"
      :value="search"
      @search="filter"
      @newRecord="setNewRecord"
    />
    <MiddleBar
      type="Receivals"
      :title="receival?.grower?.grower_name || 'New'"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      :access="{
        new: true,
        edit: Object.values(receival).length > 0,
        delete: Object.values(receival).length > 0,
        duplicate: true
      }"
      @newRecord="setNewRecord"
      @editRecord="() => setEdit(receival?.id)"
      @deleteRecord="() => deleteReceival(receival?.id)"
      @duplicate="() => showDuplicateModal(receival)"
    />

    <!-- tab-section -->
    <div class="tab-section">
      <div class="row row0">
        <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
          <LeftBar
            :items="receivals"
            :active-tab="activeTab"
            :row-1="{title: 'Grower', value: 'grower.grower_name'}"
            :row-2="{title: 'Receival Id', value: 'id'}"
            @click="getReceival"
          />
        </div>
        <div class="col-lg-8 col-sm-6">
          <div class="tab-content" v-if="Object.values(receival).length > 0 || isNewRecord">
            <div class="tab-pane active">
              <Details
                :receival="receival"
                :is-edit="!!edit"
                :is-new="isNewRecord"
                :users="users"
                :categories="categories"
                @update="() => getReceival(activeTab)"
                @create="() => setActiveTab(receival?.id)"
                col-size="col-md-6"
              />
            </div>
          </div>
          <div class="col-sm-12" v-if="Object.values(receival).length <= 0 && !isNewRecord">
            <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
    <!-- tab-section -->

    <!-- Modal -->
    <div class="modal right fade user-details" id="user-details" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <ModalHeader
            title="Receivals"
            :access="{
              new: isNewRecord,
              edit: true,
              delete: true,
              duplicate: true
            }"
            @edit="() => setEdit(receival?.id)"
            @delete="() => deleteReceival(receival?.id)"
            @duplicate="() => showDuplicateModal(receival)"
          />
          <div class="modal-body" v-if="receival">
            <ModalBreadcrumb
              page="Receivals"
              :title="receival?.grower?.grower_name || 'New'"
            />
            <Details
              :receival="receival"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              :users="users"
              :categories="categories"
              @update="() => getReceival(activeTab)"
              @create="() => setActiveTab(receival?.id)"
              col-size="col-md-12"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="duplicate-details" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Duplicate</h4>
          </div>
          <div class="modal-body">
            <div class="tab-section">
              <div class="user-boxes" style="box-shadow: none;margin-bottom: 0;">
                <ul v-if="duplicateReceival">
                  <template v-for="(value, input) in duplicateForm.inputs" :key="input">
                    <li v-if="input !== 'grower_id'">
                      <template v-if="duplicateReceival[input]">
                        <a
                          role="button"
                          @click="() => duplicateForm.inputs[input] = !duplicateForm.inputs[input]"
                          :class="{'black-btn' : duplicateForm.inputs[input]}"
                        >
                          <template v-if="input === 'paddocks'">{{ duplicateReceival[input][0] }}</template>
                          <template v-else>{{ duplicateReceival[input] }}</template>
                        </a>
                      </template>
                      <template
                        v-else-if="getCategoriesByType(duplicateReceival.categories, input.replaceAll('_', '-')).length"
                      >
                        <a
                          role="button"
                          @click="() => duplicateForm.inputs[input] = !duplicateForm.inputs[input]"
                          :class="{'black-btn' : duplicateForm.inputs[input]}"
                        >
                          <template
                            v-for="(category, key) in getCategoriesByType(duplicateReceival.categories, input.replaceAll('_', '-'))"
                            :key="category.id"
                          >
                            <template v-if="key > 0 && ['delivery_type', 'transport'].includes(input)">,</template>
                            {{ category.category.name }}
                          </template>
                        </a>
                      </template>
                    </li>
                  </template>
                </ul>

                <div class="text-right">
                  <a
                    role="button"
                    aria-label="Close"
                    class="btn btn-red"
                    data-dismiss="modal"
                    @click="duplicateRecord"
                  >Create</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
