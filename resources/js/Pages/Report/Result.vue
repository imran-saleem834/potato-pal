<script setup>
import { onMounted, ref, watchEffect } from 'vue';
import Multiselect from '@vueform/multiselect'
import { router, Link } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';

import DataTable from "datatables.net-vue3";
// import DataTablesCore from 'datatables.net';
import DataTablesCore from 'datatables.net-bs5';
import 'datatables.net-responsive';
// import 'datatables.net-responsive-dt';
// import 'datatables.net-responsive-bs5';

DataTable.use(DataTablesCore);

const props = defineProps({
  report: Object,
  name: String,
  type: String,
});

const columns = ref([]);
const visibleColumns = ref([]);

watchEffect(async () => {
  try {
    const module = await import(`./Columns/${props.type}.js`);
    columns.value = module.default;
    visibleColumns.value = columns.value.map((c, index) => index);
  } catch (error) {
    console.error("Error loading columns:", error);
  }
});

const table = ref(null);
const onChangeVisibleColumns = (targetVisibleColumns) => {
  columns.value.forEach((c, index) => table.value.dt.column(index).visible(false))
  targetVisibleColumns.forEach((columnIdx) => {
    let column = table.value.dt.column(columnIdx);
    column.visible(true);
  });
}

onMounted(() => {
  setupInertiaLinks();
});

const setupInertiaLinks = () => {
  const tableContainer = document.querySelectorAll('.inertia-link');
  tableContainer.forEach(el => {
    el.addEventListener('click', (event) => {
      const url = event.target.getAttribute('href');
      if (!url) return;

      event.preventDefault();
      router.visit(url);
    });
  });
}
</script>

<template>
  <AppLayout title="Reports">
    <TopBar
      :type="report?.name"
      :title="report?.name"
      :active-tab="report.id"
      :access="{ search : false, new: false, delete: false }"
      @edit="router.visit(route('reports.edit', report.id))"
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
            <Link :href="route('reports.index')"><span class="fa fa-arrow-left"></span> Reports</Link>
          </li>
          <li><i class="bi bi-chevron-right"></i></li>
          <li>
            <Link :href="route('reports.show', type)">{{ name }} Reports</Link>
          </li>
          <li><i class="bi bi-chevron-right"></i></li>
          <li>
            <a>Results</a>
          </li>
        </ul>
      </template>
    </TopBar>

    <div class="tab-section">
      <div v-if="columns.length > 0" class="tab-content">
        <div class="user-boxes">
          <table class="table mb-0">
            <tr>
              <th>Visible Columns: </th>
              <td>
                <Multiselect
                  v-model="visibleColumns"
                  mode="tags"
                  placeholder="Choose a columns"
                  :searchable="true"
                  @change="onChangeVisibleColumns"
                  :options="columns.map((column, index) => {
                    return { 'value': index, 'label': column.title }
                  })"
                />
              </td>
            </tr>
          </table>
        </div>

        <div class="user-boxes">
          <DataTable
            class="table table-striped table-hover table-bordered display"
            ref="table"
            :options="{ responsive: true }"
            :columns="columns"
            :data="report.data"
          />
        </div>
      </div>
      <div v-else>
        <div class="w-100 text-center" style="margin-top: calc(50vh - 120px);">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
      <div v-if="Object.values(report.data).length <= 0">
        <p class="w-100 text-center" style="margin-top: calc(50vh - 120px);">No Records Found</p>
      </div>
    </div>
  </AppLayout>
</template>

<style>
/*@import 'datatables.net-dt';*/
@import 'datatables.net-bs5';

/*@import 'datatables.net-responsive-dt';*/
@import 'datatables.net-responsive-bs5';
</style>
