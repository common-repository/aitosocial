<?php
// modified plugin code
namespace {

	function fsp__ ( $text = '', $binds = [], $esc_html = TRUE )
	{
		$text = $esc_html ? esc_html__( $text, 'aitosocial' ) : __( $text, 'aitosocial' );

		if ( ! empty( $binds ) && is_array( $binds ) )
		{
			$text = vsprintf( $text, $binds );
		}

		return $text;
	}
}

namespace FSPoster\App\Providers {

	/**
	 * Class Bootstrap
	 * @package FSPoster\App\Providers
	 */
	class Bootstrap
	{
		/**
		 * Bootstrap constructor.
		 */
		public function __construct ()
		{
            if ( Helper::canLoadPlugin() )
            {
                CronJob::init();
            }

			$this->registerDefines();

			$this->loadPluginTextdomain();
			$this->loadPluginLinks();
			$this->createCustomPostTypes();
			$this->createPostSaveEvent();

			if ( is_admin() )
			{
				new BackEnd();
			}
			else
			{
				new FrontEnd();
			}
			
			register_activation_hook( FS_ROOT_DIR . '/init.php', [ Helper::class, 'createDBTables' ] );
			
		}

		private function registerDefines ()
		{
			define( 'FS_ROOT_DIR', dirname( dirname( __DIR__ ) ) );
		}

		private function loadPluginLinks ()
		{
			add_filter( 'plugin_action_links_fs-poster/init.php', function ( $links ) {
				$newLinks = [
					'<a href="#" target="_blank">' . fsp__( 'Support' ) . '</a>',
					'<a href="#" target="_blank">' . fsp__( 'Documentation' ) . '</a>'
				];

				return array_merge( $newLinks, $links );
			} );
		}

		private function loadPluginTextdomain ()
		{
			add_action( 'init', function () {
				load_plugin_textdomain( 'aitosocial', FALSE, 'aitosocial/languages' );
			} );
		}

		private function createCustomPostTypes ()
		{
			add_action( 'init', function () {
				register_post_type( 'fs_post', [
					'labels'      => [
						'name'          => fsp__( 'FS Posts' ),
						'singular_name' => fsp__( 'FS Post' )
					],
					'public'      => FALSE,
					'has_archive' => TRUE
				] );

				register_post_type( 'fs_post_tmp', [
					'labels'      => [
						'name'          => fsp__( 'FS Posts' ),
						'singular_name' => fsp__( 'FS Post' )
					],
					'public'      => FALSE,
					'has_archive' => TRUE
				] );
			} );
		}

		private function createPostSaveEvent ()
		{
			add_action( 'transition_post_status', [ 'FSPoster\App\Providers\ShareService', 'postSaveEvent' ], 10, 3 );
			add_action( 'delete_post', [ 'FSPoster\App\Providers\ShareService', 'deletePostFeeds' ], 10 );
			
			$_enable_ai_option = get_option('ai-poster-prompt_enable');
			if(isset($_enable_ai_option[0]) && $_enable_ai_option[0] == 'yes'){
				add_action( 'transition_post_status', [ 'FSPoster\App\Providers\BackEnd', 'func_add_to_schedule_posts_table' ], 10, 3 );
			}
		}
	}
}
