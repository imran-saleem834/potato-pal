<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import { useToast } from 'vue-toastification';
import { useWindowSize } from 'vue-window-size';
import Form from '@/Pages/Report/Form.vue';

const toast = useToast();
const { width, height } = useWindowSize();

const props = defineProps({
  report: Object,
  name: String,
  type: String,
});

const reportForm = ref(null);
</script>

<template>
  <AppLayout title="Reports">
    <TopBar
      :type="`Edit ${name.toLowerCase()} report`"
      :title="`Edit ${name.toLowerCase()} report`"
      :is-edit-record-selected="true"
      :access="{ new: false, search: false }"
      @update="() => reportForm.updateRecord()"
    >
      <template #back>
        <Link :href="route('reports.show', type)"><i class="bi bi-chevron-compact-left"></i></Link>
      </template>
      <template #breadcrumbs>
        <ul>
          <li>
            <Link :href="route('dashboard')"><span class="fa fa-arrow-left"></span> Menu</Link>
          </li>
          <li><i class="bi bi-chevron-right"></i></li>
          <li>
            <Link :href="route('reports.index')"> <span class="fa fa-arrow-left"></span> Reports </Link>
          </li>
          <li><i class="bi bi-chevron-right"></i></li>
          <li>
            <Link :href="route('reports.show', type)">{{ name }} Reports</Link>
          </li>
          <li><i class="bi bi-chevron-right"></i></li>
          <li>
            <a>Edit</a>
          </li>
        </ul>
      </template>
    </TopBar>

    <div class="tab-section">
      <div class="tab-content">
        <Form ref="reportForm" :report="report" :is-edit="true" />
      </div>
    </div>
  </AppLayout>
</template>
