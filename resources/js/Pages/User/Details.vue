<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { getCategoriesDropDownByType, getCategoryIdsByType } from "@/helper.js";
import Multiselect from '@vueform/multiselect'
import TextInput from "@/Components/TextInput.vue";

const UserAccess = [
    { value: 'admin', label: 'Admin' },
    { value: 'buyer-group', label: 'Buyer Group' },
    { value: 'grower-group', label: 'Grower Group' },
    { value: 'unloading', label: 'Unloading' },
    { value: 'grower', label: 'Grower' },
    { value: 'buyer', label: 'Buyer' },
    { value: 'transport-companies', label: 'Transport Companies' },
    { value: 'tia-sampling', label: 'TIA Sampling' },
    { value: 'cool-store-access', label: 'CoolStore Access' },
];

const props = defineProps({
    user: Object,
    colSize: String,
    isEdit: Boolean,
    isNew: Boolean,
    categories: Array,
});

const emit = defineEmits(['update', 'create']);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    username: props.user.username,
    phone: props.user.phone,
    role: props.user.role,
    grower: getCategoryIdsByType(props.user.categories, 'grower'),
    grower_name: props.user.grower_name,
    grower_tags: props.user.grower_tags,
    buyer: getCategoryIdsByType(props.user.categories, 'buyer'),
    buyer_tags: props.user.buyer_tags,
    paddocks: props.user.paddocks === undefined || props.user.paddocks === null ? [] : props.user.paddocks,
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
        form.role = user.role
        form.password = ''
        form.password_confirmation = ''
        form.grower = getCategoryIdsByType(user.categories, 'grower')
        form.grower_name = user.grower_name
        form.grower_tags = user.grower_tags
        form.buyer = getCategoryIdsByType(user.categories, 'buyer')
        form.buyer_tags = user.buyer_tags
        form.paddocks = user.paddocks === undefined || user.paddocks === null ? [] : user.paddocks
    }
);

const isForm = computed(() => {
    return props.isEdit || props.isNew;
})

const addMorePaddocks = () => {
    if (props.user.paddocks === undefined || props.user.paddocks === null) {
        props.user.paddocks = [];
    }

    props.user.paddocks.push({ name: '', hectares: '' });
    form.paddocks = props.user.paddocks;
}

const removePaddocks = (index) => {
    props.user.paddocks = props.user.paddocks.filter((paddocks, i) => i !== index)
}

const updateRecord = () => {
    form.patch(route('users.update', props.user.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('update')
        },
    });
}

const storeRecord = () => {
    form.post(route('users.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('create')
        },
    });
}
</script>

