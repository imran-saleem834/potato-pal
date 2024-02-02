<script setup>
defineProps({
  id: String,
  title: {
    type: String,
    default: 'You want to remove this record?',
  },
  cancel: {
    type: String,
    default: 'Cancel',
  },
  ok: {
    type: String,
    default: 'Yes',
  }
});

defineEmits(['ok']);
</script>

<template>
  <div class="modal fade" :id="id" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content rounded-1">
        <div class="modal-body">
          <div class="d-flex flex-column align-items-center gap-2">
            <div class="error_icon">
              <i class="bx bxs-error"></i>
            </div>
            <div class="d-flex flex-column align-items-center">
              <h4 class="fw-bold fs-4">Are you sure?</h4>
              <p v-text="title"/>
            </div>
          </div>
          <div class="d-flex justify-content-center pt-3 gap-3">
            <div>
              <button
                class="border-0 w-100 rounded-1 px-5 p-2 btn-secondary font-16"
                data-bs-dismiss="modal"
                v-text="cancel"
              />
            </div>
            <div>
              <button
                @click="$emit('ok')"
                class="border-0 w-100 rounded-1 px-5 p-2 btn-red"
                data-bs-dismiss="modal"
                v-text="ok"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
