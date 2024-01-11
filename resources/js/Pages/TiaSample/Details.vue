<script setup>
import { computed, watch } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import { getCategoriesByType, toCamelCase } from "@/helper.js";
import moment from 'moment';
import Multiselect from '@vueform/multiselect';
import TextInput from "@/Components/TextInput.vue";
import Images from "@/Components/Images.vue";
import UlLiButton from "@/Components/UlLiButton.vue";
import { binSizes, getBinSizesValue } from "@/tonnes.js";

const props = defineProps({
  tiaSample: Object,
  colSize: String,
  isEdit: Boolean,
  isNew: Boolean,
  receivals: Array,
  categories: Array,
});

const emit = defineEmits(['update', 'create']);

const addEmptyValues = (name, values, noOfValues) => {
  const length = (noOfValues - 1);
  for (let i = 0; i <= length; i++) {
    values[i] = values[i] || '';
  }
  if (name === 'tubers' && values[4]) {
    return values;
  }
  if (values[4] && !values[4].toString().includes('%')) {
    values[4] = values[4] + '%';
  }
  if (values[5] && !values[5].toString().includes('%')) {
    values[5] = values[5] + '%';
  }
  return values;
}

const displaySampleValue = (name) => {
  if (name === 'tubers') {
    return getTotalTurbersValue();
  }

  const values = props.tiaSample[name];
  if (!values) return '';

  const length = (values.length - 1);

  if (['sub_total', 'total_disease', 'total_defects'].includes(name)) {
    return values[length] || '-';
  }

  return values.slice(0, length)
    .filter(val => val !== '')
    .join(',  ') + ' - ' + values.slice(-1);
}

const displaySampleValue2 = (name) => {
  const values = props.tiaSample[name];
  if (!values) return '';

  const length = (values.length - 2);

  return values.slice(0, length)
    .filter(val => val !== '')
    .join(',  ') + ' - ' + values[length];
}

const sum = (accumulator, current) => parseFloat(accumulator) + parseFloat(current);
const round = (num, decimalPlaces = 2) => {
  const p = Math.pow(10, decimalPlaces);
  return Math.round(num * p) / p;
}

const changeSampleValue = (name, length) => {
  const input = form[name].slice(0, length)
    .filter(val => val !== '')
    .reduce(sum, 0);

  if (name === 'tubers') {
    form[name][length] = input;
    return;
  }

  const totalTurbers = getTotalTurbersValue();
  form[name][length] = totalTurbers ? round(input * 100 / getTotalTurbersValue()) + '%' : '';

  console.log('changeSampleValue', name, form[name][length]);

  updateTotal(length);
}

const updateTotal = (length) => {
  let addition = 0;
  ['dry_rot', 'black_scurf', 'powdery_scab', 'root_knot_nematode', 'soft_rots', 'pink_rot'].forEach(name => {
    addition += parseFloat(form[name][length].replace('%', '') || 0);
  });

  form['sub_total'][length] = round(addition) + '%';

  addition += parseFloat(form['common_scab'][length].replace('%', '') || 0);

  form['total_disease'][length] = round(addition) + '%';

  ['black_scurf_scatter', 'insect_damage', 'malformed_tubers', 'mechanical_damage', 'stem_end_discolour', 'foreign_cultivars', 'misc_frost'].forEach(name => {
    addition += parseFloat(form[name][length].replace('%', '') || 0);
  });

  form['total_defects'][length] = round(addition) + '%';
}

const getTotalTurbersValue = () => {
  return form.tubers.slice(0, 4).filter(val => val !== '').reduce(sum, 0);
}

