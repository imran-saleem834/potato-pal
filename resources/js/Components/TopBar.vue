<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import LogoutButton from '@/Components/LogoutButton.vue';
import ConfirmedModal from '@/Components/ConfirmedModal.vue';
import Actions from '@/Components/Actions.vue';

const props = defineProps({
  type: String,
  title: String,
  search: {
    type: String,
    default: '',
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
      search: true,
      new: true,
      edit: true,
      delete: true,
      duplicate: false,
      print: false,
      filter: false,
    },
  },
});

const access = computed(() => ({
  search: true,
  new: true,
  edit: true,
  delete: true,
  duplicate: false,
  ...props.access,
}));

const keyword = ref(props.search);
const isSearchVisible = ref(false);

const emit = defineEmits(['search', 'unset', 'edit', 'new', 'store', 'update', 'delete', 'duplicate', 'print']);

const search = () => {
  emit('search', keyword.value);
};
</script>

<template>
  <div class="main-section">
    <div class="container-fluid">
      <div class="d-flex d-md-none justify-content-between mt-3 mobile-topbar">
        <template v-if="$slots.back">
          <slot name="back" />
        </template>
        <template v-else>
          <Link :href="route('dashboard')"><i class="bi bi-chevron-compact-left"></i></Link>
        </template>
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
            <Link :href="route('dashboard')"><img src="/images/logo.png" alt="logo" /></Link>
          </div>
        </div>
        <div :class="{ 'd-none': !isSearchVisible }" class="col-12 col-md-6 d-md-block order-3 order-md-2">
          <div v-if="access.search" class="form-group position-relative">
            <i class="bi bi-search form-control-feedback"></i>
            <input
              type="text"
              class="form-control custom-input"
              v-model="keyword"
              @input="search"
              :placeholder="`Search ${type}`"
            />
          </div>
        </div>
        <div class="col-6 col-md-3 d-none d-md-block order-2 order-md-3">
          <div class="logout-top">
            <LogoutButton />
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="middle-section mt-3 mt-md-0">
    <div class="row g-0">
      <div class="col-4 col-lg-5 col-xl-4 d-none d-lg-block">
        <div class="d-flex justify-content-between align-items-center middle-left h-100">
          <template v-if="$slots.breadcrumbs">
            <slot name="breadcrumbs" />
          </template>
          <template v-else>
            <ul>
              <li>
                <Link :href="route('dashboard')"><span class="fa fa-arrow-left"></span> Menu</Link>
              </li>
              <li><i class="bi bi-chevron-right"></i></li>
              <li>
                <Link :href="route(route().current())">{{ type }}</Link>
              </li>
            </ul>
          </template>

          <ul>
            <li v-if="!isNewRecordSelected && !isEditRecordSelected && access.filter" title="Filter">
              <a
                role="button"
                class="btn btn-transparent"
                data-bs-toggle="offcanvas"
                data-bs-target="#search-filter"
                aria-controls="search-filter"
              ><i class="bi bi-funnel"></i></a>
            </li>
            <li v-if="!isNewRecordSelected && access.new && access.edit">
              <a role="button" @click="$emit('new')" class="btn btn-red"> <i class="bi bi-plus-lg"></i> Add </a>
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
          <ul class="text-start d-inline-block d-md-none">
            <li v-if="activeTab || isNewRecordSelected">
              <a role="button" @click="$emit('unset')" class="btn btn-transparent" title="Back to list">
                <i class="bi bi-arrow-90deg-left"></i>
              </a>
            </li>
          </ul>

          <Actions
            :active-tab="activeTab"
            :is-edit-record-selected="isEditRecordSelected"
            :is-new-record-selected="isNewRecordSelected"
            :access="access"
            @new="$emit('new')"
            @edit="$emit('edit')"
            @unset="$emit('unset')"
            @store="$emit('store')"
            @update="$emit('update')"
            @delete="$emit('delete')"
            @duplicate="$emit('duplicate')"
          />
        </div>
      </div>
    </div>
  </div>

  <ConfirmedModal id="delete-record" cancel="No, Keep it" ok="Yes, Delete!" @ok="$emit('delete')" />

  <ConfirmedModal id="store-record" title="You want to store this record?" @ok="$emit('store')" />

  <ConfirmedModal id="update-record" title="You want to update this record?" ok="Yes, Update!" @ok="$emit('update')" />
</template>
