require('babel-polyfill');

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
window.Vue = require('vue');

// import BootstrapVue from 'bootstrap-vue'
// Vue.use(BootstrapVue);

// import 'bootstrap/dist/css/bootstrap.css'
// import 'bootstrap-vue/dist/bootstrap-vue.css'

// import bTooltip from 'bootstrap-vue/es/components/tooltip/tooltip';
// import bTooltipDirective from 'bootstrap-vue/es/directives/tooltip/tooltip';
// Vue.use(bTooltip);
// Vue.use(bTooltipDirective);

import { Tooltip } from 'bootstrap-vue/es/directives';
Vue.use(Tooltip);

// import 'bootstrap/dist/css/bootstrap.css'
// import 'bootstrap-vue/dist/bootstrap-vue.css'

// import bNavbar from 'bootstrap-vue/es/components/navbar/navbar';
// Vue.component('b-navbar', bNavbar);

// import bNavbarToogle from 'bootstrap-vue/es/components/navbar/navbar-toggle';
// Vue.component('b-navbar-toggle', bNavbarToogle);

// import bNavbarNav from 'bootstrap-vue/es/components/navbar/navbar-nav';
// Vue.component('b-navbar-nav', bNavbarToogle);

// import bCollapse from 'bootstrap-vue/es/components/collapse/collapse';
// Vue.component('b-collapse', bCollapse);

// import bNavItemDropdown from 'bootstrap-vue/es/components/nav/nav-item-dropdown';
// Vue.component('b-nav-item-dropdown', bNavItemDropdown);

// import bDropdownItem from 'bootstrap-vue/es/components/dropdown/dropdown-item';
// Vue.component('b-dropdown-item', bDropdownItem);

// import bCard from 'bootstrap-vue/es/components/card/card';
// Vue.component('b-card', bCard);

// import bTabs from 'bootstrap-vue/es/components/tabs/tabs';
// import bTab from 'bootstrap-vue/es/components/tabs/tab';
// Vue.component('b-tabs', bTabs);
// Vue.component('b-tab', bTab);


import ToggleButton from 'vue-js-toggle-button';
Vue.use(ToggleButton);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('alert', require('./components/Alert.vue'));
Vue.component('checkout-checkin', require('./components/CheckoutCheckin.vue'));
Vue.component('checkout-checkin-status', require('./components/CheckoutCheckinStatus.vue'));
Vue.component('datatable', require('./components/Datatable.vue'));
Vue.component('loan-actions', require('./components/LoanActions.vue'));
Vue.component('loans-table', require('./components/LoansTable.vue'));
Vue.component('things-table', require('./components/ThingsTable.vue'));

const app = new Vue({
    el: '#app'
});
