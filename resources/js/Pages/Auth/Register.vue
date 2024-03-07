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
  <Head><title>Register</title></Head>

  <div class="signup-section">
    <div class="row">
      <div class="col-12 col-sm-6">
        <LoginCard type="register" @submit="submit">
          <div class="mb-3 position-relative">
            <i class="bi bi-person-add form-control-feedback"></i>
            <input
              id="name"
              v-model="form.name"
              type="text"
              class="form-control custom-input"
              placeholder="Shehar Yar"
              :class="{ 'is-invalid': form.errors.name }"
              autofocus
              required
              autocomplete="name"
            />
            <span v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</span>
          </div>
          <div class="mb-3 position-relative">
            <i class="bi bi-envelope form-control-feedback"></i>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="form-control custom-input"
              placeholder="shehar@next-x.com.au"
              :class="{ 'is-invalid': form.errors.email }"
              autocomplete="username"
            />
            <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
          </div>
          <div class="mb-3 position-relative">
            <i class="bi bi-person form-control-feedback"></i>
            <input
              id="name"
              v-model="form.username"
              type="text"
              class="form-control custom-input"
              :class="{ 'is-invalid': form.errors.username }"
              placeholder="sheharyar"
              required
              autocomplete="username"
            />
            <div v-if="form.errors.username" class="invalid-feedback">
              {{ form.errors.username }}
            </div>
          </div>
          <div class="mb-3 position-relative">
            <i class="bi bi-telephone form-control-feedback"></i>
            <input
              id="name"
              v-model="form.phone"
              type="text"
              class="form-control custom-input"
              placeholder="04727294564"
              :class="{ 'is-invalid': form.errors.phone }"
              autocomplete="phone"
            />
            <div v-if="form.errors.phone" class="invalid-feedback">{{ form.errors.phone }}</div>
          </div>
          <div class="mb-3 position-relative">
            <i class="bi bi-globe form-control-feedback"></i>
            <input
              id="password"
              v-model="form.password"
              type="password"
              class="form-control custom-input"
              :class="{ 'is-invalid': form.errors.password }"
              placeholder="***************"
              required
              autocomplete="new-password"
            />
            <div v-if="form.errors.password" class="invalid-feedback">
              {{ form.errors.password }}
            </div>
          </div>
          <div class="mb-3 position-relative">
            <i class="bi bi-globe form-control-feedback"></i>
            <input
              id="password"
              v-model="form.password_confirmation"
              type="password"
              class="form-control custom-input"
              :class="{ 'is-invalid': form.errors.password_confirmation }"
              placeholder="***************"
              required
              autocomplete="new-password"
            />
            <div v-if="form.errors.password_confirmation" class="invalid-feedback">
              {{ form.errors.password_confirmation }}
            </div>
          </div>
          <div class="mb-3">
            <input
              type="submit"
              value="Create Account"
              class="btn btn-lg-red"
              :disabled="form.processing"
            />
          </div>
        </LoginCard>
      </div>

      <div class="d-none d-sm-block col-sm-6">
        <div class="slider-box">
          <div id="register-carousel" class="carousel slide" data-bs-ride="true">
            <div class="carousel-indicators">
              <button
                type="button"
                data-bs-target="#register-carousel"
                data-bs-slide-to="0"
                class="active"
                aria-current="true"
                aria-label="Slide 1"
              ></button>
              <button
                type="button"
                data-bs-target="#register-carousel"
                data-bs-slide-to="1"
                aria-label="Slide 2"
              ></button>
              <button
                type="button"
                data-bs-target="#register-carousel"
                data-bs-slide-to="2"
                aria-label="Slide 3"
              ></button>
            </div>
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="/images/carrot.png" class="d-block w-100" alt="carrot" />
                <div>
                  <h4>Baby Steps Count</h4>
                  <p>One step forward at a time</p>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/carrot.png" class="d-block w-100" alt="carrot" />
                <div>
                  <h4>Baby Steps Count</h4>
                  <p>One step forward at a time</p>
                </div>
              </div>
              <div class="carousel-item">
                <img src="/images/carrot.png" class="d-block w-100" alt="carrot" />
                <div>
                  <h4>Baby Steps Count</h4>
                  <p>One step forward at a time</p>
                </div>
              </div>
            </div>
          </div>

          <div id="carousel-example-generic" class="carousel slide d-none" data-ride="carousel">
            <!-- Indicators -->
            <ol class="carousel-indicators">
              <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
              <li data-target="#carousel-example-generic" data-slide-to="1"></li>
              <li data-target="#carousel-example-generic" data-slide-to="2"></li>
            </ol>
            <!-- Wrapper for slides -->
            <div class="carousel-inner" role="listbox">
              <div class="item active">
                <img src="/images/carrot.png" alt="..." />
                <div class="">
                  <h4>Baby Steps Count</h4>
                  <p>One step forward at a time</p>
                </div>
              </div>
              <div class="item">
                <img src="/images/carrot.png" alt="..." />
                <div class="">
                  <h4>Baby Steps Count</h4>
                  <p>One step forward at a time</p>
                </div>
              </div>
              <div class="item">
                <img src="/images/carrot.png" alt="..." />
                <div class="">
                  <h4>Baby Steps Count</h4>
                  <p>One step forward at a time</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
