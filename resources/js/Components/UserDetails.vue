<template>
    <div class="row">
        <div :class="colSize">
            <div class="user-boxes">
                <h6>Name</h6>
                <div v-if="isForm" class="form-group" :class="{'has-error' : form.errors.name}">
                    <input type="text" class="form-control" v-model="form.name">
                    <span v-show="form.errors.name" class="help-block text-left">{{ form.errors.name }}</span>
                </div>
                <h5 v-else>{{ user.name }}</h5>

                <h6>Email</h6>
                <div v-if="isForm" class="form-group" :class="{'has-error' : form.errors.email}">
                    <input type="text" class="form-control" v-model="form.email">
                    <span v-show="form.errors.email" class="help-block text-left">{{ form.errors.email }}</span>
                </div>
                <h5 v-else>{{ user.email }}</h5>

                <h6>Username</h6>
                <div v-if="isForm" class="form-group" :class="{'has-error' : form.errors.username}">
                    <input type="text" class="form-control" v-model="form.username">
                    <span v-show="form.errors.username" class="help-block text-left">{{ form.errors.username }}</span>
                </div>
                <h5 v-else>{{ user.username }}</h5>

                <h6>Phone</h6>
                <div v-if="isForm" class="form-group" :class="{'has-error' : form.errors.phone}">
                    <input type="text" class="form-control" v-model="form.phone">
                    <span v-show="form.errors.phone" class="help-block text-left">{{ form.errors.phone }}</span>
                </div>
                <h5 v-else>{{ user.phone }}</h5>

                <div v-if="isNew">
                    <h6>Password</h6>
                    <div class="form-group" :class="{'has-error' : form.errors.password}">
                        <input type="text" class="form-control" v-model="form.password">
                        <span v-show="form.errors.password" class="help-block text-left">{{ form.errors.password }}</span>
                    </div>
                    <h6>Confirmation Password</h6>
                    <div class="form-group" :class="{'has-error' : form.errors.password_confirmation}">
                        <input type="text" class="form-control" v-model="form.password_confirmation">
                        <span v-show="form.errors.password_confirmation" class="help-block text-left">{{ form.errors.password_confirmation }}</span>
                    </div>
                </div>

                <h6 class="hidden">User Access</h6>
                <ul class="hidden">
                    <li><a href="">Admin</a></li>
                    <li><a href="">Grower</a></li>
                    <li><a href="">Buyer</a></li>
                </ul>
            </div>

            <h4>Grower Details</h4>
            <div class="user-boxes">
                <h6 class="hidden">Grower Group</h6>
                <ul class="hidden">
                    <li><a href="">McCain</a></li>
                    <li><a href="">Simplot</a></li>
                </ul>

                <h6>Grower Name</h6>
                <div v-if="isForm" class="form-group" :class="{'has-error' : form.errors.grower_name}">
                    <input type="text" class="form-control" v-model="form.grower_name">
                    <span v-show="form.errors.grower_name" class="help-block text-left">{{ form.errors.grower_name }}</span>
                </div>
                <h5 v-else>{{ user.grower_name }}</h5>

                <h6 class="hidden">Unique Tags</h6>
                <ul class="hidden">
                    <li><a href="">Abcd</a></li>
                    <li><a href="">Xyzd</a></li>
                </ul>
            </div>
        </div>
        <div :class="colSize">
            <h4 class="hidden">Buyer Details</h4>
            <div class="user-boxes hidden">
                <h6>Grower Group</h6>
                <h5>McCain</h5>
                <h6>Unique Tags</h6>
                <ul>
                    <li><a href="">Abcd</a></li>
                    <li><a href="">Xyzd</a></li>
                </ul>
            </div>

            <h4 class="hidden">Paddocks</h4>
            <div class="user-boxes hidden">
                <div class="user-column-two">
                    <div>
                        <h6>Paddock Name</h6>
                        <h5>Sheharyar’s Paddock 1</h5>
                        <h6>Paddock Name</h6>
                        <h5>Sheharyar’s Paddock 1</h5>
                        <h6>Paddock Name</h6>
                        <h5>Sheharyar’s Paddock 1</h5>
                    </div>
                    <div>
                        <h6>No of Hactares</h6>
                        <h5>40</h5>
                        <h6>No of Hactares</h6>
                        <h5>40</h5>
                        <h6>No of Hactares</h6>
                        <h5>40</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a v-if="isEdit" role="button" @click="updateRecord" class="add-button btn btn-red">Update</a>
    <a v-if="isNew" role="button" @click="storeRecord" class="add-button btn btn-red">Create</a>
</template>

<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";

const options = [{
    id: 'a',
    label: 'a',
    children: [{
        id: 'aa',
        label: 'aa',
    }, {
        id: 'ab',
        label: 'ab',
    }],
}, {
    id: 'b',
    label: 'b',
}, {
    id: 'c',
    label: 'c',
}];

const props = defineProps({
    user: Object,
    colSize: String,
    isEdit: Boolean,
    isNew: Boolean,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    username: props.user.username,
    phone: props.user.phone,
    role: props.user.role,
    grower_name: props.user.grower_name,
    grower_tags: props.user.grower_tags,
    buyer_tags: props.user.buyer_tags,
    paddocks: props.user.paddocks,
    password: '',
    password_confirmation: '',
});

watch(() => props.user,
    (user) => {
        form.clearErrors();
        form.name = user.name
        form.email = user.email
        form.username = user.username
        form.phone = user.phone
        form.password = ''
        form.password_confirmation = ''
        form.grower_name = user.grower_name
        form.grower_tags = user.grower_tags
        form.buyer_tags = user.buyer_tags
        form.paddocks = user.paddocks
    }
);

const isForm = computed(() => {
    return props.isEdit || props.isNew;
})

const updateRecord = () => {
    form.patch(route('users.update', props.user.id), {
        onSuccess: () => {
            console.log('Hello user updated');
        },
    });
}

const storeRecord = () => {
    form.post(route('users.store'), {
        onSuccess: () => {
            console.log('Hello user updated');
        },
    });
}
</script>
