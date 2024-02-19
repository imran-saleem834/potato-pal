<script setup>
import moment from "moment";
import { onMounted, ref } from 'vue';
import Multiselect from '@vueform/multiselect'
import { router, Link } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import DataTable from "datatables.net-vue3";
// import DataTablesCore from 'datatables.net';
import DataTablesCore from 'datatables.net-bs5';
import 'datatables.net-responsive-dt';
import { getCategoriesByType, getSingleCategoryNameByType } from "@/helper.js";

DataTable.use(DataTablesCore);

const props = defineProps({
  report: Object,
  name: String,
  type: String,
});

const columns = [
  {
    title: 'Receival Id',
    data: 'id',
    render: function (data, type, row) {
      const url = route('receivals.index', { receivalId: data });
      return `<a href="${url}" class="text-black inertia-link">${data}</a>`;
    }
  },
  {
    title: 'Grower',
    data: 'grower',
    render: function (data, type, row) {
      const url = route('users.index', { userId: data.id });
      return row.grower ? `<a href="${url}" class="text-black inertia-link">${data.grower_name}</a>` : '-';
    }
  },
  {
    title: 'Paddock',
    data: 'paddocks',
    render: function (data, type, row) {
      return data?.length ? data[0] : '-';
    }
  },
  {
    title: 'Time Added',
    data: 'created_at',
    render: function (data, type, row) {
      return moment(data).format('DD/MM/YYYY hh:mm A')
    }
  },
  {
    title: 'Grower Group',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'grower-group').length) {
        return getSingleCategoryNameByType(categories, 'grower-group')
      }
      return '-';
    }
  },
  {
    title: 'Seed Variety',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'seed-variety').length) {
        return getSingleCategoryNameByType(categories, 'seed-variety')
      }
      return '-';
    }
  },
  {
    title: 'Seed Generation',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'seed-generation').length) {
        return getSingleCategoryNameByType(categories, 'seed-generation')
      }
      return '-';
    }
  },
  {
    title: 'Seed Class',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'seed-class').length) {
        return getSingleCategoryNameByType(categories, 'seed-class')
      }
      return '-';
    }
  },
  {
    title: 'Cool Store',
    data: 'grower.categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'cool-store').length) {
        return getSingleCategoryNameByType(categories, 'cool-store')
      }
      return '-';
    }
  },
  {
    title: 'Transport Co.',
    data: 'categories',
    render: function (categories, type, row) {
      if (getCategoriesByType(categories, 'transport').length) {
        return getSingleCategoryNameByType(categories, 'transport')
      }
      return '-';
    }
  },
];

const visibleColumns = ref(columns.map((c, index) => index));
const table = ref(null);
const onChangeVisibleColumns = (targetVisibleColumns) => {
  columns.forEach((c, index) => table.value.dt.column(index).visible(false))
  targetVisibleColumns.forEach((columnIdx) => {
    let column = table.value.dt.column(columnIdx);
    column.visible(true);
  })
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
      :access="{ search : false, new: false }"
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
      <div class="tab-content">
        <div class="user-boxes">
          <table class="table mb-0">
            <tr>
              <th>Visible Columns</th>
              <td>
                <Multiselect
                  v-model="visibleColumns"
                  mode="tags"
                  placeholder="Choose a columns"
                  :searchable="true"
                  @change="onChangeVisibleColumns"
                  :options="columns.map((column, index) => {
                    return {
                      'value': index,
                      'label': column.title
                    }
                  })"
                />
              </td>
            </tr>
          </table>
        </div>

        <div class="user-boxes">
          <DataTable
            class="table w-100"
            ref="table"
            :options="{ responsive: true }"
            :columns="columns"
            :data="report.data"
          />
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
@import 'datatables.net-responsive-dt';
@import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
/*@import 'datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css';*/
</style>
