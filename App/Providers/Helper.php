<?php

namespace FSPoster\App\Providers;

use FSPoster\App\Libraries\linkedin\Linkedin;
use FSPoster\App\Libraries\pinterest\Pinterest;

class Helper
{
	use WPHelper, URLHelper;

	public static function pluginDisabled ()
	{
		return Helper::getOption( 'plugin_disabled', '0', TRUE ) > 0;
	}

	public static function hasPurchaseKey ()
	{
		return TRUE;
	}

	public static function canLoadPlugin ()
	{
		return Helper::hasPurchaseKey() && ! Helper::pluginDisabled();
	}

	public static function response ( $status, $arr = [] )
	{
		$arr = is_array( $arr ) ? $arr : ( is_string( $arr ) ? [ 'error_msg' => $arr ] : [] );

		if ( $status )
		{
			$arr[ 'status' ] = 'ok';
		}
		else
		{
			$arr[ 'status' ] = 'error';
			if ( ! isset( $arr[ 'error_msg' ] ) )
			{
				$arr[ 'error_msg' ] = 'Error!';
			}
		}

		echo json_encode( $arr );
		exit();
	}

	public static function spintax ( $text )
	{
		$text = is_string( $text ) ? (string) $text : '';

		return preg_replace_callback( '/\{(((?>[^\{\}]+)|(?R))*)\}/x', function ( $text ) {
			$text  = Helper::spintax( $text[ 1 ] );
			$parts = explode( '|', $text );

			return $parts[ array_rand( $parts ) ];
		}, $text );
	}

	public static function cutText ( $text, $n = 35 )
	{
		return mb_strlen( $text, 'UTF-8' ) > $n ? mb_substr( $text, 0, $n, 'UTF-8' ) . '...' : $text;
	}

	public static function getVersion ()
	{
		$plugin_data = get_file_data( FS_ROOT_DIR . '/init.php', [ 'Version' => 'Version' ], FALSE );

		return isset( $plugin_data[ 'Version' ] ) ? $plugin_data[ 'Version' ] : '1.0.0';
	}

	public static function getInstalledVersion ()
	{
		$ver = Helper::getOption( 'poster_plugin_installed', '0', TRUE );

		return ( $ver === '1' || empty( $ver ) ) ? '1.0.0' : $ver;
	}

	public static function debug ()
	{
		error_reporting( E_ALL );
		ini_set( 'display_errors', 'on' );
	}

	public static function disableDebug ()
	{
		error_reporting( 0 );
		ini_set( 'display_errors', 'off' );
	}

	public static function hexToRgb ( $hex )
	{
		if ( strpos( '#', $hex ) === 0 )
		{
			$hex = substr( $hex, 1 );
		}

		return sscanf( $hex, "%02x%02x%02x" );
	}

	public static function downloadRemoteFile ( $destination, $sourceURL )
	{
		return file_put_contents( $destination, Curl::getURL( $sourceURL ) );
	}

	public static function getOption ( $optionName, $default = NULL, $network_option = FALSE )
	{
		$network_option = ! is_multisite() && $network_option == TRUE ? FALSE : $network_option;
		$fnName         = $network_option ? 'get_site_option' : 'get_option';

		return $fnName( 'fs_' . $optionName, $default );
	}

	public static function setOption ( $optionName, $optionValue, $network_option = FALSE, $autoLoad = NULL )
	{
		$network_option = ! is_multisite() && $network_option == TRUE ? FALSE : $network_option;
		$fnName         = $network_option ? 'update_site_option' : 'update_option';

		$arguments = [ 'fs_' . $optionName, $optionValue ];

		if ( ! is_null( $autoLoad ) && ! $network_option )
		{
			$arguments[] = $autoLoad;
		}

		return call_user_func_array( $fnName, $arguments );
	}

	public static function deleteOption ( $optionName, $network_option = FALSE )
	{
		$network_option = ! is_multisite() && $network_option == TRUE ? FALSE : $network_option;
		$fnName         = $network_option ? 'delete_site_option' : 'delete_option';

		return $fnName( 'fs_' . $optionName );
	}

	public static function setCustomSetting ( $setting_key, $setting_val, $node_type, $node_id )
	{
		if ( isset( $node_id, $node_type ) )
		{
			$node_type   = $node_type === 'account' ? 'account' : 'node';
			$setting_key = 'setting' . ':' . $node_type . ':' . $node_id . ':' . $setting_key;
			self::setOption( $setting_key, $setting_val );
		}
	}

	public static function getCustomSetting ( $setting_key, $default = NULL, $node_type = NULL, $node_id = NULL )
	{
		if ( isset( $node_id, $node_type ) )
		{
			$node_type    = $node_type === 'account' ? 'account' : 'node';
			$setting_key1 = 'setting' . ':' . $node_type . ':' . $node_id . ':' . $setting_key;
			$setting      = self::getOption( $setting_key1 );
		}

		if ( ! isset( $setting ) || $setting === FALSE )
		{
			return self::getOption( $setting_key, $default );
		}
		else
		{
			return $setting;
		}
	}

	public static function deleteCustomSettings ( $node_type, $node_id )
	{
		$like  = 'fs_setting' . ':' . $node_type . ':' . $node_id . ':' . '%';
		$table = DB::DB()->options;
		DB::DB()->query( DB::DB()->prepare( "DELETE FROM `$table` WHERE option_name LIKE %s", [ $like ] ) );
	}

