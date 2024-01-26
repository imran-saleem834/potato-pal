<script setup>
import moment from 'moment';
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useForm, Link, usePage } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import UlLiButton from "@/Components/UlLiButton.vue";
import Multiselect from '@vueform/multiselect';
import { binSizes, getBinSizesValue } from "@/tonnes.js";
import {
  toCamelCase,
  getSingleCategoryNameByType,
  getCategoriesDropDownByType,
  getCategoriesByType,
  getCategoryIdsByType,
} from "@/helper.js";

const props = defineProps({
  receival: Object,
  categories: Array,
  colSize: String,
  isEdit: Boolean,
});

const bins = ref([]);
const weight = ref([]);
const weightError = ref([]);
const getWeightIndex = ref(null);

const emit = defineEmits(['update', 'create']);

const form = useForm({
  fungicide: getCategoryIdsByType(props.receival.categories, 'fungicide'),
  status: props.receival.status,
  unloads: props.receival.unloads,
});

watch(() => props.receival,
  (receival) => {
    form.clearErrors();
    form.fungicide = getCategoryIdsByType(receival.categories, 'fungicide')
    form.unloads = receival.unloads;

    updateUnloadsOnChangeReceival(receival);
  }
);

const defaultUnloadFields = {
  seed_type: null,
  bin_size: null,
  no_of_bins: 0,
  weight: 0,
  channel: null,
  system: null,
  isSeedTypeOversize: false
};

const resetBinsAndWeight = (index) => {
  form.unloads[index].no_of_bins = null;
  form.unloads[index].weight = null;
}

const onChangeChannel = (value, index) => {
  form.unloads[index].channel = value;
  form.unloads[index].bin_size = value === 'weighbridge' ? 1000 : 2000;

  resetBinsAndWeight(index);
}

const onChangeSeedType = (value, index) => {
  const selectedCategoryLabel = getCategoriesDropDownByType(props.categories, 'seed-type')
    .find(category => category.value === value)?.label

  resetBinsAndWeight(index);
  
  form.unloads[index].seed_type = value;
  form.unloads[index].isSeedTypeOversize = (selectedCategoryLabel === 'Oversize');
}

const updateUnloadsOnChangeReceival = (receival) => {
  form.fungicide = getCategoryIdsByType(receival.categories, 'fungicide')
  form.unloads = receival.unloads.length <= 0 ? [{ ...defaultUnloadFields }] : receival.unloads;

  form.unloads.forEach((unload, index) => {
    onChangeSeedType(getCategoryIdsByType(unload.categories, 'seed-type')[0], index);

    bins.value[index] = null;
    weight.value[index] = null;
    weightError.value[index] = null;
  });
}

const onChangeTotalBins = (index) => {
  if (!form.unloads[index].isSeedTypeOversize) {
    return;
  }
  const oversizeSeedWeightOneTonne = 920;
  if (form.unloads[index].bin_size === 500) {
    form.unloads[index].weight = form.unloads[index].no_of_bins * (oversizeSeedWeightOneTonne / 2)
  }
  if (form.unloads[index].bin_size === 1000) {
    form.unloads[index].weight = form.unloads[index].no_of_bins * oversizeSeedWeightOneTonne
  }
  if (form.unloads[index].bin_size === 2000) {
    form.unloads[index].weight = form.unloads[index].no_of_bins * (oversizeSeedWeightOneTonne * 2)
  }
}

const onChangeBinSize = (value, index) => {
  form.unloads[index].bin_size = value;

  if (!form.unloads[index].isSeedTypeOversize) {
    resetBinsAndWeight(index);
  }
  
  onChangeTotalBins(index);
}

const onChangeSystem = (value, index) => {
  form.unloads[index].system = value;
  
  resetBinsAndWeight(index);
}

updateUnloadsOnChangeReceival(props.receival);

const addMoreUnload = () => form.unloads.push({ ...defaultUnloadFields });

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
  exWhomName: props.receival.grower.grower_name || '',
  exWhomGroupName: getSingleCategoryNameByType(props.receival.categories, 'group'),
  potIdentifierCode: props.receival.id,
  taskOwner: page.props.auth.user.name,
  emptyBinWeight: props.receival.bin_size === 1 ? '120' : '204',
  binSize: props.receival.bin_size,
  numberOfBinsToWeigh: null,
  seedType: getSingleCategoryNameByType(props.receival.categories, 'seed-type'),
  taskStartDate: null,
  taskStatus: 'Pending',
  fungicide: getSingleCategoryNameByType(props.receival.categories, 'fungicide'),
  receivalID: props.receival.id,
  staffID: page.props.auth.user.id,
});

const getWeight = (index) => {
  getWeightIndex.value = index;
  weightError.value[index] = null;
  if (!form.unloads[index].channel) {
    weightError.value[index] = 'Channel not selected.';
    return;
  }
  if (!form.unloads[index].system) {
    weightError.value[index] = 'System not selected.';
    return;
  }
  if (!bins.value[index]) {
    weightError.value[index] = 'Bins not selected.';
    return;
  }
  getWeightMessage.responseChannel = window.pubnub.deviceId;
  getWeightMessage.system = form.unloads[index].system;
  const publishPayload = {
    channel: form.unloads[index].channel,
    message: getWeightMessage
  };
  window.pubnub.instance.publish(publishPayload);
}

