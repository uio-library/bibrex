<template>
    <div>
        <form enctype="multipart/form-data" novalidate @submit.prevent="save" class="card mb-3">
            <div class="card-header">
                <div class="row align-items-center">
                    <h5 class="col mb-0">
                        <a name="image">Bilde</a>
                    </h5>
                    <div v-if="editMode">
                        <button type="button" class="btn btn-warning col-auto mx-1" @click="reset()">
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

            <div :style="`height: ${height}px;`">
                <div v-if="editMode">
                    <div class="dropbox">
                        <p v-if="isSaving && file && file.name">
                            Uploading {{ file.name }} files...
                        </p>
                        <p v-if="!isSaving">
                            <b-form-file
                                    accept="image/jpeg, image/png, image/gif"
                                    v-model="file" placeholder="Choose a file or drop it here..."
                                    :disabled="isSaving"
                            ></b-form-file>

                            <span v-if="file && file.name">{{ file.name}}</span>
                        </p>
                    </div>
                </div>
                <div v-else style="text-align:center;">
                    <div v-if="this.current.thumb" :style="`display: inline-block;`">
                        <img :src="'/storage/' + this.current.thumb.name" alt="Bilde av tingen">
                    </div>
                </div>
            </div>
        </form>

        <alert variant="danger" v-if="isFailed">Opplastingen feilet :(</alert>
        <alert variant="success" v-if="isSuccess">Opplastingen var vellykka :)</alert>
    </div>
</template>

<script>
    import axios from 'axios';
    import { cloneDeep } from 'lodash/lang';
    import { BFormFile } from 'bootstrap-vue/esm/components/form-file';

    const STATUS_INITIAL = 0, STATUS_SAVING = 1, STATUS_SUCCESS = 2, STATUS_FAILED = 3;

    export default {
        components: {
            'b-form-file': BFormFile,
        },
        props: {
            thingId: Number,
            data: Object,
        },
        computed: {
            height() {
                if (this.current.thumb && this.current.thumb.height > 100) {
                    return this.current.thumb.height;
                } else {
                    return 100;
                }
            },
            isInitial() {
                return this.currentStatus === STATUS_INITIAL;
            },
            isSaving() {
                return this.currentStatus === STATUS_SAVING;
            },
            isSuccess() {
                return this.currentStatus === STATUS_SUCCESS;
            },
            isFailed() {
                return this.currentStatus === STATUS_FAILED;
            },
        },
        data: () => {
            return {
                current: null,
                file: null,
                editMode: false,
                uploadError: null,
                currentStatus: null,
            };
        },
        methods: {
            reset() {
                // reset form to initial state
                this.editMode = false;
                this.currentStatus = STATUS_INITIAL;
                this.file = null;
                this.uploadError = null;
            },
            upload(formData) {
                const url = `/things/${this.thingId}/image`;

                // upload data to the server
                this.currentStatus = STATUS_SAVING;

                return axios.post(url, formData)
                    .then(x => x.data)
                    .then(x => {
                        this.uploadedFiles = [].concat(x);
                        this.currentStatus = STATUS_SUCCESS;
                        this.editMode = false;
                        this.current = x.thing.image;
                    })
                    .catch(err => {
                        this.uploadError = err.response;
                        this.currentStatus = STATUS_FAILED;
                        this.editMode = false;
                    });
            },
            save() {
                // handle file changes
                const formData = new FormData();

                if (!this.file || !this.file.name) return;

                // append the files to FormData
                formData.append('thingimage', this.file, this.file.name);

                // save it
                this.upload(formData);
            },
        },
        created() {
            this.reset();
            this.current = cloneDeep(this.data);
        },
    };
</script>
