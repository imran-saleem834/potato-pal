<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from "vue";

const props = defineProps({
  type: String,
  title: String,
  search: {
    type: String,
    default: ''
  },
  activeTab: {
    type: Number,
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

const keyword = ref(props.search);

const emit = defineEmits(['search', 'editRecord', 'newRecord', 'deleteRecord', 'duplicate']);

const search = () => {
  emit('search', keyword.value);
};
</script>

<template>
  <div class="middle-section mt-3 mt-md-0">
    <div class="row g-0">
      <div class="col-4 col-md-4 d-none d-md-block">
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
              <a role="button" @click="$emit('newRecord')" class="btn btn-red">
                <i class="bi bi-plus-lg"></i> Add
              </a>
            </li>
          </ul>
        </div>
      </div>
      <div class="col-12 col-md-8">
        <div class="d-flex justify-content-between align-items-center middle-right h-100">
          <h5 class="mb-0 d-none d-md-block">{{ title }}</h5>
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
              <a role="button" @click="$emit('deleteRecord')" class="btn btn-transparent">
                <i class="bi bi-trash"></i>
              </a>
            </li>
            <li v-if="!isNewRecordSelected && activeTab && !isEditRecordSelected && access.edit">
              <a role="button" @click="$emit('editRecord')" class="btn btn-red">
                <i class="bi bi-pen"></i> Edit
              </a>
            </li>
            <li v-if="!isNewRecordSelected && access.new && !access.edit">
              <a role="button" @click="$emit('newRecord')" class="btn btn-red">
                <i class="bi bi-plus-lg"></i> Add
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
                <i class="bi bi-copy"></i> Duplicate
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
