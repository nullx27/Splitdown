var Splitdown;

Splitdown = {
    source: '',
    destination: '',
    converter: '',

    init: function () {
        Splitdown.source = jQuery( '#content' );
        Splitdown.destination = jQuery( '#splitdown-preview' );

        // Check to see if the source and destination exist
        if (!Splitdown.source.length || !Splitdown.destination.length) {
            return;
        }

        Splitdown.converter = new Showdown.converter();
 
        this.source.on( 'keyup', this.update );

        jQuery( '#splitdown-dmode').on( 'click', this.dmode );

        jQuery(document).on(screenfull.raw.fullscreenchange, this.dmodeChange );

        //only convert html to markdown when there is a preview but no markdown
        if( this.source.text().length == 0 && this.destination.html() != 0 ){
             this.source.val( html2markdown( this.destination.html() ) );
        }

        jQuery(document).ajaxSuccess( this.ajaxIntercept );

        // Update at the start to generate the HTML (otherwise when we save after
        // no changes, the_content() will be blank!)
        this.update();

    },

    ajaxIntercept: function( event, xhr, settings){
        // we need to parse the data string form the ajax settings...
        data = settings.data.replace( '#', ' ' ).split("&");

        var n = []
        jQuery.each( data, function( k, v ){
            tmp = v.split( '=' );
            n[  decodeURIComponent( tmp[0] )] =  decodeURIComponent( tmp[1]).replace( /\+/g, ' ' );
        } );

        if( n[ 'action' ] == 'send-attachment-to-editor' ){
            var url = n['html'].replace( /src/g, "src=\"" + n['attachment[url]'] + "\"" );

            // we need this chuck of code for inserting the image at the cursor
            // taken form http://stackoverflow.com/questions/11076975/insert-text-into-textarea-at-cursor-position-javascript
            var cursorPos = Splitdown.source.prop('selectionStart');
            var v = Splitdown.source.val();
            var textBefore = v.substring(0,  cursorPos );
            var textAfter  = v.substring( cursorPos, v.length );

            Splitdown.source.val( textBefore+ html2markdown( url ) +textAfter );
            Splitdown.update();
        }
    },

    update: function () {
        Splitdown.destination.html(
            Splitdown.converter.makeHtml(
                Splitdown.source.val()
            )
        );

        jQuery( '#splitdown-markdown').val( Splitdown.destination.html() );
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