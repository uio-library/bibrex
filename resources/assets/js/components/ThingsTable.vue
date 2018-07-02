<template>
  <div>
    <p>
      {{error}}
    </p>

    <table style="width:100%" class="table">
      <thead>
        <tr>
          <th>Ting</th>
          <th>Aktiv?</th>
          <th>Strekkode?</th>
          <th>Purres?</th>
        </tr>
      </thead>
      <tbody>

        <tr v-for="thing in things">

          <td>
            <a :href="'/things/' + thing.id">{{ thing.name }}</a>
          </td>

          <td>
             <toggle-button
                v-b-tooltip.hover title="Kan denne tingen lånes ut i mitt bibliotek?"
                :value="thing.at_my_library"
                :color="'#82C7EB'"
                :sync="true"
                :labels="{checked: 'På', unchecked: 'Av'}"
                :width="60"
                :height="30"
                @change="ev => onToggle(thing, ev.value)"
              />
          </td>

          <td>
            <div v-if="thing.at_my_library">
              <toggle-button
                v-b-tooltip.hover title="Skal det alltid lånes ut et bestemt eksemplar med strekkode?"
                :value="thing.library_settings.require_item"
                :color="'#82C7EB'"
                :sync="true"
                :labels="{checked: 'På', unchecked: 'Av'}"
                :width="60"
                :height="30"
                @change="ev => onUpdateSetting(thing, 'require_item', ev.value)"
              />
            </div>
          </td>

          <td>
            <div v-if="thing.at_my_library">
              <toggle-button
                v-b-tooltip.hover title="Skal det sendes purringer for denne tingen?"
                :value="thing.library_settings.send_reminders"
                :color="'#82C7EB'"
                :sync="true"
                :labels="{checked: 'På', unchecked: 'Av'}"
                :width="60"
                :height="30"
                @change="ev => onUpdateSetting(thing, 'send_reminders', ev.value)"
              />
            </div>
          </td>
        </tr>

      </tbody>
    </table>
  </div>
</template>

<script>
  import axios from 'axios';

  export default {
      data: () => {
          return {
              error: '',
              things: [],
          };
      },
      methods: {
        onToggle(thing, value) {
          axios.post(`/things/toggle/${thing.id}`, {value: value})
          .then((resp) => {
              thing.at_my_library = value;
              thing.library_settings = resp.data.library_settings ;
          });

        },
        onUpdateSetting(thing, key, value) {
          axios.post(`/things/settings/${thing.id}`, {key: key, value: value})
          .then((resp) => {
              console.log(resp.data.library_settings);
              thing.library_settings = resp.data.library_settings ;
          });
        }
      },
      mounted() {
        this.error = '';
        axios.get('/things')
        .then(res => {
          this.things = res.data;
        })
        .catch(err => {
          this.things = [];
          this.error = err;
        });
      },
  }
</script>
