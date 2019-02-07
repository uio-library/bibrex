<template>

    <div>

        <alert v-if="hasErrors" variant="danger" @close="errors={}">
            <ul style="margin-bottom: 0">
                <li v-for="err in allErrors">
                    {{ err }}
                </li>
            </ul>
        </alert>

        <alert v-if="status" variant="success" @close="status=null">
            {{ status }}
        </alert>

        <form @submit.prevent="save" class="card mb-3">

            <div class="card-header">
                <div class="row align-items-center">
                    <h5 class="col mb-0">
                        <span v-if="isNew">Ny ting</span>
                        <span v-else>Generelle innstillinger for tingen</span>
                    </h5>
                    <div v-if="editMode">
                        <button type="button" class="btn btn-warning col-auto mx-1" @click="reset()" v-if="!isNew">
                            <i class="far fa-pencil"></i>
                            Avbryt
                        </button>
                        <button type="submit" class="btn btn-success col-auto mx-1">
                            <i class="far fa-pencil"></i>
                            Lagre
                        </button>
                    </div>
                    <div v-else>
                        <button type="button" v-if="current.deleted_at" class="btn btn-warning col col-auto mx-1" @click="restore()">
                            <i class="far fa-box-full"></i>
                            Gjenopprett
                        </button>
                        <button type="button" v-else class="btn btn-danger col-auto mx-1" @click="trash()">
                            <i class="far fa-trash"></i>
                            Slett
                        </button>
                        <button type="button" class="btn btn-primary col-auto mx-1" @click="edit()">
                            <i class="far fa-pencil"></i>
                            Rediger
                        </button>
                    </div>
                </div>
            </div>


            <ul class="list-group list-group-flush">

                <!-- ETIKETTER -->

                <li class="list-group-item">

                    <div class="row mb-3" v-for="lang in languages">

                        <div class="col col-sm-1 col-form-label">
                          {{ languageLabels[lang] }}:
                        </div>

                        <div class="col col-sm-3">
                            <input placeholder="Navn" type="text" :readonly="!editMode"
                                   :name="'name.' + lang"
                                   v-model="current.properties.name[lang]"
                                   :class="{
                                    'is-invalid': errors['properties.name.' + lang],
                                    'form-control': editMode,
                                    'form-control-plaintext': !editMode
                                }">
                            <p class="invalid-feedback" v-if="editMode && errors['properties.name.' + lang]">
                                {{ errors['properties.name.' + lang][0] }}
                            </p>
                            <p class="small form-text" v-if="editMode">
                                {{ help[lang] }}
                            </p>
                        </div>

                        <div class="col col-sm-4">
                            <input placeholder="Ubestemt form med artikkel" type="text" :readonly="!editMode"
                                :name="'name_indefinite.' + lang"
                                v-model="current.properties.name_indefinite[lang]"
                                :class="{
                                    'is-invalid': errors['properties.name_indefinite.' + lang],
                                    'form-control': editMode,
                                    'form-control-plaintext': !editMode
                                }">
                            <p class="invalid-feedback" v-if="editMode && errors['properties.name_indefinite.' + lang]">
                                {{ errors['properties.name_indefinite.' + lang][0] }}
                            </p>
                            <p class="small form-text" v-if="editMode">
                                {{ helpIndefinite[lang] }}
                            </p>
                        </div>

                        <div class="col col-sm-4">
                            <input placeholder="Bestemt form med artikkel" type="text" :readonly="!editMode"
                                v-model="current.properties.name_definite[lang]"
                                :name="'name_definite.' + lang"
                                :class="{
                                    'is-invalid': errors['properties.name_definite.' + lang],
                                    'form-control': editMode,
                                    'form-control-plaintext': !editMode
                                }">
                            <p class="invalid-feedback" v-if="editMode && errors['properties.name_definite.' + lang]">
                                {{ errors['properties.name_definite.' + lang][0] }}
                            </p>
                            <p class="small form-text" v-if="editMode">
                                {{ helpDefinite[lang] }}
                            </p>
                        </div>

                    </div>
                </li>

                <!-- LOAN TIME -->

                <li class="list-group-item">
                    <div class="row">
                        <label for="loan_time" class="col-sm-3 col-form-label">Utlånstid:</label>
                        <div class="col col-auto">
                            <input id="loan_time" name="loan_time" type="number" :readonly="!editMode" min="1" max="9999" required
                                :value="current.properties.loan_time"
                                @input="current.properties.loan_time = parseInt($event.target.value)"
                                style="width: 65px; display: inline"
                                :class="{
                                    'is-invalid': errors['properties.loan_time'],
                                    'form-control': editMode,
                                    'form-control-plaintext': !editMode
                                }">
                            <span class="pl-3">{{ current.properties.loan_time == 1 ? 'dag' : 'dager' }}</span>
                            <small>(Minimum er «1», som innebærer at tingen purres neste morgen.)</small>
                        </div>
                    </div>
                </li>

            </ul>
        </form>
    </div>
