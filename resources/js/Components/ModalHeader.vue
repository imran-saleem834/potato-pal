<script setup>
defineProps({
  title: String,
  access: {
    type: Object,
    default: {
      new: false,
      edit: true,
      delete: true,
      duplicate: false,
    }
  }
});

defineEmits(['delete', 'edit', 'duplicate']);
</script>

<template>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span class="fa fa-arrow-left"></span>
    </button>
    <h4 class="modal-title" id="myModalLabel3">{{ title }}</h4>
    <div class="modal-menu">
      <div v-if="!access.new && (access.delete || access.edit)" class="btn-group">
        <button
          type="button"
          class="dropdown-toggle"
          data-toggle="dropdown"
          aria-haspopup="true"
          aria-expanded="false"
        >
          <span class="fa fa-ellipsis-v"></span>
        </button>
        <ul class="dropdown-menu">
          <li v-if="access.delete">
            <a role="button" @click="$emit('delete')">
              <span class="fa fa-trash-o"></span> Delete
            </a>
          </li>
          <li v-if="access.edit">
            <a role="button" @click="$emit('edit')">
              <span class="fa fa-edit"></span> Edit
            </a>
          </li>
          <li v-if="access.duplicate">
            <a 
               role="button"
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
</template>
