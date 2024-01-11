<script setup>
import moment from 'moment';
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useForm, Link, usePage } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import UlLiButton from "@/Components/UlLiButton.vue";
import Multiselect from '@vueform/multiselect';
import { getBinSizesValue } from "@/tonnes.js";
import {
  toCamelCase,
  getSingleCategoryNameByType,
  getCategoriesDropDownByType,
  getCategoriesByType, getCategoryIdsByType
} from "@/helper.js";

const props = defineProps({
  unload: Object,
  colSize: String,
  isEdit: Boolean,
  isNew: Boolean,
  categories: Array,
});

const bins = ref(null);
const weight = ref(null);

const emit = defineEmits(['update', 'create']);

const form = useForm({
  fungicide: getCategoryIdsByType(props.unload.categories, 'fungicide'),
  no_of_bins: props.unload.no_of_bins,
  weight: props.unload.weight,
  status: props.unload.status,
  channel: props.unload.channel,
  system: props.unload.system,
});

watch(() => props.unload,
  (unload) => {
    form.clearErrors();
    form.fungicide = getCategoryIdsByType(unload.categories, 'fungicide')
    form.no_of_bins = unload.no_of_bins
    form.weight = unload.weight
    form.status = unload.status
    form.channel = unload.channel
    form.system = unload.system
  }
);

const page = usePage();
const getWeightMessage = reactive({
  seed: '',
  bins: '',
  responseChannel: null,
  staffID: page.props.auth.user.id,
  system: null,
  terminalCommand: 'S',
});

const startWeighingMessage = reactive({
  responseChannel: null, 
  startWeighing: "PotatoPal", 
  exWhomName: props.unload.grower.grower_name || '',
  exWhomGroupName: getSingleCategoryNameByType(props.unload.categories, 'group'),
  potIdentifierCode: props.unload.id, 
  taskOwner: page.props.auth.user.name,
  emptyBinWeight: props.unload.bin_size === 1 ? '120' : '204', 
  binSize: props.unload.bin_size, 
  numberOfBinsToWeigh: null, 
  seedType: getSingleCategoryNameByType(props.unload.categories, 'seed-type'), 
  taskStartDate: null, 
  taskStatus: 'Pending', 
  fungicide: getSingleCategoryNameByType(props.unload.categories, 'fungicide'),
  receivalID: props.unload.id, 
  staffID: page.props.auth.user.id,
});

const getWeight = () => {
  getWeightMessage.responseChannel = window.pubnub.deviceId;
  getWeightMessage.system = form.system;
  const publishPayload = {
    channel: form.channel,
    message: getWeightMessage
  };
  window.pubnub.instance.publish(publishPayload);
}

const startWeighing = () => {
  startWeighingMessage.responseChannel = window.pubnub.deviceId;
  startWeighingMessage.taskStartDate = new Date();
  startWeighingMessage.numberOfBinsToWeigh = bins.value;
  const publishPayload = {
    channel: form.channel,
    message: startWeighingMessage
  };
  window.pubnub.instance.publish(publishPayload);
}

onMounted(() => {
  document.addEventListener('GetWeight', (event) => {
    console.log('WeightReceived', event?.detail?.CurrentWeight, (event?.detail?.CurrentWeight / 40));
    weight.value = (event?.detail?.CurrentWeight / 40).toFixed(3);
  })
})

const acceptWeight = () => {
  form.no_of_bins = parseInt(form.no_of_bins) + parseInt(bins.value);
  form.weight = parseFloat(form.weight) + parseFloat(weight.value);
  bins.value = null;
  weight.value = null;
}

const rejectWeight = () => {
  bins.value = null;
  weight.value = null;
}

const isForm = computed(() => props.isEdit || props.isNew)

const updateRecord = () => {
  form.patch(route('unloading.update', props.unload.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update')
    },
  });
}

