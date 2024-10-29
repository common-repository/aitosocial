'use strict';

( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		doc.on( 'click', '#fspUseCookieMethod', function () {
			let checked = ! ( $( this ).is( ':checked' ) );
			$( '#fspCookieMethodContainer' ).toggleClass( 'fsp-hide', checked );
		} );
		doc.on( 'click', '#fspUseLogPassMethod', function () {
			let checked = ! ( $( this ).is( ':checked' ) );
			$( '#fspLoginPasswordContainer' ).toggleClass( 'fsp-hide', checked );
		} );
		doc.on( 'click', '.fsp-checkbox-option', function () {
			let _this = $( this );
			let step = _this.data( 'step' );

			$( '.fsp-checkbox-option.fsp-is-selected' ).removeClass( 'fsp-is-selected' );
			_this.addClass( 'fsp-is-selected' );

			/*if ( step )
			{
				if ( $( `#fspModalStep_${ step }` ).length )
				{
					$( '.fsp-modal-step' ).addClass( 'fsp-hide' );
					$( `#fspModalStep_${ step }` ).removeClass( 'fsp-hide' );
				}
			}*/
		} );
	} );
} )( jQuery );