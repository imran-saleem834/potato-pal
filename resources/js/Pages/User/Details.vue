<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Multiselect from '@vueform/multiselect';
import TextInput from '@/Components/TextInput.vue';
import ItemOfCategories from '@/Components/ItemOfCategories.vue';
import { getCategoriesDropDownByType, getCategoryIdsByType } from '@/helper.js';

const toast = useToast();

const UserAccess = [
  { value: 'admin', label: 'Admin' },
  { value: 'buyer-group', label: 'Buyer Group' },
  { value: 'grower-group', label: 'Grower Group' },
  { value: 'receivals', label: 'Receivals' },
  { value: 'unloading', label: 'Unloading' },
  { value: 'grower', label: 'Grower' },
  { value: 'buyer', label: 'Buyer' },
  { value: 'transport-companies', label: 'Transport Companies' },
  { value: 'tia-sampling', label: 'TIA Sampling' },
  { value: 'allocations', label: 'Allocations' },
  { value: 'reallocations', label: 'Reallocations' },
  { value: 'dispatch', label: 'Dispatch' },
  { value: 'cutting', label: 'Cutting' },
  { value: 'weighbridge', label: 'Weighbridges' },
  { value: 'notification', label: 'Notifications' },
  { value: 'notes', label: 'Notes' },
  { value: 'files', label: 'Files' },
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
  buyer_name: props.user.buyer_name,
  buyer_tags: props.user.buyer_tags,
  paddocks:
    props.user.paddocks === undefined || props.user.paddocks === null ? [] : props.user.paddocks,
  password: '',
  password_confirmation: '',
});

watch(
  () => props.user,
  (user) => {
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.username = user.username;
    form.phone = user.phone;
    form.role = user.role;
    form.password = '';
    form.password_confirmation = '';
    form.cool_store = getCategoryIdsByType(user.categories, 'cool-store');
    form.grower_group = getCategoryIdsByType(user.categories, 'grower-group');
    form.grower_name = user.grower_name;
    form.grower_tags = user.grower_tags;
    form.buyer_group = getCategoryIdsByType(user.categories, 'buyer-group');
    form.buyer_tags = user.buyer_tags;
    form.paddocks = user.paddocks === undefined || user.paddocks === null ? [] : user.paddocks;
  },
);

const isForm = computed(() => props.isEdit || props.isNew);

const isBuyerSelected = computed(() => form.role?.find((r) => r === 'buyer'));
const isGrowerSelected = computed(() => form.role?.find((r) => r === 'grower'));

const addMorePaddocks = () => form.paddocks.push({ name: '', address: '', gps: '', hectares: '' });
const removePaddocks = (index) =>
  (form.paddocks = form.paddocks.filter((paddocks, i) => i !== index));

const updateRecord = () => {
  form.patch(route('users.update', props.user.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The user has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route('users.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The user has been created successfully!');
    },
  });
};

defineExpose({
  updateRecord,
  storeRecord,
});

const getGpsLocation = (index) => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const userLat = position.coords.latitude;
        const userLng = position.coords.longitude;

        form.paddocks[index].gps = `${userLat}, ${userLng}`;

        // Fetch the address using the Geocoding API
        fetch(
          `https://maps.googleapis.com/maps/api/geocode/json?latlng=${userLat},${userLng}&key=${import.meta.env.VITE_GOOGLE_MAPS_API_KEY}`,
        )
          .then((response) => response.json())
          .then((data) => {
            if (data.status === 'OK') {
              if (data.results[0]) {
                form.paddocks[index].address = data.results[0].formatted_address;
              } else {
                form.paddocks[index].address = null;
              }
            } else {
              form.errors[`paddocks.${index}.address`] = 'Geocoder failed due to: ' + data.status;
            }
          })
          .catch((error) => {
            form.errors[`paddocks.${index}.address`] = 'Error getting your address';
            console.log('Error fetching address: ', error);
          });
      },
      (error) => {
        form.errors[`paddocks.${index}.gps`] = 'Error getting your location';
        console.log('Error getting your location: ', error.message);
      },
    );
  } else {
    form.errors[`paddocks.${index}.gps`] = 'Geolocation is not supported by this browser.';
  }
};

const autocompleteInput = (index) => {
  const input = document.getElementById(`autocomplete-input-${index}`);
  const autocomplete = new google.maps.places.Autocomplete(input);

  autocomplete.addListener('place_changed', () => {
    const place = autocomplete.getPlace();

    if (!place.geometry) {
      form.errors[`paddocks.${index}.address`] = 'Place details not available for input';
      console.log('Place details not available for input: ' + place.name);
      return;
    }

    const latitude = place.geometry.location.lat();
    const longitude = place.geometry.location.lng();

    form.paddocks[index].address = place.formatted_address;
    form.paddocks[index].gps = `${latitude}, ${longitude}`;
  });
};
</script>

