<template>
    <div>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" :class="{active: activeTab == 'checkout'}" id="nav-checkout-tab" href="#nav-checkout" role="tab" @click="onTabClick('checkout')" v-shortkey.once="checkoutShortkey" @shortkey="onTabClick('checkout')" v-b-tooltip.hover :title="'Snarvei: ' + checkoutShortkey.join('+')">Utlån</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{active: activeTab == 'checkin'}" id="nav-checkin-tab" href="#nav-checkin" role="tab" @click="onTabClick('checkin')" v-shortkey.once="checkinShortkey" @shortkey="onTabClick('checkin')" v-b-tooltip.hover :title="'Snarvei: ' + checkinShortkey.join('+')">Retur</a>
            </li>
        </ul>
        <div class="tab-content p-3 mb-3">

            <div class="tab-pane fade show" :class="{active: activeTab == 'checkout'}" id="nav-checkout" role="tabpanel" aria-labelledby="nav-checkout-tab">
                <form method="post" class="form row px-2" @submit.prevent="checkout">
                    <input name="_token" type="hidden" :value="csrf_token">

                    <div class="col-sm-5 px-2">
                        <label for="user">Til hvem?</label>
                        <typeahead
                            :tabindex="1"
                            name="user"
                            :class="{'is-invalid': errors.user}"
                            @input="setCurrentUser($event)"
                            prefetch="/users"
                            remote="/users/search-alma"
                            placeholder="Til hvem?"
                            :min-length="4"
                            :alma="true"
                        ></typeahead>
                        <div class="form-text text-muted" v-show="!errors.user">
                            Navn eller låne-ID
                        </div>
                        <div class="invalid-feedback" v-show="errors.user">
                          {{ errors.user }}
                        </div>
                    </div>

                    <div class="col-sm-5 px-2">
                        <label for="thing">Hva?</label>
                        <typeahead
                            :tabindex="2"
                            name="thing"
                            :class="{'is-invalid': errors.thing}"
                            @input="setCurrentThing($event)"
                            prefetch="/things.json?withoutBarcode=1"
                            remote="/items/search"
                            :min-length="0"
                            :limit="30"
                        ></typeahead>
                        <small class="form-text text-muted" v-show="!errors.thing">
                            Scann eller velg ting
                        </small>
                        <div class="invalid-feedback" v-show="errors.thing">
                          {{ errors.thing }}
                        </div>
                    </div>

                    <div class="col px-2">
                        <label>&nbsp;</label>
                        <spin-button :busy="this.busy" class="checkout" tabindex="3">
                            <i class="far fa-paper-plane"></i>
                            Lån ut
                        </spin-button>
                    </div>
                </form>
            </div>

            <div class="tab-pane fade show" :class="{active: activeTab == 'checkin'}" id="nav-checkin" role="tabpanel" aria-labelledby="nav-checkin-tab">
                <form method="post" class="form row px-2" @submit.prevent="checkin">
                    <input name="_token" type="hidden" :value="csrf_token">

                    <div class="col-sm-8 px-2">
                        <label for="barcode">Strekkode:</label>
                        <input class="form-control" tabindex="1" type="text" name="barcode" v-model="currentBarcode" autocomplete="off">
                        <small class="form-text text-muted">
                            &nbsp;
                        </small>
                    </div>

                    <div class="col px-2">
                        <label>&nbsp;</label>
                        <spin-button :busy="this.busy" class="checkout" tabindex="2">
                            <i class="far fa-paper-plane"></i>
                            Returner
                        </spin-button>
                    </div>
                </form>
            </div>
        </div>

        <checkout-checkin-status></checkout-checkin-status>

        <loans-table :library="libraryId"></loans-table>

    </div>
</template>

<script>
import { get } from 'lodash/object';
import axios from 'axios';
import platform from 'platform';
import Typeahead from './Typeahead';
import SpinButton from './SpinButton';

