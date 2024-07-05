<script setup>
import moment from 'moment';
import { computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { booleanArray } from '@/const.js';
import { useToast } from 'vue-toastification';
import { toCamelCase } from '@/helper.js';
import TextInput from '@/Components/TextInput.vue';
import UlLiButton from '@/Components/UlLiButton.vue';

const toast = useToast();

const props = defineProps({
  grade: Object,
  colSize: String,
  routeName: String,
  isEdit: Boolean,
  isNew: Boolean,
  unloads: Array,
});

const emit = defineEmits(['update', 'create', 'unset']);

const tonnes = {
  half_tonne: 0,
  one_tonne: 0,
  two_tonne: 0,
};

const form = useForm({
  unload_id: props.grade.unload_id,
  bins_tipped: props.grade.bins_tipped || { ...tonnes },
  whole_seed: props.grade.whole_seed || { ...tonnes },
  oversize: props.grade.oversize || { ...tonnes },
  round: props.grade.round || { ...tonnes },
  cut_sets: props.grade.cut_sets || { ...tonnes },
  waste: props.grade.waste,
  no_of_bulk_bags_out: props.grade.no_of_bulk_bags_out,
  net_weight_bags_out: props.grade.net_weight_bags_out,
  fungicide: props.grade.fungicide,
  fungicide_used: props.grade.fungicide_used,
  start: props.grade.start,
  end: props.grade.end,
  no_of_crew: props.grade.no_of_crew,
  comments: props.grade.comments,
});

watch(
  () => props.grade,
  (grade) => {
    form.clearErrors();
    form.unload_id = grade.unload_id;
    form.bins_tipped = grade.bins_tipped || { ...tonnes };
    form.whole_seed = grade.whole_seed || { ...tonnes };
    form.oversize = grade.oversize || { ...tonnes };
    form.round = grade.round || { ...tonnes };
    form.cut_sets = grade.cut_sets || { ...tonnes };
    form.waste = grade.waste;
    form.no_of_bulk_bags_out = grade.no_of_bulk_bags_out;
    form.net_weight_bags_out = grade.net_weight_bags_out;
    form.fungicide = grade.fungicide;
    form.fungicide_used = grade.fungicide_used;
    form.start = grade.start;
    form.end = grade.end;
    form.no_of_crew = grade.no_of_crew;
    form.comments = grade.comments;
  },
);

const isForm = computed(() => props.isEdit || props.isNew);

const updateRecord = () => {
  form.patch(route(`${props.routeName}.update`, props.grade.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The grade has been updated successfully!');
    },
  });
};

const storeRecord = () => {
  form.post(route(`${props.routeName}.store`), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The grade has been created successfully!');
    },
  });
};

defineExpose({
  updateRecord,
  storeRecord,
});
</script>

