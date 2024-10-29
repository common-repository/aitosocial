<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Libraries\planly\Planly;
use FSPoster\App\Libraries\threads\Threads;
use FSPoster\App\Libraries\fb\Facebook;
use FSPoster\App\Libraries\twitter\Twitter;
use FSPoster\App\Libraries\linkedin\Linkedin;
use FSPoster\App\Libraries\pinterest\Pinterest;
use FSPoster\App\Libraries\fb\FacebookCookieApi;
use FSPoster\App\Libraries\instagram\InstagramApi;
use FSPoster\App\Libraries\twitter\TwitterPrivateAPI;
use FSPoster\App\Libraries\pinterest\PinterestCookieApi;

class AccountService
{
	public static function checkAccounts ()
	{
		$all_accountsSQL = DB::DB()->prepare( 'SELECT * FROM ' . DB::table( 'accounts' ) . ' WHERE ((id IN (SELECT account_id FROM ' . DB::table( 'account_status' ) . ')) OR (id IN (SELECT account_id FROM ' . DB::table( 'account_nodes' ) . ' WHERE id IN (SELECT node_id FROM ' . DB::table( 'account_node_status' ) . ')))) AND `blog_id` = %d', [
			Helper::getBlogId()
		] );
		$all_accounts    = DB::DB()->get_results( $all_accountsSQL, ARRAY_A );

		foreach ( $all_accounts as $account )
		{
			$node_info = Helper::getAccessToken( 'account', $account[ 'id' ] );

			$appId             = $node_info[ 'app_id' ];
			$driver            = $node_info[ 'driver' ];
			$accessToken       = $node_info[ 'access_token' ];
			$accessTokenSecret = $node_info[ 'access_token_secret' ];
			$proxy             = $node_info[ 'info' ][ 'proxy' ];
			$options           = $node_info[ 'options' ];
			$accountId         = $node_info[ 'account_id' ];

			if ( $driver === 'fb' )
			{
				if ( empty( $options ) ) // app method
				{
					$appInf = DB::fetch( 'apps', [ 'id' => $appId ] );
					$fb     = new Facebook( $appInf, $accessToken, $proxy );
					$result = $fb->checkAccount();
				}
				else // cookie method
				{
					$fbDriver = new FacebookCookieApi( $accountId, $options, $proxy );
					$result   = $fbDriver->checkAccount();
				}
			}
			else if ( $driver === 'instagram' )
			{
				$result = InstagramApi::checkAccount( $node_info );
			}
			else if ( $driver === 'twitter' )
			{
				if ( empty( $options ) )
				{
					$result = Twitter::checkAccount( $appId, $accessToken, $accessTokenSecret, $proxy );
				}
				else
				{
					$tp     = new TwitterPrivateAPI( $options, $proxy );
					$result = $tp->checkAccount();
				}
			}
			else if ( $driver === 'planly' )
			{
				$result = ( new Planly( $options, $proxy ) )->checkAccount();
			}
			else if ( $driver === 'linkedin' )
			{
				$result = Linkedin::checkAccount( $accessToken, $proxy );
			}
			else if ( $driver === 'pinterest' )
			{
				if ( empty( $options ) ) // app method
				{
					$result = Pinterest::checkAccount( $accessToken, $proxy );
				}
				else // cookie method
				{
					$getCookie = DB::fetch( 'account_sessions', [
						'driver'   => 'pinterest',
						'username' => $node_info[ 'username' ]
					] );

					$pinterest = new PinterestCookieApi( $getCookie[ 'cookies' ], $proxy );
					$result    = $pinterest->checkAccount();
				}
			}
            else if ( $driver === 'threads' )
            {
                $threads = new Threads( json_decode($options, true), $proxy );
                $result  = $threads->checkAccount();
            }

			if ( isset( $result[ 'error' ] ) )
			{
				if ( $result[ 'error' ] )
				{
					$error_msg = isset( $result[ 'error_msg' ] ) ? substr( $result[ 'error_msg' ], 0, 300 ) : fsp__( 'The account is disconnected from the AItoSocial plugin. Please add your account to the plugin without deleting the account from the plugin; as a result, account settings will remain as it is.' );

					self::disable_account( $account[ 'id' ], $error_msg );
				}
				else
				{
					DB::DB()->update( DB::table( 'accounts' ), [
						'status'    => NULL,
						'error_msg' => NULL
					], [
						'id' => $account[ 'id' ]
					] );
				}
			}

			Helper::setOption( 'check_accounts_last', Date::epoch() );
		}
	}

	public static function disable_account ( $account_id, $error_msg )
	{
		DB::DB()->update( DB::table( 'accounts' ), [
			'status'    => 'error',
			'error_msg' => $error_msg
		], [ 'id' => $account_id ] );

		if ( Helper::getOption( 'check_accounts_disable', 0 ) )
		{
			DB::DB()->delete( DB::table( 'account_status' ), [
				'account_id' => $account_id
			] );

			DB::DB()->query( DB::DB()->prepare( 'DELETE FROM ' . DB::table( 'account_node_status' ) . ' WHERE node_id IN (SELECT id FROM ' . DB::table( 'account_nodes' ) . ' WHERE account_id = %d)', [ $account_id ] ) );
		}
	}
}