var Splitdown;

Splitdown = {
    source: '',
    desination: '',
    converter: '',

    init: function () {
        Splitdown.source = jQuery( '#content' );
        Splitdown.desination = jQuery( '#splitdown-preview' );

        Splitdown.converter = new Showdown.converter();

        this.source.on( 'keyup', this.update );

        jQuery( '#splitdown-dmode').on( 'click', this.dmode );

        jQuery(document).on(screenfull.raw.fullscreenchange, this.dmodeChange );

        if( this.source.text().length == 0 ){
             this.source.val( html2markdown( this.desination.html() ) );
        }
    },

    update: function () {
        Splitdown.desination.html(
            Splitdown.converter.makeHtml(
                Splitdown.source.val()
            )
        );

        jQuery( '#splitdown-markdown').val( Splitdown.desination.html() );
    },



    dmode: function() {

        if (screenfull.enabled) {
            screenfull.toggle( jQuery( '#splitdown-wrapper' )[0] );

        }
    },

    dmodeChange: function(){

        if( screenfull.isFullscreen ){
            jQuery( '#splitdown-wrapper').css( 'height', document.body.offsetHeight +30 );
            jQuery( '#splitdown-preview').css( 'height', '100%' );
            jQuery( '#splitdown-editor').css( 'height', document.body.offsetHeight +30 );
        }
        else {
            jQuery( '#splitdown-preview').css( 'height', '500px' );
            jQuery( '#splitdown-wrapper').css( 'height', '100%' );
            jQuery( '#splitdown-editor').css( 'height', '100%' );
        }
    }
};

jQuery( 'document' ).ready(function(){
    Splitdown.init();
});