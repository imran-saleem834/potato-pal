<script setup>
import { computed, ref, watch } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import { getCategoriesByType, getCategoryIdsByType } from "@/helper.js";
import Multiselect from '@vueform/multiselect'
import TextInput from "@/Components/TextInput.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';

DataTable.use(DataTablesCore);

const props = defineProps({
  uniqueKey: String,
  allocation: {
    type: Object,
    default: {}
  },
  isNew: {
    type: Boolean,
    default: false,
  },
  isNewItem: {
    type: Boolean,
    default: false,
  },
  growers: Object,
  buyers: Object,
});

const emit = defineEmits(['create', 'delete']);

const isEdit = ref(false);

const form = useForm({
  buyer_id: props.allocation.buyer_id,
  grower_id: props.allocation.grower_id,
  unique_key: props.allocation.unique_key,
  no_of_bins: props.allocation.no_of_bins,
  weight: props.allocation.weight,
  bin_size: props.allocation.bin_size,
  paddock: props.allocation.paddock,
  comment: props.allocation.comment,
  grower_group: getCategoryIdsByType(props.allocation.categories, 'grower-group'),
  seed_type: getCategoryIdsByType(props.allocation.categories, 'seed-type'),
  seed_variety: getCategoryIdsByType(props.allocation.categories, 'seed-variety'),
  seed_generation: getCategoryIdsByType(props.allocation.categories, 'seed-generation'),
  seed_class: getCategoryIdsByType(props.allocation.categories, 'seed-class'),
  select_receival: {},
});

watch(() => props.allocation,
  (allocation) => {
    if (props.isNewItem || isEdit.value) {
      return;
    }
    form.clearErrors();
    form.buyer_id = allocation.buyer_id
    form.grower_id = allocation.grower_id
    form.unique_key = allocation.unique_key
    form.no_of_bins = allocation.no_of_bins
    form.weight = allocation.weight
    form.bin_size = allocation.bin_size
    form.paddock = allocation.paddock
    form.comment = allocation.comment
    form.grower_group = getCategoryIdsByType(allocation.categories, 'grower-group')
    form.seed_type = getCategoryIdsByType(allocation.categories, 'seed-type')
    form.seed_variety = getCategoryIdsByType(allocation.categories, 'seed-variety')
    form.seed_generation = getCategoryIdsByType(allocation.categories, 'seed-generation')
    form.seed_class = getCategoryIdsByType(allocation.categories, 'seed-class')

    if (allocation.unique_key) {
      selectReceivalOnEdit(allocation);
    }
  }
);

const selectReceivalOnEdit = (allocation) => {
  form.select_receival = props.growers
    ?.find(grower => grower.value === allocation.grower_id)
    ?.receivals
    ?.find(receival => receival.unique_key === allocation.unique_key) || {};
}

selectReceivalOnEdit(props.allocation);

const onSelectReceival = (receival) => {
  form.select_receival = receival
  form.unique_key = receival.unique_key
  form.no_of_bins = null
  form.weight = null
  form.bin_size = receival.bin_size
  form.paddock = receival.paddock
  form.grower_group = getCategoryIdsByType(receival.categories, 'grower-group')
  form.seed_type = getCategoryIdsByType(receival.categories, 'seed-type')
  form.seed_variety = getCategoryIdsByType(receival.categories, 'seed-variety')
  form.seed_generation = getCategoryIdsByType(receival.categories, 'seed-generation')
  form.seed_class = getCategoryIdsByType(receival.categories, 'seed-class')
}

const onChangeGrower = () => {
  form.select_receival = {};
  form.unique_key = null;
  form.no_of_bins = null
  form.weight = null
}

const isForm = computed(() => {
  return isEdit.value || props.isNew || props.isNewItem;
})

const setIsEdit = () => {
  isEdit.value = true
}

const updateRecord = () => {
  form.patch(route('allocations.update', props.allocation.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isEdit.value = false;
    },
  });
}

const storeRecord = () => {
  form.post(route('allocations.store'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('create');
    },
  });
}

const deleteAllocation = () => {
  const form = useForm({});
  form.delete(route('allocations.destroy', props.allocation.id), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      emit('delete');
    },
  });
}
</script>

