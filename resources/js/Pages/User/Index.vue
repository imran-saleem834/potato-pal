<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import Details from '@/Pages/User/Details.vue';
import LeftBar from "@/Components/LeftBar.vue";
import { useToast } from "vue-toastification";
const toast = useToast();

const props = defineProps({
  users: Object,
  single: Object,
  categories: Object,
  filters: Object,
  errors: Object
});

const user = ref(props.single || {});
const activeTab = ref(null);
const edit = ref(null);
const isNewRecord = ref(false);
const search = ref(props.filters.search);
const details = ref(null);

watch(() => props?.single,
  (single) => {
    if (Object.values(props.errors).length === undefined || Object.values(props.errors).length <= 0) {
     user.value = single || {};
    }
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
      toast.success('The user has been deleted successfully!');
    },
  });
}

if (props.users.current_page === 1) {
  setActiveTab(user.value?.id);
}
</script>

<template>
  <AppLayout title="Users">
    <TopBar
      type="Users"
      :title="user?.name || 'New'"
      :active-tab="activeTab"
      :search="search"
      @search="filter"
      :is-edit-record-selected="!!edit"
      :is-new-record-selected="isNewRecord"
      @new="setNewRecord"
      @edit="() => setEdit(user?.id)"
      @unset="() => setActiveTab(null)"
      @store="() => details.storeRecord()"
      @update="() => details.updateRecord()"
      @delete="() => deleteUser(user?.id)"
    />

    <!-- tab-section -->
    <div class="tab-section">
      <div class="row g-0">
        <div class="col-12 col-lg-5 col-xl-4 nav-left d-lg-block" :class="{'d-none' : activeTab || isNewRecord}">
          <LeftBar
            :items="users.data"
            :links="users.links"
            :active-tab="activeTab"
            :row-1="{title: 'Name', value: 'name'}"
            :row-2="{title: 'Email', value: 'email'}"
            @click="getUser"
          />
        </div>
        <div class="col-12 col-lg-7 col-xl-8 d-lg-block" :class="{'d-none': !activeTab && !isNewRecord}">
          <div class="tab-content" v-if="Object.values(user).length > 0 || isNewRecord">
            <Details
              ref="details"
              :user="user"
              :is-edit="!!edit"
              :is-new="isNewRecord"
              :categories="categories"
              @update="() => getUser(activeTab)"
              @create="() => setActiveTab(user?.id)"
              col-size="col-12 col-xl-6"
            />
          </div>
          <div v-if="Object.values(user).length <= 0 && !isNewRecord">
            <p class="w-100 text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
          </div>
        </div>
      </div>
    </div>
    <!-- tab-section -->
  </AppLayout>
</template>
