<script setup>
import moment from 'moment';
import { computed, ref, watch } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import Multiselect from '@vueform/multiselect';
import TextInput from "@/Components/TextInput.vue";
import UlLiButton from "@/Components/UlLiButton.vue";
import Images from "@/Components/Images.vue";
import {
  toCamelCase,
  getDropDownOptions,
  getCategoriesDropDownByType,
  getCategoryIdsByType,
  getCategoriesByType
} from "@/helper.js";

const userGrowerGroups = ref([]);
const paddockOptions = ref([]);

const props = defineProps({
  receival: Object,
  colSize: String,
  isEdit: Boolean,
  isNew: Boolean,
  users: Array,
  categories: Array,
});

const emit = defineEmits(['update', 'create']);

const form = useForm({
  grower_id: props.receival.grower_id,
  grower_group: getCategoryIdsByType(props.receival.categories, 'grower-group'),
  paddocks: props.receival.paddocks,
  seed_variety: getCategoryIdsByType(props.receival.categories, 'seed-variety'),
  seed_generation: getCategoryIdsByType(props.receival.categories, 'seed-generation'),
  seed_class: getCategoryIdsByType(props.receival.categories, 'seed-class'),
  delivery_type: getCategoryIdsByType(props.receival.categories, 'delivery-type'),
  transport: getCategoryIdsByType(props.receival.categories, 'transport'),
  grower_docket_no: props.receival.grower_docket_no,
  chc_receival_docket_no: props.receival.chc_receival_docket_no,
  driver_name: props.receival.driver_name,
  comments: props.receival.comments,
});

watch(() => props.receival,
  (receival) => {
    form.clearErrors();
    form.grower_id = receival.grower_id
    form.grower_group = getCategoryIdsByType(receival.categories, 'grower-group')
    form.paddocks = receival.paddocks
    form.seed_variety = getCategoryIdsByType(receival.categories, 'seed-variety')
    form.seed_generation = getCategoryIdsByType(receival.categories, 'seed-generation')
    form.seed_class = getCategoryIdsByType(receival.categories, 'seed-class')
    form.delivery_type = getCategoryIdsByType(receival.categories, 'delivery-type')
    form.transport = getCategoryIdsByType(receival.categories, 'transport')
    form.grower_docket_no = receival.grower_docket_no
    form.chc_receival_docket_no = receival.chc_receival_docket_no
    form.driver_name = receival.driver_name
    form.comments = receival.comments

    updatePaddock(receival.grower_id);
    resetGrowerGroups(receival.grower_id);
  }
);

watch(() => form.grower_id,
  (growerId) => {
    updatePaddock(growerId);
    resetGrowerGroups(growerId);
  }
);

const onChangeGrowerUser = (grower_id) => {
  resetGrowerGroups(grower_id);
  form.grower = [];
}

const resetGrowerGroups = (grower_id) => {
  const categories = props.users
    ?.find(user => user.id === grower_id)
    ?.categories;

  userGrowerGroups.value = getCategoriesByType(categories, 'grower-group')?.map(item => {
    return { 'value': item.category.id, 'label': item.category.name };
  });
}

const updatePaddock = (growerId) => {
  if (growerId) {
    const user = props.users.find(user => user.id === growerId);
    paddockOptions.value = (user.paddocks || []).map(paddock => paddock.name);
    paddockOptions.value = [...paddockOptions.value, ...props.receival?.paddocks || []];
    return;
  }
  paddockOptions.value = [];
}

updatePaddock(props.receival.grower_id);
resetGrowerGroups(props.receival.grower_id);

const isForm = computed(() => props.isEdit || props.isNew)
const isRequireFieldsEmpty = computed(() => {
  const growerGroup = getCategoryIdsByType(props.receival.categories, 'grower-group');
  if (growerGroup.length <= 0) {
    return true;
  }
  const seedVariety = getCategoryIdsByType(props.receival.categories, 'seed-variety');
  if (seedVariety.length <= 0) {
    return true;
  }
  const seedClass = getCategoryIdsByType(props.receival.categories, 'seed-class');
  if (seedClass.length <= 0) {
    return true;
  }
  const seedGeneration = getCategoryIdsByType(props.receival.categories, 'seed-generation');
  if (seedGeneration.length <= 0) {
    return true;
  }
  return props.receival.paddocks?.length <= 0;
});

const updateRecord = () => {
  form.patch(route('receivals.update', props.receival.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update')
    },
  });
}

const storeRecord = () => {
  form.post(route('receivals.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create')
    },
  });
}

const pushForTiaSample = () => {
  form.post(route('receivals.push.tia-sample', props.receival.id), {
    onSuccess: () => {
      emit('updateRecord')
    },
  });
}

