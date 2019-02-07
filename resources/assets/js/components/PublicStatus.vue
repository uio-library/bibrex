<i18n>
    {
        "nob": {
            "things": "Ting til utlån",
            "loading": "Henter data...",
            "crashed": "Beklager, Bibrex kræsja!",
            "no_things_found": "Ingen ting ble funnet",
            "n_things": "{0} ting",
            "items_available": "{0} av {1} tilgjengelig"
        },
        "nno": {
            "things": "Ting til utlån",
            "loading": "Henter data...",
            "crashed": "Beklager, Bibrex kræsja!",
            "no_things_found": "Ingen ting ble funnet",
            "n_things": "{0} ting",
            "items_available": "{0} av {1} tilgjengelig"
        },
        "eng": {
            "things": "Things for loan",
            "loading": "Fetching data...",
            "crashed": "Sorry, Bibrex crashed!",
            "no_things_found": "No things found",
            "n_things": "{0} things",
            "items_available": "{0} of {1} available"
        }
    }
</i18n>
<template>
    <div class="container-fluid">
        <h1>{{ $t('things') }}
        <span v-if="library">@ {{ library.name[lang] }}</span>
        </h1>

        <div v-if="loading">
            <h3>{{ $t('loading') }}</h3>
        </div>
        <div v-else-if="err">
            <h3>{{ $t('crashed') }}</h3>
        </div>
        <div v-else>
            <div v-if="!things.length">{{ $t('no_things_found') }}</div>
            <div class="card-columns">
                <div class="card p-3" v-for="thing in things">
                    <div class="card-block">
                        <h4 class="card-title">{{ thing.name[lang] }}</h4>

                        <div class="media text-muted" v-for="(items, library_id) in thing.items">

                            <span v-if="!items.filter(item => item.available).length" class="mr-2 rounded bg-danger" style="width: 32px; height: 22px;"></span>

                            <span v-else class="mr-2 rounded bg-success" style="width: 32px; height: 22px;"></span>

                            <p v-if="library">
                                <strong class="d-block text-gray-dark">{{ $t('items_available', [items.filter(item => item.available).length, items.length] ) }}</strong>
                            </p>
                            <p class="small" v-else>
                                <strong class="d-block text-gray-dark">{{ libraries[library_id].name[lang] }}</strong>
                                {{ $t('items_available', [items.filter(item => item.available).length, items.length] ) }}
                            </p>                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style>

</style>
<script>
    import axios from 'axios';
    import { groupBy } from 'lodash/collection';
    import { get } from 'lodash/object';

    import qs from 'qs';

    export default {
        data: function() {
            return {
                lang: 'nob',
                library: null,
                err: false,
                loading: true,
                things: [],
            };
        },
        methods: {
        },
        mounted() {
            const req = qs.parse(document.location.search.substring(1));
            this.lang = req.lang || 'nob';
            this.$i18n.locale = this.lang;
            let params = qs.stringify({
                items: 'true',
                library: req.library,
            });
            axios.get('/api/things?' + params)
                .then(response => {
                    this.loading = false;
                    this.libraries = {};
                    this.things = response.data.data.map(thing => {
                        thing.items = thing.items.filter(item => item.library);
                        if (req.library) {
                            thing.items = thing.items.filter(item => item.library.id == req.library);
                        }
                        thing.items = groupBy(thing.items, item => {
                            if (!this.libraries[item.library.id]) {
                                this.libraries[item.library.id] = item.library;
                            }
                            return item.library.id
                        });
                        return thing;
                    }).filter(thing => Object.keys(thing.items).length);
                    console.log(`Found ${this.things.length} things`);
                    if (req.library) {
                        this.library = this.libraries[req.library];
                    }
                })
                .catch(error => {
                    console.error(error);
                    this.loading = false;
                    this.err = true
                });
        },
    }
</script>