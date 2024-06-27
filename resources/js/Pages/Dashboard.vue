<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import LogoutButton from '@/Components/LogoutButton.vue';
import InstallConfirmedModal from '@/Components/InstallConfirmedModal.vue';

const page = usePage();
const hasRole = (roles) => page.props.auth.user.role.some((role) => roles.includes(role));

// route('labels.index')
// route('invoices.index')
// route('reports.index')
const menus = [
  { route: route('users.index'), image: 'menu1.png', permission: ['admin'], label: 'Users' },
  {
    route: route('receivals.index'),
    image: 'menu2.png',
    permission: ['admin', 'receivals'],
    label: 'Receivals',
  },
  {
    route: route('unloading.index'),
    image: 'menu3.png',
    permission: ['admin', 'unloading'],
    label: 'Unloading',
  },
  {
    route: route('tia-samples.index'),
    image: 'menu4.png',
    permission: ['admin', 'tia-sampling'],
    label: 'TIA Sampling',
  },
  {
    route: route('allocations.index'),
    image: 'menu5.png',
    permission: ['admin', 'allocations'],
    label: 'Allocations',
  },
  {
    route: route('reallocations.index'),
    image: 'reallocation.png',
    permission: ['admin', 'reallocations'],
    label: 'Reallocations',
  },
  { route: '', image: 'menu7.png', permission: ['admin'], label: 'Invoices' },
  { route: '', image: 'menu8.png', permission: ['admin'], label: 'Labels' },
  {
    route: route('dispatches.index'),
    image: 'menu9.png',
    permission: ['admin', 'dispatch'],
    label: 'Dispatch',
  },
  { route: '', image: 'menu10.png', permission: ['admin'], label: 'Reports' },
  {
    route: route('cuttings.index'),
    image: 'cutting.png',
    permission: ['admin', 'cutting'],
    label: 'Cutting',
  },
  {
    route: route('weighbridges.index'),
    image: 'menu12.png',
    permission: ['admin', 'weighbridges'],
    label: 'Weighbridge',
  },
  {
    route: route('gradings.index'),
    image: 'menu13.png',
    permission: ['admin', 'grading'],
    label: 'Grading',
  },
  {
    route: route('categories.index'),
    image: 'menu13.png',
    permission: ['admin'],
    label: 'Admin Options',
  },
  {
    route: route('notifications.index'),
    image: 'menu14.png',
    permission: ['admin', 'notifications'],
    label: 'Notifications',
  },
  {
    route: route('notes.index'),
    image: 'menu15.png',
    permission: ['admin', 'notes'],
    label: 'Notes',
  },
  {
    route: route('files.index'),
    image: 'menu16.png',
    permission: ['admin', 'files'],
    label: 'Files',
  },
];
</script>

<template>
  <Head><title>Dashboard</title></Head>

  <div class="admin-access">
    <div class="container-fluid">
      <div class="logout-text">
        <LogoutButton />
      </div>
      <Link :href="route('dashboard')" class="d-block text-center">
        <img src="/images/logo.png" alt="Potato Pal" class="logo" />
      </Link>
      <div class="admin-menu">
        <h5>Welcome {{ $page.props.auth.user.name }}</h5>
        <div class="row">
          <template v-for="menu in menus" :key="menu.image">
            <div v-if="hasRole(menu.permission)" class="col-4 col-sm-3">
              <div class="menu-icon" :class="{ 'opacity-50': menu.route === '' }">
                <Link :href="menu.route">
                  <img :src="`/images/${menu.image}`" :alt="menu.label" />
                  <span>{{ menu.label }}</span>
                </Link>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>

  <InstallConfirmedModal />
</template>