	public static function removePlugin ()
	{

		$fsTables = [
			'account_access_tokens',
			'account_node_status',
			'account_nodes',
			'account_sessions',
			'account_status',
			'grouped_accounts',
			'accounts',
			'apps',
			'feeds',
			'schedules',
			'account_groups',
			'account_groups_data',
			'post_comments'
		];

		foreach ( $fsTables as $tableName )
		{
			DB::DB()->query( "DROP TABLE IF EXISTS `" . DB::table( $tableName ) . "`" );
		}

		DB::DB()->query( 'DELETE FROM `' . DB::DB()->base_prefix . 'options` WHERE `option_name` LIKE "fs_%"' );

		if ( is_multisite() )
		{
			DB::DB()->query( 'DELETE FROM `' . DB::DB()->base_prefix . 'sitemeta` WHERE `meta_key` LIKE "fs_%"' );
		}

		DB::DB()->query( "DELETE FROM " . DB::WPtable( 'posts', TRUE ) . " WHERE post_type='fs_post_tmp' OR post_type='fs_post'" );
		// modified plugin code 
		delete_option( 'ai-poster-prompt_enable' );
		delete_option( 'ai-poster-prompt_select_ai_engine' );
		delete_option( 'ai-poster-prompt_select_model' );
		delete_option( 'ai-poster-prompt_select_post_types' );
		delete_option( 'ai-poster-social_media_options' );
		delete_option( 'ai-poster-content_publish_after' );
		delete_option( 'ai-poster-prompt_api_key' );
		delete_option( 'ai-poster-prompt_fb_prompt' );
		delete_option( 'ai-poster-prompt_twitter_prompt' );
		delete_option( 'ai-poster-prompt_linkedin_prompt' );
		delete_option( 'ai-poster-prompt_threads_prompt' );
		delete_option( 'ai-poster-prompt_instagram_prompt' );
		delete_option( 'ai-poster-prompt_tiktok_prompt' );
		delete_option( 'ai-poster-prompt_pinterest_prompt' );
		// end
	}

	//TODO this method has no usage
	public static function socialIcon ( $driver )
	{
		switch ( $driver )
		{
			case 'fb':
				return "fab fa-facebook-f";
			case 'instagram':
			case 'twitter':
			case 'linkedin':
			case 'pinterest':
			case 'planly':
				return 'planly-icon planly-icon-12';
			case 'webhook':
				return "fas fa-atlas";
            case 'threads':
                return 'threads-icon threads-icon-12';
		}
	}

	public static function profilePic ( $info, $w = 40, $h = 40 )
	{
		if ( ! isset( $info[ 'driver' ] ) )
		{
			return '';
		}

		if ( empty( $info ) )
		{
			return Pages::asset( 'Base', 'img/no-photo.png' );
		}

		if ( is_array( $info ) && key_exists( 'cover', $info ) ) // nodes
		{
			if ( ! empty( $info[ 'cover' ] ) )
			{
				return $info[ 'cover' ];
			}
			else
			{
				if ( $info[ 'driver' ] === 'fb' )
				{
					return "https://graph.facebook.com/" . esc_html( $info[ 'node_id' ] ) . "/picture?redirect=1&height={$h}&width={$w}&type=normal";
				}
				else if ( $info[ 'driver' ] === 'instagram' )
				{
					return Pages::asset( 'Base', 'img/no-photo.png' );
				}
				else if ( $info[ 'driver' ] === 'linkedin' )
				{
					return Pages::asset( 'Base', 'img/no-photo.png' );
				}
				else if ( $info[ 'driver' ] === 'planly' )
				{
					return Pages::asset( 'Base', 'img/no-photo.png' );
				}
			}
		}
		else
		{
			if ( $info[ 'driver' ] === 'fb' )
			{
				return "https://graph.facebook.com/" . esc_html( $info[ 'profile_id' ] ) . "/picture?redirect=1&height={$h}&width={$w}&type=normal";
			}
			else if ( $info[ 'driver' ] === 'instagram' )
			{
				if ( $info[ 'password' ] == '#####' )
				{
					return "https://graph.facebook.com/" . esc_html( $info[ 'profile_id' ] ) . "/picture?redirect=1&height={$h}&width={$w}&type=normal";
				}
				$parsedURL = parse_url( $info[ 'profile_pic' ] );
				if ( isset( $parsedURL[ 'host' ] ) and isset( $parsedURL[ 'path' ] ) and isset( $parsedURL[ 'query' ] ) )
				{
					$host = str_replace( '.', '-', str_replace( '-', '--', $parsedURL[ 'host' ] ) );

					return $info[ 'profile_pic' ] ? 'https://' . $host . '.translate.goog' . $parsedURL[ 'path' ] . '?' . $parsedURL[ 'query' ] : $info[ 'profile_pic' ];
				}

				return Pages::asset( 'Base', 'img/no-photo.png' );

			}
			else if ( $info[ 'driver' ] === 'twitter' )
			{
				return empty( $info[ 'profile_pic' ] ) ? 'https://abs.twimg.com/sticky/default_profile_images/default_profile_normal.png' : $info[ 'profile_pic' ];
			}
            else if ( $info[ 'driver' ] === 'threads' )
            {
                $parsedURL = parse_url( $info[ 'profile_pic' ] );
                if ( isset( $parsedURL[ 'host' ] ) and isset( $parsedURL[ 'path' ] ) and isset( $parsedURL[ 'query' ] ) )
                {
                    $host = str_replace( '.', '-', str_replace( '-', '--', $parsedURL[ 'host' ] ) );

                    return $info[ 'profile_pic' ] ? 'https://' . $host . '.translate.goog' . $parsedURL[ 'path' ] . '?' . $parsedURL[ 'query' ] : $info[ 'profile_pic' ];
                }
            }
			else if ( $info[ 'driver' ] === 'planly' )
			{
				return $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'linkedin' )
			{
				return $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'pinterest' )
			{
				return $info[ 'profile_pic' ];
			}
			else if ( $info[ 'driver' ] === 'webhook' )
			{
				return $info[ 'profile_pic' ];
			}
		}

		return Pages::asset( 'Base', 'img/no-photo.png' );
	}