const startWeighing = (index) => {
  startWeighingMessage.responseChannel = window.pubnub.deviceId;
  startWeighingMessage.taskStartDate = new Date();
  startWeighingMessage.numberOfBinsToWeigh = bins.value[index];
  const publishPayload = {
    channel: form.unloads[index].channel,
    message: startWeighingMessage
  };
  window.pubnub.instance.publish(publishPayload);
}

onMounted(() => {
  document.addEventListener('GetWeight', (event) => {
    console.log('WeightReceived', event?.detail);
    weight.value[getWeightIndex.value] = event?.detail?.CurrentWeight?.toFixed(2);
    getWeightIndex.value = null;
  })
})

const acceptWeight = (index) => {
  form.unloads[index].no_of_bins = parseInt(form.unloads[index].no_of_bins ?? 0) + parseInt(bins.value[index]);
  form.unloads[index].weight = parseFloat(form.unloads[index].weight ?? 0) + parseFloat(weight.value[index]);

  rejectWeight(index);
}

const rejectWeight = (index) => {
  bins.value[index] = null;
  weight.value[index] = null;
}

const isForm = computed(() => props.isEdit);
const growerName = computed(() => {
  return props.receival.grower.name + (props.receival.grower?.grower_name ? ' (' + props.receival.grower?.grower_name + ')' : '');
});

