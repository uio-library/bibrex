<template>
    <div>


        <div v-if="status.message">
            <alert variant="success" @close="status={}">
                <div v-html="status.message"></div>
                <button v-if="status.undoLink" class="btn alert-link" @click="undo"><i class="fas fa-undo"></i> Angre</button>
            </alert>
        </div>

    </div>
</template>
<script>
    import axios from 'axios';
    import { get } from 'lodash/object';

    export default {
        data: function() {
            return {
                status: {},
                error: {},
                timeout: -1,
            };
        },
        methods: {
            undo() {
                Vue.nextTick(() => this.$root.$emit('status', {}));
                axios.post(this.status.undoLink)
                .then(response => {
                    this.busy = false;
                    this.$root.$emit('status', {
                        message: get(response, 'data.status'),
                    });
                    this.$root.$emit('updateLoansTable', {});
                })
                .catch(error => {
                    this.busy = false;
                    console.error(error);
                    this.$root.$emit('error', {message: 'Auda, det oppsto en ukjent feil!' +
                        ' Du kan eventuelt prøve på nytt i en annen nettleser.' +
                        ' Feilen er forøvrig logget og vil bli analysert, men det hjelper jo ikke deg akkurat nå.'});
                });
            }
        },
        mounted() {

            this.$root.$on('status', status => {
                Vue.nextTick(() => {
                    this.error = '';
                    this.status = status;
                });
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => {
                    this.status = {};
                }, 120000);
            });

            this.$root.$on('error', error => {
                Vue.nextTick(() => {
                    this.status = '';
                    this.error = error;
                });
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => {
                    this.error = {};
                }, 120000);
            });

        },
    }
</script>
