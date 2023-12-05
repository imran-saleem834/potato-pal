<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LogoutButton from '@/Components/LogoutButton.vue';

const menus = [
    { route: route('users.index'), image: 'menu1.png', label: 'Users' },
    { route: route('receivals.index'), image: 'menu2.png', label: 'Receivals' },
    { route: route('unloading.index'), image: 'menu3.png', label: 'Unloading' },
    { route: route('tia-samples.index'), image: 'menu4.png', label: 'TIA Samples' },
    { route: route('allocations.index'), image: 'menu5.png', label: 'Allocations' },
    { route: '', image: 'menu6.png', label: 'Inventory' },
    { route: '', image: 'menu7.png', label: 'Invoices' },
    { route: '', image: 'menu8.png', label: 'Labels' },
    { route: '', image: 'menu9.png', label: 'Dispatch' },
    { route: '', image: 'menu10.png', label: 'Reports' },
    { route: '', image: 'menu11.png', label: 'Cutting' },
    { route: '', image: 'menu12.png', label: 'Weighbridge' },
    { route: route('categories.index'), image: 'menu13.png', label: 'Admin Options' },
    { route: route('notifications.index'), image: 'menu14.png', label: 'Notifications' },
    { route: route('notes.index'), image: 'menu15.png', label: 'Notes' },
    { route: route('files.index'), image: 'menu16.png', label: 'Files' },
];

</script>

<template>
    <Head><title>Dashboard</title></Head>

    <div class="admin-access">
        <div class="container">
            <div class="logout-text">
                <LogoutButton/>
            </div>
            <div class="top-logo">
                <Link :href="route('dashboard')">
                    <img src="/images/logo.png" alt="Potato Pal">
                </Link>
            </div>
            <div class="admin-menu">
                <h5>Welcome {{ $page.props.auth.user.name }}</h5>
                <div class="row">
                    <div
                        v-for="menu in menus"
                        :key="menu.image"
                        class="col-md-3 col-sm-3 col-xs-3"
                    >
                        <div class="menu-icon" :class="{ 'opacity-0-5' : menu.route === '' }">
                            <Link :href="menu.route">
                                <img :src="`/images/${menu.image}`" :alt="menu.label">
                                <span>{{ menu.label }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
