<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import moment from 'moment';

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
      :value="search"
      @search="filter"
      @newRecord=""
    />
    <MiddleBar
      type="Notifications"
      title="Notifications"
      :access="{
                new: false,
                edit: false,
                delete: false,
            }"
      @newRecord=""
      @editRecord=""
      @deleteRecord=""
    />

    <!-- tab-section -->
    <div class="tab-section" style="margin-top: 30px">
      <div class="row row0">
        <div v-for="notification in notifications"
             :key="notification.id"
             class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
          <div class="card">
            <div class="user-boxes" style="padding: 10px 20px;">
              <h5 style="margin-bottom: 0;">
                Status :
                <strong :class="getNotificationActionColor(notification.action)">
                  {{ notification.action }}
                </strong>
              </h5>
              <h6>
                {{ moment(notification.created_at).format('YYYY-MM-DD HH:MM:SS') }}
              </h6>
              <h6>
                <br> {{ notification.notification }}
              </h6>
            </div>
          </div>
        </div>
        <div class="col-sm-12" v-if="notifications.length <= 0">
          <p class="text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
    <!-- tab-section -->
  </AppLayout>
</template>
