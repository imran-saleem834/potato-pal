<script setup>
defineProps({
  title: String,
  isNew: Boolean,
});

defineEmits(['delete', 'edit']);
</script>

<template>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span class="fa fa-arrow-left"></span>
    </button>
    <h4 class="modal-title" id="myModalLabel3">{{ title }}</h4>
    <div class="modal-menu">
      <div v-if="!isNew" class="btn-group">
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
          <li>
            <a role="button" @click="$emit('delete')">
              <span class="fa fa-trash-o"></span> Delete
            </a>
          </li>
          <li>
            <a role="button" @click="$emit('edit')">
              <span class="fa fa-edit"></span> Edit
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
