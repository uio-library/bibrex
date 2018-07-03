<template>
    <div>
        <div v-if="error" class="alert alert-danger">
            {{ error }}
        </div>
        <div v-if="loans.length">
            <datatable :data="loans">
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
                          <span v-if="loan.item.dokid">(<samp>{{ loan.item.dokid }}</samp>)</span>
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
                              <span v-else-if="loan.days_left == 0" style="color: orange">i dag</span>
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

                          <div class="text-danger" v-for="notification in loan.notifications">
                              <a class="text-danger" :href="notification.url">
                                  <em class="glyphicon glyphicon-envelope text-danger"></em>
                                  Påminnelse</a>
                              ble sendt {{ notification.created_at }}.
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
            error: '',
            highlight: [],
            loans: [],
        };
    },
    methods: {
        loadTableData: function(highlight) {
            this.error = '';
            axios.get('/loans.json')
            .then(res => {
                this.loans = [];
                Vue.nextTick(() => {
                    this.highlight = highlight;
                    this.loans = res.data;
                });
            })
            .catch(err => {
                this.loans = [];
                this.error = err;
            });
        }
    },
    mounted() {
        this.loadTableData([]);

        Echo.private(`loans.${this.library}`)
          .listen('LoanTableUpdated', (ev) => {
              console.log('Got notification, will update table.');
              setTimeout(() => this.loadTableData(ev.highlight ? ev.highlight : []), 100);
          });
    },
}
</script>