<template>
  <div>
    <p>
      {{error}}
    </p>

    <table style="width:100%" class="table" id="thingsTable">
      <thead>
        <tr>
          <th>Ting</th>
          <th class="text-center">Lånetid</th>
          <th class="text-center">Påminnelser</th>
          <th class="text-center">Utlån uten strekkode</th>
          <th class="text-center">Mitt bibliotek<br><small>Tilgjengelig / totalt</small></th>
          <th class="text-center">Alle bibliotek<br><small>Tilgjengelig / totalt</small></th>
        </tr>
      </thead>
      <tbody>

        <tr v-for="thing in things">

          <td>
            <a :href="'/things/' + thing.id" style="vertical-align: middle;" v-b-tooltip.hover :title="thing.tooltip">
              <div :style="`display:inline-block; width: ${thumbwidth}px;`" class="mr-2">
                <img v-if="thing.image.thumb" style="display:inline-block; max-width:100%;" :src="'/storage/' + thing.image.thumb.name" alt="Sånn ser den ut">
                <img v-else style="display:inline-block; max-width:100%;" :src="'/images/placeholder.png'" alt="Sånn ser den ikke ut">
              </div>
              {{ thing.name }}
            </a>
          </td>

          <td class="text-center">
            {{ thing.properties.loan_time }}
          </td>

          <td class="text-center">
            <span v-if="thing.library_settings.reminders" class="text-success">
              <i class="far fa-check-circle"></i>
              Aktivert
            </span>
          </td>

          <td class="text-center">
            <span v-if="thing.library_settings.loans_without_barcode" class="text-success">
              <i class="far fa-check-circle"></i>
              Aktivert
            </span>
          </td>

          <td class="text-center">
            {{ thing.avail_mine}}
            /
            {{ thing.items_mine}}
          </td>

          <td class="text-center">
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
              thumbwidth: process.env.MIX_THUMBNAIL_WIDTH/4,
              thumbheight: process.env.MIX_THUMBNAIL_HEIGHT/4,
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
