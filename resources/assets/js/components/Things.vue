<template>
  <div>
    <p>
      {{error}}
    </p>

    <table style="width:100%" class="table">
      <thead>
        <tr>
          <th>Ting</th>
          <th>Purrenavn</th>
          <th>Aktiv?</th>
          <th>Krev eks.?</th>
        </tr>
      </thead>
      <tbody>

        <tr v-for="thing in things">

          <td>
            <a :href="'/things/' + thing.id">{{ thing.name }}</a>
          </td>

          <td>
             <span v-if="thing.send_reminders">
                (<strong>nob</strong>: {{ thing.email_name_nob }} / {{ thing.email_name_definite_nob }},
                <strong>eng</strong>: {{ thing.email_name_eng}} / {{ thing.email_name_definite_eng }})
            </span>
            <span v-else>
                (Purres ikke)
            </span>
              <p class="text-danger" v-if="thing.disabled">
                  Nye utlån tillates ikke
              </p>
          </td>

          <td>
             <toggle-button
                v-b-tooltip.hover title="Skal denne tingen lånes ut i mitt bibliotek?"
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
                v-b-tooltip.hover title="Krev strekkode"
                :value="thing.library_settings.require_item"
                :color="'#82C7EB'"
                :sync="true"
                :labels="{checked: 'På', unchecked: 'Av'}"
                :width="60"
                :height="30"
                @change="ev => onToggleBarcode(thing, ev.value)"
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
          axios.post(`/things/toggle/${thing.id}`, {value: value});
          thing.at_my_library = value;
        },
        onToggleBarcode(thing, value) {
          axios.post(`/things/toggle-require-item/${thing.id}`, {value: value});
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
