'use strict';

( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		$( '.fsp-modal-footer .share_btn' ).click( function () {
			var nodes = [];
			$( '.fsp-modal-body input[name=\'share_on_nodes[]\']' ).each( function () {
				nodes.push( $( this ).val() );
			} );

			if ( nodes.length == 0 )
			{
				FSPoster.toast( fsp__( 'No selected account!' ), 'warning' );
				return;
			}

			let background = $( '#background_share_chckbx' ).is( ':checked' ) ? 1 : 0;

			let custom_messages = {};
			$( '#fspMetaboxCustomMessages textarea[name]' ).each( function () {
				custom_messages[ $( this ).attr( 'name' ).replace( 'fs_post_text_message_', '' ) ] = $( this ).val();
			} );

			let instagramPin = $( '#instagram_pin_post' ).is( ':checked' ) ? 1 : 0;

			FSPoster.ajax( 'share_saved_post', {
				'post_id': FSPObject.postID,
				'nodes': nodes,
				'background': background,
				'custom_messages': custom_messages,
				'shared_from': 'manual_share',
				'instagram_pin_the_post': instagramPin
			}, function () {
				$( '[data-modal-close=true]' ).click();

				if ( background )
				{
					FSPoster.toast( fsp__( 'The post will be shared in the background!' ), 'info' );
				}
				else
				{
					FSPoster.loadModal( 'share_feeds', { 'post_id': FSPObject.postID }, true );
				}
			} );
		} );
	} );
} )( jQuery );