	public static function profileLink ( $info )
	{
		$link = '';
		if ( isset( $info[ 'driver' ] ) )
		{
			// IF NODE
			if ( is_array( $info ) && key_exists( 'cover', $info ) ) // nodes
			{
				if ( $info[ 'driver' ] === 'fb' )
				{
					$link = "https://fb.com/" . esc_html( $info[ 'node_id' ] );
				}
				else if ( $info[ 'driver' ] === 'instagram' )
				{
					$link = "https://instagram.com/" . esc_html( $info[ 'screen_name' ] );
				}
				else if ( $info[ 'driver' ] === 'linkedin' )
				{
					$link = "https://www.linkedin.com/company/" . esc_html( $info[ 'node_id' ] );
				}
				else if ( $info[ 'driver' ] === 'pinterest' )
				{
					$link = "https://www.pinterest.com/" . esc_html( $info[ 'screen_name' ] );
				}
				else if ( $info[ 'driver' ] === 'planly' )
				{
					$link = $info[ 'screen_name' ];
				}
			}
			else
			{
				if ( $info[ 'driver' ] === 'fb' )
				{
					if ( empty( $info[ 'options' ] ) )
					{
						$info[ 'profile_id' ] = 'me';
					}

					$link = "https://fb.com/" . esc_html( $info[ 'profile_id' ] );
				}
				else if ( $info[ 'driver' ] === 'instagram' )
				{
					if ( $info[ 'password' ] == '#####' )
					{
						$link = 'https://fb.com/me';
					}
					else
					{
						$link = "https://instagram.com/" . esc_html( $info[ 'username' ] );
					}
				}
				else if ( $info[ 'driver' ] === 'twitter' )
				{
					$link = "https://twitter.com/" . esc_html( $info[ 'username' ] );
				}
				else if ( $info[ 'driver' ] === 'planly' )
				{
					$link = 'https://app.planly.com/';
				}
				else if ( $info[ 'driver' ] === 'linkedin' )
				{
					$link = "https://www.linkedin.com/in/" . esc_html( str_replace( [
							'https://www.linkedin.com/in/',
							'http://www.linkedin.com/in/'
						], '', $info[ 'username' ] ) );
				}
				else if ( $info[ 'driver' ] === 'pinterest' )
				{
					$link = "https://www.pinterest.com/" . esc_html( $info[ 'username' ] );
				}
				else if ( $info[ 'driver' ] === 'webhook' )
				{
					$options = json_decode( $info[ 'options' ], TRUE );
					$link    = empty( $options[ 'url' ] ) ? '' : $options[ 'url' ];
				}
                else if ( $info[ 'driver' ] === 'threads' )
                {
                    $link = 'https://threads.net/@' . $info[ 'username' ];
                }
			}
		}

		return $link;
	}

	public static function appIcon ( $appInfo )
	{
		if ( $appInfo[ 'driver' ] === 'fb' )
		{
			return "https://graph.facebook.com/" . esc_html( $appInfo[ 'app_id' ] ) . "/picture?redirect=1&height=40&width=40&type=small";
		}
		else
		{
			return Pages::asset( 'Base', 'img/app_icon.svg' );
		}
	}

	public static function replacePartOfTags ( $message, $postInf )
	{
		$postInf[ 'post_content' ] = Helper::removePageBuilderShortcodes( $postInf[ 'post_content' ] );

		$message = preg_replace_callback( '/\{content_short_?([0-9]+)?\}/', function ( $n ) use ( $postInf ) {
			if ( isset( $n[ 1 ] ) && is_numeric( $n[ 1 ] ) )
			{
				$cut = $n[ 1 ];
			}
			else
			{
				$cut = 40;
			}

			return htmlspecialchars_decode( Helper::cutText( strip_tags( $postInf[ 'post_content' ] ), $cut ), ENT_QUOTES );
		}, $message );

		return preg_replace_callback( '/\{cf_(.+)\}/iU', function ( $n ) use ( $postInf ) {
			$customField = isset( $n[ 1 ] ) ? $n[ 1 ] : '';

			return get_post_meta( $postInf[ 'ID' ], $customField, TRUE );
		}, $message );
	}

	public static function replaceTags ( $message, $postInf, $link, $shortLink )
	{
		$message  = self::replacePartOfTags( $message, $postInf );
		$getPrice = Helper::getProductPrice( $postInf );

		$productRegularPrice = $getPrice[ 'regular' ];
		$productSalePrice    = $getPrice[ 'sale' ];

		$productCurrentPrice = ( isset( $productSalePrice ) && ! empty( $productSalePrice ) ) ? $productSalePrice : $productRegularPrice;

		$mediaId = get_post_thumbnail_id( $postInf[ 'ID' ] );

		if ( empty( $mediaId ) )
		{
			$media   = get_attached_media( 'image', $postInf[ 'ID' ] );
			$first   = reset( $media );
			$mediaId = isset( $first->ID ) ? $first->ID : 0;
		}

		$featuredImage = $mediaId > 0 ? wp_get_attachment_url( $mediaId ) : '';

		return str_replace( [
			'{id}',
			'{title}',
			'{title_ucfirst}',
			'{content_full}',
			'{link}',
			'{short_link}',
			'{product_regular_price}',
			'{product_sale_price}',
			'{product_current_price}',
			'{uniq_id}',
			'{tags}',
			'{categories}',
			'{excerpt}',
			'{product_description}',
			'{author}',
			'{featured_image_url}',
			'{terms}',
			'{terms_comma}',
			'{terms_space}',
		], [
			$postInf[ 'ID' ],
			$postInf[ 'post_title' ],
			ucfirst( mb_strtolower( $postInf[ 'post_title' ] ) ),
			$postInf[ 'post_content' ],
			$link,
			$shortLink,
			$productRegularPrice,
			$productSalePrice,
			$productCurrentPrice,
			uniqid(),
			Helper::getPostTerms( $postInf, 'post_tag', TRUE, FALSE ),
			Helper::getPostTerms( $postInf, 'category', TRUE, FALSE ),
			html_entity_decode( get_the_excerpt( $postInf[ 'ID' ] ) ),
			html_entity_decode( get_the_excerpt( $postInf[ 'ID' ] ) ),
			get_the_author_meta( 'display_name', $postInf[ 'post_author' ] ),
			$featuredImage,
			Helper::getPostTerms( $postInf, NULL, TRUE, FALSE ),
			Helper::getPostTerms( $postInf, NULL, FALSE, FALSE, ', ' ),
			Helper::getPostTerms( $postInf, NULL, FALSE, FALSE ),
		], $message );
	}

	public static function replaceAltTextTags ( $message, $postInf )
	{
		$message = self::replacePartOfTags( $message, $postInf );
		$mediaId = get_post_thumbnail_id( $postInf[ 'ID' ] );
		$altText = get_post( $mediaId );

		return str_replace( [
			'{site_name}',
			'{media_caption}',
			'{media_description}',
			'{media_name}',
			'{title}',
			'{tags}',
			'{excerpt}'
		], [
			get_bloginfo( 'name' ),
			$altText->post_excerpt,
			$altText->post_content,
			$altText->post_name,
			$postInf[ 'post_title' ],
			Helper::getPostTerms( $postInf, 'post_tag', TRUE, FALSE ),
			get_the_excerpt( $postInf[ 'ID' ] ),
		], $message );
	}