</template>

<script>
import { flatMap } from 'lodash/collection';
import { get } from 'lodash/object';
import { cloneDeep } from 'lodash/lang';
import axios from 'axios';

export default {
    props: {
        data: Object,
    },
    computed: {
        id: function () {
            return this.current.id;
        },
        isNew: function() {
            return !this.id;
        },
        hasErrors: function() {
            return Object.keys(this.errors).length;
        },
        allErrors: function() {
            return flatMap(this.errors);
        }
    },
    data: () => {
        return {
            languages: ['nob', 'nno', 'eng'],
            languageLabels: {'nob': 'Bokmål', 'nno': 'Nynorsk', 'eng': 'Engelsk'},
            help: {
                nob: 'Navn på tingen. Begynner vanligvis med stor bokstav. Eksempler: «Hørselvern», «Skjøteledning», «Nøkkel til hvilerommet».',
                nno: 'Eksempel: «Høyrselsvern», «Skøyteleidning», «Nykel til kvilerommet».',
                eng: 'Examples: «Earmuffs», «Extension cord», «Resting room key».',
            },
            helpDefinite: {

                'nob': 'Form som passer inn i setningen «____ må leveres».' +
                    ' Noen eksempler: «hørselvernet», «skjøteledningen», «nøkkelen til hvilerommet».',

                'nno': 'Form som passer inn i setninga «____ må leverast».' +
                    ' Nokre eksempel: «høyrselsvernet», «skøyteleidninga», «nykelen til kvilerommet».',

                'eng': 'Form som passer inn i setningen «____ must be returned».' +
                    ' Noen eksempler:  «the earmuffs», «the extension cord», «the resting room key».' +
                    ' (første bokstav kan godt være liten, programvaren gjør den automatisk stor ved behov).',
            },
            helpIndefinite: {

                'nob': 'Form som passer inn i setningen «Du lånte ____ fra oss i går».' +
                    ' Noen eksempler: «et hørselvern», «en skjøteledning», «nøkkelen til hvilerommet» ' +
                    ' (bestemt form i dette eksempelet fordi det bare finnes én).',

                'nno': 'Form som passer inn i setninga «Du lånte ____ frå oss i går».' +
                    ' Nokre eksempel: «eit høyrselsvern», «ei skøyteleidning», «nykelen til kvilerommet»',

                'eng': 'Form som passer inn i setningen «You borrowed ____ from us yesterday.' +
                    ' Noen eksempler: «a pair of earmuffs», «an extension cord», «the resting room key».',
            },
            status: null,
            busy: false,
            original: {},
            current: {},
            editMode: false,
            errors: {},
        };
    },
    methods: {
        reset() {
            this.editMode = false;
            this.current = cloneDeep(this.original);
            this.errors = {};
            this.status = null;
        },
        edit() {
            this.reset();
            this.editMode = true;
        },
        trash() {
            this.busy = true;
            axios.post(`/things/${this.id}/delete`)
            .then(response => {
                this.busy = false;
                this.status = response.data.status;
                this.original = cloneDeep(response.data.thing);
                this.current = cloneDeep(response.data.thing);
            })
            .catch(error => {
                this.busy = false;
                this.errors = error.response.data.errors || {};
            });
        },
        restore() {
            this.busy = true;
            axios.post(`/things/${this.id}/restore`)
            .then(response => {
                this.busy = false;
                this.status = response.data.status;
                this.original = cloneDeep(response.data.thing);
                this.current = cloneDeep(response.data.thing);
            })
            .catch(error => {
                this.busy = false;
                this.errors = error.response.data.errors || {};
            });
        },
        save() {
            this.errors = {};
            this.saved = false;
            this.busy = true;
            let url = this.isNew ? '/things/_new' : '/things/' + this.id;
            axios.post(url, this.current)
            .then(response => {
                if (this.isNew) {
                    window.location.assign('/things?created=' + response.data.thing.id);
                } else {
                    this.busy = false;
                    this.editMode = false;
                    this.status = response.data.status;
                    this.original = cloneDeep(response.data.thing);
                    this.current = cloneDeep(response.data.thing);
                }
            })
            .catch(error => {
                this.busy = false;
                console.error(error);
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                } else {
                    this.errors = {'misc': ['Kunne ikke lagre pg.a. en ukjent feil.']};
                }
            });

        }
    },
    created() {
        console.log(this.data);
        this.original = cloneDeep(this.data);
        this.current = cloneDeep(this.data);
        if (this.isNew) {
            this.edit();
        }
    }
};
</script>
