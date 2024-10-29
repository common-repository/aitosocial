<?php

namespace FSPoster\App\Pages\Share\Views;

use FSPoster\App\Providers\Date;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'ABSPATH' ) or exit;

global $aits_fs;
$fsp_params = Pages::action( 'Share', 'get_share' );
wp_enqueue_media();
?>
<div class="fsp-col-12 aits-popup-hrd">
	<div class="fsp-modal-header">
		<div class="fsp-modal-title">
			<div class="fsp-modal-title-icon">
				<i class="fas fa-plus"></i>
			</div>
			<div class="fsp-modal-title-text"><?php echo fsp__( 'Calendar' ); ?></div>
		</div>
		<div class="fsp-modal-close2">
			<i class="fas fa-times"></i>
		</div>
	</div>
</div>
<div class="fsp-modal-body">
<div class="fsp-row">
	<div class="fsp-col-12 fsp-col-lg-6 fsp-share-leftcol">
		<div class="fsp-card">
			<div class="fsp-card-body">
				<div class="fsp-form-group">
					<div class="fsp-form-input-has-icon">
						<i class="far fa-question-circle fsp-tooltip" data-title="<?php echo fsp__( 'Optional field to enter a name for the post if you are going to save the post.' ); ?>"></i>
						<input id="fspPostTitle" autocomplete="off" type="text" class="fsp-form-input" placeholder="<?php echo fsp__( 'Untitled' ); ?> " value="<?php echo esc_html( $fsp_params[ 'title' ] ); ?>">
					</div>
				</div>
				<div class="fsp-form-group">
					<div id="wpMediaBtn" class="fsp-form-image <?php echo $fsp_params[ 'imageId' ] > 0 ? 'fsp-hide' : ''; ?>">
						<i class="fas fa-camera"></i>
					</div>
					<div class="fsp-direct-share-images">
						<?php
						if ( ! empty( $fsp_params[ 'images' ] ) )
						{
							foreach ( $fsp_params[ 'images' ] as $image )
							{
								?>
								<div class="fsp-direct-share-form-image-preview" data-id="<?php echo $image[ 'id' ]; ?>">
									<img src="<?php echo esc_html( $image[ 'url' ] ); ?>">
									<i class="fas fa-times fsp-direct-share-close-img"></i>
								</div>
							<?php }
						} ?>
					</div>
				</div>
				<div id="fspShareURL" class="fsp-form-group">
					<label><?php echo fsp__( 'Link' ); ?></label>
					<div class="fsp-form-input-has-icon">
						<i class="far fa-question-circle fsp-tooltip fsp-tooltip-is-info <?php echo ( ! empty( $fsp_params[ 'images' ] ) && count( $fsp_params[ 'images' ] ) > 0 ? '' : 'fsp-hide'); ?>" data-title="<?php echo fsp__( 'This is an image post. The link will be used as a backlink for supporting social networks (story link, image source etc.). To make a linkcard post you may remove the images from this post.' ); ?>"></i>
						<input autocomplete="off" type="text" class="fsp-form-input link_url" placeholder="<?php echo fsp__( 'Example: https://example.com' ); ?> " value="<?php echo esc_html( $fsp_params[ 'link' ] ); ?>">
					</div>
				</div>
				<div class="fsp-custom-messages-container">
					<div class="fsp-form-group">
						<div class="fsp-custom-messages-tabs">
							<div data-tab="default" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Default custom message<br>for all social networks', [], FALSE ); ?>">
								<i class="fas fa-grip-horizontal"></i>
							</div>
							<div data-tab="fb" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Facebook' ); ?>">
								<i class="fab fa-facebook-f"></i>
							</div>
							<?php if ( $aits_fs->is_plan('pro', true) ) { ?>
							<div data-tab="instagram" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Instagram' ); ?>">
								<i class="fab fa-instagram"></i>
							</div>
                            <div data-tab="threads" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Threads' ); ?>">
                                <i class="threads-icon threads-icon-12"></i>
                            </div>
							<div data-tab="twitter" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Twitter' ); ?>">
								<i class="fab fa-twitter"></i>
							</div>
							<div data-tab="planly" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Planly' ); ?>">
								<i class="planly-icon planly-icon-12"></i>
							</div>
							<div data-tab="linkedin" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Linkedin' ); ?>">
								<i class="fab fa-linkedin-in"></i>
							</div>
							<div data-tab="pinterest" class="fsp-custom-messages-tab fsp-tooltip fsp-temp-tooltip" data-title="<?php echo fsp__( 'Custom message for Pinterest' ); ?>">
								<i class="fab fa-pinterest-p"></i>
							</div>
							<?php };?>
						</div>
						
						<div id="fspCustomMessages" class="fsp-custom-messages">
							<?php foreach ( $fsp_params[ 'sn_list' ] as $sn ) { ?>
								<div data-driver="<?php echo $sn; ?>">
									<?php if ( $sn == 'instagram' ) { ?>
										<div class="fsp-form-checkbox-group">
											<input id="instagram_pin_post" type="checkbox" class="fsp-form-checkbox" <?php echo( $fsp_params[ 'instagramPinThePost' ] === 1 ? 'checked' : '' ) ?>>
											<label for="instagram_pin_post">
												<?php echo fsp__( 'Pin the post' ); ?>
											</label>
										</div>
									<?php } ?>
									<div class="fsp-custom-post">
										<div class="fsp-custom-post-counter">
											<span data-character-counter="<?php echo $sn; ?>">0</span><?php echo fsp__( ' chars.' ); ?>
										</div>
										<textarea data-sn-id="<?php echo $sn; ?>" name="fs_post_text_message_<?php echo $sn; ?>" class="fsp-form-textarea message_box" rows="4" placeholder="<?php echo fsp__( 'Enter the custom post message' ); ?>"><?php echo esc_html( $fsp_params[ 'message' ][ $sn ] ); ?></textarea>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<div class="fsp-card-footer">
				<button type="button" class="fsp-button shareNowBtn"><?php echo fsp__( 'SHARE NOW' ); ?></button>
				<button type="button" class="fsp-button fsp-is-info schedule_button"><?php echo fsp__( 'SCHEDULE' ); ?></button>
			</div>
		</div>
	</div>
	<div class="fsp-col-12 fsp-col-lg-6 fsp-share-rightcol">
		<?php Pages::controller( 'Base', 'MetaBox', 'post_meta_box', [
			'post_id' => $fsp_params[ 'post_id' ]
		] ); ?>
	</div>
</div>

<script>
	FSPObject.saveID = <?php echo (int) $fsp_params[ 'post_id' ]; ?>;
	FSPObject.scheduleDate = "<?php echo $_POST[ 'schedule_date' ]; ?>";
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo  Pages::asset( 'Base', 'js/fsp.js' ); ?>' );
		FSPoster.load_script( '<?php echo  Pages::asset( 'Share', 'js/fsp-share.js' ); ?>' );
	} );
</script>
</div>