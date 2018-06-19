<template>
  <div>
    <p>
      {{error}}
    </p>
    <ul class="list-group list-group-flush">

        <li class="row p-3">
            <div class="col">
                Ting
            </div>
            <div class="col col-sm-2 mx-1">
                Aktiv?
            </div>
            <div class="col col-sm-2 mr-3">
                Krev eksemplar?
            </div>
        </li>
      <li v-for="thing in things"
        class="list-group-item" style="display:flex; color: #999">

        <div style="flex: 1 0 auto">
          <a :href="'/things/' + thing.id">{{ thing.name }}</a>

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

        </div>

        <div class="col col-sm-2 mx-1">
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
        </div>

        <div v-if="thing.at_my_library" class="col col-sm-2 mr-3">
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
        <div v-else class="col col-sm-2 mr-3">

        </div>

      </li>
    </ul>
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