const pushForUnload = () => {
  form.post(route('receivals.push.unload', props.receival.id), {
    onSuccess: () => {
      emit('updateRecord')
    },
  });
}
</script>

<template>
  <div class="row">
    <div v-if="isRequireFieldsEmpty" class="col-md-12">
      <div class="alert alert-warning" role="alert">
        Require these fields (Grower Group, Paddocks, Seed Variety, Class and Generation) to list in the allocations
      </div>
    </div>
    <div v-if="isEdit || isNew" class="col-md-12">
      <div class="flex-end create-update-btn">
        <a v-if="isEdit" role="button" @click="updateRecord" class="btn btn-red">Update</a>
        <a v-if="isNew" role="button" @click="storeRecord" class="btn btn-red">Create</a>
      </div>
    </div>
    <div :class="colSize">
      <div class="user-boxes">
        <h6>Grower</h6>
        <Multiselect
          v-if="isForm"
          v-model="form.grower_id"
          mode="single"
          placeholder="Choose a grower"
          :searchable="true"
          @change="onChangeGrowerUser"
          :options="getDropDownOptions(users, true)"
        />
        <h5 v-else-if="receival.grower">
          <Link :href="route('users.index', { userId: receival.grower_id })">
            {{ receival.grower?.name }} {{ receival.grower?.grower_name ? ' (' + receival.grower?.grower_name + ')' : '' }}
          </Link>
        </h5>
        <h5 v-else>-</h5>

        <h6>Grower Group</h6>
        <div v-if="form.errors.grower" class="has-error">
          <span class="help-block text-left">{{ form.errors.grower }}</span>
        </div>
        <Multiselect
          v-if="isForm"
          v-model="form.grower_group"
          mode="tags"
          placeholder="Choose a grower group"
          :searchable="true"
          :create-option="true"
          :options="userGrowerGroups"
        />
        <ul v-else-if="getCategoriesByType(receival.categories, 'grower-group').length">
          <li v-for="category in getCategoriesByType(receival.categories, 'grower-group')" :key="category.id">
            <a>{{ category.category.name }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6 v-if="!isForm">Time Added</h6>
        <h5 v-if="!isForm">{{ moment(receival.created_at).format('DD/MM/YYYY hh:mm A') }}</h5>

        <h6>Paddock</h6>
        <div v-if="form.errors.paddocks" class="has-error">
          <span class="help-block text-left">{{ form.errors.paddocks }}</span>
        </div>
        <Multiselect
          v-if="isForm"
          v-model="form.paddocks"
          mode="tags"
          placeholder="Choose a paddock"
          :searchable="true"
          :create-option="true"
          :options="paddockOptions"
        />
        <ul v-else-if="receival.paddocks?.length">
          <li v-for="paddock in receival.paddocks" :key="paddock">
            <a>{{ paddock }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>
      </div>

      <h4>Seed Information</h4>
      <div class="user-boxes">
        <h6>Seed Variety</h6>
        <div v-if="form.errors.seed_variety" class="has-error">
          <span class="help-block text-left">{{ form.errors.seed_variety }}</span>
        </div>
        <Multiselect
          v-if="isForm"
          v-model="form.seed_variety"
          mode="tags"
          placeholder="Choose a seed variety"
          :searchable="true"
          :create-option="true"
          :options="getCategoriesDropDownByType(categories, 'seed-variety')"
        />
        <ul v-else-if="getCategoriesByType(receival.categories, 'seed-variety').length">
          <li v-for="category in getCategoriesByType(receival.categories, 'seed-variety')" :key="category.id">
            <a>{{ category.category.name }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Seed Generation</h6>
        <div v-if="form.errors.seed_generation" class="has-error">
          <span class="help-block text-left">{{ form.errors.seed_generation }}</span>
        </div>
        <Multiselect
          v-if="isForm"
          v-model="form.seed_generation"
          mode="tags"
          placeholder="Choose a seed generation"
          :searchable="true"
          :create-option="true"
          :options="getCategoriesDropDownByType(categories, 'seed-generation')"
        />
        <ul v-else-if="getCategoriesByType(receival.categories, 'seed-generation').length">
          <li v-for="category in getCategoriesByType(receival.categories, 'seed-generation')"
              :key="category.id">
            <a>{{ category.category.name }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Seed Class</h6>
        <div v-if="form.errors.seed_class" class="has-error">
          <span class="help-block text-left">{{ form.errors.seed_class }}</span>
        </div>
        <Multiselect
          v-if="isForm"
          v-model="form.seed_class"
          mode="tags"
          placeholder="Choose a seed class"
          :searchable="true"
          :create-option="true"
          :options="getCategoriesDropDownByType(categories, 'seed-class')"
        />
        <ul v-else-if="getCategoriesByType(receival.categories, 'seed-class').length">
          <li v-for="category in getCategoriesByType(receival.categories, 'seed-class')" :key="category.id">
            <a>{{ category.category.name }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <div v-if="!isForm">
          <h6>Tia Sample Status</h6>
          <ul>
            <li>
              <a
                v-if="receival.tia_sample"
                role="button"
                :class="{'btn-pending' : receival.tia_sample?.status === 'pending'}"
              >{{ toCamelCase(receival.tia_sample.status) }}</a>
              <a v-else role="button" class="black-btn" @click="pushForTiaSample">Push for TIA Sample</a>
            </li>
          </ul>

          <h6>TIA Sample ID</h6>
          <h5 v-if="receival.tia_sample">
            <Link :href="route('tia-samples.index', { tiaSampleId: receival.tia_sample.id })">
              {{ receival.tia_sample.id }}
            </Link>
          </h5>
          <h5 v-else>-</h5>
        </div>
      </div>

      <h4 v-if="!isNew">Docket Receipts</h4>
      <div v-if="!isNew" class="user-boxes notes-list">
        <Images
          :images="receival.images"
          :upload-route="route('receivals.upload', receival.id || 0)"
          :delete-route="route('receivals.delete', receival.id || 0)"
          @updateRecord="emit('update')"
        />
      </div>
    </div>
    <div :class="colSize">
      <h4 v-if="!isForm">Unloading Information</h4>
      <div v-if="!isForm" class="user-boxes">
        <h6>Cool Store</h6>
        <ul v-if="getCategoriesByType(receival.grower.categories, 'cool-store').length">
          <li v-for="category in getCategoriesByType(receival.grower.categories, 'cool-store')" :key="category.id">
            <a>{{ category.category.name }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Unloading Status</h6>
        <ul>
          <li>
            <a
              v-if="receival.status"
              role="button"
              :class="{'btn-pending' : receival.status === 'pending'}"
            >{{ toCamelCase(receival.status) }}</a>
            <a v-else role="button" class="black-btn" @click="pushForUnload">Push for Unload</a>
          </li>
        </ul>

        <h6>Unloading ID</h6>
        <h5 v-if="receival.status">
          <Link :href="route('unloading.index', { unloadId: receival.id })">
            {{ receival.id }}
          </Link>
        </h5>
        <h5 v-else>-</h5>
      </div>

      <h4>Other Information</h4>
      <div class="user-boxes">
        <h6>Transport Co.</h6>
        <div v-if="form.errors.transport" class="has-error">
          <span class="help-block text-left">{{ form.errors.transport }}</span>
        </div>
        <Multiselect
          v-if="isForm"
          v-model="form.transport"
          mode="tags"
          placeholder="Choose a transport"
          :searchable="true"
          :create-option="true"
          :options="getCategoriesDropDownByType(categories, 'transport')"
        />
        <ul v-else-if="getCategoriesByType(receival.categories, 'transport').length">
          <li v-for="category in getCategoriesByType(receival.categories, 'transport')" :key="category.id">
            <a>{{ category.category.name }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Delivery Type</h6>
        <Multiselect
          v-if="isForm"
          v-model="form.delivery_type"
          mode="tags"
          placeholder="Choose a delivery type"
          :searchable="true"
          :create-option="true"
          :options="getCategoriesDropDownByType(categories, 'delivery-type')"
        />
        <ul v-else-if="getCategoriesByType(receival.categories, 'delivery-type').length">
          <li v-for="category in getCategoriesByType(receival.categories, 'delivery-type')"
              :key="category.id">
            <a>{{ category.category.name }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Growers’s Docket No</h6>
        <TextInput
          v-if="isForm"
          v-model="form.grower_docket_no"
          :error="form.errors.grower_docket_no"
          type="text"
        />
        <h5 v-else-if="receival.grower_docket_no">{{ receival.grower_docket_no }}</h5>
        <h5 v-else>-</h5>

        <h6>CHC Receival Docket No</h6>
        <TextInput
          v-if="isForm"
          v-model="form.chc_receival_docket_no"
          :error="form.errors.chc_receival_docket_no"
          type="text"
        />
        <h5 v-else-if="receival.chc_receival_docket_no">{{ receival.chc_receival_docket_no }}</h5>
        <h5 v-else>-</h5>

        <h6>Driver’s Name</h6>
        <TextInput v-if="isForm" v-model="form.driver_name" :error="form.errors.driver_name" type="text"/>
        <h5 v-else-if="receival.driver_name">{{ receival.driver_name }}</h5>
        <h5 v-else>-</h5>

        <h6>Comments</h6>
        <TextInput v-if="isForm" v-model="form.comments" :error="form.errors.comments" type="text"/>
        <h5 v-else-if="receival.comments">{{ receival.comments }}</h5>
        <h5 v-else>-</h5>
      </div>
    </div>
  </div>
</template>
