<script setup>
const props = defineProps({
  message: String,
  toastId: Number,
});

const emit = defineEmits(['callback', 'closeToast']);

const confirm = () => {
  emit('callback');
  emit('closeToast', props.toastId);
};
</script>

<template>
  {{ message }}
  <a href="javascript:;" class="text-danger" @click.stop="confirm">confirm it</a>
</template>
