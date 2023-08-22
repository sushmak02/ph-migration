(function( $ ) {

    $(document).ready(function() {
        $button_wrap = $('.migrate-to-surecart');
        $button_wrap_text = $('.migrate-to-surecart a');
        /* Live Preview Button */
		if( $button_wrap.length ) {
            $button_wrap.click(function( event ) {

                event.preventDefault();

                var data = {
                    'email'  : "dummy@bsf.io",
                    'name'   : "Dummy",
                };

                // var redirect_url = "https://projecthuddle.com";

                $.post( phMigrationScript.ajaxurl, {
                    action: 'ph_migrate_button',
                    data: data,
                    nonce: phMigrationScript.nonce,
                    method: 'post',
                    dataType: 'json',
                    beforeSend: function () {

                        $button_wrap.animate({
                            opacity: '0.45'
                        }, 500 ).addClass( 'ph-form-waiting' );

                        if( ! $button_wrap_text.hasClass( 'disabled' ) ) {
                            $button_wrap_text.addClass( 'disabled' );
                            $button_wrap_text.append( '<span class="ph-migrate-loader"></span>' );
                        }
                    },
                }, function ( response ) {

                    $button_wrap.animate({
                        opacity: '1'
                    }, 100 ).removeClass( 'ph-form-waiting' );

                    $button_wrap_text.find( '.ph-migrate-loader' ).remove();
                    $button_wrap_text.removeClass( 'disabled' );

                    if ( true === response.success ) {
                        // $scope.find( '.uael-register-field-message' ).remove();
                        // window.location = redirect_url;
                    }

                });


            });
        }
    });
})( jQuery );