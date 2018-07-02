<template>
    <button type="submit" class="btn btn-success" :disabled="busy" @click="$emit('click', $event)">
        <div v-show="busy" class="lds-ellipsis spinner"><div></div><div></div><div></div><div></div></div>
        <span v-show="!busy">
            <slot/>
        </span>
    </button>
</template>
<script>
export default {
    props: {
        busy: {
            type: Boolean,
            default: false,
        },
    }
}
</script>
