<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  type: String,
  title: String,
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

defineEmits(['editRecord', 'newRecord', 'deleteRecord', 'duplicate']);
</script>

<template>
  <div class="middle-section">
    <div class="middle-left">
      <div class="user-menu">
        <div class="menu-left">
          <ul>
            <li>
              <Link :href="route('dashboard')"><span class="fa fa-arrow-left"></span> Menu</Link>
            </li>
            <li><span class="fa fa-chevron-right"></span></li>
            <li>
              <Link :href="route(route().current())">{{ type }}</Link>
            </li>
          </ul>
        </div>
        <div class="menu-right">
          <ul>
            <li class="hidden">
              <a role="button" data-toggle="modal" data-target="#myModal2" class="filter-btn">
                <span class="fa fa-filter"></span>
              </a>
            </li>
            <li v-if="!isNewRecordSelected && access.new && access.edit">
              <a role="button" @click="$emit('newRecord')" class="btn-red">
                <span class="fa fa-plus"></span> Add
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="middle-right">
      <div class="user-right-flex">
        <div><h4>{{ title }}</h4></div>
        <div class="user-right-button">
          <ul>
            <li v-if="!isNewRecordSelected && access.delete">
              <a role="button" @click="$emit('deleteRecord')" class="filter-btn">
                <span class="fa fa-trash-o"></span>
              </a>
            </li>
            <li v-if="!isNewRecordSelected && !isEditRecordSelected && access.edit">
              <a role="button" @click="$emit('editRecord')" class="btn-red">
                <span class="fa fa-edit"></span> Edit
              </a>
            </li>
            <li v-if="!isNewRecordSelected && access.new && !access.edit">
              <a role="button" @click="$emit('newRecord')" class="btn-red">
                <span class="fa fa-plus"></span> Add
              </a>
            </li>
            <li v-if="!isNewRecordSelected && !isEditRecordSelected && access.duplicate">
              <a
                role="button"
                class="btn-red"
                data-toggle="modal"
                @click="$emit('duplicate')"
                data-target="#duplicate-details"
              >
                <span class="fa fa-clone"></span> Duplicate
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