<template>
  <div class="row">
    <div v-if="isNew" class="col-md-12">
      <div class="flex-end create-update-btn">
        <a role="button" @click="storeRecord" class="btn btn-red">Create</a>
      </div>
    </div>
    <div class="col-md-12">
      <div v-if="isNew" class="user-boxes">
        <h6>Buyer Name</h6>
        <Multiselect
          v-model="form.buyer_id"
          mode="single"
          placeholder="Choose a buyer"
          :searchable="true"
          :options="buyers"
        />
        <div v-if="form.errors.buyer_id" class="has-error">
          <span class="help-block text-left">{{ form.errors.buyer_id }}</span>
        </div>
      </div>

      <h4 v-if="isNew">Allocations Details</h4>
      <div class="user-boxes allocation-boxes">
        <template v-if="isForm">
          <div class="row">
            <div class="col-sm-10">
              <h6>Grower Name</h6>
              <Multiselect
                v-model="form.grower_id"
                @change="onChangeGrower"
                mode="single"
                placeholder="Choose a grower"
                :searchable="true"
                :options="growers"
              />
              <div v-if="form.errors.grower_id" class="has-error">
                <span class="help-block text-left">{{ form.errors.grower_id }}</span>
              </div>
              <div v-if="form.errors.unique_key" class="has-error">
                <span class="help-block text-left">{{ form.errors.unique_key }}</span>
              </div>
            </div>
            <div class="col-sm-2">
              <h6>&nbsp;</h6>
              <button
                class="btn-red btn-select-receival"
                data-toggle="modal"
                :data-target="`#receivals-${uniqueKey}`"
              >Select Receival
              </button>
            </div>
          </div>

          <div v-if="Object.values(form.select_receival).length" class="user-table" style="margin: 10px 0;">
            <table class="table">
              <thead>
              <tr>
                <th>Seed Type</th>
                <th>Bin Size</th>
                <th>No of Bins Available</th>
                <th>Weight in Tonnes</th>
              </tr>
              </thead>
              <tbody>
              <tr>
                <td>{{ form.select_receival.seed_type }}</td>
                <td>{{ form.select_receival.bin_size }} Tonnes</td>
                <td>{{ form.select_receival.no_of_bins }}</td>
                <td>{{ form.select_receival.weight }} Tonnes</td>
              </tr>
              </tbody>
            </table>
          </div>

          <div class="row">
            <div class="col-sm-3">
              <h6>Allocated No of Bins</h6>
              <TextInput v-model="form.no_of_bins" :error="form.errors.no_of_bins" type="text"/>
            </div>
            <div class="col-sm-3">
              <h6>Allocated Tonnes</h6>
              <TextInput v-model="form.weight" :error="form.errors.weight" type="text"/>
            </div>
            <div class="col-sm-6">
              <h6>Comment</h6>
              <TextInput v-model="form.comment" :error="form.errors.comment" type="text"/>
            </div>
          </div>
          <div v-if="isEdit || isNewItem" class="row">
            <div class="col-sm-12 text-right">
              <a v-if="isEdit" role="button" @click="updateRecord" class="btn btn-red">Update</a>
              <a v-if="isNewItem" role="button" @click="storeRecord" class="btn btn-red">Create</a>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="row">
            <div class="col-sm-2">
              <h5>
                <strong>Grower Name: </strong>
                <Link :href="route('users.index', {userId: allocation.grower_id})">
                  {{ allocation.grower?.name }} {{ allocation.grower?.grower_name ? ' (' + allocation.grower?.grower_name + ')' : '' }}
                </Link>
              </h5>
            </div>
            <div class="col-sm-2">
              <template v-for="category in getCategoriesByType(allocation.categories, 'seed-type')" :key="category.id">
                <h5>Seed Type: {{ category.category.name }}</h5>
              </template>
            </div>
            <div class="col-sm-2">
              <h5>Size of Bin: {{ allocation.bin_size }} Tonnes</h5>
            </div>
            <div class="col-sm-2">
              <h5>Allocated No of Bins: {{ allocation.no_of_bins }}</h5>
            </div>
            <div class="col-sm-2">
              <h5>Allocated: {{ allocation.weight.toFixed(2) }} Tonnes</h5>
            </div>
            <div class="col-sm-2 text-right">
              <a role="button" @click="setIsEdit" class="btn btn-red">Edit</a>
              <a role="button" @click="deleteAllocation" class="btn btn-red">Delete</a>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-2">
              <template
                v-for="category in getCategoriesByType(allocation.categories, 'grower-group')"
                :key="category.id"
              >
                <h5>Receival Group: {{ category.category.name }}</h5>
              </template>
            </div>
            <div class="col-sm-2">
              <template
                v-for="category in getCategoriesByType(allocation.categories, 'seed-variety')"
                :key="category.id"
              >
                <h5>Seed Variety: {{ category.category.name }}</h5>
              </template>
            </div>
            <div class="col-sm-2">
              <template
                v-for="category in getCategoriesByType(allocation.categories, 'seed-generation')"
                :key="category.id"
              >
                <h5>Seed Generation: {{ category.category.name }}</h5>
              </template>
            </div>
            <div class="col-sm-2">
              <template
                v-for="category in getCategoriesByType(allocation.categories, 'seed-class')"
                :key="category.id"
              >
                <h5>Seed Class: {{ category.category.name }}</h5>
              </template>
            </div>
            <div class="col-sm-2">
              <h5>Paddock: {{ allocation.paddock }}</h5>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>

  <div class="modal fade" :id="`receivals-${uniqueKey}`" tabindex="-1" role="dialog" aria-labelledby="myModalLabel4">
    <div class="modal-dialog modal-lg" role="document" style="width: 90%;">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">Select Receival</h4>
        </div>
        <div class="modal-body">
          <template v-for="grower in growers" :key="grower.value">
            <div v-if="form.grower_id === grower.value && isForm" style="margin: 20px 0;">
              <DataTable class="table">
                <thead>
                <tr>
                  <th>Seed Type</th>
                  <th>Seed Variety</th>
                  <th>Seed Class</th>
                  <th>Seed Generation</th>
                  <th>Grower Group</th>
                  <th>Paddock</th>
                  <th>Bin Size</th>
                  <th>No Of Bins</th>
                  <th>Weight</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="receival in grower?.receivals" :key="receival.id">
                  <tr
                    v-if="receival.no_of_bins > 0 || receival.weight > 0"
                    @click="() => onSelectReceival(receival)"
                    style="cursor: pointer"
                    data-dismiss="modal"
                    aria-label="Close"
                  >
                    <td>{{ receival.seed_type }}</td>
                    <td>{{ receival.seed_variety }}</td>
                    <td>{{ receival.seed_class }}</td>
                    <td>{{ receival.seed_generation }}</td>
                    <td>{{ receival.grower_group }}</td>
                    <td>{{ receival.paddock }}</td>
                    <td>{{ receival.bin_size }} Tonnes</td>
                    <td>{{ receival.no_of_bins }}</td>
                    <td>{{ receival.weight }} Tonnes</td>
                  </tr>
                </template>
                </tbody>
              </DataTable>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@import 'datatables.net-dt';
</style>