const samples = [
  { title: 'No. of tubers Samples', name: 'tubers', allow: 'Allowable tolerances' },
  { title: 'Dry Rot', name: 'dry_rot', allow: '2%' },
  { title: 'Black Scurf 2mm', name: 'black_scurf', allow: '2%' },
  { title: 'Powdery Scab', name: 'powdery_scab', allow: '2%' },
  { title: 'Root Knot Nematode', name: 'root_knot_nematode', allow: '2%' },
  { title: 'Soft Rots', name: 'soft_rots', allow: '0.25%' },
  { title: 'Pink Rot', name: 'pink_rot', allow: '0.25%' },
  { title: 'Sub Total', bold: true, name: 'sub_total', allow: '2%' },
  { title: 'Common Scab', name: 'common_scab', allow: '4% (2%)' },
  { title: 'Total Disease', bold: true, name: 'total_disease', allow: '4% (2%)' },
  { title: 'Black Scurf Scatter', name: 'black_scurf_scatter', allow: '10%' },
  { title: 'Other Tuber Defects' },
  { title: 'Insect Damage', name: 'insect_damage', allow: '1.5%' },
  { title: 'Malformed Tubers', name: 'malformed_tubers', allow: '2%' },
  { title: 'Mechanical Damage', name: 'mechanical_damage', allow: '2%' },
  { title: 'Stem End Discolour', name: 'stem_end_discolour', allow: '2%' },
  { title: 'Foreign Cultivars', name: 'foreign_cultivars', allow: '0%' },
  { title: 'Misc. Frost', name: 'misc_frost', allow: '1%' },
  { title: 'Total Defects', bold: true, name: 'total_defects', allow: '2%' },
  { title: 'Minimal Insect Feeding', name: 'minimal_insect_feeding', allow: 'Additional 2%' },
  { title: 'Oversize', name: 'oversize', allow: '' },
  { title: 'Undersize', name: 'undersize', allow: '' },
]

const sample2 = [
  { title: 'Powdery Scab', name: 'disease_powdery_scab', allow: '', inputs: 6 },
  { title: 'Rootknot Nematode', name: 'disease_root_knot_nematode', allow: '', inputs: 6 },
  { title: 'Common Scab', name: 'disease_common_scab', allow: '', inputs: 6 },
]

samples.concat(sample2).forEach(sample => {
  props.tiaSample[sample.name] = addEmptyValues(sample.name, props.tiaSample[sample.name] || [], sample.inputs || 5);
});

const form = useForm({
  receival_id: props.tiaSample.receival_id,
  processor: props.tiaSample.processor,
  inspection_no: props.tiaSample.inspection_no,
  inspection_date: moment(props.tiaSample.inspection_date).format('YYYY-MM-DD'),
  cool_store: props.tiaSample.cool_store,
  size: props.tiaSample.size,
  tubers: props.tiaSample.tubers,
  dry_rot: props.tiaSample.dry_rot,
  black_scurf: props.tiaSample.black_scurf,
  powdery_scab: props.tiaSample.powdery_scab,
  root_knot_nematode: props.tiaSample.root_knot_nematode,
  soft_rots: props.tiaSample.soft_rots,
  pink_rot: props.tiaSample.pink_rot,
  sub_total: props.tiaSample.sub_total,
  common_scab: props.tiaSample.common_scab,
  total_disease: props.tiaSample.total_disease,
  black_scurf_scatter: props.tiaSample.black_scurf_scatter,
  insect_damage: props.tiaSample.insect_damage,
  malformed_tubers: props.tiaSample.malformed_tubers,
  mechanical_damage: props.tiaSample.mechanical_damage,
  stem_end_discolour: props.tiaSample.stem_end_discolour,
  foreign_cultivars: props.tiaSample.foreign_cultivars,
  misc_frost: props.tiaSample.misc_frost,
  total_defects: props.tiaSample.total_defects,
  minimal_insect_feeding: props.tiaSample.minimal_insect_feeding,
  oversize: props.tiaSample.oversize,
  undersize: props.tiaSample.undersize,
  disease_scoring: props.tiaSample.disease_scoring,
  disease_powdery_scab: props.tiaSample.disease_powdery_scab,
  disease_root_knot_nematode: props.tiaSample.disease_root_knot_nematode,
  disease_common_scab: props.tiaSample.disease_common_scab,
  excessive_dirt: props.tiaSample.excessive_dirt,
  minor_skin_cracking: props.tiaSample.minor_skin_cracking,
  skinning: props.tiaSample.skinning,
  regarding: props.tiaSample.regarding,
  comment: props.tiaSample.comment,
  status: props.tiaSample.status,
});

