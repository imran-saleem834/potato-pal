<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import LogoutButton from '@/Components/LogoutButton.vue';

const props = defineProps({
  type: String,
  title: String,
  search: {
    type: String,
    default: ''
  },
  activeTab: {
    type: [String, Number],
    default: null,
  },
  isEditRecordSelected: {
    type: Boolean,
    default: false,
  },
  isNewRecordSelected: {
    type: Boolean,
    default: false,
  },
  access: {
    type: Object,
    default: {
      new: true,
      edit: true,
      delete: true,
      duplicate: false,
    }
  }
});

const access = computed(() => ({
  new: true,
  edit: true,
  delete: true,
  duplicate: false,
  ...props.access,
}));

const keyword = ref(props.search);

const emit = defineEmits(['search', 'unset', 'edit', 'new', 'store', 'update', 'delete', 'duplicate']);

const search = () => {
  emit('search', keyword.value);
};
</script>

<template>
  <div class="main-section">
    <div class="container-fluid">
      <div class="d-flex d-md-none justify-content-between mt-3 mobile-topbar">
        <Link :href="route('dashboard')"><i class="bi bi-chevron-compact-left"></i></Link>
        <h4>
          <template v-if="activeTab || isEditRecordSelected">{{ title }}</template>
          <template v-else>{{ type }}</template>
        </h4>
        <div class="mt-1">
          <LogoutButton />
        </div>
      </div>
      <div class="row topbar">
        <div class="col-6 col-md-3 d-none d-md-block order-1">
          <div class="user-logo">
            <Link :href="route('dashboard')"><img src="/images/logo.png" alt="logo"></Link>
          </div>
        </div>
        <div class="col-12 col-md-6 d-none d-md-block order-3 order-md-2">
          <div class="form-group position-relative">
            <i class="bi bi-search form-control-feedback"></i>
            <input
              type="text"
              class="form-control custom-input"
              v-model="keyword"
              @input="search"
              :placeholder="`Search ${type}`"
            >
          </div>
        </div>
        <div class="col-6 col-md-3 d-none d-md-block order-2 order-md-3">
          <div class="logout-top">
            <LogoutButton/>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="middle-section mt-3 mt-md-0">
    <div class="row g-0">
      <div class="col-4  col-lg-5 col-xl-4 d-none d-lg-block">
        <div class="d-flex justify-content-between align-items-center middle-left h-100">
          <ul>
            <li>
              <Link :href="route('dashboard')"><span class="fa fa-arrow-left"></span> Menu</Link>
            </li>
            <li><i class="bi bi-chevron-right"></i></li>
            <li>
              <Link :href="route(route().current())">{{ type }}</Link>
            </li>
          </ul>

          <ul>
            <li v-if="!isNewRecordSelected && access.new && access.edit">
              <a role="button" @click="$emit('new')" class="btn btn-red">
                <i class="bi bi-plus-lg"></i> Add
              </a>
            </li>
          </ul>
        </div>
      </div>
      <div class="col-12 col-lg-7 col-xl-8">
        <div class="d-flex justify-content-between align-items-center middle-right h-100">
          <h5 class="mb-0 d-none d-md-block">
            <template v-if="activeTab || isEditRecordSelected">{{ title }}</template>
            <template v-else>{{ type }}</template>
          </h5>
          <div class="form-group position-relative d-block d-md-none">
            <i class="bi bi-search form-control-feedback"></i>
            <input
              type="text"
              class="form-control custom-input"
              v-model="keyword"
              @input="search"
              :placeholder="`Search ${type}`"
            >
          </div>
          <ul>
            <li v-if="!isNewRecordSelected && activeTab && access.delete">
              <a role="button" @click="$emit('delete')" class="btn btn-transparent">
                <i class="bi bi-trash"></i>
              </a>
            </li>
            <li v-if="!isNewRecordSelected && activeTab && !isEditRecordSelected && access.edit">
              <a role="button" @click="$emit('edit')" class="btn btn-red">
                <i class="bi bi-pen"></i> <span class="d-none d-md-inline-block">Edit</span>
              </a>
            </li>
            <li v-if="!isNewRecordSelected && isEditRecordSelected">
              <a role="button" @click="$emit('update')" class="btn btn-red">
                <i class="bi bi-check-lg"></i> <span class="d-none d-md-inline-block">Update</span>
              </a>
            </li>
            <li v-if="isNewRecordSelected">
              <a role="button" @click="$emit('store')" class="btn btn-red">
                <i class="bi bi-check-lg"></i> <span class="d-none d-md-inline-block">Create</span>
              </a>
            </li>
            <li v-if="!isNewRecordSelected && access.new && access.edit" class="d-inline-block d-lg-none">
              <a role="button" @click="$emit('new')" class="btn btn-red">
                <i class="bi bi-plus-lg"></i> <span class="d-none d-md-inline-block">Add</span>
              </a>
            </li>
            <li v-if="!isNewRecordSelected && access.new && !access.edit">
              <a role="button" @click="$emit('new')" class="btn btn-red">
                <i class="bi bi-plus-lg"></i> <span class="d-none d-md-inline-block">Add</span>
              </a>
            </li>
            <li v-if="activeTab || isNewRecordSelected" class="d-inline-block d-lg-none">
              <a role="button" @click="$emit('unset')" class="btn-transparent">
                <i class="bi bi-x-circle"></i>
              </a>
            </li>
            
            <li v-if="!isNewRecordSelected && activeTab && !isEditRecordSelected && access.duplicate">
              <a
                role="button"
                class="btn btn-black"
                data-toggle="modal"
                @click="$emit('duplicate')"
                data-target="#duplicate-details"
              >
                <i class="bi bi-copy"></i> <span class="d-none d-md-inline-block">Duplicate</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
