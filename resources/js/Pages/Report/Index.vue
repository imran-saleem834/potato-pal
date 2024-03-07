<script setup>
import moment from 'moment';
import { ref, watch } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import { useToast } from 'vue-toastification';
import { useWindowSize } from 'vue-window-size';
import Pagination from '@/Components/Pagination.vue';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';

const toast = useToast();
const { width, height } = useWindowSize();

const props = defineProps({
  reports: Object,
  name: String,
  type: String,
  filters: Object,
});

const search = ref(props.filters.search);

const filter = (keyword) => (search.value = keyword);

watch(search, (value) => {
  router.get(
    route('reports.show', props.type),
    { search: value },
    { preserveState: true, preserveScroll: true },
  );
});

const deleteReport = (id) => {
  const form = useForm({});
  form.delete(route('reports.destroy', id), {
    preserveState: true,
    onSuccess: () => {
      toast.success('The report has been deleted successfully!');
    },
  });
};
</script>

<template>
  <AppLayout title="Reports">
    <TopBar
      :type="`${name} Reports`"
      :title="name"
      :search="search"
      @search="filter"
      :access="{
        edit: false,
        delete: false,
      }"
      @new="router.visit(route('reports.create', { type: type }))"
    >
      <template #back>
        <Link :href="route('reports.index')"><i class="bi bi-chevron-compact-left"></i></Link>
      </template>
      <template #breadcrumbs>
        <ul>
          <li>
            <Link :href="route('dashboard')"><span class="fa fa-arrow-left"></span> Menu</Link>
          </li>
          <li><i class="bi bi-chevron-right"></i></li>
          <li>
            <Link :href="route('reports.index')">
              <span class="fa fa-arrow-left"></span> Reports
            </Link>
          </li>
          <li><i class="bi bi-chevron-right"></i></li>
          <li>
            <Link :href="route('reports.show', type)">{{ name }} Reports</Link>
          </li>
        </ul>
      </template>
    </TopBar>

    <div class="tab-section p-2 mt-4">
      <div class="container-fluid">
        <div class="row">
          <div
            v-for="report in reports.data"
            :key="report.id"
            class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-3"
          >
            <div class="user-boxes">
              <table class="table input-table">
                <tr>
                  <th>Name</th>
                  <td>
                    <Link class="text-black p-0" :href="route('reports.result', report.id)">
                      {{ report.name }}
                    </Link>
                  </td>
                </tr>
                <tr>
                  <th>Filter</th>
                  <td>{{ report.filter }}</td>
                </tr>
                <tr>
                  <th>Created at</th>
                  <td>{{ moment(report.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
                </tr>
              </table>

              <ul class="mt-4 d-flex justify-content-end">
                <li>
                  <Link :href="route('reports.edit', report.id)" class="btn btn-red">Edit</Link>
                </li>
                <li>
                  <button
                    data-bs-toggle="modal"
                    :data-bs-target="`#delete-report-record-${report.id}`"
                    class="btn btn-red"
                  >
                    Delete
                  </button>
                </li>
              </ul>
            </div>

            <ConfirmedModal
              :id="`delete-report-record-${report.id}`"
              cancel="No, Keep it"
              ok="Yes, Delete!"
              @ok="deleteReport(report.id)"
            />
          </div>
          <div class="col-12" v-if="reports.data?.length <= 0">
            <p class="text-center" style="margin-top: calc(50vh - 120px)">No Records Found</p>
          </div>
        </div>
        <div class="float-end">
          <Pagination :links="reports.links" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