<template>
  <div class="row">
    <div :class="colSize">
      <h4>Grower Information</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Unload ID</th>
            <td>
              <Multiselect
                v-if="isForm"
                v-model="form.unload_id"
                mode="single"
                placeholder="Choose a receival unload"
                :searchable="true"
                :class="{ 'is-invalid': form.errors.unload_id }"
                :options="unloads"
              />
              <Link
                v-else-if="grade.unload"
                class="p-0"
                :href="route('unloading.index', { receivalId: grade.unload.receival_id })"
              >
                {{ grade.unload.id }}
              </Link>
              <template v-else>-</template>
              <div v-if="form.errors.unload_id" class="invalid-feedback">
                {{ form.errors.unload_id }}
              </div>
            </td>
          </tr>
          <tr v-if="!isForm">
            <th>Receival ID</th>
            <td>
              <Link
                v-if="grade.unload.receival"
                class="p-0"
                :href="route('receivals.index', { receivalId: grade.unload.receival_id })"
              >
                {{ grade.unload.receival_id }}
              </Link>
              <template v-else>-</template>
            </td>
          </tr>
          <tr v-if="!isForm">
            <th>Grower name</th>
            <td>
              <Link
                v-if="grade.unload.receival.grower"
                class="p-0"
                :href="route('users.index', { userId: grade.unload.receival.grower_id })"
              >
                {{ grade.unload.receival.grower.grower_name }}
              </Link>
              <template v-else>-</template>
            </td>
          </tr>
          <tr v-if="!isForm">
            <th>Time added</th>
            <td>{{ moment(grade.created_at).format('DD/MM/YYYY hh:mm A') }}</td>
          </tr>
        </table>
      </div>

      <h4>Other Information</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>No of bulk bags out</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.no_of_bulk_bags_out"
                :error="form.errors.no_of_bulk_bags_out"
                type="text"
              />
              <template v-else-if="grade.no_of_bulk_bags_out">
                {{ grade.no_of_bulk_bags_out }}
              </template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Net weight of bulk bags</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.net_weight_bags_out"
                :error="form.errors.net_weight_bags_out"
                type="text"
              />
              <template v-else-if="grade.net_weight_bags_out">
                {{ grade.net_weight_bags_out }}
              </template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Fungicide</th>
            <td class="pb-0">
              <UlLiButton
                :is-form="isForm"
                :value="form.fungicide"
                :error="form.errors.fungicide"
                :items="booleanArray"
                @click="(value) => (form.fungicide = value)"
              />
            </td>
          </tr>
          <tr v-if="form.fungicide">
            <th>Fungicide used</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.fungicide_used"
                :error="form.errors.fungicide_used"
                type="text"
              >
                <template #addon>
                  <div class="input-group-text">litres</div>
                </template>
              </TextInput>
              <template v-else-if="grade.fungicide_used">
                {{ grade.fungicide_used }} litres
              </template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Start time</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.start"
                :error="form.errors.start"
                type="time"
              />
              <template v-else-if="grade.start">{{ grade.start }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>End time</th>
            <td>
              <TextInput v-if="isForm" v-model="form.end" :error="form.errors.end" type="time" />
              <template v-else-if="grade.end">{{ grade.end }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>No of crew</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.no_of_crew"
                :error="form.errors.no_of_crew"
                type="text"
              />
              <template v-else-if="grade.no_of_crew">{{ grade.no_of_crew }}</template>
              <template v-else>-</template>
            </td>
          </tr>
          <tr>
            <th>Comments</th>
            <td>
              <TextInput
                v-if="isForm"
                v-model="form.comments"
                :error="form.errors.comments"
                type="text"
              />
              <template v-else-if="grade.comments">{{ grade.comments }}</template>
              <template v-else>-</template>
            </td>
          </tr>
        </table>
      </div>
    </div>
    <div :class="colSize">
      <h4>Bins Graded</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr
            v-for="type in ['bins_tipped', 'whole_seed', 'oversize', 'round', 'cut_sets']"
            :key="type"
          >
            <th>{{ toCamelCase(type.replace('_', ' ')) }}</th>
            <td>
              <div class="row p-0 g-1">
                <div class="col">
                  <label class="mb-1">Half Tonne</label>
                  <TextInput
                    v-model="form[type].half_tonne"
                    :error="form.errors[`${type}.half_tonne`]"
                    :disabled="!isForm"
                    type="text"
                  />
                </div>
                <div class="col">
                  <label class="mb-1">One Tonne</label>
                  <TextInput
                    v-model="form[type].one_tonne"
                    :error="form.errors[`${type}.one_tonne`]"
                    :disabled="!isForm"
                    type="text"
                  />
                </div>
                <div class="col">
                  <label class="mb-1">Two Tonnes</label>
                  <TextInput
                    v-model="form[type].two_tonne"
                    :error="form.errors[`${type}.two_tonne`]"
                    :disabled="!isForm"
                    type="text"
                  />
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <th>Waste</th>
            <td>
              <TextInput
                v-model="form.waste"
                :error="form.errors.waste"
                :disabled="!isForm"
                type="text"
              >
                <template #addon>
                  <div
                    class="input-group-text d-none d-md-inline-block d-lg-none d-xl-inline-block"
                  >
                    tonnes
                  </div>
                </template>
              </TextInput>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>
