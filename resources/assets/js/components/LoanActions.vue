<template>
    <div class="btn-group btn-group" role="group">
        <button :disabled="this.busy" title="Returnér tingen" class="btn btn-success" @click="checkin()">
            Retur
        </button>
        <button :disabled="this.busy" title="Merk som tapt" class="btn btn-danger" @click="lost()">
            Tapt
        </button>
    </div>
</template>
<script>
    import { get } from 'lodash/object';
    import axios from 'axios';
    import SpinButton from './SpinButton';
    export default {
        components: {
            'spin-button': SpinButton,
        },
        data: function() {
            return {
                busy: false,
            };
        },
        props: {
            loan: {
                type: Number,
            },
        },
        methods: {
            checkin() {
                document.activeElement.blur();
                this.busy = true;
                this.$root.$emit('status', {});
                axios.post('/loans/checkin', {
                    loan: this.loan,
                })
                .then(response => {
                    this.busy = false;
                    this.$root.$emit('status', {
                        message: get(response, 'data.status'),
                        undoLink: get(response, 'data.undoLink'),
                    });
                    this.$root.$emit('updateLoansTable', {});
                })
                .catch(error => {
                    this.busy = false;
                    console.error(error.response);
                    this.$root.$emit('error', {message: 'Tingen kunne ikke returneres fordi det skjedde noe uventet!' +
                        ' Du kan eventuelt prøve på nytt i en annen nettleser.' +
                        ' Feilen er forøvrig logget og vil bli analysert, men det hjelper jo ikke deg akkurat nå.'});
                });
            },
            lost() {
                document.activeElement.blur();
                this.busy = true;
                this.$root.$emit('status', {});
                axios.post(`/loans/${this.loan}/lost`)
                .then(response => {
                    this.busy = false;
                    this.$root.$emit('status', {
                        message: get(response, 'data.status'),
                        undoLink: get(response, 'data.undoLink'),
                    });
                    this.$root.$emit('updateLoansTable', {});
                })
                .catch(error => {
                    this.busy = false;
                    console.log(error.response);
                    this.$root.$emit('error', {message: 'Som om ikke det var nok at tingen var tapt så oppsto det også en feil' +
                        ' som gjorde at den ikke kunne registreres som tapt! For en dag!' +
                        ' Du kan eventuelt prøve på nytt i en annen nettleser.' +
                        ' Feilen er forøvrig logget og vil bli analysert, men det hjelper jo ikke deg akkurat nå.'});
                });
            },
        }
    }
</script>