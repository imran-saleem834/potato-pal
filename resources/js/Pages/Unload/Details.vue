<script setup>
import { computed, watch } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import { getCategoriesByType, toCamelCase } from "@/helper.js";
import moment from 'moment';
import TextInput from "@/Components/TextInput.vue";
import UlLiButton from "@/Components/UlLiButton.vue";
import { getBinSizesValue } from "@/tonnes.js";

const props = defineProps({
  unload: Object,
  colSize: String,
  isEdit: Boolean,
  isNew: Boolean,
  categories: Array,
});

const emit = defineEmits(['update', 'create']);

const form = useForm({
  no_of_bins: props.unload.no_of_bins,
  weight: props.unload.weight,
  status: props.unload.status,
});

watch(() => props.unload,
  (unload) => {
    form.clearErrors();
    form.no_of_bins = unload.no_of_bins
    form.weight = unload.weight
    form.status = unload.status
  }
);

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
      <div class="user-boxes">
        <h6>Name</h6>
        <h5 v-if="unload.grower">
          <Link :href="route('users.index', { userId: unload.grower.id })">
            {{ unload.grower.name }}
          </Link>
        </h5>
        <h5 v-else>-</h5>

        <h6>Receival Id</h6>
        <Link :href="route('receivals.index', {receivalId: unload.id})">
          {{ unload.id }}
        </Link>

        <h6>Time Added</h6>
        <h5>{{ moment(unload.created_at).format('DD/MM/YYYY hh:mm A') }}</h5>

        <h6>Paddock</h6>
        <ul v-if="unload.paddocks">
          <li v-for="paddock in unload.paddocks" :key="paddock">
            <a>{{ paddock }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Fungicide</h6>
        <ul v-if="getCategoriesByType(unload.categories, 'fungicide').length">
          <li v-for="category in getCategoriesByType(unload.categories, 'fungicide')"
              :key="category.id">
            <a>{{ category.category?.name }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Status</h6>
        <ul>
          <li>
            <a role="button" :class="{'btn-pending' : unload.status === 'pending'}">
              {{ toCamelCase(unload.status) }}
            </a>
          </li>
        </ul>
        
        <h6>TIA Sampled</h6>
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
      <h4>Seed information to Record</h4>
      <div class="user-boxes">
        <h6>Seed Type</h6>
        <ul v-if="getCategoriesByType(unload.categories, 'seed-type').length">
          <li v-for="category in getCategoriesByType(unload.categories, 'seed-type')"
              :key="category.id">
            <a>{{ category.category?.name }}</a>
          </li>
        </ul>
        <h5 v-else>-</h5>

        <h6>Bin Size</h6>
        <ul v-if="unload.bin_size">
          <li><a>{{ getBinSizesValue(unload.bin_size) }}</a></li>
        </ul>
        <h5 v-else>-</h5>
        
        <h6>Number of bins</h6>
        <TextInput
          v-if="isForm"
          v-model="form.no_of_bins"
          :error="form.errors.no_of_bins"
          type="text"
        />
        <h5 v-else-if="unload.no_of_bins">{{ unload.no_of_bins }}</h5>
        <h5 v-else>-</h5>

        <h6>Weight of total bins</h6>
        <TextInput
          v-if="isForm"
          v-model="form.weight"
          :error="form.errors.weight"
          type="text"
        />
        <h5 v-else-if="unload.weight">{{ unload.weight }} Tonnes</h5>
        <h5 v-else>-</h5>

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
