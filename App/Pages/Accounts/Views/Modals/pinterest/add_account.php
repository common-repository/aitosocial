<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'MODAL' ) or exit;
?>

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fab fa-pinterest-p"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Add a Pinterest account' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<p class="fsp-modal-p fsp-is-jb">
		<?php echo fsp__( 'Select an authorization method to add a new Pinterest account' ); ?>
		<a href="https://project.puredevs.com/documentation/fs-poster-schedule-auto-publish-wordpress-posts-to-pinterest" target="_blank" class="fsp-tooltip" data-title="<?php echo fsp__( 'How to?' ); ?>">
			<i class="far fa-question-circle"></i>
		</a>
	</p>
	<!--<div class="fsp-modal-options">
        <div class="fsp-modal-option fsp-is-selected" data-step="1">
            <div class="fsp-modal-option-image">
                <img src="<?php echo Pages::asset( 'Accounts', 'img/android.svg' ); ?>">
            </div>
			<?php echo fsp__( 'App method' ); ?>
        </div>
		<div class="fsp-modal-option" data-step="2">
			<div class="fsp-modal-option-image">
				<img src="<?php echo Pages::asset( 'Accounts', 'img/rocket.svg' ); ?>">
			</div>
			<?php echo fsp__( 'Cookie method' ); ?>
		</div>
	</div>-->
	<div id="fspModalStep_1" class="fsp-modal-step">
        <div class="fsp-form-checkbox-group">
            <input id="fspUseCustomApp" type="checkbox" class="fsp-form-checkbox">
            <label for="fspUseCustomApp">
				<?php echo fsp__( 'Use a custom App' ); ?>
            </label>
            <span class="fsp-tooltip" data-title="<?php echo fsp__( 'Check the option to select an App that was created by you. Otherwise, the Standard App will be used to add the account to the plugin.' ); ?>"><i class="far fa-question-circle"></i></span>
        </div>
        <div id="fspCustomAppContainer" class="fsp-form-group fsp-hide">
            <label><?php echo fsp__( 'Select an App' ); ?></label>
			<select class="fsp-form-select" id="fspModalAppSelector">
				<?php foreach ( $fsp_params[ 'applications' ] as $app ) { ?>
					<option value="<?php echo $app[ 'id' ]; ?>"><?php echo esc_html( $app[ 'name' ] ); ?></option>
				<?php }
				if ( empty( $fsp_params[ 'applications' ] ) )
				{ ?>
					<option disabled><?php echo fsp__( 'There isn\'t a Pinterest App!' ); ?></option>
				<?php } ?>
			</select>
		</div>
	</div>
	<div id="fspModalStep_2" class="fsp-modal-step">
		<div class="fsp-form-checkbox-group">
            <input id="fspUseCookieMethod" type="checkbox" class="fsp-form-checkbox">
            <label for="fspUseCookieMethod">
                <?php echo fsp__( 'Cookie method' ); ?>
            </label>
        </div>
		<div id="fspCookieMethodContainer" class="fsp-form-group fsp-hide">
			<div class="fsp-form-group">
				<label><?php echo fsp__( 'Enter the cookie' ); ?> _pinterest_sess</label>
				<div class="fsp-form-input-has-icon">
					<i class="far fa-copy"></i>
					<input id="fspCookie_sess" autocomplete="off" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the cookie' ); ?> _pinterest_sess">
				</div>
			</div>
		</div>
	</div>
	<div class="fsp-form-checkbox-group">
		<input id="fspUseProxy" type="checkbox" class="fsp-form-checkbox">
		<label for="fspUseProxy">
			<?php echo fsp__( 'Use a proxy' ); ?>
		</label>
		<span class="fsp-tooltip" data-title="<?php echo fsp__( 'Optional field. Supported proxy formats: https://127.0.0.1:8888 or https://user:pass@127.0.0.1:8888' ); ?>"><i class="far fa-question-circle"></i></span>
	</div>
	<div id="fspProxyContainer" class="fsp-form-group fsp-hide fsp-proxy-container">
		<div class="fsp-form-input-has-icon">
			<i class="fas fa-globe"></i>
			<input id="fspProxy" autocomplete="off" class="fsp-form-input fsp-proxy" placeholder="<?php echo fsp__( 'Enter a proxy address' ); ?>">
		</div>
	</div>
</div>
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Cancel' ); ?></button>
	<button id="fspModalAddButton" class="fsp-button"><?php echo fsp__( 'ADD' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-accounts-pinterest.js' ); ?>' );
	} );
</script>
