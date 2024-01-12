<script setup>
const emit = defineEmits(['click']);

const props = defineProps({
    items: {
        type: Array,
        default: [],
    },
    error: {
        type: String,
        default: null,
    },
    value: {
        type: [Number, String],
        default: null,
    },
});
</script>

<template>
    <div v-if="error" class="has-error">
        <span class="help-block text-left">{{ error }}</span>
    </div>
    <ul>
        <li v-for="item in items" :key="item.value">
            <a
                role="button"
                @click="$emit('click', item.value)"
                :class="{'black-btn' : value === item.value}"
            >{{ item.label }}</a>
        </li>
    </ul>
</template>
