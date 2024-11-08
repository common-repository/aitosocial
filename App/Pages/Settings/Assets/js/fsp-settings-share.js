( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		$( '#fspSaveSettings' ).on( 'click', function () {
			let data = FSPoster.serialize( $( '#fspSettingsForm' ) );

			FSPoster.ajax( 'settings_share_save', data, function ( res ) {
				FSPoster.toast( res[ 'msg' ], 'success');
			} );
		} );

		$( '#fs_share_on_background' ).on( 'change', function () {
			if ( $( this ).is( ':checked' ) )
			{
				$( '#fspSharingTimerRow' ).slideDown();
			}
			else
			{
				$( '#fspSharingTimerRow' ).slideUp();
			}
		} ).trigger( 'change' );

		$( '#fspInterval' ).on( 'change', function () {
			if ( $( this ).val() > 0 )
			{
				$( '#fspIntervalLimit' ).slideDown();
			}
			else
			{
				$( '#fspIntervalLimit' ).slideUp();
			}
		} ).trigger( 'change' );
	} );

	$( ".select2-init" ).select2();
} )( jQuery );