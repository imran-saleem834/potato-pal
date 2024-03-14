<script setup>
import { computed } from 'vue';

const props = defineProps({
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

const emit = defineEmits([
  'unset',
  'edit',
  'new',
  'store',
  'update',
  'delete',
  'duplicate',
  'print',
]);
</script>

<template>
  <ul class="text-end">
    <li v-if="!isNewRecordSelected && activeTab && access.delete">
      <a
        role="button"
        data-bs-toggle="modal"
        data-bs-target="#delete-record"
        class="btn btn-transparent"
        title="Delete"
      >
        <i class="bi bi-trash"></i>
      </a>
    </li>
    <li v-if="!isNewRecordSelected && activeTab && !isEditRecordSelected && access.edit">
      <a role="button" @click="$emit('edit')" class="btn btn-red" title="Edit">
        <i class="bi bi-pen"></i> <span class="d-none d-md-inline-block">Edit</span>
      </a>
    </li>
    <li v-if="!isNewRecordSelected && isEditRecordSelected">
      <a
        role="button"
        title="Update"
        class="btn btn-red"
        data-bs-toggle="modal"
        data-bs-target="#update-record"
      >
        <i class="bi bi-check-lg"></i> <span class="d-none d-md-inline-block">Update</span>
      </a>
    </li>
    <li v-if="isNewRecordSelected">
      <a
        role="button"
        title="Create"
        class="btn btn-red"
        data-bs-toggle="modal"
        data-bs-target="#store-record"
      >
        <i class="bi bi-check-lg"></i> <span class="d-none d-md-inline-block">Create</span>
      </a>
    </li>
    <li
      v-if="!isNewRecordSelected && access.new && access.edit"
      class="d-inline-block d-lg-none"
      title="Add new"
    >
      <a role="button" @click="$emit('new')" class="btn btn-red">
        <i class="bi bi-plus-lg"></i> <span class="d-none d-md-inline-block">Add</span>
      </a>
    </li>
    <li v-if="!isNewRecordSelected && access.new && !access.edit">
      <a role="button" @click="$emit('new')" class="btn btn-red" title="Add new">
        <i class="bi bi-plus-lg"></i> <span class="d-none d-md-inline-block">Add</span>
      </a>
    </li>
    <li
      v-if="!isNewRecordSelected && activeTab && !isEditRecordSelected && access.duplicate"
    >
      <a
        role="button"
        title="Duplicate"
        class="btn btn-black"
        data-bs-toggle="modal"
        data-bs-target="#duplicate-details"
        @click="$emit('duplicate')"
      >
        <i class="bi bi-copy"></i> <span class="d-none d-md-inline-block">Duplicate</span>
      </a>
    </li>
    <li v-if="!isNewRecordSelected && !isEditRecordSelected && access.print">
      <a role="button" @click="$emit('print')" class="btn btn-red" title="Print">
        <i class="bi bi-printer"></i> <span class="d-none d-md-inline-block">Print</span>
      </a>
    </li>
    <li v-if="activeTab || isNewRecordSelected" class="d-none d-md-inline-block d-lg-none">
      <a
        role="button"
        @click="$emit('unset')"
        class="btn btn-transparent"
        title="Back to list"
      >
        <i class="bi bi-x-lg"></i>
      </a>
    </li>
  </ul>
</template>
