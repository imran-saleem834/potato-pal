<script setup>
import { Link } from '@inertiajs/vue3';
import LogoutButton from '@/Components/LogoutButton.vue';
import { ref } from 'vue';

const props = defineProps({
  type: String,
  value: {
    type: String,
    default: ''
  },
  access: {
    type: Object,
    default: {
      new: true,
    }
  }
});

const keyword = ref(props.value);

const emit = defineEmits(['newRecord', 'search']);

const newRecord = () => {
  emit('newRecord');
};

const search = () => {
  emit('search', keyword.value);
};
</script>

<template>
  <div class="main-section">
    <div class="container-fluid">
      <div class="topbar">
        <div class="row">
          <div class="col-lg-3 col-sm-3">
            <div class="user-logo">
              <Link :href="route('dashboard')"><img src="/images/logo.png" alt="logo"></Link>
            </div>
          </div>
          <div class="col-lg-6 col-sm-6">
            <div class="form-group has-feedback">
              <span class="fa fa-search form-control-feedback"></span>
              <input
                type="text"
                class="form-control customInput"
                v-model="keyword"
                @input="search"
                :placeholder="`Search ${type}`"
              >
            </div>
          </div>
          <div class="col-lg-3 col-sm-3">
            <div class="logout-top">
              <LogoutButton/>
            </div>
          </div>
        </div>
      </div>
      <div class="mobile-topbar visible-xs">
        <div class="container">
          <div class="topbar-flex">
            <p>
              <Link :href="route('dashboard')"><span class="fa fa-angle-left"></span></Link>
            </p>
            <h4>{{ type }}</h4>
            <p>
              <a
                v-if="access.new"
                role="button"
                @click="newRecord"
                class="mobile-redbtn"
                data-toggle="modal"
                data-target="#user-details"
              >Add</a>
            </p>
          </div>
          <!-- topbar-flex -->
          <p class="mobile-filtericon hidden">
            <a href="#" data-toggle="modal" data-target="#myModal2">Filter <img src="/images/filter-list.png"
                                                                                alt="filter"></a>
          </p>
          <div class="form-group">
            <input
              type="text"
              class="form-control"
              v-model="keyword"
              @input="search"
              :placeholder="`Search for a ${type.toLowerCase()} here...`"
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