	public static function scheduleFilters ( $schedule_info )
	{
		$scheduleId = $schedule_info[ 'id' ];

		/* Post type filter */
		$_postTypeFilter = $schedule_info[ 'post_type_filter' ];

		$allowedPostTypes = explode( '|', Helper::getOption( 'allowed_post_types', 'post|page|attachment|product' ) );

		if ( ! in_array( $_postTypeFilter, $allowedPostTypes ) )
		{
			$_postTypeFilter = '';
		}

		$_postTypeFilter = esc_sql( $_postTypeFilter );

		if ( ! empty( $_postTypeFilter ) )
		{
			$postTypeFilter = "AND post_type='" . $_postTypeFilter . "'";

			if ( $_postTypeFilter === 'product' && isset( $schedule_info[ 'dont_post_out_of_stock_products' ] ) && $schedule_info[ 'dont_post_out_of_stock_products' ] == 1 && function_exists( 'wc_get_product' ) )
			{
				$postTypeFilter .= ' AND IFNULL((SELECT DISTINCT `meta_value` FROM `' . DB::WPtable( 'postmeta', TRUE ) . '` WHERE `post_id`=tb1.id AND `meta_key`=\'_stock_status\'), \'\')<>\'outofstock\'';
			}
		}
		else
		{
			$post_types = "'" . implode( "','", array_map( 'esc_sql', $allowedPostTypes ) ) . "'";

			$postTypeFilter = "AND `post_type` IN ({$post_types})";
		}
		/* /End of post type filer */

		/* Categories filter */
		$categories_arr    = explode( '|', $schedule_info[ 'category_filter' ] );
		$categories_arrNew = [];

		foreach ( $categories_arr as $categ )
		{
			if ( is_numeric( $categ ) && $categ > 0 )
			{
				$categInf = get_term( (int) $categ );

				if ( ! $categInf )
				{
					continue;
				}

				$categories_arrNew[] = (int) $categ;

				// get sub categories
				$child_cats = get_categories( [
					'taxonomy'   => $categInf->taxonomy,
					'child_of'   => (int) $categ,
					'hide_empty' => FALSE
				] );

				foreach ( $child_cats as $child_cat )
				{
					$categories_arrNew[] = (int) $child_cat->term_id;
				}
			}
		}

		$categories_arr = $categories_arrNew;

		unset( $categories_arrNew );

		if ( empty( $categories_arr ) )
		{
			$categoriesFilter = '';
		}
		else
		{
			$get_taxs = DB::DB()->get_col( DB::DB()->prepare( "SELECT `term_taxonomy_id` FROM `" . DB::WPtable( 'term_taxonomy', TRUE ) . "` WHERE `term_id` IN ('" . implode( "' , '", $categories_arr ) . "')" ), 0 );

			$categoriesFilter = " AND `id` IN ( SELECT object_id FROM `" . DB::WPtable( 'term_relationships', TRUE ) . "` WHERE term_taxonomy_id IN ('" . implode( "' , '", $get_taxs ) . "') ) ";
		}
		/* / End of Categories filter */

		/* post_date_filter */
		switch ( $schedule_info[ 'post_date_filter' ] )
		{
			case "this_week":
				$week = Date::format( 'w' );
				$week = $week == 0 ? 7 : $week;

				$startDateFilter = Date::format( 'Y-m-d 00:00', '-' . ( $week - 1 ) . ' day' );
				$endDateFilter   = Date::format( 'Y-m-d 23:59' );
				break;
			case "previously_week":
				$week = Date::format( 'w' );
				$week = $week == 0 ? 7 : $week;
				$week += 7;

				$startDateFilter = Date::format( 'Y-m-d 00:00', '-' . ( $week - 1 ) . ' day' );
				$endDateFilter   = Date::format( 'Y-m-d 23:59', '-' . ( $week - 7 ) . ' day' );
				break;
			case "this_month":
				$startDateFilter = Date::format( 'Y-m-01 00:00' );
				$endDateFilter   = Date::format( 'Y-m-t 23:59' );
				break;
			case "previously_month":
				$startDateFilter = Date::format( 'Y-m-01 00:00', '-1 month' );
				$endDateFilter   = Date::format( 'Y-m-t 23:59', '-1 month' );
				break;
			case "this_year":
				$startDateFilter = Date::format( 'Y-01-01 00:00' );
				$endDateFilter   = Date::format( 'Y-12-31 23:59' );
				break;
			case "last_30_days":
				$startDateFilter = Date::format( 'Y-m-d 00:00', '-30 day' );
				$endDateFilter   = Date::format( 'Y-m-d 23:59' );
				break;
			case "last_60_days":
				$startDateFilter = Date::format( 'Y-m-d 00:00', '-60 day' );
				$endDateFilter   = Date::format( 'Y-m-d 23:59' );
				break;
			case "custom":
				$startDateFilter = Date::format( 'Y-m-d 00:00', $schedule_info[ 'filter_posts_date_range_from' ] );
				$endDateFilter   = Date::format( 'Y-m-d 23:59', $schedule_info[ 'filter_posts_date_range_to' ] );
				break;
		}

		$dateFilter = "";

		if ( isset( $startDateFilter ) && isset( $endDateFilter ) )
		{
			$dateFilter = " AND post_date BETWEEN '{$startDateFilter}' AND '{$endDateFilter}'";
		}
		/* End of post_date_filter */

		/* Filter by id */
		$postIDs      = explode( ',', $schedule_info[ 'post_ids' ] );
		$postIDFilter = [];

		foreach ( $postIDs as $post_id1 )
		{
			if ( is_numeric( $post_id1 ) && $post_id1 > 0 )
			{
				$postIDFilter[] = (int) $post_id1;
			}
		}

		if ( empty( $postIDFilter ) )
		{
			$postIDFilter = '';
		}
		else
		{
			$postIDFilter     = " AND id IN ('" . implode( "','", $postIDFilter ) . "') ";
			$postTypeFilter   = '';
			$categoriesFilter = '';
			$dateFilter       = '';
		}

		/* End ofid filter */

		/* post_sort */
		$sortQuery    = '';
		$scheduleData = isset( $schedule_info[ 'data' ] ) ? json_decode( $schedule_info[ 'data' ], TRUE ) : [];
		$cycle        = ! empty( $scheduleData ) && isset( $scheduleData[ 'autoReschdulesDone' ] ) ? $scheduleData[ 'autoReschdulesDone' ] : 0;

		if ( $scheduleId > 0 )
		{
			switch ( $schedule_info[ 'post_sort' ] )
			{
				case "random":
					$sortQuery .= 'ORDER BY RAND()';
					break;
				case "random2":
					$sortQuery .= ' AND id NOT IN (SELECT post_id FROM `' . DB::table( 'feeds' ) . "` WHERE schedule_id='" . (int) $scheduleId . "' AND schedule_cycle=" . $cycle . " ) ORDER BY RAND()";
					break;
				case "old_first":
					$last_shared_post = DB::DB()->get_row( 'SELECT `post_id`, `wp_post_date` FROM `' . DB::table( 'feeds' ) . '` WHERE `schedule_id` = \'' . ( int ) $scheduleId . '\' AND schedule_cycle=' . $cycle . ' ORDER BY `id` DESC LIMIT 1', ARRAY_A );

					if ( ! empty( $last_shared_post[ 'post_id' ] ) )
					{
						$last_post_date = empty( $last_shared_post[ 'wp_post_date' ] ) ? Date::dateTimeSQL( get_the_date( 'Y-m-d H:i:s', $last_shared_post[ 'post_id' ] ) ) : $last_shared_post[ 'wp_post_date' ];
						if ( $last_post_date )
						{
							$sortQuery .= " AND ((post_date = '" . $last_post_date . "' AND ID > " . $last_shared_post[ 'post_id' ] . " ) OR post_date > '" . $last_post_date . "') ";
						}
					}

					$sortQuery .= 'ORDER BY post_date ASC, ID ASC';
					break;
				case "new_first":
					$last_shared_post = DB::DB()->get_row( 'SELECT `post_id`, `wp_post_date` FROM `' . DB::table( 'feeds' ) . '` WHERE `schedule_id` = \'' . ( int ) $scheduleId . '\' AND schedule_cycle=' . $cycle . '  ORDER BY `id` DESC LIMIT 1', ARRAY_A );

					if ( ! empty( $last_shared_post[ 'post_id' ] ) )
					{
						$last_post_date = empty( $last_shared_post[ 'wp_post_date' ] ) ? Date::dateTimeSQL( get_the_date( 'Y-m-d H:i:s', $last_shared_post[ 'post_id' ] ) ) : $last_shared_post[ 'wp_post_date' ];

						if ( $last_post_date )
						{
							$sortQuery .= " AND ( (post_date = '" . $last_post_date . "' AND ID < " . $last_shared_post[ 'post_id' ] . " ) OR post_date < '" . $last_post_date . "') ";
						}
					}

					$sortQuery .= 'ORDER BY post_date DESC, ID DESC';
					break;
			}
		}

		return "{$postIDFilter} {$postTypeFilter} {$categoriesFilter} {$dateFilter} {$sortQuery}";
	}

