<script setup>
import { Link, } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import TopBar from '@/Components/TopBar.vue';
import MiddleBar from '@/Components/MiddleBar.vue';
import Details from '@/Pages/AdminOption/Details.vue';

const categories = ref(null);
const user = ref(null);
const activeTab = ref(null);
const isNewRecord = ref(false);

const optionTypes = [
    { slug: 'seed-class', label: 'Seed Class' },
    { slug: 'delivery-type', label: 'Delivery Type' },
    { slug: 'fungicide', label: 'Fungicide' },
    { slug: 'seed-generation', label: 'Seed Generation' },
    { slug: 'buyer', label: 'Buyer Group Type' },
    { slug: 'grower', label: 'Grower Group Type' },
    { slug: 'seed-type', label: 'Seed Type' },
    { slug: 'seed-variety', label: 'Seed Variety' },
    { slug: 'transport', label: 'Transport Co.' },
];

const setActiveTab = (id) => {
    activeTab.value = id;
    isNewRecord.value = false;
};

const setNewRecord = () => {
    isNewRecord.value = true;
    user.value = {};
}

const getCategories = (type, keyword) => {
    axios.get(route('categories.index'), { params: { type: [type], keyword: keyword } }).then(response => {
        categories.value = response.data;

        setActiveTab(type);
    });
}

const title = computed(() => {
    return optionTypes.find(option => option.slug === activeTab.value)?.label;
})

getCategories('seed-class');
</script>

<template>
    <AppLayout title="Admin Options">
        <TopBar
            v-if="activeTab"
            :type="title"
            @search="(keyword) => getCategories(activeTab, keyword)"
            @newRecord="setNewRecord"
        />
        <MiddleBar
            v-if="activeTab"
            type="Admin Options"
            :title="title"
            :is-new-record-selected="isNewRecord"
            :access="{
                new: true,
                edit: false,
                delete: false,
            }"
            @newRecord="setNewRecord"
            @editRecord="() => {}"
            @deleteRecord="() => {}"
        />

        <!-- tab-section -->
        <div class="tab-section">
            <div class="row row0">
                <div class="col-lg-3 col-sm-6">
                    <ul class="nav nav-tabs tabs-left sideways">
                        <li
                            v-for="optionType in optionTypes"
                            :key="optionType.slug"
                            :class="{'active' : activeTab === optionType.slug}"
                        >
                            <a
                                role="button"
                                :data-toggle="$windowWidth <= 767 ? 'modal' : 'tab'"
                                :data-target="$windowWidth <= 767 ? '#user-details' : ''"
                                @click="getCategories(optionType.slug)"
                            >
                                <div class="user-table">
                                    <table class="table">
                                        <tbody>
                                        <tr>
                                            <th>{{ optionType.label }}</th>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <span class="fa fa-angle-right angle-right"></span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-8 col-sm-6">
                    <div class="tab-content">
                        <div class="tab-pane active">
                            <div class="row">
                                <div v-if="isNewRecord" class="col-sm-6 col-md-4">
                                    <Details
                                        :category="{}"
                                        :type="activeTab"
                                        :is-new="true"
                                        @updateRecord="() => getCategories(activeTab)"
                                    />
                                </div>
                                <div v-for="category in categories" :key="category.id" class="col-sm-6 col-md-4">
                                    <Details
                                        :category="category"
                                        :type="activeTab"
                                        :is-new="false"
                                        @updateRecord="() => getCategories(activeTab)"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <!-- tab-section -->

        <!-- Modal -->
        <div class="modal right fade user-details" id="user-details" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel3">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span class="fa fa-arrow-left"></span>
                        </button>
                        <h4 class="modal-title" id="myModalLabel3">{{ title }}</h4>
                    </div>
                    <div class="modal-body">
                        <ol class="breadcrumb">
                            <li>
                                <Link :href="route('dashboard')" data-dismiss="modal" aria-label="Close">Menu</Link>
                            </li>
                            <li>
                                <Link href="" data-dismiss="modal" aria-label="Close">Admin Options</Link>
                            </li>
                            <li class="active">{{ title }}</li>
                        </ol>
                        <div v-if="isNewRecord">
                            <Details
                                :category="{}"
                                :type="activeTab"
                                :is-new="true"
                                @updateRecord="() => getCategories(activeTab)"
                            />
                        </div>
                        <div v-for="category in categories" :key="category.id">
                            <Details
                                :category="category"
                                :type="activeTab"
                                :is-new="false"
                                @updateRecord="() => getCategories(activeTab)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
