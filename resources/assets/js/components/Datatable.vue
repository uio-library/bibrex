<template>
    <table style="width:100%" class="table" v-once>
        <slot/>
    </table>
</template>
<script>
export default {
    props: {
        sortOrder: {
            type: Array,
        },
    },
    mounted() {
        console.log('Mounting table', new Date());
        var $hl = $(this.$el).find('.highlight');
        setTimeout(() => {
            $hl.addClass('highlight-active');
        });
        setTimeout(() => {
              $hl.removeClass('highlight-active');
        }, 3000);


        $(this.$el).DataTable({
            order: this.sortOrder,
            paging: false,
            info: false,

            // Source: https://datatables.net/plug-ins/i18n/Norwegian-Bokmal
            language: {
                "sEmptyTable": "Ingen data tilgjengelig i tabellen",
                "sInfo": "Viser _START_ til _END_ av _TOTAL_ linjer",
                "sInfoEmpty": "Viser 0 til 0 av 0 linjer",
                "sInfoFiltered": "(filtrert fra _MAX_ totalt antall linjer)",
                "sInfoPostFix": "",
                "sInfoThousands": " ",
                "sLoadingRecords": "Laster...",
                "sLengthMenu": "Vis _MENU_ eksemplarer",
                "sLoadingRecords": "Laster...",
                "sProcessing": "Laster...",
                "sSearch": "S&oslash;k:",
                "sUrl": "",
                "sZeroRecords": "Ingen linjer matcher s&oslash;ket",
                "oPaginate": {
                    "sFirst": "F&oslash;rste",
                    "sPrevious": "Forrige",
                    "sNext": "Neste",
                    "sLast": "Siste"
                },
                "oAria": {
                    "sSortAscending": ": aktiver for å sortere kolonnen stigende",
                    "sSortDescending": ": aktiver for å sortere kolonnen synkende"
                }
            },
        });
    },
    beforeDestroy: function() {
        console.log('Destroying table');
        $(this.$el).DataTable().destroy();
    }
}
</script>