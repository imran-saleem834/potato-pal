<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    page: String,
    title: String,
});
</script>

<template>
    <ol class="breadcrumb">
        <li>
            <Link :href="route('dashboard')" data-dismiss="modal" aria-label="Close">Menu</Link>
        </li>
        <li>
            <Link href="" data-dismiss="modal" aria-label="Close">{{ page }}</Link>
        </li>
        <li class="active">{{ title }}</li>
    </ol>
</template>
