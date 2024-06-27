<script setup>
import { router } from '@inertiajs/vue3';

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <div class="collapse navbar-collapse show">
    <ul class="navbar-nav">
      <li class="nav-item dropdown">
        <a
          class="nav-link dropdown-toggle"
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
          <span class="ms-2 d-none d-sm-inline-block">{{ $page.props.auth.user.name }}</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><h6 class="dropdown-header">{{ $page.props.auth.user.name }}</h6></li>
          <li><a class="dropdown-item" href="javascript:;" @click.prevent="logout">Logout</a></li>
        </ul>
      </li>
    </ul>
  </div>
</template>
