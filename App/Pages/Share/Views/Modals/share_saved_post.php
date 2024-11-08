<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'MODAL' ) or exit;
?>

<link rel="stylesheet" href="<?php echo Pages::asset( 'Share', 'css/fsp-share-popup.css' ); ?>">

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fas fa-share"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Share' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<div class="fsp-form-checkbox-group">
		<input id="background_share_chckbx" type="checkbox" class="fsp-form-checkbox" <?php echo Helper::getOption( 'share_on_background', '1' ) == 1 ? 'checked' : ''; ?>>
		<label for="background_share_chckbx">
			<?php echo fsp__( 'Share in the background' ); ?>
		</label>
	</div>
	<div class="fsp-form-group">
		<?php
		$post_id = (int) $fsp_params[ 'parameters' ][ 'post_id' ];
		define( 'NOT_CHECK_SP', 'true' );

		Pages::controller( 'Base', 'MetaBox', 'post_meta_box', [
			'post_id'                => $post_id,
			'minified_metabox'       => TRUE,
            'instagram_pin_the_post' => 0 //default for posts column
		] );
		?>
	</div>
</div>
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'CANCEL' ); ?></button>
	<button class="fsp-button share_btn"><?php echo fsp__( 'SHARE' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Share', 'js/fsp-share-popup.js' ); ?>' );
	} );

	FSPObject.postID = '<?php echo (int) $fsp_params[ 'parameters' ][ 'post_id' ]; ?>';
</script>
