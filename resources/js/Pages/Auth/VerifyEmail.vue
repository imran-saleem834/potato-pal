<script setup>
import { Link, Head } from '@inertiajs/vue3';

const props = defineProps({
    status: String,
});
</script>

<template>
    <Head title="Account Verification" />

    <div  class="login-section account-approval">
        <div class="container">
            <div class="login-box">
                <p>
                    <Link :href="route('dashboard')">
                        <img src="/images/logo.png" class="logo" alt="Potato Pal">
                    </Link>
                </p>
                <div class="approval-img">
                    <img src="/images/hourglass-start.png" alt="hourglass">
                </div>
                <p>Your account is under progress once <span class="block"></span> completed you’ll be notified.</p>

                <a href="tel:0364261590" class="btn btn-red"><span class="fa fa-call"></span> (03) 6426 1590</a>

                <p>For any inquiries free to give us a call</p>
            </div>
        </div>
    </div>
</template>
