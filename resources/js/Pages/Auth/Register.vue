<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import LoginCard from '@/Components/LoginCard.vue';

const form = useForm({
    name: '',
    email: '',
    username: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms: true,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <div class="signup-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-offset-2 col-lg-3 col-md-offset-2 col-md-4 col-sm-6">
                    <LoginCard
                        type="register"
                        @submit="submit"
                    >
                        <div class="form-group has-feedback" :class="{'has-error' : form.errors.name}">
                            <span class="fa fa-user-o form-control-feedback"></span>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="form-control customInput"
                                placeholder="Shehar Yar"
                                autofocus
                                required
                                autocomplete="name"
                            >
                            <span v-show="form.errors.name" class="help-block text-left">{{ form.errors.name }}</span>
                        </div>
                        <div class="form-group has-feedback" :class="{'has-error' : form.errors.email}">
                            <span class="fa fa-envelope-o form-control-feedback"></span>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="form-control customInput"
                                placeholder="shehar@next-x.com.au"
                                required
                                autocomplete="username"
                            >
                            <span v-show="form.errors.email" class="help-block text-left">{{ form.errors.email }}</span>
                        </div>
                        <div class="form-group has-feedback" :class="{'has-error' : form.errors.username}">
                            <span class="fa fa-user-plus form-control-feedback"></span>
                            <input
                                id="name"
                                v-model="form.username"
                                type="text"
                                class="form-control customInput"
                                placeholder="sheharyar"
                                required
                                autocomplete="username"
                            >
                            <span v-show="form.errors.username" class="help-block text-left">{{ form.errors.username }}</span>
                        </div>
                        <div class="form-group has-feedback" :class="{'has-error' : form.errors.phone}">
                            <span class="fa fa-phone form-control-feedback"></span>
                            <input
                                id="name"
                                v-model="form.phone"
                                type="text"
                                class="form-control customInput"
                                placeholder="04727294564"
                                required
                                autocomplete="phone"
                            >
                            <span v-show="form.errors.phone" class="help-block text-left">{{ form.errors.phone }}</span>
                        </div>
                        <div class="form-group has-feedback" :class="{'has-error' : form.errors.password}">
                            <span class="fa fa-globe form-control-feedback"></span>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="form-control customInput"
                                placeholder="***************"
                                required
                                autocomplete="new-password"
                            >
                            <span v-show="form.errors.password" class="help-block text-left">{{ form.errors.password }}</span>
                        </div>
                        <div class="form-group has-feedback" :class="{'has-error' : form.errors.password_confirmation}">
                            <span class="fa fa-globe form-control-feedback"></span>
                            <input
                                id="password"
                                v-model="form.password_confirmation"
                                type="password"
                                class="form-control customInput"
                                placeholder="***************"
                                required
                                autocomplete="new-password"
                            >
                            <span v-show="form.errors.password_confirmation" class="help-block text-left">{{ form.errors.password_confirmation }}</span>
                        </div>
                        <p>
                            <input
                                type="submit"
                                value="Create Account"
                                class="btn btn-red-large"
                                :disabled="form.processing"
                            >
                        </p>
                    </LoginCard>
                </div>
                <!-- col -->
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="slider-box">
                        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                            <!-- Indicators -->
                            <ol class="carousel-indicators">
                                <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
                                <li data-target="#carousel-example-generic" data-slide-to="1"></li>
                                <li data-target="#carousel-example-generic" data-slide-to="2"></li>
                            </ol>
                            <!-- Wrapper for slides -->
                            <div class="carousel-inner" role="listbox">
                                <div class="item active">
                                    <img src="images/carrot.png" alt="...">
                                    <div class="">
                                        <h4>Baby Steps Count</h4>
                                        <p>
                                            One step forward at a time
                                        </p>
                                    </div>
                                </div>
                                <div class="item">
                                    <img src="images/carrot.png" alt="...">
                                    <div class="">
                                        <h4>Baby Steps Count</h4>
                                        <p>
                                            One step forward at a time
                                        </p>
                                    </div>
                                </div>
                                <div class="item">
                                    <img src="images/carrot.png" alt="...">
                                    <div class="">
                                        <h4>Baby Steps Count</h4>
                                        <p>
                                            One step forward at a time
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- slider-box -->
                </div>
            </div>
        </div>
    </div>
</template>