const storeRecord = () => {
  form.post(route('unloading.store'), {
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
    <div v-if="!isNew" :class="colSize">
      <h4>Seed Information</h4>
      <div class="user-boxes">
        <h6>Grower</h6>
        <h5 v-if="unload.grower">
          <Link :href="route('users.index', { userId: unload.grower.id })">
            {{ unload.grower?.name }} {{ unload.grower?.grower_name ? ' (' + unload.grower?.grower_name + ')' : '' }}
          </Link>
        </h5>
        <h5 v-else>-</h5>

        <h6>Receival Id</h6>
        <h5>
          <Link :href="route('receivals.index', {receivalId: unload.id})">
            {{ unload.id }}
          </Link>
        </h5>

        <h6>Time Added</h6>
        <h5>{{ moment(unload.created_at).format('DD/MM/YYYY hh:mm A') }}</h5>

        <h6>Grower Group</h6>
        <ul v-if="getCategoriesByType(unload.categories, 'grower-group').length">
          <li><a>{{ getSingleCategoryNameByType(unload.categories, 'grower-group') }}</a></li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Paddock</h6>
        <ul v-if="unload.paddocks">
          <li v-for="paddock in unload.paddocks" :key="paddock">
            <a>{{ paddock }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Seed Variety</h6>
        <ul v-if="getCategoriesByType(unload.categories, 'seed-variety').length">
          <li><a>{{ getSingleCategoryNameByType(unload.categories, 'seed-variety') }}</a></li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Seed Generation</h6>
        <ul v-if="getCategoriesByType(unload.categories, 'seed-generation').length">
          <li><a>{{ getSingleCategoryNameByType(unload.categories, 'seed-generation') }}</a></li>
        </ul>
        <h5 v-else>-</h5>

        <h6>TIA Sampling</h6>
        <ul>
          <li>
            <a role="button" :class="{'btn-pending' : (unload.tia_sample?.status || 'pending') === 'pending'}">
              {{ toCamelCase(unload.tia_sample?.status || 'pending') }}
            </a>
          </li>
        </ul>
      </div>
    </div>
    <div :class="colSize">
      <h4>Unloading Information to Record</h4>
      <div class="user-boxes">
        <h6>Status</h6>
        <ul>
          <li>
            <a role="button" :class="{'btn-pending' : unload.status === 'pending'}">
              {{ toCamelCase(unload.status) }}
            </a>
          </li>
        </ul>
        
        <h6>Seed Type</h6>
        <ul v-if="getCategoriesByType(unload.categories, 'seed-type').length">
          <li><a>{{ getSingleCategoryNameByType(unload.categories, 'seed-type') }}</a></li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Fungicide</h6>
        <Multiselect
          v-if="isForm"
          v-model="form.fungicide"
          mode="tags"
          placeholder="Choose a fungicide"
          :searchable="true"
          :create-option="true"
          :options="getCategoriesDropDownByType(categories, 'fungicide')"
        />
        <ul v-else-if="getCategoriesByType(unload.categories, 'fungicide').length">
          <li v-for="category in getCategoriesByType(unload.categories, 'fungicide')" :key="category.id">
            <a>{{ category.category.name }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Bin Size</h6>
        <ul v-if="unload.bin_size">
          <li><a>{{ getBinSizesValue(unload.bin_size) }}</a></li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Channel</h6>
        <UlLiButton
          v-if="isForm"
          :value="form.channel"
          :error="form.errors.channel"
          :items="[
              { key: 'weighbridge', value: 'BU1' },
              { key: 'BU2', value: 'BU2' },
              { key: 'BU3', value: 'BU3' },
            ]"
          @click="(key) => form.channel = key"
        />
        <h5 v-else-if="unload.channel">{{ unload.channel === 'weighbridge' ? 'BU1' : unload.channel.toUpperCase() }}</h5>
        <h5 v-else>-</h5>
        
        <h6>System</h6>
        <UlLiButton
          v-if="isForm"
          :value="form.system"
          :error="form.errors.system"
          :items="form.channel === 'weighbridge' ? [ { key: '1', value: 'System 1' } ] : [
              { key: '1', value: 'System 1' },
              { key: '2', value: 'System 2' },
            ]"
          @click="(key) => form.system = key"
        />
        <h5 v-else-if="unload.system">{{ `System ${unload.system}` }}</h5>
        <h5 v-else>-</h5>

        <div class="row">
          <div class="col col-lg-4">
            <h6>Number of total bins</h6>
            <TextInput
              v-if="isForm"
              :disabled="true"
              v-model="form.no_of_bins"
              :error="form.errors.no_of_bins"
              type="text"
            />
            <h5 v-else-if="unload.no_of_bins">{{ unload.no_of_bins }}</h5>
            <h5 v-else>-</h5>
          </div>
          <div class="col col-lg-4">
            <h6>Weight of total bins</h6>
            <TextInput
              v-if="isForm"
              :disabled="true"
              v-model="form.weight"
              :error="form.errors.weight"
              type="text"
            />
            <h5 v-else-if="unload.weight">{{ unload.weight }} Tonnes</h5>
            <h5 v-else>-</h5>
          </div>
          <div v-if="isForm" class="col col-lg-4">
            <h6>&nbsp;</h6>
            <button @click="startWeighing" class="btn btn-red">Start Weight</button>
          </div>
        </div>

        <div v-if="isForm" class="row">
          <div class="col col-lg-4">
            <h6>Number of bins</h6>
            <UlLiButton
              :value="bins"
              :items="[
                { key: 1, value: '1' },
                { key: 2, value: '2' },
              ]"
              @click="(key) => bins = key"
            />
          </div>
          <div class="col col-lg-4">
            <h6>Weight of bins</h6>
            <input
              :disabled="true"
              :value="weight"
              class="form-control"
              type="text"
            >
          </div>
          <div class="col col-lg-4">
            <h6>&nbsp;</h6>
            <div v-if="weight && bins">
              <button @click="acceptWeight" class="btn btn-red"><i class="fa fa-check"></i></button>
              <button @click="rejectWeight" class="btn btn-red"><i class="fa fa fa-ban"></i></button>
            </div>
            <button v-else @click="getWeight" class="btn btn-red">Get Weight</button>
          </div>
        </div>

        <div v-if="isForm">
          <h6>Status</h6>
          <UlLiButton
            :value="form.status"
            :error="form.errors.status"
            :items="[
              { key: 'pending', value: 'Pending' },
              { key: 'completed', value: 'Completed' },
            ]"
            @click="(key) => form.status = key"
          />
        </div>
      </div>
    </div>
  </div>
</template>
