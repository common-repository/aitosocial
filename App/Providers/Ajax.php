<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Pages\Base\Controllers\Ajax as BaseAjax;
use FSPoster\App\Pages\Logs\Controllers\Ajax as LogsAjax;
use FSPoster\App\Pages\Apps\Controllers\Ajax as AppsAjax;
use FSPoster\App\Pages\Share\Controllers\Ajax as ShareAjax;
use FSPoster\App\Pages\Accounts\Controllers\Ajax as AccountsAjax;
use FSPoster\App\Pages\Settings\Controllers\Ajax as SettingsAjax;
use FSPoster\App\Pages\Schedules\Controllers\Ajax as SchedulesAjax;

Helper::disableDebug();

class Ajax
{
	use BaseAjax, AccountsAjax, SchedulesAjax, ShareAjax, LogsAjax, AppsAjax, SettingsAjax;

	public function __construct ()
	{
		if ( Helper::canLoadPlugin() )
		{
			$methods = get_class_methods( $this );

			foreach ( $methods as $method )
			{
				if ( $method === '__construct' )
				{
					continue;
				}

				add_action( 'wp_ajax_' . $method, function () use ( $method ) {
					$this->$method();

					exit;
				} );
			}
		}
		else
		{
			add_action( 'wp_ajax_update_app', function () {
				$this->update_app();

				exit;
			} );
		}
	}

	public function update_app ()
	{
		$code = Request::post( 'code', '', 'string' );

		if ( empty( $code ) )
		{
			Helper::response( FALSE, fsp__( 'Please type purchase key!' ) );
		}

		if ( Helper::getOption( 'poster_plugin_installed', '0', TRUE ) == Helper::getVersion() )
		{
			Helper::response( FALSE, fsp__( 'Your plugin is already updated!' ) );
		}

		$result = self::updatePlugin( $code );

		if ( $result[ 0 ] )
		{
			Helper::response( TRUE, [ 'msg' => fsp__( 'Plugin updated!' ) ] );
		}
		else
		{
			Helper::response( FALSE, $result[ 1 ] );
		}
	}

	private function getIcon( $driver )
	{
		switch ( $driver )
		{
			case 'fb':
				return 'fab fa-facebook';
			case 'instagram':
				return 'fab fa-instagram';
			case 'twitter':
				return 'fab fa-twitter';
			case 'planly':
				return 'planly-icon planly-icon-12';
			case 'linkedin':
				return 'fab fa-linkedin';
			case 'pinterest':
				return 'fab fa-pinterest';
			case 'webhook':
				return 'fas fa-atlas';
            case 'threads':
                return 'threads-icon threads-icon-12';
			default:
				return '';
		}
	}
}