	public static function getAccessToken ( $nodeType, $nodeId )
	{
		if ( $nodeType === 'account' )
		{
			$node_info     = DB::fetch( 'accounts', $nodeId );
			$nodeProfileId = $node_info[ 'profile_id' ];
			$n_accountId   = $nodeProfileId;

			$accessTokenGet    = DB::fetch( 'account_access_tokens', [ 'account_id' => $nodeId ] );
			$accessToken       = isset( $accessTokenGet ) && array_key_exists( 'access_token', $accessTokenGet ) ? $accessTokenGet[ 'access_token' ] : '';
			$accessTokenSecret = isset( $accessTokenGet ) && array_key_exists( 'access_token_secret', $accessTokenGet ) ? $accessTokenGet[ 'access_token_secret' ] : '';
			$appId             = isset( $accessTokenGet ) && array_key_exists( 'app_id', $accessTokenGet ) ? $accessTokenGet[ 'app_id' ] : '';
			$driver            = $node_info[ 'driver' ];
			$username          = $node_info[ 'username' ];
			$email             = $node_info[ 'email' ];
			$password          = $node_info[ 'password' ];
			$name              = $node_info[ 'name' ];
			$proxy             = $node_info[ 'proxy' ];
			$options           = $node_info[ 'options' ];
			$poster_id         = NULL;

			/*if ( $driver === 'reddit' )
			{
				$accessToken = Reddit::accessToken( $accessTokenGet );
			}
			else if ( $driver === 'ok' )
			{
				$accessToken = OdnoKlassniki::accessToken( $accessTokenGet );
			}
			else if ( $driver === 'medium' )
			{
				$accessToken = Medium::accessToken( $accessTokenGet );
			}
			else if ( $driver === 'google_b' && empty( $options ) )
			{
				$accessToken = GoogleMyBusinessAPI::accessToken( $accessTokenGet );
			}
			else if ( $driver === 'blogger' )
			{
				$accessToken = Blogger::accessToken( $accessTokenGet );
			}
			else */
			if ( $driver === 'pinterest' && empty( $options ) )
			{
				$accessToken = Pinterest::accessToken( $accessTokenGet );
			}
			else if ( $driver === 'linkedin' )
			{
				$accessToken = Linkedin::accessToken( $nodeId, $accessTokenGet );
			}
		}
		else
		{
			$node_info    = DB::fetch( 'account_nodes', $nodeId );
			$account_info = DB::fetch( 'accounts', $node_info[ 'account_id' ] );

			if ( $node_info )
			{
				$node_info[ 'proxy' ] = $account_info[ 'proxy' ];
			}

			$name        = $account_info[ 'name' ];
			$username    = $account_info[ 'username' ];
			$email       = $account_info[ 'email' ];
			$password    = $account_info[ 'password' ];
			$proxy       = $account_info[ 'proxy' ];
			$options     = $account_info[ 'options' ];
			$n_accountId = $account_info[ 'profile_id' ];
			$poster_id   = NULL;

			$nodeProfileId     = $node_info[ 'node_id' ];
			$driver            = $node_info[ 'driver' ];
			$appId             = 0;
			$accessTokenSecret = '';

			if ( $driver === 'fb' && $node_info[ 'node_type' ] === 'ownpage' )
			{
				$accessToken    = $node_info[ 'access_token' ];
				$accessTokenGet = DB::fetch( 'account_access_tokens', [ 'account_id' => $node_info[ 'account_id' ] ] );
				$appId          = $accessTokenGet[ 'app_id' ];
			}
			else
			{
				$accessTokenGet    = DB::fetch( 'account_access_tokens', [ 'account_id' => $node_info[ 'account_id' ] ] );
				$accessToken       = isset( $accessTokenGet ) && array_key_exists( 'access_token', $accessTokenGet ) ? $accessTokenGet[ 'access_token' ] : '';
				$accessTokenSecret = isset( $accessTokenGet ) && array_key_exists( 'access_token_secret', $accessTokenGet ) ? $accessTokenGet[ 'access_token_secret' ] : '';
				$appId             = isset( $accessTokenGet ) && array_key_exists( 'app_id', $accessTokenGet ) ? $accessTokenGet[ 'app_id' ] : '';

				/*if ( $driver === 'reddit' )
				{
					$accessToken = Reddit::accessToken( $accessTokenGet );
				}
				else if ( $driver === 'ok' )
				{
					$accessToken = OdnoKlassniki::accessToken( $accessTokenGet );
				}
				else if ( $driver === 'medium' )
				{
					$accessToken = Medium::accessToken( $accessTokenGet );
				}
				else if ( $driver === 'google_b' && empty( $options ) )
				{
					$accessToken = GoogleMyBusinessAPI::accessToken( $accessTokenGet );
				}
				else if ( $driver === 'blogger' )
				{
					$accessToken = Blogger::accessToken( $accessTokenGet );
				}
				else */
				if ( $driver === 'pinterest' && empty( $options ) )
				{
					$accessToken = Pinterest::accessToken( $accessTokenGet );
				}
				else if ( $driver === 'linkedin' )
				{
					$accessToken = Linkedin::accessToken( $node_info[ 'account_id' ], $accessTokenGet );
				}
				else if ( $driver === 'fb' && $nodeType === 'group' && isset( $node_info[ 'poster_id' ] ) && $node_info[ 'poster_id' ] > 0 )
				{
					$poster_id = $node_info[ 'poster_id' ];
				}
			}

			if ( $driver === 'vk' )
			{
				$nodeProfileId = '-' . $nodeProfileId;
			}
		}

		$node_info[ 'node_type' ] = empty( $node_info[ 'node_type' ] ) ? 'account' : $node_info[ 'node_type' ];

		return [
			'id'                  => $nodeId,
			'node_id'             => $nodeProfileId,
			'node_type'           => $nodeType,
			'access_token'        => $accessToken,
			'access_token_secret' => $accessTokenSecret,
			'app_id'              => $appId,
			'driver'              => $driver,
			'info'                => $node_info,
			'username'            => $username,
			'email'               => $email,
			'password'            => $password,
			'proxy'               => $proxy,
			'options'             => $options,
			'account_id'          => $n_accountId,
			'poster_id'           => $poster_id,
			'name'                => $name
		];
	}

