<template>
    <div>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" :class="{active: activeTab == 'checkout'}" id="nav-checkout-tab" href="#" role="tab" @click.prevent="onTabClick('checkout')" v-shortkey.once="checkoutShortkey" @shortkey="onTabClick('checkout')" v-b-tooltip.hover :title="'Snarvei: ' + checkoutShortkey.join('+')">Utlån</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" :class="{active: activeTab == 'checkin'}" id="nav-checkin-tab" href="#" @click.prevent="onTabClick('checkin')" v-shortkey.once="checkinShortkey" @shortkey="onTabClick('checkin')" v-b-tooltip.hover :title="'Snarvei: ' + checkinShortkey.join('+')">Retur</a>
            </li>
        </ul>
        <div class="tab-content p-3 mb-3">

            <div class="tab-pane fade show" :class="{active: activeTab == 'checkout'}" id="nav-checkout" role="tabpanel" aria-labelledby="nav-checkout-tab">
                <form method="post" class="form row px-2" @submit.prevent="checkout">

                    <div class="col-sm-5 px-2">
                        <label for="user">Til hvem?</label>
                        <a id="userHelp" class="btn btn-link btn-small text-info" @click="showHelp1 = !showHelp1">
                            <em class="far fa-question-circle"></em>
                            Hjelp
                        </a>
                        <b-popover target="userHelp"
                                 :show.sync="showHelp1"
                                 triggers=""
                                 placement="rightbottom"
                                 ref="userHelpPopover"
                                 title="Bruker">
                            <ul>
                                <li>
                                    Her leser du vanligvis inn <a href="https://www.ub.uio.no/bruk/alt-om-lan/finn-lanenummer.html" target="_blank">låne-ID</a>, men du kan også skrive inn
                                    personens navn.
                                </li>
                                <li>
                                    <strong>Hvis personen ikke blir funnet</strong>:
                                    Bibrex søker i sanntid mot Alma. Hvis en person ikke finnes der kan du skynde deg og registrere hen i <a href="https://bim.bibsys.no/" target="_blank">BIM</a> (<a href="https://www.uio.no/for-ansatte/enhetssider/ub/aktuelt/aktuelle-saker/2016/bim-bruksanvisning.pdf" target="_blank">bruksanvisning</a>).
                                    I spesielle tilfeller, som når en student har fått et kort som ikke har blitt importert enda, kan du i stedet <a href="/users/_new/edit">opprette</a> en lokal bruker i Bibrex.
                                </li>
                                <li>
                                    <strong>Effektivitetstips I</strong>: Du trenger ikke trykke i feltet før du leser inn eller begynner å skrive.
                                </li>
                                <li>
                                    <strong>Effektivitetstips II</strong>: Still inn strekkodeleseren din din til å sende Enter på slutten, så hopper Bibrex rett til neste felt etter innlesing.
                                </li>
                            </ul>
                        </b-popover>

                        <typeahead
                            :tabindex="1"
                            name="user"
                            :class="{'is-invalid': errors.user}"
                            :value="currentUser"
                            @input="setCurrentUser($event)"
                            prefetch="/users.json"
                            remote="/users/search-alma"
                            placeholder="Navn eller låne-ID"
                            :min-length="4"
                            :alma="true"
                        ></typeahead>
                        <!--<small class="form-text text-muted" v-show="!errors.user">
                            Navn eller låne-ID
                        </small>-->
                        <div class="text-danger" v-show="errors.user" v-html="errors.user"></div>
                    </div>

                    <div class="col-sm-5 px-2">
                        <label for="thing">Hvilken ting?</label>
                        <a id="thingHelp" class="btn btn-link btn-small text-info"  data-toggle="popover" title="Popover title" data-content="And here's some amazing content. It's very engaging. Right?" @click="showHelp2 = !showHelp2">
                            <em class="far fa-question-circle"></em>
                            Hjelp
                        </a>
                        <b-popover target="thingHelp"
                                 :show.sync="showHelp2"
                                 triggers=""
                                 placement="rightbottom"
                                 ref="thingHelpPopover"
                                 title="Ting">
                            <ul>
                                <li>
                                    Her leser du vanligvis inn strekkoden til tingen.
                                </li>
                                <li>
                                    Hvis biblioteket tillater utlån av enkelte ting uten strekkode vil disse vises i en liste når du trykker i feltet eller begynner å skrive.
                                </li>
                                <li>
                                    Finner du ikke tingen (eller et bestemt eksemplar av den), gå til siden <a href="/things">ting</a> for å registrere den.
                                </li>
                            </ul>
                        </b-popover>
                        <typeahead
                            :tabindex="2"
                            name="thing"
                            :class="{'is-invalid': errors.thing}"
                            :value="currentThing"
                            @input="setCurrentThing($event)"
                            prefetch="/things.json?withoutBarcode=1"
                            remote="/items/search"
                            placeholder="Scann eller velg ting"
                            :min-length="0"
                            :limit="30"
                        ></typeahead>
                        <!--<small class="form-text text-muted" v-show="!errors.thing">
                            Scann eller velg ting
                        </small>-->
                        <div class="text-danger" v-show="errors.thing" v-html="errors.thing"></div>
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
import bPopover from 'bootstrap-vue/es/components/popover/popover';
import Typeahead from './Typeahead';
import SpinButton from './SpinButton';

