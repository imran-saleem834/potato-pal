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
  cool_store: getCategoryIdsByType(props.user.categories, 'cool-store'),
  grower_group: getCategoryIdsByType(props.user.categories, 'grower-group'),
  grower_name: props.user.grower_name,
  grower_tags: props.user.grower_tags,
  buyer_group: getCategoryIdsByType(props.user.categories, 'buyer-group'),
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
    form.cool_store = getCategoryIdsByType(user.categories, 'cool-store')
    form.grower_group = getCategoryIdsByType(user.categories, 'grower-group')
    form.grower_name = user.grower_name
    form.grower_tags = user.grower_tags
    form.buyer_group = getCategoryIdsByType(user.categories, 'buyer-group')
    form.buyer_tags = user.buyer_tags
    form.paddocks = user.paddocks === undefined || user.paddocks === null ? [] : user.paddocks
  }
);

const isForm = computed(() => props.isEdit || props.isNew)

const addMorePaddocks = () => form.paddocks.push({ name: '', hectares: '' });
const removePaddocks = (index) => form.paddocks = form.paddocks.filter((paddocks, i) => i !== index);

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
        <a v-if="isEdit" role="button" @click="updateRecord" class="btn btn-red">Update</a>
        <a v-if="isNew" role="button" @click="storeRecord" class="btn btn-red">Create</a>
      </div>
    </div>
    <div :class="colSize">
      <div class="user-boxes">
        <h6>Name</h6>
        <TextInput v-if="isForm" v-model="form.name" :error="form.errors.name" type="text"/>
        <h5 v-else-if="user.name">{{ user.name }}</h5>
        <h5 v-else>-</h5>

        <h6>Email</h6>
        <TextInput v-if="isForm" v-model="form.email" :error="form.errors.email" type="text"/>
        <h5 v-else-if="user.email">{{ user.email }}</h5>
        <h5 v-else>-</h5>

        <h6>Username</h6>
        <TextInput v-if="isForm" v-model="form.username" :error="form.errors.username" type="text"/>
        <h5 v-else-if="user.username">{{ user.username }}</h5>
        <h5 v-else>-</h5>

        <h6>Phone</h6>
        <TextInput v-if="isForm" v-model="form.phone" :error="form.errors.phone" type="text"/>
        <h5 v-else-if="user.phone">{{ user.phone }}</h5>
        <h5 v-else>-</h5>

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
        <h5 v-else>-</h5>

        <h6>Cool Store</h6>
        <Multiselect
          v-if="isForm"
          v-model="form.cool_store"
          mode="tags"
          placeholder="Choose a cool store"
          :searchable="true"
          :create-option="true"
          :options="getCategoriesDropDownByType(categories, 'cool-store')"
        />
        <ul v-else-if="getCategoryIdsByType(user.categories, 'cool-store').length">
          <li v-for="group in getCategoryIdsByType(user.categories, 'cool-store')" :key="group">
            <a>{{
                getCategoriesDropDownByType(categories, 'cool-store').find(g => parseInt(g.value) === parseInt(group))?.label || group
              }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>
      </div>

      <h4>Grower Details</h4>
      <div class="user-boxes">
        <h6>Grower Group</h6>
        <Multiselect
          v-if="isForm"
          v-model="form.grower_group"
          mode="tags"
          placeholder="Choose a grower group"
          :searchable="true"
          :create-option="true"
          :options="getCategoriesDropDownByType(categories, 'grower-group')"
        />
        <ul v-else-if="getCategoryIdsByType(user.categories, 'grower-group').length">
          <li v-for="group in getCategoryIdsByType(user.categories, 'grower-group')" :key="group">
            <a>{{
                getCategoriesDropDownByType(categories, 'grower-group').find(g => parseInt(g.value) === parseInt(group))?.label || group
              }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Grower</h6>
        <TextInput v-if="isForm" v-model="form.grower_name" :error="form.errors.grower_name" type="text"/>
        <h5 v-else-if="user.grower_name">{{ user.grower_name }}</h5>
        <h5 v-else>-</h5>

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
        <h5 v-else>-</h5>
      </div>
    </div>
    <div :class="colSize">
      <h4>Buyer Details</h4>
      <div class="user-boxes">
        <h6>Buyer Group</h6>
        <Multiselect
          v-if="isForm"
          v-model="form.buyer_group"
          mode="tags"
          placeholder="Choose a buyer group"
          :searchable="true"
          :create-option="true"
          :options="getCategoriesDropDownByType(categories, 'buyer-group')"
        />
        <ul v-else-if="getCategoryIdsByType(user.categories, 'buyer-group').length">
          <li v-for="group in getCategoryIdsByType(user.categories, 'buyer-group')" :key="group">
            <a>{{
                getCategoriesDropDownByType(categories, 'buyer-group').find(g => parseInt(g.value) === parseInt(group))?.label || group
              }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

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
        <h5 v-else>-</h5>
      </div>

      <h4>Paddocks</h4>
      <div class="user-boxes">
        <template v-for="(paddocks, index) in form.paddocks" :key="index">
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
            <h5 v-else-if="paddocks.name">{{ paddocks.name }}</h5>
            <h5 v-else>-</h5>

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
            <h5 v-else-if="paddocks.hectares">{{ paddocks.hectares }}</h5>
            <h5 v-else>-</h5>
          </div>
        </template>
        <div v-if="form.paddocks.length <= 0" style="text-align: center; margin: 50px 0;">
          No Records Found
        </div>
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