<template>
  <div class="row">
    <div :class="colSize">
      <h4>User Details</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Name</th>
            <td>
              <TextInput v-if="isForm" v-model="form.name" :error="form.errors.name" type="text" />
              <template v-else-if="user.name">{{ user.name }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Email</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.email"
                :error="form.errors.email"
                type="text"
              />
              <template v-else-if="user.email">{{ user.email }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Username</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.username"
                :error="form.errors.username"
                type="text"
              />
              <template v-else-if="user.username">{{ user.username }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Phone</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.phone"
                :error="form.errors.phone"
                type="text"
              />
              <template v-else-if="user.phone">{{ user.phone }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr v-if="isForm">
            <th>Password</th>
            <td>
              <TextInput v-model="form.password" :error="form.errors.password" type="password" />
            </td>
          </tr>
          <tr v-if="isForm">
            <th>Confirmation Password</th>
            <td>
              <TextInput
                v-model="form.password_confirmation"
                :error="form.errors.password_confirmation"
                type="password"
              />
            </td>
          </tr>
          <tr>
            <th>User Access</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.role"
                mode="tags"
                placeholder="Choose a user access"
                :searchable="true"
                :options="UserAccess"
              />
              <ul class="p-0" v-else-if="user.role">
                <li v-for="role in user.role" :key="role">
                  <a>{{ UserAccess.find((access) => access.value === role)?.label }}</a>
                </li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Cool Store</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.cool_store"
                mode="tags"
                placeholder="Choose a cool store"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'cool-store')"
              />
              <ItemOfCategories v-else :categories="user.categories" type="cool-store" />
            </td>
          </tr>
        </table>
      </div>

      <h4 v-if="isBuyerSelected">Buyer Details</h4>
      <div v-if="isBuyerSelected" class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Buyer Group</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.buyer_group"
                mode="tags"
                placeholder="Choose a buyer group"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'buyer-group')"
              />
              <ItemOfCategories v-else :categories="user.categories" type="buyer-group" />
            </td>
          </tr>
          <tr>
            <th>Buyer</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.buyer_name"
                :error="form.errors.buyer_name"
                type="text"
              />
              <template v-else-if="user.buyer_name">{{ user.buyer_name }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Unique Tags</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.buyer_tags"
                mode="tags"
                placeholder="Choose a buyer tags"
                :searchable="true"
                :create-option="true"
                :options="form.buyer_tags"
              />
              <ul class="p-0" v-else-if="user.buyer_tags">
                <li v-for="tag in user.buyer_tags" :key="tag">
                  <a>{{ tag }}</a>
                </li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
        </table>
      </div>
    </div>

    <div :class="colSize">
      <h4 v-if="isGrowerSelected">Grower Details</h4>
      <div v-if="isGrowerSelected" class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Grower Group</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.grower_group"
                mode="tags"
                placeholder="Choose a grower group"
                :searchable="true"
                :create-option="true"
                :options="getCategoriesDropDownByType(categories, 'grower-group')"
              />
              <ItemOfCategories v-else :categories="user.categories" type="grower-group" />
            </td>
          </tr>
          <tr>
            <th>Grower</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.grower_name"
                :error="form.errors.grower_name"
                type="text"
              />
              <template v-else-if="user.grower_name">{{ user.grower_name }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Unique Tags</th>
            <td :class="{ 'pb-0': !isForm }">
              <Multiselect
                v-if="isForm"
                v-model="form.grower_tags"
                mode="tags"
                placeholder="Choose a grower tags"
                :searchable="true"
                :create-option="true"
                :options="form.grower_tags"
              />
              <ul class="p-0" v-else-if="user.grower_tags">
                <li v-for="tag in user.grower_tags" :key="tag">
                  <a>{{ tag }}</a>
                </li>
              </ul>
              <template v-else>-</template>
            </td>
          </tr>
        </table>
      </div>

      <h4 v-if="isGrowerSelected">Paddocks</h4>
      <div v-if="isGrowerSelected" class="user-boxes">
        <table class="table input-table paddock-table mb-0">
          <tr>
            <th>Paddock Name</th>
            <th>Paddock Location</th>
            <td>No of Hectares</td>
          </tr>
          <tr v-for="(paddocks, index) in form.paddocks" :key="index">
            <th class="pe-3">
              <TextInput
                v-if="isForm && form.paddocks[index]"
                v-model="form.paddocks[index].name"
                :error="form.errors[`paddocks.${index}.name`]"
                type="text"
              />
              <template v-else-if="paddocks.name">{{ paddocks.name }}</template>
              <template v-else>-</template>
            </th>
            <td>
              <div v-if="isForm && form.paddocks[index]" class="input-group py-0">
                <input
                  type="text"
                  class="form-control"
                  v-model="form.paddocks[index].address"
                  :id="`autocomplete-input-${index}`"
                  @click="autocompleteInput(index)"
                />
                <a href="javascript:;" class="input-group-text" @click="getGpsLocation(index)">
                  <i class="bi bi-geo-alt"></i>
                </a>
                <div v-if="form.errors[`paddocks.${index}.address`]" class="invalid-feedback">
                  {{ form.errors[`paddocks.${index}.address`] }}
                </div>
              </div>
              <template v-else-if="paddocks.address">{{ paddocks.address }}</template>
              <template v-else-if="paddocks.gps">{{ paddocks.gps }}</template>
              <template v-else>-</template>
            </td>
            <td>
              <TextInput
                v-if="isForm && form.paddocks[index]"
                v-model="form.paddocks[index].hectares"
                :error="form.errors[`paddocks.${index}.hectares`]"
                type="text"
              >
                <template #addon>
                  <a href="javascript:;" class="input-group-text" @click="removePaddocks(index)">
                    <i class="bi bi-trash"></i>
                  </a>
                </template>
              </TextInput>
              <template v-else-if="paddocks.hectares">{{ paddocks.hectares }}</template>
              <template v-else>-</template>
            </td>
          </tr>
        </table>
        <div v-if="form.paddocks.length <= 0" class="text-center" style="margin: 50px 0">
          No Records Found
        </div>
        <div v-if="isForm" class="d-flex justify-content-end mt-3">
          <button class="btn btn-red" type="button" @click="addMorePaddocks">+ Add</button>
        </div>
      </div>
    </div>
  </div>
</template>