export default {
    props: {
        libraryId: Number,
        user: Object,
        thing: Object,
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
            showHelp1: false,
            showHelp2: false,
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
            idleSince: (new Date()).getTime(),
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
            setTimeout(this.focusFirstTextInput.bind(this), 150);  // Small timeout for IE11
        },
        handleError(what, error) {
            this.busy = false;
            if (!error.response) {
                // Some kind of network error. No response at all from server.
                Raven.captureException(error);
                console.error(error);
                this.$root.$emit('error', {
                    message: `Serveren svarer ikke. ${what} kunne trolig ikke gjennomføres.
                        Last siden på nytt og prøv på nytt. Meld fra hvis feilen vedvarer!`,
                });
            } else if (error.response.status === 419) {
                // CSRF token/session timeout
                this.$root.$emit('error', {
                    message: `Siden har vært inaktiv for lenge. Last siden på nytt og prøv igjen.`
                });
            } else if (error.response.status === 422) {
                // Validation error
                this.$root.$emit('error', {message: `${what} kunne ikke gjennomføres. Se detaljer over.`});
                this.errors = {
                    thing: get(error, 'response.data.errors.thing.0'),
                    user: get(error, 'response.data.errors.user.0'),
                };
            } else {
                // We got some kind of error response from the server, typically a 5xx error.
                console.error(error.response);
                Raven.captureException(error, {
                    extra: {
                        response: error.response
                    },
                });

                this.$root.$emit('error', {message: `${what} kunne ikke gjennomføres fordi det skjedde noe uventet.
                    Du kan evt. prøve på nytt i en annen nettleser. Feilen er forøvrig logget og vil bli analysert,
                    men det hjelper jo ikke deg akkurat nå.`
                });
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
                this.$root.$emit('updateLoansTable', {loan: response.data.loan});
                this.$root.$emit('status', {
                    message: get(response, 'data.status'),
                    editLink: get(response, 'data.editLink'),
                    variant: get(response, 'data.warn') ? 'warning' : 'success' ,
                });
            })
            .catch(error => this.handleError('Utlånet', error));
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
            .catch(error => this.handleError('Innleveringen', error));
        },
        checkIdleTime() {
            let oneHour = 3600000;
            let idleHours = ((new Date()).getTime() - this.idleSince) / oneHour;
            if (idleHours > 12) {
                window.location.reload();
            } else {
                setTimeout(() => this.checkIdleTime(), oneHour);
            }
        }
    },
    created() {
        this.currentUser = this.user || {name:""};
        this.currentThing = this.thing || {name:""};
    },
    mounted() {
        window.addEventListener('keypress', (evt) => {
            if (evt.altKey || evt.ctrlKey || evt.metaKey) return;
            if (evt.target === document.body && evt.key) {
                let code = evt.which || evt.keyCode;
                if (code <= 32) {
                    return;
                }
                setTimeout(() => {
                    this.focusFirstTextInput().value += evt.key;
                });
            }
        });
        window.addEventListener('click', (evt) => {
            if (['A', 'BUTTON', 'I', 'EM'].indexOf(evt.target.tagName) == -1) {
                if (this.showHelp1) this.showHelp1 = false;
                if (this.showHelp2) this.showHelp2 = false;
            }
        });

        window.addEventListener('click', () => { this.idleSince = (new Date()).getTime(); })
        window.addEventListener('keypress', () => { this.idleSince = (new Date()).getTime(); })
        this.checkIdleTime();
    },
    components: {
        'b-popover': bPopover,
        'typeahead': Typeahead,
        'spin-button': SpinButton,
    }
};
</script>
