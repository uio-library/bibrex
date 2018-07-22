<template>
  <div>
    <p v-if="error">
      {{error}}
    </p>

    <div style="float:left; margin-top: 10px;" class="col col-auto" v-b-tooltip.hover title="Merk to brukere som du ønsker å slå sammen." v-b-tooltip.hover>
        <button id="flett" class="btn btn-success" @click="merge" style="width:100%;" :disabled="selection.length != 2">
            <i class="far fa-compress-alt"></i>
            Flett to brukere
        </button>
    </div>

    <datatable :sort-order="[[ 1, 'asc' ]]" :checkboxes="true" @select="select">
      <thead>
        <tr>
          <th></th>
          <th>Navn</th>
          <th>Låne-ID</th>
          <th>Merknader</th>
          <th>Opprettet</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :id="'user_' + user.id">
          <td></td>
          <td>
            <i v-if="user.in_alma" class="far fa-user-check text-success" v-b-tooltip.hover title="Importert fra Alma"></i>
            <i v-else class="far fa-user text-warning" v-b-tooltip.hover title="Lokal bruker"></i>
            <a :href="'/users/' + user.id">{{ user.name }}</a>
          </td>
          <td>
            {{ user.barcode }}
          </td>
          <td>
            {{ user.note }}
          </td>
          <td>
            {{ user.created_at }}
          </td>
        </tr>
      </tbody>
    </datatable>

  </div>
</template>

<script>
  import { get } from 'lodash/object';
  import { cloneDeep } from 'lodash/lang';

  export default {
      props: {
        data: Array,
      },
      data: () => {
          return {
              error: '',
              users: [],
              selection: [],
          };
      },
      methods: {
        select(selection) {
          this.selection = selection.map((x) => {
            return this.users[x];
          });
          console.log(this.selection);
        },
        merge() {
          window.location = `/users/merge/${this.selection[0].id}/${this.selection[1].id}`;
        },
        filter() {

        }
        // onToggle(thing, value) {
        //   axios.post(`/things/toggle/${thing.id}`, {value: value})
        //   .then((resp) => {
        //       thing.at_my_library = value;
        //       thing.library_settings = resp.data.library_settings ;
        //   });

        // },
        // onUpdateSetting(thing, key, value) {
        //   axios.post(`/things/settings/${thing.id}`, {key: key, value: value})
        //   .then((resp) => {
        //       thing.library_settings = resp.data.library_settings ;
        //   });
        // }
      },
      created() {
        this.users = cloneDeep(this.data);
      },
  }
</script>
