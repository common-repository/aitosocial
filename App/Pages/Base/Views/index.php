<?php

namespace FSPoster\App\Pages\Base\Views;

use FSPoster\App\Providers\Pages;

defined( 'ABSPATH' ) or exit;
?>

<div class="fsp-container">
	<div class="fsp-header">
		<div class="fsp-nav">
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Dashboard' ? 'active' : '' ); ?>" href="?page=ai-poster"><?php echo fsp__( 'Dashboard' ); ?></a>
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Accounts' ? 'active' : '' ); ?>" href="?page=ai-poster-accounts"><?php echo fsp__( 'Accounts' ); ?></a>
			<!-- modified plugin code -->
			<a class="fsp-nav-link <?php echo( $_GET[ 'page' ] === 'ai-poster-calendar' ? 'active' : '' ); ?>" href="?page=ai-poster-calendar"><?php echo fsp__( 'Calendar' ); ?></a>
			<!-- end -->
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Logs' ? 'active' : '' ); ?>" href="?page=ai-poster-logs"><?php echo fsp__( 'Logs' ); ?></a>
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Apps' ? 'active' : '' ); ?>" href="?page=ai-poster-apps"><?php echo fsp__( 'Apps' ); ?></a>
			<!-- modified plugin code -->
			<a class="fsp-nav-link <?php echo( $_GET[ 'page' ] === 'ai-poster-prompts' ? 'active' : '' ); ?>" href="?page=ai-poster-prompts"><?php echo fsp__( 'Prompt Settings' ); ?></a>
			<!-- end -->
			<?php if ( ( current_user_can( 'administrator' ) || defined( 'AI_POSTER_IS_DEMO' ) ) ) { ?>
				<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Settings' ? 'active' : '' ); ?>" href="?page=ai-poster-settings"><?php echo fsp__( 'Settings' ); ?></a>
			<?php } ?>
		</div>
	</div>
	<div class="fsp-body">
		<?php Pages::controller( $fsp_params[ 'page_name' ], 'Main', 'index' ); ?>
	</div>
</div>
