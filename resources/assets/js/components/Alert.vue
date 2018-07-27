<template>
    <div class="alert" :class="cssClasses" v-show="visible">
        <button v-if="closable" type="button" class="close" @click="close()">&times;</button>
        <slot/>
    </div>
</template>
<script>
export default {
    props: {
        closable: {
            type: Boolean,
            default: true,
        },
        variant: {
            type: String,
            default: 'info',
        },
    },
    data: function() {
        return {
            visible: true,
            cssClasses: [],
        };
    },
    methods: {
        close: function() {
            this.visible = false;
            this.$emit('close');
        }
    },
    mounted() {
        this.cssClasses.push(`alert-${this.variant}`);
        setTimeout(() => this.cssClasses.push('visible'));
    },
}
</script>