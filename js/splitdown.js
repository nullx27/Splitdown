var Splitdown;

Splitdown = {
    source: '',
    desination: '',
    converter: '',

    init: function () {
        Splitdown.source = jQuery('#content');
        Splitdown.desination = jQuery('#splitdown-preview');

        Splitdown.converter = new Showdown.converter();

        this.source.on('keyup', this.update);

        jQuery( '.splitdown-help').on( 'click', this.help );
    },

    update: function () {
        Splitdown.desination.html(
            Splitdown.converter.makeHtml(
                Splitdown.source.val()
            )
        );

        jQuery( '#splitdown-markdown').val( Splitdown.desination.html() );
    },

    help: function(){
        jQuery( '.splitdown-helpbox' ).dialog(
            {   modal: true,
                closeOnEscape: true,
            }
        );


    }
};

jQuery( 'document' ).ready(function(){
    Splitdown.init();
});