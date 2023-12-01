<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import Images from "@/Components/Images.vue";

const props = defineProps({
    file: Object,
    colSize: String,
    isEdit: Boolean,
    isNew: Boolean,
});

const emit = defineEmits(['updateRecord']);

const form = useForm({
    title: props.file.title,
});

watch(() => props.file,
    (file) => {
        form.clearErrors();
        form.title = file.title
    }
);

const isForm = computed(() => {
    return props.isEdit || props.isNew;
})

const updateRecord = () => {
    form.patch(route('files.update', props.file.id), {
        onSuccess: () => {
            emit('updateRecord')
        },
    });
}

const storeRecord = () => {
    form.post(route('files.store'), {
        onSuccess: () => {
            emit('updateRecord')
        },
    });
}
</script>

<template>
    <div class="row">
        <div v-if="isEdit || isNew" class="col-md-12">
            <div class="flex-end create-update-btn">
                <a v-if="isEdit" role="button" @click="updateRecord" class="btn btn-red">Update</a>
                <a v-if="isNew" role="button" @click="storeRecord" class="btn btn-red">Create</a>
            </div>
        </div>
        <div class="col-md-12">
            <div class="user-boxes">
                <h6>Title</h6>
                <TextInput v-if="isForm" v-model="form.title" :error="form.errors.title" type="text"/>
                <h5 v-else>{{ file.title }}</h5>

                <div v-if="!isNew" class="notes-list">
                    <Images
                        v-if="!isNew"
                        :images="file.images"
                        :upload-route="route('files.upload', file.id || 0)"
                        :delete-route="route('files.delete', file.id || 0)"
                        @updateRecord="emit('updateRecord')"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
