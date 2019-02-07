<template>
    <div class="container-fluid">

        <div v-if="loading">
            <h3>Tøyeblikk...</h3>
        </div>
        <div v-else>
            <h3>{{ things.length }} ting</h3>
            <div class="card-columns">
                <div class="card p-3" v-for="thing in things">
                    <div class="card-block">
                        <h4 class="card-title">{{ thing.name }}</h4>

                        <div class="media text-muted" v-for="(items, library) in thing.items">

                            <span v-if="!items.filter(item => item.available).length" class="mr-2 rounded bg-danger" style="width: 32px; height: 32px;"></span>

                            <span v-else class="mr-2 rounded bg-success" style="width: 32px; height: 32px;"></span>

                            <p class="small">
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
<style>

</style>
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
                        thing.items = thing.items.filter(item => item.library);
                        thing.items = groupBy(thing.items, item => item.library.name);
                        return thing;
                    }).filter(thing => Object.keys(thing.items).length);
                    console.log(`Found ${this.things.length} things`);
                })
                .catch(error => {
                });
        },
    }
</script>