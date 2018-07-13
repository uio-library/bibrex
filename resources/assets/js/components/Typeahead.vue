<template>
    <div ref="container" style="position: relative">
        <input type="hidden" :name="name + '_id'" :value="selectedId">
        <input type="text"
               ref="textinput"
               autocomplete="off"
               :name="name"
               :placeholder="placeholder"
               class="form-control typeahead"
               style="display: block"
               :tabindex="tabindex">
    </div>
</template>

<script>

    import Bloodhound from 'corejs-typeahead'
    //import typeahead from 'corejs-typeahead/dist/typeahead.jquery'

    //import Handlebars from "handlebars";
    //import Handlebars from 'handlebars/dist/handlebars.min.js';

    export default {
        props: {
            name: {
                type: String
            },
            placeholder: {
                type: String,
                default: 'Hvilken ting?',
            },
            tabindex: {
                type: Number
            },
            prefetch: {
                type: String
            },
            remote: {
                type: String
            },
            minLength: {
              type: Number,
              default: 4
            },
            limit: {
              type: Number,
              default: 5
            },
            alma: {
              type: Boolean,
              default: false
            },
            value: {
                type: Object,
                default: {},
            },
        },
        data: () => {
            return {
                selectedId: '',
                waitingTimer: null,
                waitingSince: null,
            };
        },
        methods: {
            setValue(value) {
                this.$emit('input', value);
            },
            focusNextElement () {
                let nextElement = document.querySelector(`*[tabindex="${this.tabindex + 1}"]`);
                setTimeout(_ => nextElement.focus());
            },

            stillWaiting () {
                if (!this.waitingSince) return;

                var dt = new Date().getTime() - this.waitingSince;

                var $el = $(this.$refs.container.querySelector('.tt-pending'));

                if (dt < 2000) {
                    $el.html('Venter på Alma... (' + Math.round(dt/1000) + ')');
                } else if (dt < 4000) {
                    $el.html('Venter fortsatt på Alma... (' + Math.round(dt/1000) + ')');
                } else if (dt < 6000) {
                    $el.html('Aaaaaalma, svar da! (' + Math.round(dt/1000) + ')');
                } else if (dt < 8000) {
                    $el.html('Et respektabelt API svarer innen 1 sekund... Nå har vi ventet ' + Math.round(dt/1000) ) ;
                }

                this.waitingTimer = setTimeout(this.stillWaiting.bind(this), 500);

            },

            init () {

              let options = {
                // prefetch: this.prefetch,
                sufficient: 5,
                indexRemote: false,
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('primaryId', 'name', 'barcode'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                identify: datum => datum.primaryId ? datum.primaryId : datum.id,
                prefetch: (this.prefetch ? {
                    url: this.prefetch,
                    cache: false
                  } : null),
                remote: (this.remote ? {
                    url: this.remote + '?query=%QUERY',
                    wildcard: '%QUERY',
                  } : null),
              };

              let hound = new Bloodhound(options);

              $(this.$refs.textinput).typeahead({
                minLength: this.minLength,
                // highlight: true,
              }, {
                name: this.name,
                source: (query, sync, async) => {
                  if (query === '') {
                    sync(hound.all());
                    async([]);
                  } else {
                    hound.search(query, sync, async);
                  }
                },
                limit: this.limit,
                display: item => item.name,
                templates: {
                  suggestion: d => {
                      if (d.primaryId) {
                          // Alma user
                          return `
                                <div>
                                    <span class="right">${d.primaryId}</span>
                                    <span class="main">${d.name} - ${d.group}</span>
                                </div>`;
                      }
                      if (d.group) {
                          // Thing?
                          return `<div>
                                <span class="right"><samp>${d.name}</samp></span>
                                <span class="main">${d.group}</span>
                             </div>`;

                      }
                      if (d.barcode) {
                          // Local user
                          return `<div>
                                <span class="right"><samp>${d.barcode}</samp></span>
                                <span class="main">${d.name}</span>
                             </div>`;

                      }
                      return `<div>
                            <span class="right">uten strekkode</span>
                            <span class="main">${d.name}</span>
                      </div>`;
                  },
                  notFound: '<div class="tt-empty"><span>No matches</span></div>',
                  pending: '<div class="tt-pending">Looking...</div>',
                }
              })
              .on('typeahead:asyncrequest', (u) => {
                  $(this.$refs.textinput).addClass('busy');
                  if (this.alma) {
                      this.waitingSince = new Date().getTime();
                      setTimeout(this.stillWaiting.bind(this));
                  }
              })
              .on('typeahead:asynccancel typeahead:asyncreceive', () => {
                  $(this.$refs.textinput).removeClass('busy');
                  clearTimeout(this.waitingTimer);
                  this.waitingSince = null;
              })
              .on('input', (ev) => {
                  this.selectedId = '';
                  this.setValue({
                      name: ev.currentTarget.value,
                  });
              })
              .on('change', (ev) => {
                  this.selectedId = '';
                  this.setValue({
                      name: ev.currentTarget.value,
                  });
              })
              .on('typeahead:select', (ev, datum) => {
                  this.selectedId = datum.id ? datum.id : datum.primaryId;
                  this.setValue({
                      type: datum.type,
                      id: this.selectedId,
                      name: datum.name,
                      barcode: datum.barcode,
                  });
                  this.focusNextElement();
              })
              .on('typeahead:autocomplete', (ev, datum) => {
                  this.selectedId = datum.id ? datum.id : datum.primaryId;
                  this.setValue({
                      type: datum.type,
                      id: this.selectedId,
                      name: datum.name,
                      barcode: datum.barcode,
                  });
                  this.focusNextElement();
              })
              ;
          }
        },
        mounted() {
            // We don't want to bind to the text input, since we're giving control of it to Typeahead,
            // but we want to control the initial value
            this.$refs.textinput.value = this.value ? this.value.name : '';

            // Initialize Typeahead in next tick
            Vue.nextTick(this.init.bind(this));
        }
    }
</script>
