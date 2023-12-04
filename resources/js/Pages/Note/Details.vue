<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Multiselect from '@vueform/multiselect'
import TextInput from "@/Components/TextInput.vue";
import Images from "@/Components/Images.vue";

const props = defineProps({
    note: Object,
    isEdit: Boolean,
    isNew: Boolean,
});

const emit = defineEmits(['updateRecord']);

const form = useForm({
    title: props.note.title,
    note: props.note.note,
    tags: props.note.tags,
});

watch(() => props.note,
    (note) => {
        form.clearErrors();
        form.title = note.title
        form.note = note.note
        form.tags = note.tags
    }
);

const isForm = computed(() => {
    return props.isEdit || props.isNew;
})

const updateRecord = () => {
    form.patch(route('notes.update', props.note.id), {
        onSuccess: () => {
            emit('updateRecord')
        },
    });
}

const storeRecord = () => {
    form.post(route('notes.store'), {
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
            <div v-if="isForm" class="user-boxes">
                <h6>Title</h6>
                <TextInput v-model="form.title" :error="form.errors.title" type="text"/>

                <h6>Note</h6>
                <div class="form-group" :class="{'has-error' : form.errors.note}">
                    <textarea v-model="form.note" class="form-control" rows="5"></textarea>
                    <span v-show="form.errors.note" class="help-block text-left">{{ form.errors.note }}</span>
                </div>

                <h6>Unique Tags</h6>
                <div v-if="form.errors.tags" class="has-error">
                    <span v-show="form.errors.tags" class="help-block text-left" v-text="form.errors.tags" />
                </div>
                <div v-if="form.errors['tags.0']" class="has-error">
                    <span v-show="form.errors['tags.0']" class="help-block text-left" v-text="form.errors['tags.0']" />
                </div>
                <div v-if="form.errors['tags.1']" class="has-error">
                    <span v-show="form.errors['tags.1']" class="help-block text-left" v-text="form.errors['tags.1']" />
                </div>
                <Multiselect
                    v-model="form.tags"
                    mode="tags"
                    placeholder="Choose a tags"
                    :searchable="true"
                    :create-option="true"
                    :options="form.tags"
                />
            </div>
            <div v-if="!isNew" class="user-boxes notes-list">
                <p v-if="!isForm">{{ note.note }}</p>

                <Images
                    v-if="!isNew"
                    :images="note.images"
                    :upload-route="route('notes.upload', note.id || 0)"
                    :delete-route="route('notes.delete', note.id || 0)"
                    @updateRecord="emit('updateRecord')"
                />
            </div>
        </div>
    </div>
</template>
