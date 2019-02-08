<template>
    <div>
        <form @submit.prevent="save" class="card mb-3">
            <div class="card-header">
                <div class="row align-items-center">
                    <h5 class="col mb-0">
                        <a name="library_settings">Biblioteksspesifikke innstillinger for tingen</a>
                    </h5>
                    <div v-if="editMode">
                        <button type="button" class="btn btn-warning col-auto mx-1" @click="cancel()">
                            <i class="far fa-pencil"></i>
                            Avbryt
                        </button>
                        <button type="submit" class="btn btn-success col-auto mx-1">
                            <i class="far fa-pencil"></i>
                            Lagre
                        </button>
                    </div>
                    <div v-else>
                        <button type="button" class="btn btn-primary col-auto mx-1" @click="editMode=!editMode">
                            <i class="far fa-pencil"></i>
                            Rediger
                        </button>
                    </div>
                </div>
            </div>
            <ul class="list-group list-group-flush">

                <!-- LOANS WITHOUT BARCODE -->

                <li class="list-group-item">
                    <div class="row">
                        <label for="loans_without_barcode" class="col-sm-3 col-form-label">Utlån uten strekkode:</label>
                        <div class="col-sm-8">

                            <toggle-button
                                v-if="editMode"
                                v-model="current.loans_without_barcode"
                                :color="'#82C7EB'"
                                :labels="{checked: 'Japp', unchecked: 'Nope'}"
                                :width="70"
                                :height="30"
                            />
                            <div v-else class="col-form-label">
                                <span v-if="current.loans_without_barcode" class="text-success">
                                    <i class="far fa-check-circle"></i>
                                    Aktivert
                                </span>
                                <span v-else>Deaktivert</span>
                            </div>

                        </div>
                    </div>
                </li>

                <!-- REMINDERS -->

                <li class="list-group-item">
                    <div class="row">
                        <label for="reminders" class="col-sm-3 col-form-label">Påminnelser:</label>
                        <div class="col-sm-8">

                            <toggle-button
                                v-if="editMode"
                                v-model="current.reminders"
                                :color="'#82C7EB'"
                                :labels="{checked: 'Japp', unchecked: 'Nope'}"
                                :width="70"
                                :height="30"
                            />
                            <div v-else class="col-form-label">
                                <span v-if="current.reminders" class="text-success">
                                    <i class="far fa-check-circle"></i>
                                    Aktivert
                                </span>
                                <span v-else>Deaktivert</span>
                            </div>

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
        thingId: Number,
        data: Object,
    },
    computed: {
        hasErrors: function() {
            return Object.keys(this.errors).length;
        },
        allErrors: function() {
            return flatMap(this.errors);
        }
    },
    data: () => {
        return {
            saved: false,
            busy: false,
            isNew: false,
            original: {},
            current: {},
            editMode: false,
            errors: {},
        };
    },
    methods: {
        cancel() {
            this.editMode = false;
            this.current = cloneDeep(this.original);
            this.errors = {};
            this.saved = false;
        },
        save() {
            this.errors = {};
            this.saved = false;
            this.busy = true;
            axios.post(`/things/${this.thingId}/settings`, this.current)
            .then(response => {
                this.busy = false;
                this.saved = true;
                this.original = cloneDeep(this.current);
                this.editMode = false;
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
        this.original = cloneDeep(this.data);
        this.current = cloneDeep(this.data);
    }
};
</script>