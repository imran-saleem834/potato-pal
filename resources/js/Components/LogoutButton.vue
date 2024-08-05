<script setup>
import { router } from '@inertiajs/vue3';

const logout = () => {
  router.post(route('logout'));
};

const loginAs = (role) => {
  router.post(route('change-role', role));
};
</script>

<template>
  <div class="collapse navbar-collapse show">
    <ul class="navbar-nav">
      <li class="nav-item dropdown">
        <a
          class="nav-link dropdown-toggle d-none d-sm-inline-block"
          href="javascript:;"
          role="button"
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
          <img
            :src="$page.props.auth.user.profile_photo_url"
            :alt="$page.props.auth.user.name"
            class="rounded-circle"
          />
          <span class="ms-2">{{ $page.props.auth.user.name }}</span>
        </a>
        <a
          class="nav-link dropdown-toggle d-sm-none"
          href="javascript:;"
          role="button"
          data-bs-toggle="modal"
          data-bs-target="#modal-logout"
        >
          <img
            :src="$page.props.auth.user.profile_photo_url"
            :alt="$page.props.auth.user.name"
            class="rounded-circle"
          />
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <h6 class="dropdown-header">{{ $page.props.auth.user.name }}</h6>
          </li>
          <li v-for="role in $page.props.auth.user.access" :key="role">
            <a
              href="javascript:;"
              class="dropdown-item" 
              @click.prevent="loginAs(role)"
              :class="{ 'active' : $page.props.auth.user.role === role }"
            >
              Login as {{ role.toUpperCase() }}
            </a>
          </li>
          <li><a class="dropdown-item" href="javascript:;" @click.prevent="logout">Logout</a></li>
        </ul>
      </li>
    </ul>
  </div>

  <div class="modal fade" id="modal-logout" tabindex="-1" aria-labelledby="modal-logout-label" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-logout">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="modal-logout-label">{{ $page.props.auth.user.name }}</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <ul class="list-unstyled">
            <li v-for="role in $page.props.auth.user.access" :key="role" class="p-1">
              <a
                href="javascript:;"
                class="dropdown-item"
                @click.prevent="loginAs(role)"
                :class="{ 'active' : $page.props.auth.user.role === role }"
              >
                Login as {{ role.toUpperCase() }}
              </a>
            </li>
          </ul>
          <div class="d-flex justify-content-between">
            <div>
              <img
                :src="$page.props.auth.user.profile_photo_url"
                :alt="$page.props.auth.user.name"
                class="rounded-circle"
              />
              <span class="ms-2">{{ $page.props.auth.user.name }}</span>
            </div>
            <button class="btn btn-red" type="button" @click.prevent="logout">Logout</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
