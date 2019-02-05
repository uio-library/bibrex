<template>
    <div class="container-fluid">

        <div v-if="loading">
            <h3>Tøyeblikk...</h3>
        </div>
        <div v-else>
            <h3>{{ things.length }} ting</h3>
            <div class="d-flex flex-row flex-wrap">

                <div class="p-2" v-for="thing in things">

                    <div class="my-3 p-3 bg-white rounded shadow-sm"">

                        <h6 class="border-bottom border-gray pb-2 mb-0">{{ thing.name }}</h6>

                        <div class="media text-muted pt-3" v-for="(items, library) in thing.items">

                            <span v-if="items.filter(item => item.available).length == 0" class="mr-2 rounded bg-danger" style="width: 32px; height: 32px;"></span>

                            <span v-else class="mr-2 rounded bg-success" style="width: 32px; height: 32px;"></span>

                            <p class="media-body pb-3 mb-0 small lh-125 border-bottom border-gray">
                                <strong class="d-block text-gray-dark">{{ library }}</strong>
                                {{ items.filter(item => item.available).length }} av {{ items.length }} tilgjengelig.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import { groupBy } from 'lodash/collection';
    import { get } from 'lodash/object';

    export default {
        data: function() {
            return {
                loading: true,
                things: [],
            };
        },
        methods: {
        },
        mounted() {
            axios.get('/api/things?items=true')
                .then(response => {
                    this.loading = false;
                    this.things = response.data.data.map(thing => {
                        thing.items = groupBy(
                            (get(thing, 'items') || []).filter(item => item.library),
                            item => item.library.name
                        );
                        return thing;
                    }).filter(thing => Object.keys(thing.items).length);
                    console.log(`Found ${this.things.length} things`);
                })
                .catch(error => {
                });
        },
    }
</script>