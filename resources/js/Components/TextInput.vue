<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String|Number,
    type: {
        type: String,
        default: 'text',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: ''
    }
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="form-group" :class="{'has-error' : error}">
        <div :class="{ 'input-group' : $slots.addon }">
            <input
                ref="input"
                :type="type"
                class="form-control"
                :disabled="disabled"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
            >
            <slot name="addon"/>
        </div>
        <span v-show="error" class="help-block text-left">{{ error }}</span>
    </div>
</template>
