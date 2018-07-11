<template>
    <div>
        <div v-if="error" class="alert visible alert-danger">
            {{ error }}
        </div>
        <div v-if="showNewVersionNotice" class="alert visible alert-warning">
          Bibrex har akkurat blitt oppdatert. Du bør laste siden på nytt nå, ellers kan det skje uventede ting.
        </div>
        <div v-if="loans.length">
            <datatable :data="loans" :sort-order="[[ 2, 'desc' ]]">
                <thead>
                    <tr>
                        <th>Lån</th>
                        <th>Bruker</th>
                        <th>Utlånt</th>
                        <th>Forfall</th>
                        <th>Merknader</th>
                        <th>Knapper</th>
                    </tr>
                </thead>
                <tbody>
                  <tr v-for="loan in loans" :class="{ 'highlight': highlight.indexOf(loan.id) !== -1 }">

                      <td>
                          <a :href="loan.url">{{ loan.item.thing.name }}</a>
                          <span v-if="loan.item.barcode">(<samp>{{ loan.item.barcode }}</samp>)</span>
                      </td>

                      <td :data-order="loan.user.name">
                          <i v-if="loan.user.in_alma" class="far fa-user-check text-success" title="Importert fra Alma"></i>
                          <a v-else-if="loan.user.barcode" :href="loan.user.id + '/sync'" title="Prøv å importere brukeropplysninger fra Alma">
                              <i class="far fa-sync text-warning"></i>
                          </a>
                          <i v-else class="far fa-exclamation-triangle text-danger"></i>
                          <a :href="loan.user.url">{{ loan.user.name }}</a>
                      </td>

                      <td :data-order="loan.created_at">
                          {{ loan.created_at_relative }}
                      </td>

                      <td :data-order="loan.due_at">
                          <a class="btn" title="Rediger forfallsdato" :href="loan.url + '/edit'">
                              <span v-if="loan.days_left > 1" style="color: green">om {{ loan.days_left }} dager</span>
                              <span v-else-if="loan.days_left == 1" style="color: orange">i morgen</span>
                              <span v-else-if="loan.days_left == 0" style="color: red">i dag</span>
                              <span v-else-if="loan.days_left == -1" style="color: red">i går</span>
                              <span v-else style="color: red">for {{ -loan.days_left }} dager siden</span>
                              <i class="far fa-pencil"></i>
                          </a>
                      </td>

                      <td>
                          <div class="text-danger" v-if="!loan.user.barcode">
                              <em class="far fa-exclamation-triangle"></em>
                              OBS: Ingen låne-ID registrert på brukeren!
                          </div>

                          <div class="text-danger" v-if="!loan.user.email">
                              <em class="far fa-exclamation-triangle"></em>
                              OBS: Ingen e-postadresse registrert på brukeren!
                          </div>

                          <div class="text-info" v-if="loan.user.note">
                              <i class="far fa-comment"></i>
                              {{ loan.user.note }}
                          </div>

                          <div class="text-info" v-b-tooltip.hover title="Merknad på lånet" v-if="loan.note">
                              <i class="far fa-comment"></i>
                              {{ loan.note }}
                          </div>

                          <div class="text-info" v-b-tooltip.hover title="Merknad på eksemplaret" v-if="loan.item.note">
                              <i class="far fa-comment"></i>
                              {{ loan.item.note }}
                          </div>

                          <div class="text-info" v-b-tooltip.hover title="Merknad på tingen" v-if="loan.item.thing.note">
                              <i class="far fa-comment"></i>
                              {{ loan.item.thing.note }}
                          </div>

                          <div v-for="notification in loan.notifications">
                              <a class="btn text-danger" :href="notification.url">
                                  <em class="glyphicon glyphicon-envelope text-danger"></em>
                                  Påminnelse sendt {{ notification.created_at }}
                              </a>
                          </div>

                      </td>

                      <td>
                          <loan-actions :loan="loan.id"></loan-actions>
                      </td>
                  </tr>

                </tbody>
            </datatable>
        </div>
    </div>
</template>

<script>

import axios from 'axios';
export default {
    props: {
        library: {
          type: Number,
        },
        refresh: {
          type: Number,
          default: 30,
        },
    },
    data: function() {
        return {
            busy: false,
            error: '',
            highlight: [],
            loans: [],
            showNewVersionNotice: false,
        };
    },
    methods: {
        loadTableData: function(highlight) {
            if (this.busy) {
                console.log('Already waiting for table to update');
                return;
            }
            this.busy = true;
            this.error = '';
            axios.get('/loans.json')
            .then(res => {
                this.busy = false;
                this.loans = [];
                Vue.nextTick(() => {
                    this.highlight = highlight;
                    this.loans = res.data;
                });
            })
            .catch(error => {
                this.busy = false;
                this.error = error;
                this.loans = [];
            });
        },
    },
    mounted() {
        this.loadTableData([]);

        this.$root.$on('updateLoansTable', ev => {
            console.log('Got local notification, will update the loans table.', ev);
            this.loadTableData(ev.loan ? [ev.loan.id] : []);
        });

        if (window.Echo) {
            Echo.private(`loans.${this.library}`)
              .listen('LoanTableUpdated', (ev) => {
                  if (ev.sender != sessionStorage.getItem('bibrexWindowId')) {
                      console.log('Got notification from another window, will update the loans table.', ev);
                      this.loadTableData(ev.loan ? [ev.loan.id] : []);
                  }
              });

            Echo.channel(`bibrex`)
              .listen('NewVersionDeployed', (ev) => {
                  console.log('Got notification about new version.');
                  this.showNewVersionNotice = true;
              });
        } else {
            console.error('Echo did not initialize, cannot listen to notifications.');
        }
    },
}
</script>