<template>
    <div class="row">
        <div v-if="isEdit || isNew" class="col-md-12">
            <div class="flex-end create-update-btn">
            <a v-if="isEdit" role="button" @click="updateRecord" class="btn btn-red">
                <span class="fa fa-edit"></span> Update
            </a>
            <a v-if="isNew" role="button" @click="storeRecord" class="btn btn-red">
                <span class="fa fa-edit"></span> Create
            </a>
            </div>
        </div>
        <div :class="colSize">
            <div class="user-boxes">
                <h6>Name</h6>
                <TextInput v-if="isForm" v-model="form.name" :error="form.errors.name" type="text"/>
                <h5 v-else>{{ user.name }}</h5>

                <h6>Email</h6>
                <TextInput v-if="isForm" v-model="form.email" :error="form.errors.email" type="text"/>
                <h5 v-else>{{ user.email }}</h5>

                <h6>Username</h6>
                <TextInput v-if="isForm" v-model="form.username" :error="form.errors.username" type="text"/>
                <h5 v-else>{{ user.username }}</h5>

                <h6>Phone</h6>
                <TextInput v-if="isForm" v-model="form.phone" :error="form.errors.phone" type="text"/>
                <h5 v-else>{{ user.phone }}</h5>

                <div v-if="isNew">
                    <h6>Password</h6>
                    <TextInput v-if="isForm" v-model="form.password" :error="form.errors.password" type="password"/>

                    <h6>Confirmation Password</h6>
                    <TextInput
                        v-if="isForm"
                        v-model="form.password_confirmation"
                        :error="form.errors.password_confirmation"
                        type="password"
                    />
                </div>

                <h6>User Access</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.role"
                    mode="tags"
                    placeholder="Choose a user access"
                    :searchable="true"
                    :options="UserAccess"
                />
                <ul v-else-if="user.role">
                    <li v-for="role in user.role" :key="role">
                        <a>{{ UserAccess.find(access => access.value === role)?.label }}</a>
                    </li>
                </ul>
            </div>

            <h4>Grower Details</h4>
            <div class="user-boxes">
                <h6>Grower Group</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.grower"
                    mode="tags"
                    placeholder="Choose a grower group"
                    :searchable="true"
                    :create-option="true"
                    :options="getCategoriesDropDownByType(categories, 'grower')"
                />
                <ul v-else-if="getCategoryIdsByType(user.categories, 'grower')">
                    <li v-for="group in getCategoryIdsByType(user.categories, 'grower')" :key="group">
                        <a>{{ getCategoriesDropDownByType(categories, 'grower').find(g => parseInt(g.value) === parseInt(group))?.label || group }}</a>
                    </li>
                </ul>

                <h6>Grower Name</h6>
                <TextInput v-if="isForm" v-model="form.grower_name" :error="form.errors.grower_name" type="text"/>
                <h5 v-else>{{ user.grower_name }}</h5>

                <h6>Unique Tags</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.grower_tags"
                    mode="tags"
                    placeholder="Choose a grower tags"
                    :searchable="true"
                    :create-option="true"
                    :options="form.grower_tags"
                />
                <ul v-else-if="user.grower_tags">
                    <li v-for="tag in user.grower_tags" :key="tag"><a>{{ tag }}</a></li>
                </ul>
            </div>
        </div>
        <div :class="colSize">
            <h4>Buyer Details</h4>
            <div class="user-boxes">
                <h6>Buyer Group</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.buyer"
                    mode="tags"
                    placeholder="Choose a buyer group"
                    :searchable="true"
                    :create-option="true"
                    :options="getCategoriesDropDownByType(categories, 'buyer')"
                />
                <ul v-else-if="getCategoryIdsByType(user.categories, 'buyer')">
                    <li v-for="group in getCategoryIdsByType(user.categories, 'buyer')" :key="group">
                        <a>{{ getCategoriesDropDownByType(categories, 'buyer').find(g => parseInt(g.value) === parseInt(group))?.label || group }}</a>
                    </li>
                </ul>

                <h6>Unique Tags</h6>
                <Multiselect
                    v-if="isForm"
                    v-model="form.buyer_tags"
                    mode="tags"
                    placeholder="Choose a buyer tags"
                    :searchable="true"
                    :create-option="true"
                    :options="form.buyer_tags"
                />
                <ul v-else-if="user.buyer_tags">
                    <li v-for="tag in user.buyer_tags" :key="tag"><a>{{ tag }}</a></li>
                </ul>
            </div>

            <h4>Paddocks</h4>
            <div class="user-boxes">
                <template v-for="(paddocks, index) in user.paddocks" :key="index">
                    <div class="user-column-two">
                        <h6>Paddock Name</h6>
                        <h6>No of Hectares</h6>
                    </div>
                    <div class="user-column-two">
                        <TextInput
                            v-if="isForm && form.paddocks[index]"
                            v-model="form.paddocks[index].name"
                            :error="form.errors[`paddocks.${index}.name`]"
                            type="text"
                        />
                        <h5 v-else>{{ paddocks.name }}</h5>

                        <TextInput
                            v-if="isForm && form.paddocks[index]"
                            v-model="form.paddocks[index].hectares"
                            :error="form.errors[`paddocks.${index}.hectares`]"
                            type="text"
                        >
                            <template #addon>
                            <a href="javascript:;" class="input-group-addon" @click="removePaddocks(index)">
                                <span class="fa fa-trash-o"></span>
                            </a>
                            </template>
                        </TextInput>
                        <h5 v-else>{{ paddocks.hectares }}</h5>
                    </div>
                </template>
                <div v-if="isForm" class="user-column-two">
                    <div>&nbsp;</div>
                    <div>
                        <button class="btn-red" type="button" @click="addMorePaddocks">+ Add</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
