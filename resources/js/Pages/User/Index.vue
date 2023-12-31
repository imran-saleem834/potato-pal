<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/User/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import ModalHeader from "@/Components/ModalHeader.vue";
import ModalBreadcrumb from "@/Components/ModalBreadcrumb.vue";

const props = defineProps({
  users: Object,
  single: Object,
  categories: Object,
  filters: Object,
});

const user = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);

watch(() => props?.single,
  (single) => {
    user.value = single || {};
  }
);

watch(search, (value) => {
  router.get(
    route('users.index'),
    { search: value },
    { preserveState: true, preserveScroll: true },
  )
});

const filter = (keyword) => search.value = keyword;

const getUser = (id) => {
  axios.get(route('users.show', id)).then(response => {
    user.value = response.data;

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
  user.value = {};
  activeTab.value = null;
}

const deleteUser = (id) => {
  const form = useForm({});
  form.delete(route('users.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      setActiveTab(user.value?.id);
    },
  });
}

setActiveTab(user.value?.id);
</script>

<template>
  <AppLayout title="Users">
    <TopBar
      type="Users"
      :value="search"
      @search="filter"
      @newRecord="setNewRecord"
    />
    <MiddleBar
      type="Users"
      :title="user?.name || 'New'"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      :access="{
        new: true,
        edit: Object.values(user).length > 0,
        delete: Object.values(user).length > 0,
      }"
      @newRecord="setNewRecord"
      @editRecord="() => setEdit(user?.id)"
      @deleteRecord="() => deleteUser(user?.id)"
    />

    <!-- tab-section -->
    <div class="tab-section">
      <div class="row row0">
        <div class="col-lg-3 col-sm-6" :class="{'mobile-userlist' : $windowWidth <= 767}">
          <LeftBar
            :items="users"
            :active-tab="activeTab"
            :row-1="{title: 'Name', value: 'name'}"
            :row-2="{title: 'Email', value: 'email'}"
            @click="getUser"
          />
        </div>
        <div class="col-lg-8 col-sm-6">
          <div class="tab-content" v-if="Object.values(user).length > 0 || isNewRecord">
            <div class="tab-pane active">
              <Details
                :user="user"
                :is-edit="!!edit"
                :is-new="isNewRecord"
                :categories="categories"
                @update="() => getUser(activeTab)"
                @create="() => setActiveTab(user?.id)"
                col-size="col-md-6"
              />
            </div>
          </div>
          <div class="col-sm-12" v-if="Object.values(user).length <= 0 && !isNewRecord">
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
            title="Users"
            :access="{
              new: isNewRecord
            }"
            @edit="() => setEdit(user?.id)"
            @delete="() => deleteUser(user?.id)"
          />
          <div class="modal-body" v-if="user">
            <ModalBreadcrumb
              page="Users"
              :title="user?.name || 'New'"
            />
            <Details
              :user="user"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              :categories="categories"
              @update="() => getUser(activeTab)"
              @create="() => setActiveTab(user?.id)"
              col-size="col-md-12"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
