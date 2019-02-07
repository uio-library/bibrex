import './bootstrap';
import Vue from 'vue'
import VueShortkey from 'vue-shortkey'
import ToggleButton from 'vue-js-toggle-button';
import { Tooltip } from 'bootstrap-vue/es/directives';
import VueI18n from 'vue-i18n';
import AlertComponent from './components/Alert.vue';
import CheckoutCheckinComponent from './components/CheckoutCheckin.vue';
import CheckoutCheckinStatusComponent from './components/CheckoutCheckinStatus.vue';
import LoanActionsComponent from './components/LoanActions.vue';
import ThingEditorComponent from './components/ThingEditor.vue';
import ThingSettingsEditorComponent from './components/ThingSettingsEditor.vue';
import DatatableComponent from './components/Datatable.vue';
import LoansTableComponent from './components/LoansTable.vue';
import ThingsTableComponent from './components/ThingsTable.vue';
import ItemsTableComponent from './components/ItemsTable.vue';
import UsersTableComponent from './components/UsersTable.vue';
import PublicStatusComponent from './components/PublicStatus.vue';

Vue.use(VueI18n);
Vue.use(Tooltip);
Vue.use(VueShortkey);
Vue.use(ToggleButton);

Vue.component('alert', AlertComponent);
Vue.component('checkout-checkin', CheckoutCheckinComponent);
Vue.component('checkout-checkin-status', CheckoutCheckinStatusComponent);
Vue.component('loan-actions', LoanActionsComponent);
Vue.component('thing-editor', ThingEditorComponent);
Vue.component('thing-settings-editor', ThingSettingsEditorComponent);
Vue.component('datatable', DatatableComponent);
Vue.component('loans-table', LoansTableComponent);
Vue.component('things-table', ThingsTableComponent);
Vue.component('items-table', ItemsTableComponent);
Vue.component('users-table', UsersTableComponent);
Vue.component('public-status', PublicStatusComponent);

const app = new Vue({
    el: '#app'
});

window.Vue = Vue;
