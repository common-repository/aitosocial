<?php

namespace FSPoster\App\Pages\Settings\Views;

use FSPoster\App\Providers\Pages;

defined( 'ABSPATH' ) or exit;

// modified plugin code

global $aits_fs;
?>

<div class="fsp-row">
	<div class="fsp-col-12 fsp-title">
		<div class="fsp-title-text">
			<?php echo fsp__( 'Settings' ); ?>
		</div>
		<div class="fsp-title-button">
			<button id="fspSaveSettings" class="fsp-button">
				<i class="fas fa-check"></i>
				<span><?php echo fsp__( 'SAVE CHANGES' ); ?></span>
			</button>
		</div>
	</div>
	<div class="fsp-col-12 fsp-row">
		<div class="fsp-layout-left fsp-col-12 fsp-col-md-4 fsp-col-lg-3">
			<div class="fsp-card">
				<a href="?page=ai-poster-settings&setting=general" class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'general' ? 'fsp-is-active' : '' ); ?>">
					<div class="fsp-tab-title">
						<i class="fas fa-sliders-h fsp-tab-title-icon"></i>
						<span class="fsp-tab-title-text"><?php echo fsp__( 'General settings' ); ?></span>
					</div>
					<div class="fsp-tab-badges"></div>
				</a>
				<a href="?page=ai-poster-settings&setting=share" class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'share' ? 'fsp-is-active' : '' ); ?>">
					<div class="fsp-tab-title">
						<i class="fas fa-share-alt fsp-tab-title-icon"></i>
						<span class="fsp-tab-title-text"><?php echo fsp__( 'Publish settings' ); ?></span>
					</div>
					<div class="fsp-tab-badges"></div>
				</a>
				<a href="?page=ai-poster-settings&setting=url" class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'url' ? 'fsp-is-active' : '' ); ?>">
					<div class="fsp-tab-title">
						<i class="fas fa-link fsp-tab-title-icon"></i>
						<span class="fsp-tab-title-text"><?php echo fsp__( 'URL settings' ); ?></span>
					</div>
					<div class="fsp-tab-badges"></div>
				</a>
				<a href="?page=ai-poster-settings&setting=export_import" class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'export_import' ? 'fsp-is-active' : '' ); ?>">
					<div class="fsp-tab-title">
						<i class="fas fa-file-export fsp-tab-title-icon"></i>
						<span class="fsp-tab-title-text"><?php echo fsp__( 'Export & Import settings' ); ?></span>
					</div>
					<div class="fsp-tab-badges"></div>
				</a>
				<a href="?page=ai-poster-settings&setting=meta_tags" class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'export_import' ? 'fsp-is-active' : '' ); ?>">
					<div class="fsp-tab-title">
						<i class="fas fa-code fsp-tab-title-icon"></i>
						<span class="fsp-tab-title-text"><?php echo fsp__( 'Social media & meta tags' ); ?></span>
					</div>
					<div class="fsp-tab-badges"></div>
				</a>
				<a href="?page=ai-poster-settings&setting=facebook" class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'facebook' ? 'fsp-is-active' : '' ); ?>">
					<div class="fsp-tab-title">
						<i class="fab fa-facebook-f fsp-tab-title-icon"></i>
						<span class="fsp-tab-title-text"><?php echo fsp__( 'Facebook settings' ); ?></span>
					</div>
					<div class="fsp-tab-badges"></div>
				</a>
				<?php if ( $aits_fs->is_plan('pro', true) ) { ?>
				<a href="?page=ai-poster-settings&setting=instagram" class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'instagram' ? 'fsp-is-active' : '' ); ?>">
					<div class="fsp-tab-title">
						<i class="fab fa-instagram fsp-tab-title-icon"></i>
						<span class="fsp-tab-title-text"><?php echo fsp__( 'Instagram settings' ); ?></span>
					</div>
					<div class="fsp-tab-badges"></div>
				</a>
                <a href="?page=ai-poster-settings&setting=threads" class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'threads' ? 'fsp-is-active' : '' ); ?>">
                    <div class="fsp-tab-title">
                        <i class="threads-icon threads-icon-16 fsp-tab-title-icon"></i>
                        <span class="fsp-tab-title-text"><?php echo fsp__( 'Threads settings' ); ?></span>
                    </div>
                    <div class="fsp-tab-badges"></div>
                </a>
				<a href="?page=ai-poster-settings&setting=twitter" class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'twitter' ? 'fsp-is-active' : '' ); ?>">
					<div class="fsp-tab-title">
						<i class="fab fa-twitter fsp-tab-title-icon"></i>
						<span class="fsp-tab-title-text"><?php echo fsp__( 'Twitter settings' ); ?></span>
					</div>
					<div class="fsp-tab-badges"></div>
				</a>
                <a href="?page=ai-poster-settings&setting=planly" class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'planly' ? 'fsp-is-active' : '' ); ?>">
                    <div class="fsp-tab-title">
                        <i class="planly-icon planly-icon-16 fsp-tab-title-icon"></i>
                        <span class="fsp-tab-title-text"><?php echo fsp__( 'Planly settings' ); ?></span>
                    </div>
                    <div class="fsp-tab-badges"></div>
                </a>
				<a href="?page=ai-poster-settings&setting=linkedin" class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'linkedin' ? 'fsp-is-active' : '' ); ?>">
					<div class="fsp-tab-title">
						<i class="fab fa-linkedin-in fsp-tab-title-icon"></i>
						<span class="fsp-tab-title-text"><?php echo fsp__( 'LinkedIn settings' ); ?></span>
					</div>
					<div class="fsp-tab-badges"></div>
				</a>
                <a href="?page=ai-poster-settings&setting=pinterest" class="fsp-tab <?php echo( $fsp_params[ 'active_tab' ] === 'pinterest' ? 'fsp-is-active' : '' ); ?>">
                    <div class="fsp-tab-title">
                        <i class="fab fa-pinterest-p fsp-tab-title-icon"></i>
                        <span class="fsp-tab-title-text"><?php echo fsp__( 'Pinterest settings' ); ?></span>
                    </div>
                    <div class="fsp-tab-badges"></div>
                </a>
				<?php };?>
			</div>
		</div>
		<div id="fspComponent" class="fsp-layout-right fsp-col-12 fsp-col-md-8 fsp-col-lg-9">
			<form id="fspSettingsForm" class="fsp-card fsp-settings">
				<?php Pages::controller( 'Settings', 'Main', 'component_' . $fsp_params[ 'active_tab' ] ); ?>
			</form>
		</div>
	</div>
</div>