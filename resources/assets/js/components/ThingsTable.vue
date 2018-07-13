<template>
  <div>
    <p>
      {{error}}
    </p>

    <table style="width:100%" class="table" id="thingsTable">
      <thead>
        <tr>
          <th>Ting</th>
          <th>Lånetid</th>
          <th>Påminnelser</th>
          <th>Utlån uten strekkode</th>
          <th>Mitt bibliotek<br><small>Tilgjengelig / totalt</small></th>
          <th>Alle bibliotek<br><small>Tilgjengelig / totalt</small></th>
        </tr>
      </thead>
      <tbody>

        <tr v-for="thing in things">

          <td>
            <a :href="'/things/' + thing.id" v-b-tooltip.hover :title="thing.tooltip">{{ thing.name }}</a>
          </td>

          <td>
            {{ thing.properties.loan_time }}
          </td>

          <td>
            <span v-if="thing.library_settings.reminders" class="text-success">
              <i class="far fa-check-circle"></i>
              Aktivert
            </span>
          </td>

          <td>
            <span v-if="thing.library_settings.loans_without_barcode" class="text-success">
              <i class="far fa-check-circle"></i>
              Aktivert
            </span>
          </td>

          <td>
            {{ thing.avail_mine}}
            /
            {{ thing.items_mine}}
          </td>

          <td>
            {{ thing.avail_total}}
            /
            {{ thing.items_total}}
          </td>

        </tr>

      </tbody>
    </table>
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
              things: [],
          };
      },
      methods: {
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
        this.things = cloneDeep(this.data);
        this.things.forEach((thing) => {
          thing.tooltip = 'Bokmål: ' + get(thing.properties, 'name_indefinite.nob') +
                ' / '+ get(thing.properties, 'name_definite.nob') + '.' +
                ' Nynorsk: ' + get(thing.properties, 'name_indefinite.nno') +
                ' / '+ get(thing.properties, 'name_definite.nno') +
                '. Engelsk: ' + get(thing.properties, 'name_indefinite.eng') +
                ' / '+ get(thing.properties, 'name_definite.eng');
        });
      },
  }
</script>
