<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import LogoutButton from '@/Components/LogoutButton.vue';
import InstallConfirmedModal from '@/Components/InstallConfirmedModal.vue';

const page = usePage();
const hasRole = (roles) => roles.includes(page.props.auth.user.role);

const menus = [
  {
    route: route('reports.show', 'user'),
    permission: ['admin'],
    image: 'menu1.png',
    label: 'Users'
  },
  {
    route: route('reports.show', 'receival'),
    permission: ['admin', 'receivals', 'grower'],
    image: 'menu2.png',
    label: 'Receivals'
  },
  {
    route: route('reports.show', 'unload'),
    permission: ['admin', 'unloading', 'grower'],
    image: 'menu3.png',
    label: 'Unloading'
  },
  {
    route: route('reports.show', 'tia-sample'),
    permission: ['admin', 'tia-sampling', 'grower'],
    image: 'menu4.png',
    label: 'TIA Sampling'
  },
  {
    route: route('reports.show', 'grade'),
    permission: ['admin'],
    image: 'menu8.png',
    label: 'Grading'
  },
  {
    route: route('reports.show', 'label'),
    permission: ['admin'],
    image: 'menu8.png',
    label: 'Labels'
  },
  {
    route: route('reports.show', 'allocation'),
    permission: ['admin', 'allocation', 'buyer'],
    image: 'menu5.png',
    label: 'Allocations'
  },
  {
    route: route('reports.show', 'cutting'),
    permission: ['admin', 'cutting', 'buyer'],
    image: 'menu11.png',
    label: 'Cutting'
  },
  {
    route: route('reports.show', 'reallocation'),
    permission: ['admin', 'reallocations', 'buyer'],
    image: 'menu6.png',
    label: 'Reallocations'
  },
  {
    route: route('reports.show', 'dispatch'),
    permission: ['admin', 'dispatch', 'buyer'],
    image: 'menu9.png',
    label: 'Dispatch'
  },
];
</script>

<template>
  <Head><title>Report</title></Head>

  <div class="admin-access">
    <div class="container-fluid">
      <div class="d-flex d-md-none justify-content-between px-3 mobile-topbar">
        <Link :href="route('dashboard')"><i class="bi bi-chevron-compact-left"></i></Link>
        <div class="mt-1">
          <LogoutButton/>
        </div>
      </div>
      <Link :href="route('dashboard')" class="d-block text-center">
        <img src="/images/logo.png" alt="Potato Pal" class="logo"/>
      </Link>
      <div class="admin-menu">
        <h5 class="ms-4">Report</h5>
        <div class="row">
          <template v-for="menu in menus" :key="menu.image">
            <div v-if="hasRole(menu.permission)" class="col-6 col-sm-2">
              <div class="menu-icon" :class="{ 'opacity-50': menu.route === '' }">
                <Link :href="menu.route">
                  <img :src="`/images/${menu.image}`" :alt="menu.label"/>
                  <span>{{ menu.label }}</span>
                </Link>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>

  <InstallConfirmedModal/>
</template>
