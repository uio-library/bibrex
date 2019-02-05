// Setup a few things first

import './bootstrap';

// ... including Vue

import Vue from 'vue'
import VueShortkey from 'vue-shortkey'
import ToggleButton from 'vue-js-toggle-button';
import { Tooltip } from 'bootstrap-vue/es/directives';

Vue.use(Tooltip);
Vue.use(VueShortkey);
Vue.use(ToggleButton);

window.Vue = Vue;

// Register our Vue components

Vue.component('public-status', require('./components/PublicStatus.vue'));

Vue.component('alert', require('./components/Alert.vue'));
Vue.component('checkout-checkin', require('./components/CheckoutCheckin.vue'));
Vue.component('checkout-checkin-status', require('./components/CheckoutCheckinStatus.vue'));
Vue.component('loan-actions', require('./components/LoanActions.vue'));
Vue.component('thing-editor', require('./components/ThingEditor.vue'));
Vue.component('thing-settings-editor', require('./components/ThingSettingsEditor.vue'));

Vue.component('datatable', require('./components/Datatable.vue'));
Vue.component('loans-table', require('./components/LoansTable.vue'));
Vue.component('things-table', require('./components/ThingsTable.vue'));
Vue.component('items-table', require('./components/ItemsTable.vue'));
Vue.component('users-table', require('./components/UsersTable.vue'));

const app = new Vue({
    el: '#app'
});