const updateRecord = () => {
  form.patch(route('unloading.update', props.receival.id), {
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
    <div v-if="isEdit" class="col-md-12">
      <div class="flex-end create-update-btn">
        <a role="button" @click="updateRecord" class="btn btn-red">Update</a>
      </div>
    </div>
    <div :class="colSize">
      <h4>Seed Information</h4>
      <div class="user-boxes">
        <h6>Grower</h6>
        <h5 v-if="receival.grower">
          <Link :href="route('users.index', { userId: receival.grower.id })">{{ growerName }}</Link>
        </h5>
        <h5 v-else>-</h5>

        <h6>Receival Id</h6>
        <h5>
          <Link :href="route('receivals.index', {receivalId: receival.id})">
            {{ receival.id }}
          </Link>
        </h5>

        <h6>Time Added</h6>
        <h5>{{ moment(receival.created_at).format('DD/MM/YYYY hh:mm A') }}</h5>

        <h6>Grower Group</h6>
        <ul v-if="getCategoriesByType(receival.categories, 'grower-group').length">
          <li><a>{{ getSingleCategoryNameByType(receival.categories, 'grower-group') }}</a></li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Paddock</h6>
        <ul v-if="receival.paddocks">
          <li v-for="paddock in receival.paddocks" :key="paddock">
            <a>{{ paddock }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Seed Variety</h6>
        <ul v-if="getCategoriesByType(receival.categories, 'seed-variety').length">
          <li><a>{{ getSingleCategoryNameByType(receival.categories, 'seed-variety') }}</a></li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Seed Generation</h6>
        <ul v-if="getCategoriesByType(receival.categories, 'seed-generation').length">
          <li><a>{{ getSingleCategoryNameByType(receival.categories, 'seed-generation') }}</a></li>
        </ul>
        <h5 v-else>-</h5>

        <h6>TIA Sampling</h6>
        <ul>
          <li>
            <a role="button" :class="{'btn-pending' : (receival.tia_sample?.status || 'pending') === 'pending'}">
              {{ toCamelCase(receival.tia_sample?.status || 'pending') }}
            </a>
          </li>
        </ul>
      </div>
    </div>
    <div :class="colSize">
      <h4>Unloading Information to Record</h4>
      <div class="user-boxes">
        <h6>Status</h6>
        <UlLiButton
          v-if="isForm"
          :value="form.status"
          :error="form.errors.status"
          :items="[
            { value: 'pending', label: 'Pending' },
            { value: 'completed', label: 'Completed' },
          ]"
          @click="(value) => form.status = value"
        />
        <ul v-else>
          <li>
            <a role="button" :class="{'btn-pending' : form.status === 'pending'}">
              {{ toCamelCase(form.status) }}
            </a>
          </li>
        </ul>

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
        <ul v-else-if="getCategoriesByType(receival.categories, 'fungicide').length">
          <li v-for="category in getCategoriesByType(receival.categories, 'fungicide')" :key="category.id">
            <a>{{ category.category.name }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>
      </div>
      <div v-for="(unload, index) in form.unloads" class="user-boxes">
        <h6>Seed Type</h6>
        <UlLiButton
          v-if="isForm && form.unloads[index]"
          :value="form.unloads[index].seed_type"
          :error="form.errors[`unloads.${index}.seed_type`]"
          :items="getCategoriesDropDownByType(categories, 'seed-type')"
          @click="(value) => onChangeSeedType(value, index)"
        />
        <ul v-else-if="getCategoriesByType(unload.categories, 'seed-type').length">
          <li><a>{{ getSingleCategoryNameByType(unload.categories, 'seed-type') }}</a></li>
        </ul>
        <h5 v-else>-</h5>
        
        <template v-if="!form.unloads[index]?.isSeedTypeOversize">
          <h6>Channel</h6>
          <UlLiButton
            v-if="isForm && form.unloads[index]"
            :value="form.unloads[index].channel"
            :error="form.errors[`unloads.${index}.channel`]"
            :items="[
              { value: 'weighbridge', label: 'BU1' },
              { value: 'BU2', label: 'BU2' },
              { value: 'BU3', label: 'BU3' },
            ]"
            @click="(value) => onChangeChannel(value, index)"
          />
          <h5 v-else-if="unload.channel">
            {{ unload.channel === 'weighbridge' ? 'BU1' : unload.channel.toUpperCase() }}
          </h5>
          <h5 v-else>-</h5>
        </template>

        <h6>Bin Size</h6>
        <UlLiButton
          v-if="isForm && form.unloads[index]"
          :value="form.unloads[index].bin_size"
          :error="form.errors[`unloads.${index}.bin_size`]"
          :items="binSizes"
          @click="(value) => onChangeBinSize(value, index)"
        />
        <ul v-else-if="unload.bin_size">
          <li><a>{{ getBinSizesValue(unload.bin_size) }}</a></li>
        </ul>
        <h5 v-else>-</h5>

        <template v-if="!form.unloads[index]?.isSeedTypeOversize">
          <h6>System</h6>
          <UlLiButton
            v-if="isForm && form.unloads[index]"
            :value="form.unloads[index].system"
            :error="form.errors[`unloads.${index}.system`]"
            :items="
              form.unloads[index].channel === 'weighbridge' ? 
              [ { value: 1, label: 'System 1' } ] : 
              [ { value: 1, label: 'System 1' }, { value: 2, label: 'System 2' } ]
            "
            @click="(value) => onChangeSystem(value, index)"
          />
          <h5 v-else-if="unload.system">{{ `System ${unload.system}` }}</h5>
          <h5 v-else>-</h5>
        </template>

        <div v-if="isForm && form.unloads[index]" class="row">
          <div class="col col-lg-4">
            <h6>Number of total bins</h6>
            <TextInput
              :disabled="!form.unloads[index]?.isSeedTypeOversize"
              v-model="form.unloads[index].no_of_bins"
              :error="form.errors[`unloads.${index}.no_of_bins`]"
              type="text"
              @keyup="() => onChangeTotalBins(index)"
            />
          </div>
          <div class="col col-lg-4">
            <h6>Weight of total bins</h6>
            <TextInput
              :disabled="true"
              v-model="form.unloads[index].weight"
              :error="form.errors[`unloads.${index}.weight`]"
              type="text"
            />
          </div>
          <div class="col col-lg-4" v-if="!form.unloads[index]?.isSeedTypeOversize">
            <h6>&nbsp;</h6>
            <button @click="startWeighing(index)" class="btn btn-red">Start Weight</button>
          </div>
        </div>
        <template v-else>
          <h6>Number of total bins</h6>
          <h5 v-if="unload.no_of_bins">{{ unload.no_of_bins }}</h5>
          <h5 v-else>-</h5>

          <h6>Weight of total bins</h6>
          <h5 v-if="unload.weight">{{ unload.weight }} Kg</h5>
          <h5 v-else>-</h5>
        </template>

        <div v-if="isForm && form.unloads[index] && !form.unloads[index]?.isSeedTypeOversize" class="row">
          <div class="col col-lg-4">
            <h6>Number of bins</h6>
            <UlLiButton
              :value="bins[index]"
              :items="[
                { value: 1, label: '1' },
                { value: 2, label: '2' },
              ]"
              @click="(value) => bins[index] = value"
            />
          </div>
          <div class="col col-lg-4">
            <h6>Weight of bins</h6>
            <input
              :disabled="true"
              :value="weight[index]"
              class="form-control"
              type="text"
            >
          </div>
          <div class="col col-lg-4">
            <h6>&nbsp;</h6>
            <div v-if="weight[index] && bins[index]">
              <button @click="acceptWeight(index)" class="btn btn-red"><i class="fa fa-check"></i></button>
              <button @click="rejectWeight(index)" class="btn btn-red"><i class="fa fa fa-ban"></i></button>
            </div>
            <button v-else @click="getWeight(index)" class="btn btn-red">Get Weight</button>
          </div>
          <div v-if="weightError[index]" class="col col-lg-12 has-error">
            <span class="help-block text-left">{{ weightError[index] }}</span>
          </div>
        </div>
      </div>

      <div v-if="isForm" class="col col-sm-12 mb-2 text-right">
        <button @click="addMoreUnload" class="btn btn-red">Add More seed type weighing</button>
      </div>
    </div>
  </div>
</template>
