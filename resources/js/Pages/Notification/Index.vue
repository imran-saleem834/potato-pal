<script setup>
import moment from 'moment';
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
  notifications: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
  },
});

const search = ref(props.filters.search);

watch(search, (value) => {
  router.get(
    route("notifications.index"),
    { search: value },
    { preserveState: true, preserveScroll: true },
  );
});

const filter = (keyword) => search.value = keyword;

const getNotificationActionColor = (action) => {
  if (action === 'Deleted') {
    return 'text-danger';
  } else if (action === 'Updated') {
    return 'text-info';
  } else {
    return 'text-success';
  }
}
</script>

<template>
  <AppLayout title="Notifications">
    <TopBar
      type="Notifications"
      title="Notifications"
      :search="search"
      @search="filter"
      :access="{
        new: false,
        edit: false,
        delete: false,
      }"
    />

    <div class="tab-section p-2 mt-4">
      <div class="container-fluid">
        <div class="row g-2">
          <div
            v-for="notification in notifications.data"
            :key="notification.id"
            class="col-12 col-sm-6 col-md-4 col-lg-3 border-0"
          >
            <div class="card mb-3">
              <div class="user-boxes mb-0">
                <h6 class="mb-0">
                  Status :
                  <strong :class="getNotificationActionColor(notification.action)">
                    {{ notification.action }}
                  </strong>
                </h6>
                <h6>{{ moment(notification.created_at).format('YYYY-MM-DD HH:MM:SS') }}</h6>
                <h6><br> {{ notification.notification }}</h6>
              </div>
            </div>
          </div>
          <div class="col-12" v-if="notifications.data?.length <= 0">
            <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
          </div>
        </div>
        <div class="col-12 d-flex justify-content-end">
          <Pagination :links="notifications.links" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