export default {
    props: {
        libraryId: Number,
    },
    computed: {
        checkoutShortkey() {
            if (platform.os.family == 'OS X') {
                return ['ctrl', 'e'];
            } else {
                return ['alt', 'e'];
            }
        },
        checkinShortkey() {
            if (platform.os.family == 'OS X') {
                return ['ctrl', 'r'];
            } else {
                return ['alt', 'r'];
            }
        },
    },
    data: () => {
        return {
            activeTab: 'checkout',
            errors: {
                user: null,
                thing: null,
                barcode: null,
            },
            currentUser: {},
            currentThing: {},
            currentBarcode: '',
            busy: false,
            csrf_token: '',
        };
    },
    methods: {
        focusFirstTextInput() {
            let inp = document.querySelector('.active input[tabindex="1"]');
            inp.focus();
            return inp;
        },
        resetStatus() {
            this.$root.$emit('status', {});
            this.errors = {
                user: null,
                thing: null,
                barcode: null,
            };
        },
        setCurrentThing(value) {
            this.currentThing = value;
            Vue.set(this.errors, 'thing', null);
        },
        setCurrentUser(value) {
            this.currentUser = value;
            Vue.set(this.errors, 'user', null);
        },
        onTabClick(tab) {
            this.activeTab = tab;
            setTimeout(this.focusFirstTextInput.bind(this), 0);
        },
        getSuccessMsg(loan) {
            let msg = `Utlån av ${loan.item.thing.properties.name_indefinite.nob} til ${loan.user.name} registrert`;

            switch (Math.floor(Math.random() * 20)) {
                case 0:
                    msg += ' (og verden har forøvrig ikke gått under)';
                    break;
                case 1:
                    msg += ' (faktisk helt sant)';
                    break;
            }

            msg += `. Lånetid: ${loan.days_left} ${loan.days_left == 1 ? 'dag' : 'dager'}.`;
            return msg;
        },
        checkout() {

            if (!this.currentThing.name) {
                document.querySelector('.active input[tabindex="2"]').focus();
                return;
            }

            if (!this.currentUser.name) {
                document.querySelector('.active input[tabindex="1"]').focus();
                return;
            }

            this.busy = true;
            this.resetStatus();
            document.activeElement.blur();
            axios.post('/loans/checkout', {
                user: this.currentUser,
                thing: this.currentThing,
            })
            .then(response => {
                this.busy = false;
                this.$root.$emit('updateLoansTable', {loan: response.data.loan});
                this.$root.$emit('status', {message: this.getSuccessMsg(response.data.loan)});
            })
            .catch(error => {
                this.busy = false;
                if (error.response && error.response.status === 422) {
                    this.$root.$emit('error', {message: 'Utlånet kunne ikke gjennomføres. Se detaljer over.'});
                    this.errors = {
                        thing: get(error, 'response.data.errors.thing.0'),
                        user: get(error, 'response.data.errors.user.0'),
                    };
                } else {
                    console.error(error);
                    if (error.code == 'ECONNABORTED') {
                        this.$root.$emit('error', {message: 'Serveren svarer ikke. Utlånet ble antakelig ikke gjennomført. Last siden på nytt og prøv på nytt.'});
                    } else {
                        this.$root.$emit('error', {message: 'Utlånet kunne ikke gjennomføres fordi det skjedde noe uventet.' +
                            ' Feilen er forøvrig logget og vil bli analysert, men det hjelper jo ikke deg akkurat nå.'});
                    }
                }
            });
        },
        checkin() {
            this.busy = true;
            this.resetStatus();
            document.activeElement.blur();
            axios.post('/loans/checkin', {
                barcode: this.currentBarcode,
            })
            .then(response => {
                this.busy = false;
                this.$root.$emit('status', {
                    message: get(response, 'data.status'),
                    undoLink: get(response, 'data.undoLink'),
                });
                this.$root.$emit('updateLoansTable', {loan: response.data.loan});
            })
            .catch(error => {
                this.busy = false;
                if (error.response && error.response.status === 422) {
                    this.$root.$emit('error', {message: get(error, 'response.data.error')});
                } else {
                    console.error(error);
                    if (error.code == 'ECONNABORTED') {
                        this.$root.$emit('error', {message: 'Serveren svarer ikke. Last siden på nytt og prøv på nytt.'});
                    } else {
                        this.$root.$emit('error', {message: 'Tingen kunne ikke returneres fordi det skjedde noe uventet!' +
                            ' Du kan eventuelt prøve på nytt i en annen nettleser.' +
                            ' Feilen er forøvrig logget og vil bli analysert, men det hjelper jo ikke deg akkurat nå.'});
                    }
                }
            });
        },
    },
    mounted() {
        this.csrf_token = document.querySelector("meta[name='csrf-token']").getAttribute("content");

        window.addEventListener('keypress', (evt) => {
            if (evt.altKey || evt.ctrlKey || evt.metaKey) return;
            if (evt.target == document.body && evt.key) {
                var code = evt.which || evt.keyCode;
                if (code <= 32) {
                    return;
                }

                setTimeout(() => {
                    this.focusFirstTextInput().value += evt.key;
                });
            }
        });
    },
    components: {
        'typeahead': Typeahead,
        'spin-button': SpinButton,
    }
};
</script>