	public static function isHiddenUser ()
	{
		$hideFSPosterForRoles = explode( '|', Helper::getOption( 'hide_menu_for', '' ) );

		$userInf   = wp_get_current_user();
		$userRoles = (array) $userInf->roles;

		if ( ! in_array( 'administrator', $userRoles ) )
		{
			foreach ( $userRoles as $roleId )
			{
				if ( in_array( $roleId, $hideFSPosterForRoles ) )
				{
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	public static function removePageBuilderShortcodes ( $message )
	{
		$message = str_replace( [
			'[[',
			']]'
		], [
			'&#91;&#91;',
			'&#93;&#93;'
		], $message );

		$message = preg_replace( [ '/\[(.+)]/', '/<!--(.*?)-->/' ], '', $message );

		$message = str_replace( [
			'&#91;&#91;',
			'&#93;&#93;'
		], [
			'[[',
			']]'
		], $message );

		return $message;
	}

	public static function configureComment ( $postInf, $longLink, $shortLink, $driver, $nodeType, $nodeId )
	{
		if ( self::getCustomSetting( 'post_allow_first_comment', self::getOption( 'post_allow_first_comment_' . $driver, '0' ), $nodeType, $nodeId ) != '0' )
		{
			$comment = trim( self::getCustomSetting( 'post_first_comment', self::getOption( 'post_first_comment_' . $driver, '' ), $nodeType, $nodeId ) );
		}

		if ( empty( $comment ) )
		{
			return '';
		}

		$comment = trim( self::spintax( self::replaceTags( $comment, $postInf, $longLink, $shortLink ) ) );

		if ( empty( $comment ) )
		{
			return '';
		}

		if ( self::getOption( 'replace_wp_shortcodes', 'off' ) === 'on' )
		{
			$comment = do_shortcode( $comment );
		}
		else if ( self::getOption( 'replace_wp_shortcodes', 'off' ) === 'del' )
		{
			$comment = strip_shortcodes( $comment );
		}

		$comment = htmlspecialchars_decode( $comment, ENT_QUOTES );

		if ( ! in_array( $driver, [ 'medium', 'wordpress', 'tumblr', 'blogger' ] ) )
		{
			//$comment = $driver === 'telegram' ? strip_tags( $comment, '<b><u><i><a>' ) : strip_tags( $comment );
			$comment = strip_tags( $comment );
			$comment = str_replace( [ '&nbsp;', "\r\n" ], [ '', "\n" ], $comment );
		}

		return $comment;
	}

	/**
	 * Check the time if is between two times
	 *
	 * @param $time int time to check
	 * @param $start int start time to compare
	 * @param $end int end time to compare
	 *
	 * @return bool if given time is between two dates, then true, otherwise false
	 */
	public static function isBetweenDates ( $time, $start, $end )
	{
		if ( $start < $end )
		{
			return $time >= $start && $time <= $end;
		}
		else
		{
			return $time <= $end || $time >= $start;
		}
	}

	public static function mimeContentType ( $filename )
	{
        if ( function_exists( 'finfo_open' ) )
        {
            $finfo = finfo_open( FILEINFO_MIME_TYPE );

            if ( $finfo !== false )
            {
                $mimetype = @finfo_file( $finfo, $filename );
                finfo_close( $finfo );

                if ( ! empty( $mimetype ) )
                {
                    return $mimetype;
                }
            }
        }

		if ( function_exists( 'mime_content_type' ) )
		{
			$mimeType = @mime_content_type( $filename );

            if( $mimeType !== false )
            {
                return $mimeType;
            }
		}

        $mimeContentTypes = [
            'webp' => 'image/webp',
            'png'  => 'image/png',
            'jpeg' => 'image/jpeg',
            'jpe'  => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'ico'  => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif'  => 'image/tiff',

            'webm' => 'video/webm',
            'mp4'  => 'video/mp4',
            'qt'   => 'video/quicktime',
            'mov'  => 'video/quicktime',
            'flv'  => 'video/x-flv',
        ];

        $mime = explode( '.', $filename );

        if ( empty( $mime ) )
        {
            return 'application/octet-stream';
        }

        $mime = strtolower( array_pop( $mime ) );

        foreach ( $mimeContentTypes as $ext => $contentType )
        {
            if ( strpos( $mime, $ext ) === 0 )
            {
                return $contentType;
            }
        }

		return 'application/octet-stream';
	}

	/**
	 * @param $page int
	 * @param $pages int
	 *
	 *
	 */
	public static function calculateShowPages ( $page, $pages )
	{
		if ( $pages <= 0 )
		{
			$pages = 1;
		}

		$showPages = [ 1, $page, $pages ];

		if ( ( $page - 3 ) >= 1 )
		{
			for ( $i = $page; $i >= $page - 3; $i-- )
			{
				$showPages[] = $i;
			}
		}
		else if ( ( $page - 2 ) >= 1 )
		{
			for ( $i = $page; $i >= $page - 2; $i-- )
			{
				$showPages[] = $i;
			}
		}
		else if ( ( $page - 1 ) >= 1 )
		{
			for ( $i = $page; $i >= $page - 1; $i-- )
			{
				$showPages[] = $i;
			}
		}

		if ( ( $page + 3 ) <= $pages )
		{
			for ( $i = $page; $i <= $page + 3; $i++ )
			{
				$showPages[] = $i;
			}
		}
		else if ( ( $page + 2 ) <= $pages )
		{
			for ( $i = $page; $i <= $page + 2; $i++ )
			{
				$showPages[] = $i;
			}
		}
		else if ( ( $page + 1 ) <= $pages )
		{
			for ( $i = $page; $i <= $page + 1; $i++ )
			{
				$showPages[] = $i;
			}
		}

		$showPages = array_unique( $showPages );
		sort( $showPages );

		return $showPages;
	}

	public static function getNode ( $feed )
	{
		$nodeTable = $feed[ 'node_type' ] === 'account' ? 'accounts' : 'account_nodes';

		return DB::fetch( $nodeTable, $feed[ 'node_id' ] );
	}

	public static function webpToJpg ( $file )
	{
		if ( ! function_exists( 'imagecreatefromwebp' ) || ! function_exists( 'imagejpeg' ) )
		{
			return FALSE;
		}

		$jpg = imagecreatefromwebp( $file );

		if ( $jpg === FALSE )
		{
			return FALSE;
		}

		ob_clean();
		ob_start();
		$res   = imagejpeg( $jpg );
		$image = ob_get_clean();

		if ( $res === FALSE )
		{
			return FALSE;
		}

		return $image;
	}
	
	public static function createDBTables ()
	{
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$table_fs_accounts = $wpdb->prefix . 'fs_accounts';
		$sql = "CREATE TABLE $table_fs_accounts (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) DEFAULT NULL,
		  `driver` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `name` varchar(255) DEFAULT NULL,
		  `profile_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `username` varchar(100) DEFAULT NULL,
		  `password` varchar(255) DEFAULT NULL,
		  `profile_pic` text DEFAULT NULL,
		  `options` text DEFAULT NULL,
		  `proxy` varchar(100) DEFAULT NULL,
		  `is_public` tinyint(4) DEFAULT NULL,
		  `blog_id` int(11) DEFAULT NULL,
		  `status` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `error_msg` text DEFAULT NULL,
		  `for_all` tinyint(1) NOT NULL DEFAULT 0,
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		$table_fs_account_access_tokens = $wpdb->prefix . 'fs_account_access_tokens';
		$sql = "CREATE TABLE $table_fs_account_access_tokens (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `account_id` int(11) DEFAULT NULL,
		  `app_id` int(11) DEFAULT NULL,
		  `expires_on` timestamp NULL DEFAULT NULL,
		  `access_token` varchar(2500) DEFAULT NULL,
		  `access_token_secret` varchar(750) DEFAULT NULL,
		  `refresh_token` varchar(1000) DEFAULT NULL,
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		$table_fs_account_groups = $wpdb->prefix . 'fs_account_groups';
		$sql = "CREATE TABLE $table_fs_account_groups (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) DEFAULT NULL,
		  `blog_id` int(11) DEFAULT NULL,
		  `user_id` int(11) DEFAULT NULL,
		  `color` varchar(11) DEFAULT NULL,
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		$table_fs_account_groups_data = $wpdb->prefix . 'fs_account_groups_data';
		$sql = "CREATE TABLE $table_fs_account_groups_data (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `group_id` int(11) DEFAULT NULL,
		  `node_id` int(11) DEFAULT NULL,
		  `node_type` enum('account','node') DEFAULT NULL,
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		$table_fs_account_nodes = $wpdb->prefix . 'fs_account_nodes';
		$sql = "CREATE TABLE $table_fs_account_nodes (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) DEFAULT NULL,
		  `account_id` int(11) DEFAULT NULL,
		  `node_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `node_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `access_token` varchar(1000) DEFAULT NULL,
		  `name` varchar(350) DEFAULT NULL,
		  `added_date` timestamp NULL DEFAULT current_timestamp(),
		  `category` varchar(255) DEFAULT NULL,
		  `cover` varchar(750) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `driver` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `screen_name` varchar(350) DEFAULT NULL,
		  `is_public` tinyint(4) DEFAULT NULL,
		  `blog_id` int(11) DEFAULT NULL,
		  `error_msg` varchar(500) DEFAULT NULL,
		  `poster_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `for_all` tinyint(1) NOT NULL DEFAULT 0,
		  `data` text DEFAULT NULL,
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		$table_fs_account_node_status = $wpdb->prefix . 'fs_account_node_status';
		$sql = "CREATE TABLE $table_fs_account_node_status (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `node_id` int(11) DEFAULT NULL,
		  `user_id` int(11) DEFAULT NULL,
		  `categories` varchar(500) DEFAULT NULL,
		  `filter_type` varchar(2) DEFAULT NULL,
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		$table_fs_account_sessions = $wpdb->prefix . 'fs_account_sessions';
		$sql = "CREATE TABLE $table_fs_account_sessions (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `driver` varchar(50) DEFAULT NULL,
		  `username` varchar(255) DEFAULT NULL,
		  `settings` text DEFAULT NULL,
		  `cookies` text DEFAULT NULL,
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		$table_fs_account_status = $wpdb->prefix . 'fs_account_status';
		$sql = "CREATE TABLE $table_fs_account_status (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `account_id` int(11) DEFAULT NULL,
		  `user_id` int(11) DEFAULT NULL,
		  `categories` varchar(500) DEFAULT NULL,
		  `filter_type` varchar(2) DEFAULT NULL,
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		$table_fs_apps = $wpdb->prefix . 'fs_apps';
		$sql = "CREATE TABLE $table_fs_apps (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) DEFAULT NULL,
		  `driver` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `app_id` varchar(200) DEFAULT NULL,
		  `app_secret` varchar(200) DEFAULT NULL,
		  `app_key` varchar(200) DEFAULT NULL,
		  `app_authenticate_link` varchar(2000) DEFAULT NULL,
		  `is_public` tinyint(1) DEFAULT NULL,
		  `name` varchar(255) DEFAULT NULL,
		  `version` int(11) DEFAULT NULL,
		  `slug` varchar(48) DEFAULT NULL,
		  `data` text DEFAULT NULL,
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		$table_fs_feeds = $wpdb->prefix . 'fs_feeds';
		$sql = "CREATE TABLE $table_fs_feeds (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `post_id` bigint(20) DEFAULT NULL,
		  `user_id` int(11) DEFAULT NULL,
		  `node_id` int(11) DEFAULT NULL,
		  `node_type` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `driver` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `is_sended` tinyint(1) DEFAULT 0,
		  `status` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `error_msg` varchar(800) DEFAULT NULL,
		  `send_time` timestamp NULL DEFAULT current_timestamp(),
		  `interval` int(11) DEFAULT NULL,
		  `driver_post_id` varchar(255) DEFAULT NULL,
		  `visit_count` int(11) DEFAULT 0,
		  `feed_type` varchar(50) DEFAULT NULL,
		  `schedule_id` int(11) DEFAULT NULL,
		  `driver_post_id2` varchar(255) DEFAULT NULL,
		  `custom_post_message` text DEFAULT NULL,
		  `shared_from` varchar(255) DEFAULT NULL,
		  `share_on_background` tinyint(1) DEFAULT NULL,
		  `blog_id` int(11) DEFAULT NULL,
		  `is_seen` tinyint(1) DEFAULT NULL,
		  `wp_post_date` datetime DEFAULT NULL,
		  `data` text DEFAULT NULL,
		  `schedule_cycle` int(11) NOT NULL DEFAULT 0,
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		$table_fs_grouped_accounts = $wpdb->prefix . 'fs_grouped_accounts';
		$sql = "CREATE TABLE $table_fs_grouped_accounts (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) DEFAULT NULL,
		  `account_id` int(11) DEFAULT NULL,
		  `account_type` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `group_id` int(11) DEFAULT NULL,
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		$table_fs_post_comments = $wpdb->prefix . 'fs_post_comments';
		$sql = "CREATE TABLE $table_fs_post_comments (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `driver` text DEFAULT NULL,
		  `node_type` text DEFAULT NULL,
		  `account_id` int(11) DEFAULT NULL,
		  `comment` text DEFAULT NULL,
		  `comment_url` text DEFAULT NULL,
		  `error` text DEFAULT NULL,
		  `created_at` datetime DEFAULT current_timestamp(),
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		$table_fs_schedules = $wpdb->prefix . 'fs_schedules';
		$sql = "CREATE TABLE $table_fs_schedules (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) DEFAULT NULL,
		  `title` varchar(255) DEFAULT NULL,
		  `start_date` date DEFAULT NULL,
		  `end_date` date DEFAULT NULL,
		  `interval` int(11) DEFAULT NULL,
		  `status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `insert_date` timestamp NULL DEFAULT current_timestamp(),
		  `share_time` time DEFAULT NULL,
		  `filter_posts_date_range_from` date DEFAULT NULL,
		  `filter_posts_date_range_to` date DEFAULT NULL,
		  `post_type_filter` varchar(750) DEFAULT NULL,
		  `category_filter` varchar(255) DEFAULT NULL,
		  `post_sort` varchar(50) DEFAULT NULL,
		  `post_date_filter` varchar(50) DEFAULT NULL,
		  `post_ids` varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `next_execute_time` timestamp NULL DEFAULT NULL,
		  `custom_post_message` text DEFAULT NULL,
		  `share_on_accounts` text DEFAULT NULL,
		  `sleep_time_start` time DEFAULT NULL,
		  `sleep_time_end` time DEFAULT NULL,
		  `save_post_ids` varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `blog_id` int(11) DEFAULT NULL,
		  `dont_post_out_of_stock_products` tinyint(1) DEFAULT NULL,
		  `post_freq` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
		  `data` text DEFAULT NULL,
		  PRIMARY KEY  (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";
		dbDelta( $sql );
		
		/*$sql = "ALTER TABLE $table_fs_accounts
			ADD PRIMARY KEY (`id`) USING BTREE;";
		$query_result = $wpdb->query( $sql );
		dbDelta( $sql );
		
		$sql = "ALTER TABLE $table_fs_account_access_tokens
			ADD PRIMARY KEY (`id`) USING BTREE;";
		$query_result = $wpdb->query( $sql );*/
		
		// modified plugin code 
		$default_settings = array(
			'ai-poster-prompt_enable' => array('yes'),
			'ai-poster-prompt_select_ai_engine' => 'openai',
			'ai-poster-prompt_select_model' => 'gpt-3.5-turbo',
			'ai-poster-prompt_select_post_types' => array('post')
		);
		if(!empty($default_settings)){
			foreach($default_settings as $key => $value){
				add_option($key, $value);
			}
		}
		$table_schedule_posts = $wpdb->prefix . "schedule_posts"; 
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_schedule_posts (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			post_id int(10) NOT NULL DEFAULT '0',
			status varchar(15) DEFAULT 'not scheduled' NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta( $sql );
		// end
	}
}