watch(() => props.tiaSample,
  (tiaSample) => {
    form.clearErrors();
    form.receival_id = tiaSample.receival_id
    form.processor = tiaSample.processor
    form.inspection_no = tiaSample.inspection_no
    form.inspection_date = moment(tiaSample.inspection_date).format('YYYY-MM-DD')
    form.cool_store = tiaSample.cool_store
    form.size = tiaSample.size
    form.disease_scoring = tiaSample.disease_scoring
    form.excessive_dirt = tiaSample.excessive_dirt
    form.minor_skin_cracking = tiaSample.minor_skin_cracking
    form.skinning = tiaSample.skinning
    form.regarding = tiaSample.regarding
    form.comment = tiaSample.comment
    form.status = tiaSample.status
    samples.concat(sample2).forEach(sample => {
      form[sample.name] = addEmptyValues(sample.name, tiaSample[sample.name] || [], sample.inputs || 5);
    });
  }
);

watch(() => props.isNew,
  (isNew) => {
    if (isNew) {
      samples.concat(sample2).forEach(sample => {
        props.tiaSample[sample.name] = addEmptyValues(sample.name, [], sample.inputs || 5);
      });
    }
  }
);

const isForm = computed(() => props.isEdit || props.isNew)

const updateRecord = () => {
  form.patch(route('tia-samples.update', props.tiaSample.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update')
    },
  });
}

