<template>
    <div>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" :class="{active: activeTab == 'checkout'}" id="nav-checkout-tab" data-toggle="tab" href="#nav-checkout" role="tab" @click="onTabClick">Utlån</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{active: activeTab == 'checkin'}" id="nav-checkin-tab" data-toggle="tab" href="#nav-checkin" role="tab" @click="onTabClick">Retur</a>
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
                            prefetch="/things.json?mine=1"
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
                        <spin-button :busy="this.busy" class="checkout">
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
                        <spin-button :busy="this.busy" class="checkout">
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
import Typeahead from './Typeahead';
import SpinButton from './SpinButton';

export default {
    props: {
        libraryId: Number,
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
        onTabClick() {
            setTimeout(this.focusFirstTextInput.bind(this), 300);
        },
        getSuccessMsg() {
            switch (Math.floor(Math.random() * 20)) {
                case 0:
                    return 'Utlånet er registrert (og verden har forøvrig fortsatt ikke gått under).';
                case 1:
                    return 'Utlånet er registrert (Faktisk helt sant).';
                default:
                    return 'Utlånet er registrert.';
            }
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
                this.$root.$emit('status', {message: this.getSuccessMsg()});
            })
            .catch(response => {
                this.busy = false;
                console.log(response);
                if (response.response.status === 422) {
                    this.$root.$emit('error', {message: 'Utlånet kunne ikke gjennomføres. Se detaljer over.'});
                    this.errors = {
                        thing: get(response, 'response.data.errors.thing.0'),
                        user: get(response, 'response.data.errors.user.0'),
                    };
                } else {
                    this.$root.$emit('error', {message: 'Utlånet kunne ikke gjennomføres fordi det skjedde noe uventet.' +
                        ' Feilen er forøvrig logget og vil bli analysert, men det hjelper jo ikke deg akkurat nå.'});
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
            })
            .catch(response => {
                this.busy = false;
                if (response.response.status === 422) {
                    this.$root.$emit('error', {message: get(response, 'response.data.error')});
                } else {
                    this.$root.$emit('error', {message: 'Tingen kunne ikke returneres fordi det skjedde noe uventet!' +
                        ' Du kan eventuelt prøve på nytt i en annen nettleser.' +
                        ' Feilen er forøvrig logget og vil bli analysert, men det hjelper jo ikke deg akkurat nå.'});
                }
                console.log(response);
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
