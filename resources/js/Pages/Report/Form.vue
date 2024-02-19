<script setup>
import { computed } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import Multiselect from '@vueform/multiselect';
import { useToast } from "vue-toastification";
import TextInput from "@/Components/TextInput.vue";
import { getDropDownOptions, getCategoriesDropDownByType } from "@/helper.js";

const page = usePage();
const toast = useToast();

const props = defineProps({
  report: {
    type: Object,
    default: {},
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
  isNew: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update', 'create']);

const form = useForm({
  name: page.props.name,
  type: page.props.type,
  filters: {
    start: props.report.filters?.start,
    end: props.report.filters?.end,
    grower_ids: props.report.filters?.grower_ids,
    grower_groups: props.report.filters?.grower_groups,
    paddocks: props.report.filters?.paddocks,
    seed_varieties: props.report.filters?.seed_varieties,
    seed_generations: props.report.filters?.seed_generations,
    seed_classes: props.report.filters?.seed_classes,
    cool_stores: props.report.filters?.cool_stores,
    fungicides: props.report.filters?.fungicides,
    transports: props.report.filters?.transports,
  }
});

const isForm = computed(() => props.isEdit || props.isNew);

const updateRecord = () => {
  form.patch(route('reports.update', props.report.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('update');
      toast.success('The report has been updated successfully!');
    },
  });
}

const storeRecord = () => {
  form.post(route('reports.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
      toast.success('The report has been created successfully!');
    },
  });
}

defineExpose({
  updateRecord,
  storeRecord
});
</script>

<template>
  <div class="row">
    <div class="col-12">
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Name</th>
            <td><TextInput v-model="form.name" :error="form.errors.name" type="text" /></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="col-12 col-sm-6">
      <h4>Grower Filter</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Growers</th>
            <td>
              <Multiselect
                v-model="form.filters.grower_ids"
                mode="tags"
                placeholder="Choose a growers"
                :searchable="true"
                :class="{'is-invalid' : form.errors['filters.grower_ids']}"
                :options="getDropDownOptions(page.props.growers, true)"
              />
              <div v-if="form.errors['filters.grower_ids']" class="invalid-feedback">
                {{ form.errors['filters.grower_ids'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Grower Groups</th>
            <td>
              <Multiselect
                v-model="form.filters.grower_groups"
                mode="tags"
                placeholder="Choose a grower group"
                :searchable="true"
                :class="{'is-invalid' : form.errors['filters.grower_groups']}"
                :options="getCategoriesDropDownByType(page.props.categories, 'grower-group')"
              />
              <div v-if="form.errors['filters.grower_groups']" class="invalid-feedback">
                {{ form.errors['filters.grower_groups'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Paddocks</th>
            <td>
              <Multiselect
                v-model="form.filters.paddocks"
                mode="tags"
                placeholder="Choose a paddocks"
                :searchable="true"
                :class="{'is-invalid' : form.errors['filters.paddocks']}"
                :options="(page.props.growers || []).map(grower => grower.paddocks).flat().map(item => {
                    return { 'value': item.name, 'label': item.name };
                  })"
              />
              <div v-if="form.errors['filters.paddocks']" class="invalid-feedback">
                {{ form.errors['filters.paddocks'] }}
              </div>
            </td>
          </tr>
        </table>
      </div>

      <h4>Range Filter</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Start</th>
            <td>
              <TextInput
                type="date"
                v-model="form.filters.start"
                :error="form.errors['filters.start']"
              />
            </td>
          </tr>
          <tr>
            <th>End</th>
            <td>
              <TextInput
                type="date"
                v-model="form.filters.end"
                :error="form.errors['filters.end']"
              />
            </td>
          </tr>
        </table>
      </div>
    </div>
    <div class="col-12 col-sm-6">
      <h4>Seed Filter</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr>
            <th>Seed Varieties</th>
            <td>
              <Multiselect
                v-model="form.filters.seed_varieties"
                mode="tags"
                placeholder="Choose a seed varieties"
                :searchable="true"
                :class="{'is-invalid' : form.errors['filters.seed_varieties']}"
                :options="getCategoriesDropDownByType(page.props.categories, 'seed-variety')"
              />
              <div v-if="form.errors['filters.seed_varieties']" class="invalid-feedback">
                {{ form.errors['filters.seed_varieties'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Seed Generations</th>
            <td>
              <Multiselect
                v-model="form.filters.seed_generations"
                mode="tags"
                placeholder="Choose a seed generations"
                :searchable="true"
                :class="{'is-invalid' : form.errors['filters.seed_generations']}"
                :options="getCategoriesDropDownByType(page.props.categories, 'seed-generation')"
              />
              <div v-if="form.errors['filters.seed_generations']" class="invalid-feedback">
                {{ form.errors['filters.seed_generations'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Seed Classes</th>
            <td>
              <Multiselect
                v-model="form.filters.seed_classes"
                mode="tags"
                placeholder="Choose a seed classes"
                :searchable="true"
                :class="{'is-invalid' : form.errors['filters.seed_classes']}"
                :options="getCategoriesDropDownByType(page.props.categories, 'seed-class')"
              />
              <div v-if="form.errors['filters.seed_classes']" class="invalid-feedback">
                {{ form.errors['filters.seed_classes'] }}
              </div>
            </td>
          </tr>
        </table>
      </div>

      <h4>Unload Filter</h4>
      <div class="user-boxes">
        <table class="table input-table mb-0">
          <tr class="d-none">
            <th>Cool Stores</th>
            <td>
              <Multiselect
                v-model="form.filters.cool_stores"
                mode="tags"
                placeholder="Choose a cool stores"
                :searchable="true"
                :class="{'is-invalid' : form.errors['filters.cool_stores']}"
                :options="getCategoriesDropDownByType(page.props.categories, 'cool-store')"
              />
              <div v-if="form.errors['filters.cool_stores']" class="invalid-feedback">
                {{ form.errors['filters.cool_stores'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Fungicides</th>
            <td>
              <Multiselect
                v-model="form.filters.fungicides"
                mode="tags"
                placeholder="Choose a fungicides"
                :searchable="true"
                :class="{'is-invalid' : form.errors['filters.fungicides']}"
                :options="getCategoriesDropDownByType(page.props.categories, 'fungicide')"
              />
              <div v-if="form.errors['filters.fungicides']" class="invalid-feedback">
                {{ form.errors['filters.fungicides'] }}
              </div>
            </td>
          </tr>
          <tr>
            <th>Transports</th>
            <td>
              <Multiselect
                v-model="form.filters.transports"
                mode="tags"
                placeholder="Choose a transports"
                :searchable="true"
                :class="{'is-invalid' : form.errors['filters.transports']}"
                :options="getCategoriesDropDownByType(page.props.categories, 'transport')"
              />
              <div v-if="form.errors['filters.transports']" class="invalid-feedback">
                {{ form.errors['filters.transports'] }}
              </div>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>