const storeRecord = () => {
  form.post(route('tia-samples.store'), {
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
        <template v-if="isForm">
          <h6>Receival</h6>
          <Multiselect
            v-model="form.receival_id"
            mode="single"
            placeholder="Choose a receival"
            :searchable="true"
            :options="receivals"
          />
          <div :class="{'has-error' : form.errors.receival_id}">
            <span v-show="form.errors.receival_id" class="help-block text-left">
              {{ form.errors.receival_id }}
            </span>
          </div>
        </template>
        <template v-else>
          <h6>Grower Name</h6>
          <h5 v-if="tiaSample.receival?.grower">
            <Link :href="route('users.index', { userId: tiaSample.receival.grower.id })">
              {{ tiaSample.receival.grower?.name }} {{ tiaSample.receival.grower?.grower_name ? ' (' + tiaSample.receival.grower?.grower_name + ')' : '' }}
            </Link>
          </h5>
          <h5 v-else>-</h5>

          <h6>Tia Sample Id</h6>
          <h5>{{ tiaSample.id }}</h5>
        </template>

        <template v-if="!isNew">
          <h6>Receival Id</h6>
          <h5 v-if="tiaSample.receival">
            <Link :href="route('receivals.index', { receivalId: tiaSample.receival.id })">
              {{ tiaSample.receival.id }}
            </Link>
          </h5>
          <h5 v-else>-</h5>

          <h6>Time Added</h6>
          <h5>{{ moment(tiaSample.created_at).format('DD/MM/YYYY hh:mm A') }}</h5>

          <h6>Grower Group</h6>
          <ul v-if="getCategoriesByType(tiaSample?.receival?.categories, 'grower-group').length">
            <li v-for="category in getCategoriesByType(tiaSample?.receival?.categories, 'grower-group')"
                :key="category.id">
              <a>{{ category.category?.name }}</a>
            </li>
          </ul>
          <h5 v-else>-</h5>
        </template>

        <h6>Status</h6>
        <UlLiButton
          v-if="isForm"
          :value="form.status"
          :error="form.errors.status"
          :items="[
            {key: 'pending', value: 'Pending'},
            {key: 'certified', value: 'Certified'},
            {key: 'not-certified', value: 'Not Certified'},
            {key: 'rejected', value: 'Rejected'},
          ]"
          @click="(key) => form.status = key"
        />
        <ul v-else>
          <li>
            <a role="button" :class="{'btn-pending' : tiaSample.status === 'pending'}">
              {{ toCamelCase(tiaSample.status || 'pending') }}
            </a>
          </li>
        </ul>
      </div>

      <h4>Seed Details</h4>
      <div class="user-boxes">
        <template v-if="!isNew">
          <h6>Seed Variety</h6>
          <ul v-if="getCategoriesByType(tiaSample?.receival?.categories, 'seed-variety').length">
            <li v-for="category in getCategoriesByType(tiaSample?.receival?.categories, 'seed-variety')"
                :key="category.id">
              <a>{{ category.category?.name }}</a>
            </li>
          </ul>
          <h5 v-else>-</h5>

          <h6>Seed Generation</h6>
          <ul v-if="getCategoriesByType(tiaSample?.receival?.categories, 'seed-generation').length">
            <li v-for="category in getCategoriesByType(tiaSample?.receival?.categories, 'seed-generation')"
                :key="category.id">
              <a>{{ category.category?.name }}</a>
            </li>
          </ul>
          <h5 v-else>-</h5>

          <h6>Grower Docket No</h6>
          <h5 v-if="tiaSample.receival?.grower_docket_no">{{ tiaSample.receival.grower_docket_no }}</h5>
          <h5 v-else>-</h5>
        </template>

        <h6>Processor</h6>
        <UlLiButton
          v-if="isForm"
          :value="form.processor"
          :error="form.errors.processor"
          :items="binSizes"
          @click="(key) => form.processor = key"
        />
        <h5 v-else-if="tiaSample.processor">{{ getBinSizesValue(tiaSample.processor) }}</h5>
        <h5 v-else>-</h5>

        <h6>Inspection No</h6>
        <TextInput v-if="isForm" v-model="form.inspection_no" :error="form.errors.inspection_no" type="text"/>
        <h5 v-else-if="tiaSample.inspection_no">{{ tiaSample.inspection_no }}</h5>
        <h5 v-else>-</h5>

        <h6>Inspection Date</h6>
        <TextInput
          v-if="isForm"
          v-model="form.inspection_date"
          :error="form.errors.inspection_date"
          type="date"
        />
        <h5 v-else-if="tiaSample.inspection_date">
          {{ moment(tiaSample.inspection_date).format('YYYY-MM-DD') }}
        </h5>
        <h5 v-else>-</h5>

        <h6>Cool Store</h6>
        <TextInput v-if="isForm" v-model="form.cool_store" :error="form.errors.cool_store" type="text"/>
        <h5 v-else-if="tiaSample.cool_store">{{ tiaSample.cool_store }}</h5>
        <h5 v-else>-</h5>
      </div>

      <h4>Continue External Report</h4>
      <div class="user-boxes">
        <h6><strong>DISEASE SCORING KEY</strong></h6>
        <ul v-if="isForm">
          <li v-for="score in [1, 2, 3, 4, 5, 6]" :key="`score-${score}`">
            <a
              role="button"
              @click="() => form.disease_scoring = score"
              :class="{'black-btn' : form.disease_scoring === score}"
            >{{ score }}</a>
          </li>
        </ul>
        <div v-else-if="tiaSample.disease_scoring">{{ tiaSample.disease_scoring }}</div>
        <div v-else>-</div>

        <div v-if="form.errors.disease_scoring" class="has-error">
          <span class="help-block text-left">{{ form.errors.disease_scoring }}</span>
        </div>

        <template v-for="sample in sample2" :key="sample.name">
          <h6>{{ sample.title }}</h6>
          <ul v-if="isForm" class="multiple-inputs">
            <li v-for="(value, index) in tiaSample[sample.name]" :key="`${index}-${sample.name}`"
                :class="sample.name">
              <TextInput
                v-model="form[sample.name][index]"
                :disabled="index === 4"
                type="text"
                @keyup="changeSampleValue(sample.name, 4)"
              />
            </li>

            <template
              v-for="(value, index) in tiaSample[sample.name]"
              :key="`${index}-${sample.name}-error`"
            >
              <div v-show="form.errors[`${sample.name}.${index}`]" class="has-error">
                <span class="help-block text-left">
                  {{ form.errors[`${sample.name}.${index}`] }}
                </span>
              </div>
            </template>
          </ul>
          <h5 v-else>
            {{ displaySampleValue2(sample.name) }}
          </h5>
        </template>


        <h6>Excessive Dirt</h6>
        <div v-if="isForm" :class="{'has-error' : form.errors.excessive_dirt }">
          <span v-show="form.errors.excessive_dirt" class="help-block text-left">
            {{ form.errors.excessive_dirt }}
          </span>
        </div>
        <ul v-if="isForm">
          <li>
            <a
              role="button"
              @click="() => form.excessive_dirt = !form.excessive_dirt"
              :class="{'black-btn' : form.excessive_dirt}"
            >Excessive Dirt</a>
          </li>
        </ul>
        <h5 v-else-if="tiaSample.excessive_dirt">{{ tiaSample.excessive_dirt ? 'Yes' : 'No' }}</h5>
        <h5 v-else>-</h5>

        <h6>Minor Skin Cracking</h6>
        <div v-if="form.errors.minor_skin_cracking" class="has-error">
          <span class="help-block text-left">{{ form.errors.minor_skin_cracking }}</span>
        </div>
        <ul v-if="isForm">
          <li>
            <a
              role="button"
              @click="() => form.minor_skin_cracking = !form.minor_skin_cracking"
              :class="{'black-btn' : form.minor_skin_cracking}"
            >Minor Skin Cracking</a>
          </li>
        </ul>
        <h5 v-else-if="tiaSample.minor_skin_cracking">{{ tiaSample.minor_skin_cracking ? 'Yes' : 'No' }}</h5>
        <h5 v-else>-</h5>

        <h6>Skinning</h6>
        <div v-if="form.errors.skinning" class="has-error">
          <span class="help-block text-left">{{ form.errors.skinning }}</span>
        </div>
        <ul v-if="isForm">
          <li>
            <a
              role="button"
              @click="() => form.skinning = !form.skinning"
              :class="{'black-btn' : form.skinning}"
            >Skinning</a>
          </li>
        </ul>
        <h5 v-else-if="tiaSample.skinning">{{ tiaSample.skinning ? 'Yes' : 'No' }}</h5>
        <h5 v-else>-</h5>

        <h6>Regarding</h6>
        <div v-if="isForm" :class="{'has-error' : form.errors.regarding }">
          <span v-show="form.errors.regarding" class="help-block text-left">{{ form.errors.regarding }}</span>
        </div>
        <ul v-if="isForm">
          <li>
            <a
              role="button"
              @click="() => form.regarding = !form.regarding"
              :class="{'black-btn' : form.regarding}"
            >Regarding</a>
          </li>
        </ul>
        <h5 v-else-if="tiaSample.regarding">{{ tiaSample.regarding ? 'Yes' : 'No' }}</h5>
        <h5 v-else>-</h5>

        <h6>Comment</h6>
        <TextInput v-if="isForm" v-model="form.comment" :error="form.errors.comment" type="text"/>
        <h5 v-else-if="tiaSample.comment">{{ tiaSample.comment }}</h5>
        <h5 v-else>-</h5>
      </div>
    </div>
    <div :class="colSize">
      <h4>TIA Seed Potato Certificate Sheet</h4>
      <div class="user-boxes multiple-inputs-container">
        <h6>Size</h6>
        <UlLiButton
          v-if="isForm"
          :value="form.size"
          :error="form.errors.size"
          :items="[
            {key: '35-350g', value: '35 - 350g'},
            {key: '90mm', value: '90mm'},
            {key: '70mm', value: '70mm'},
          ]"
          @click="(key) => form.size = key"
        />
        <h5 v-else-if="tiaSample.size">{{ tiaSample.size.replace('-', ' ') }}</h5>
        <h5 v-else>-</h5>

        <template v-for="sample in samples" :key="sample.name">
          <div v-if="sample.name">
            <h6 :class="{'bold' : sample.bold }">{{ sample.title }}</h6>
            <template
              v-for="(value, index) in tiaSample[sample.name]"
              :key="`${index}-${sample.name}-error`"
            >
              <div v-show="form.errors[`${sample.name}.${index}`]" class="has-error">
                <span class="help-block text-left">
                  {{ form.errors[`${sample.name}.${index}`] }}
                </span>
              </div>
            </template>
            <ul v-if="isForm" class="multiple-inputs">
              <li v-for="(value, index) in tiaSample[sample.name]" :key="`${index}-${sample.name}`"
                  :class="sample.name">
                <div class="form-group">
                  <input
                    type="text"
                    class="form-control"
                    :disabled="index === 4"
                    v-model="form[sample.name][index]"
                    @keyup="changeSampleValue(sample.name, 4)"
                  >
                </div>
              </li>
              <li>{{ sample.allow }}</li>
            </ul>
            <h5 v-else>
              {{ displaySampleValue(sample.name) }}
            </h5>
          </div>
          <template v-else>
            <h4><strong>{{ sample.title }}</strong></h4>
          </template>
        </template>
      </div>

      <h4 v-if="!isNew">Images</h4>
      <div v-if="!isNew" class="user-boxes notes-list">
        <Images
          :images="tiaSample.images"
          :upload-route="route('tia-samples.upload', tiaSample.id || 0)"
          :delete-route="route('tia-samples.delete', tiaSample.id || 0)"
          @updateRecord="emit('update')"
        />
      </div>
    </div>
  </div>
</template>
