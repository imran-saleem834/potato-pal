<script setup>
import TextInput from "@/Components/TextInput.vue";

defineProps({
    formValues: Array,
    title: String,
    name: String,
    allow: String,
    isForm: Boolean,
    values: {
        type: Array,
        default: []
    },
    errors: {
        type: Object,
        default: {}
    }
});

defineEmits(['update:modelValue', 'change', 'display']);
</script>

<template>
    <div>
        <h6>{{ title }}</h6>
        <ul v-if="isForm" class="multiple-inputs">
            <li v-for="(value, index) in values" :key="value">
                <TextInput
                    :disabled="index === 4"
                    type="text"
                    v-model="formValues[index]"
                    @input="$emit('update:modelValue', $event.target.value)"
                    @keyup="$emit('change')"
                />
            </li>
            <li>{{ allow }}</li>

            <template
                v-for="(value, index) in values"
                :key="index"
            >
                <div v-show="errors[`${name}.${index}`]" class="has-error">
                    <span class="help-block text-left">{{ errors[`${name}.${index}`] }}</span>
                </div>
            </template>
        </ul>
        <h5 v-else>
            {{ $emit('display') }}
        </h5>
    </div>
</template>
