<?php
// modified plugin code
namespace FSPoster\App\Providers;

use FSPoster\App\Libraries\fb\Facebook;
use FSPoster\App\Libraries\twitter\Twitter;
use FSPoster\App\Libraries\linkedin\Linkedin;
use FSPoster\App\Libraries\pinterest\Pinterest;
use FSPoster\App\Libraries\instagram\InstagramAppMethod;

class FrontEnd
{
	public function __construct ()
	{
		if ( ! Helper::pluginDisabled() )
		{
			$this->addSocialMetaTags();
			add_action( 'wp', [ $this, 'boot' ] );
		}
		
		add_filter( 'cron_schedules', array( $this, 'func_custom_schedule_intervals' ) );
		add_action( 'init', array( $this, 'func_create_schedule_events' ) );
		//add_action( 'init', array( $this, 'test_func_schedule_ai_generated_content' ), 99 );
		add_action( 'generate_schedule_ai_content', array( $this, 'func_schedule_ai_generated_content' ) );
		
	}
	
	public function func_custom_schedule_intervals( $schedules ) {
		$schedules['every_five_minutes'] = array(
			'interval' => 300,
			'display' => __('Every Five Minutes')
		);
		return $schedules;
	}
	
	function func_create_schedule_events(){
		if ( ! wp_next_scheduled( 'generate_schedule_ai_content' ) ) {
			wp_schedule_event( time(), 'every_five_minutes', 'generate_schedule_ai_content' );
		}
	}
	
	/*public function test_func_schedule_ai_generated_content(){
		if(isset($_GET['test_schedule'])) {
			$this->func_schedule_ai_generated_content();
		}
	}*/
	
	public function func_schedule_ai_generated_content(){
		global $wpdb;
		$tablename = $wpdb->prefix . "schedule_posts";
		$status = "not scheduled";
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $tablename WHERE status = %s ORDER BY id ASC LIMIT 1", $status) , ARRAY_A );
		if(!empty($results)){
			$post_id = $results[0]['post_id'];
			$disable_ai_content = get_post_meta($post_id,'disable_ai_content',true);
			if($disable_ai_content == 'yes'){
				$wpdb->delete( $tablename, array( 'post_id' => $post_id ) );
			}else{
				$this->func_create_share_ai_generated_content($post_id);
			}
		}
	}
	
	public function func_create_share_ai_generated_content($post_id){
		global $wpdb;
		$table = $wpdb->prefix . 'fs_schedules';
		$tablename = $wpdb->prefix . "schedule_posts";
		$change_status = "scheduled";
		$post = get_post($post_id);
		$post_title = $post->post_title;
		$post_content = $post->post_content;
		$post_author_id = $post->post_author;
		$url = get_permalink($post);
		
		$title = 'Scheduled post: "'.$post_title.'"';
		$post_date = ($post->post_modified) ? $post->post_modified : $post->post_date;
		$post_type = $post->post_type;
		$currentDate = strtotime($post_date);

		$_api_key = get_option('ai-poster-prompt_api_key');
		$api_key = ( $_api_key ) ? $_api_key : '';
		if($api_key){
			$_selected_model = get_option('ai-poster-prompt_select_model');
			$api_url = 'https://api.openai.com/v1/chat/completions';
			$api_data = array();
			$social_media_instructions = [];
			$_fb_prompt = get_option('ai-poster-prompt_fb_prompt');
			$_twitter_prompt = get_option('ai-poster-prompt_twitter_prompt');
			$_linkedin_prompt = get_option('ai-poster-prompt_linkedin_prompt');
			$_threads_prompt = get_option('ai-poster-prompt_threads_prompt');
			$_instagram_prompt = get_option('ai-poster-prompt_instagram_prompt');
			$_tiktok_prompt = get_option('ai-poster-prompt_tiktok_prompt');
			$_pinterest_prompt = get_option('ai-poster-prompt_pinterest_prompt');
			
			if(!empty($_fb_prompt)){
				$social_media_instructions['fb'] = $_fb_prompt;
			}
			
			if(!empty($_twitter_prompt)){
				$social_media_instructions['twitter'] = $_twitter_prompt;
			}
			
			if(!empty($_linkedin_prompt)){
				$social_media_instructions['linkedin'] = $_linkedin_prompt;
			}
			
			if(!empty($_threads_prompt)){
				$social_media_instructions['threads'] = $_threads_prompt;
			}
			
			if(!empty($_instagram_prompt)){
				$social_media_instructions['instagram'] = $_instagram_prompt;
			}
			
			if(!empty($_tiktok_prompt)){
				$social_media_instructions['planly'] = $_tiktok_prompt;
			}
			
			if(!empty($_pinterest_prompt)){
				$social_media_instructions['pinterest'] = $_pinterest_prompt;
			}
			
			$_social_media_options = get_option('ai-poster-social_media_options');
			
			$nodes_list = [];
			$userId = $post_author_id;
			$accounts = $wpdb->get_results( $wpdb->prepare( "
				SELECT tb2.id, tb2.driver, tb1.filter_type, tb1.categories, 'account' AS node_type 
				FROM " . $wpdb->prefix . 'fs_account_status' . " tb1
				INNER JOIN " . $wpdb->prefix . 'fs_accounts' . " tb2 ON tb2.id=tb1.account_id
				WHERE tb1.user_id=%d AND (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d", [
				$userId,
				$userId,
				get_current_blog_id()
			] ), ARRAY_A );

			$active_nodes = $wpdb->get_results( $wpdb->prepare( "
				SELECT tb2.id, tb2.driver, tb2.node_type, tb1.filter_type, tb1.categories FROM " . $wpdb->prefix . 'fs_account_node_status' . " tb1
				LEFT JOIN " . $wpdb->prefix . 'fs_account_nodes' . " tb2 ON tb2.id=tb1.node_id
				WHERE tb1.user_id=%d AND (tb2.user_id=%d OR tb2.is_public=1) AND tb2.blog_id=%d", [
				$userId,
				$userId,
				get_current_blog_id()
			] ), ARRAY_A );

			$active_nodes = array_merge( $accounts, $active_nodes );
			
			foreach ( $active_nodes as $nodeInf )
			{
				$nodes_list[] = $nodeInf[ 'driver' ] . ':' . $nodeInf[ 'node_type' ] . ':' . $nodeInf[ 'id' ] . ':' . htmlspecialchars( $nodeInf[ 'filter_type' ] ) . ':' . htmlspecialchars( $nodeInf[ 'categories' ] );
			}
			
			$share_on_active_accounts = '';
			$share_on_accounts = [];
			
			if ( ! empty( $nodes_list ) )
			{
				foreach ( $nodes_list as $social_account )
				{
					if ( is_string( $social_account ) )
					{
						$social_account = explode( ':', $social_account );	
						if ( count( $social_account ) !== 5 )
						{
							continue;
						}
						$share_on_accounts[$social_account[ 0 ]][] = ( $social_account[ 1 ] === 'account' ? 'account' : 'node' ) . ':' . $social_account[ 2 ];
					}
				}
			}
			
			if(!empty($share_on_accounts)){
				foreach($share_on_accounts as $key => $value){
					if(!in_array($key,$_social_media_options)){
						unset($share_on_accounts[$key]);
					}
				}
			}
			
			if(!empty($social_media_instructions)){
				foreach($social_media_instructions as $key => $value){
					if(!in_array($key,$_social_media_options)){
						unset($social_media_instructions[$key]);
					}
				}
			}
			
			$default_messages = array(
				'fb' => '{title}',
				'fb_h' => '{title}',
				'instagram' => '{title}',
				'instagram_h' => '{title}',
				'linkedin' => '{title}',
				'threads' => '{title}',
				'twitter' => '{title}',
				'pinterest' => '{content_short_497}',
				'planly' => '{content_full}',
			);
			
			$filter_posts_date_range_from = '1000-00-00';
			$filter_posts_date_range_to   = '9999-12-31';
	
			$data[ 'instagram_pin_the_post' ] = 0;
			$data[ 'autoRescheduleEnabled' ]  = 0;
			$data[ 'autoRescheduleCount' ]    = 1;
			$data[ 'autoReschdulesDone' ]     = 0;
			
			$_content_publish_after = ( get_option('ai-poster-content_publish_after') ) ? get_option('ai-poster-content_publish_after') : 1;
			$futureDate = $currentDate + ( 60 * $_content_publish_after );
			$interval = 1;
			
			$start_date = date("Y-m-d", $futureDate);
			$start_time = date("H:i:s", $futureDate);
			$next_execute_time = date("Y-m-d H:i:s", $futureDate);
			
			$custom_messages = [];
			
			foreach($social_media_instructions as $key => $val){

				// prompt
				if ( isset( $post_title ) && !empty( $post_title ) ) {
					$post_content_for_gpt = substr($post_content, 0, 1600);
					$api_data['messages'] = [
					   ['role' => 'user', 'content' => $val.' : '.$post_content_for_gpt]
					];
				}

				// model
				$_selected_model = ( $_selected_model ) ? $_selected_model : 'gpt-3.5-turbo';
				$api_data['model'] = $_selected_model;
				
				// temp
				$api_data['temperature'] = 0.2;
				
				$results = $this->func_rest_api_external_trigger( $api_url, $api_data, 'POST', array(
					'Content-Type: application/json',
					'Authorization: Bearer ' . $api_key
				) );
				

				$results = $this->_check_error_by_engine( $results, 'openai' );
				
				if ( isset( $results['status'] ) && !empty( $results['status'] ) && $results['status'] == 'error' ) {
					return apply_filters( 'completion_error', array(
						'status' => 'error',
						'message' => ( isset( $results['message'] ) && !empty( $results['message'] ) ? $results['message'] : esc_html__( 'Error occurred while getting completions.', 'aitosocial' ) ),
					) , array() );
				}

				$results = $this->_render_results_by_engine( $results, 'openai', array() );
				
				$custom_messages[$key] = $results[0]->text ? $results[0]->text : '{title}';
				if($key == 'instagram' || $key == 'fb'){
					$arr_key = $key.'_h';
					$custom_messages[$arr_key] = $results[0]->text ? $results[0]->text : '{title}';
				}
			}
			
			$custom_post_message = array_merge($default_messages,$custom_messages);
			
			$_custom_messages = empty( $custom_post_message ) ? NULL : json_encode( $custom_post_message );
			
			if(!empty($share_on_accounts)){
				$share_on_accounts = array_map(function($el){
					return implode( ',', $el ); 
				}, $share_on_accounts);
			}
			
			$share_on_active_accounts = implode( ',', array_values($share_on_accounts) );
			
			$data_arr = array(
				'user_id' => $post_author_id,
				'title' => $title,
				'start_date' => $start_date,
				'end_date' => NULL,
				'interval' => $interval,
				'status' => 'active',
				'insert_date' => $post_date,
				'share_time' => $start_time,
				'filter_posts_date_range_from' => $filter_posts_date_range_from,
				'filter_posts_date_range_to' => $filter_posts_date_range_to,
				'post_type_filter' => $post_type,
				'category_filter' => NULL,
				'post_sort' => 'random2',
				'post_date_filter' => 'custom',
				'post_ids' => $post->ID,
				'next_execute_time' => $next_execute_time,
				'custom_post_message' => $_custom_messages,
				'share_on_accounts' => $share_on_active_accounts,
				'sleep_time_start' => NULL,
				'sleep_time_end' => NULL,
				'save_post_ids' => NULL,
				'blog_id' => 1,
				'dont_post_out_of_stock_products' => 0,
				'post_freq' => 'once',
				'data' => json_encode( $data ),
			);
			
			$format = array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s'
			);
			
			$query_result = $wpdb->insert(
				$table,
				$data_arr,
				$format
			);
			
			if($query_result){
				$wpdb->query( $wpdb->prepare( "UPDATE $tablename SET status = %s WHERE post_id = %d", $change_status, $post_id ) );
			}
			
		}
	}
	
	public function func_rest_api_external_trigger( $api_url = '', $formData = null, $method = 'POST', $headers = array() ) {

		$postfields = json_encode($formData);
		$curl = curl_init();
		$curl_props = [
			CURLOPT_URL => $api_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_SSL_VERIFYPEER => $this->func_rest_api_to_verify_ssl(),
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method
		];

		if ( isset( $formData ) && !empty( $formData ) ) {
			$curl_props[ CURLOPT_POSTFIELDS ] = json_encode($formData);
		}

		if ( !empty( $headers ) ) {
			$curl_props[ CURLOPT_HTTPHEADER ] = $headers;
		}

		curl_setopt_array($curl, $curl_props);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return array( 'code' => 'api_error', 'message' => esc_html__( "Error #:" , 'aitosocial' ) . $err );
		} else {
			return json_decode( $response, true );
		}
	}
	
	public function func_rest_api_to_verify_ssl() {
		$verify = false;
		if ( is_ssl() ) {
			$verify = true;
		}
		return apply_filters( 'func_rest_api_to_verify_ssl', $verify );
	}
	
	/**
	 * check for API error by engine
	 */
	private function _check_error_by_engine( $results, $engine = 'openai' ) {
        $error = null;
        switch( $engine ) {
			case 'openai':
                if ( isset( $results['error'] ) && !empty( $results['error'] ) ) {
                    $error = array(
                        'status' => 'error',
                        'message' => ( isset( $results['error']['message'] ) && !empty( $results['error']['message'] ) ? esc_attr( $results['error']['message'] ) : __( 'Unknown API Error' , 'aitosocial' ) )
                    );
                } // end - $results['error']
                break;
        }
        return ( isset( $error ) && !empty( $error ) ? $error : $results );
	}
	
	/**
	 * render results based on engine
	 */
	private function _render_results_by_engine( $results, $engine = 'openai', $data = array() ) {
        $list = array();
        $count = 0;
		$local_time  = current_datetime();
        $current_time = $local_time->getTimestamp() + $local_time->getOffset();
        switch( $engine ) {
			case 'openai':
                if ( isset( $results['model'] ) && !empty($results['model']) && strpos($results['model'], 'gpt-') === 0 ) {
                    if ( isset( $results['choices'] ) && !empty( $results['choices'] ) && is_array( $results['choices'] ) ) {
                        foreach( $results['choices'] as $result ) {
                            $count++;
                            if ( isset( $result['message']['content'] ) && !empty( $result['message']['content'] ) ) {
                                $list[] = (object) array(
                                    'id' => $results['id'] . '__' . $count,
                                    'text' => $result['message']['content'],
                                    'added_on' => $results['created'] ? $results['created'] : $current_time,
                                    'usage' => ( isset( $results['usage'] ) && !empty( $results['usage'] ) ? $results['usage'] : [] )
                                );
                            } // end - $result['text']
                        }
                    } // end - $results['completions']
                } else {
                    if ( isset( $results['choices'] ) && !empty( $results['choices'] ) && is_array( $results['choices'] ) ) {
                        foreach( $results['choices'] as $result ) {
                            $count++;
                            if ( isset( $result['text'] ) && !empty( $result['text'] ) ) {
                                $list[] = (object) array(
                                    'id' => $results['id'] . '__' . $count,
                                    'text' => ( isset( $data['mode'] ) && !empty($data['mode']) && $data['mode'] === 'insert' ? $result['text'] : $this->_filter_completion_text( $result['text'], $data['stop'], $data ) ),
                                    'added_on' => $results['created'] ? $results['created'] : $current_time,
                                    'usage' => ( isset( $results['usage'] ) && !empty( $results['usage'] ) ? $results['usage'] : [] )
                                );
                            } // end - $result['text']
                        }
                    } // end - $results['completions']
                }
                break;  
        }
        return $list;
	}
	
	/**
	 * filter completions text
	 */
	private function _filter_completion_text( $text = '', $filters = array(), $data = array() ) {
        $text = str_replace("<|endoftext|>","", $text );
        if ( !empty( $filters ) && is_array( $filters ) ) {
            foreach( $filters as $filter ) {
                $text = str_replace($filter,"", $text );
            }
        } // end - $filters
        return ( isset( $data['start_text'] ) && !empty( $data['start_text'] ) ? esc_attr( $data['start_text'] ) : '' ) . trim( $text );
	}

	public function boot ()
	{
		$this->checkVisits();

		$this->fetchAccessToken();

		$this->fbRedirect();
		$this->instagramRedirect();
		$this->twitterRedirect();
		$this->linkedinRedirect();
		$this->pinterestRedirect();

		$this->standartFSApp();
	}

	public function addSocialMetaTags ()
	{
		$the_metas = function ( $type ) {
			if ( ! is_singular() )
			{
				return;
			}

			$allowedPostTypes = Helper::getOption( 'meta_tags_allowed_post_types', 'post|page|product' );

			$currentPostType = get_post_type();

			if ( ! in_array( $currentPostType, explode( '|', $allowedPostTypes ) ) )
			{
				return;
			}

			$thumb   = get_the_post_thumbnail_url();
			$excerpt = get_the_excerpt();
			$title   = get_the_title();

			$key = 'name';

			if ( $type === 'twitter' )
			{
				echo '<meta name="twitter:card" content="summary_large_image" />';
			}
			else if ( $type === 'og' )
			{
				echo '<meta property="og:type" content="article" />';
				$key = 'property';
			}

			if ( ! empty( $title ) )
			{
				echo '<meta ' . $key . '="' . $type . ':title" content="' . htmlspecialchars( $title ) . '" />';
			}

			if ( ! empty( $excerpt ) )
			{
				echo '<meta ' . $key . '="' . $type . ':description" content="' . htmlspecialchars( $excerpt ) . '" />';
			}

			if ( ! empty( $thumb ) )
			{
				echo '<meta ' . $key . '="' . $type . ':image" content="' . $thumb . '" />';
			}
		};

		if ( Helper::getOption( 'meta_tags_enable_twitter_tags', 0 ) == 1 )
		{
			add_action( 'wp_head', function () use ( $the_metas ) {
				$the_metas( 'twitter' );
			} );
		}

		if ( Helper::getOption( 'meta_tags_enable_open_graph', 0 ) == 1 )
		{
			add_action( 'wp_head', function () use ( $the_metas ) {
				$the_metas( 'og' );
			} );
		}
	}

	public function checkVisits ()
	{
		if ( is_single() || is_page() )
		{
			$feed_id = Request::get( 'feed_id', '0', 'int' );

			$driver = '';

			if ( empty( $_SERVER[ 'HTTP_REFERER' ] ) && $feed_id > 0 )
			{
				$feedInf = DB::DB()->get_row( DB::DB()->prepare( 'SELECT driver FROM ' . DB::table( 'feeds' ) . ' WHERE id=%d', $feed_id ), 'ARRAY_A' );

				$driver = isset( $feedInf[ 'driver' ] ) ? $feedInf[ 'driver' ] : $driver;
			}

			if ( ! isset( $_COOKIE[ 'fsp_last_visited_' . $feed_id ] ) && isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) && ! preg_match( '/abacho|accona|AddThis|AdsBot|ahoy|AhrefsBot|AISearchBot|alexa|altavista|anthill|appie|applebot|arale|araneo|AraybOt|ariadne|arks|aspseek|ATN_Worldwide|Atomz|baiduspider|baidu|bbot|bingbot|bing|Bjaaland|BlackWidow|BotLink|bot|boxseabot|bspider|calif|CCBot|ChinaClaw|christcrawler|CMC\/0\.01|combine|confuzzledbot|contaxe|CoolBot|cosmos|crawler|crawlpaper|crawl|curl|cusco|cyberspyder|cydralspider|dataprovider|digger|DIIbot|DotBot|downloadexpress|DragonBot|DuckDuckBot|dwcp|EasouSpider|ebiness|ecollector|elfinbot|esculapio|ESI|esther|eStyle|Ezooms|facebookexternalhit|facebook|facebot|fastcrawler|FatBot|FDSE|FELIX IDE|fetch|fido|find|Firefly|fouineur|Freecrawl|froogle|gammaSpider|gazz|gcreep|geona|Getterrobo-Plus|get|girafabot|golem|googlebot|\-google|grabber|GrabNet|griffon|Gromit|gulliver|gulper|hambot|havIndex|hotwired|htdig|HTTrack|ia_archiver|iajabot|IDBot|Informant|InfoSeek|InfoSpiders|INGRID\/0\.1|inktomi|inspectorwww|Internet Cruiser Robot|irobot|Iron33|JBot|jcrawler|Jeeves|jobo|KDD\-Explorer|KIT\-Fireball|ko_yappo_robot|label\-grabber|larbin|legs|libwww-perl|linkedin|Linkidator|linkwalker|Lockon|logo_gif_crawler|Lycos|m2e|majesticsEO|marvin|mattie|mediafox|mediapartners|MerzScope|MindCrawler|MJ12bot|mod_pagespeed|moget|Motor|msnbot|muncher|muninn|MuscatFerret|MwdSearch|NationalDirectory|naverbot|NEC\-MeshExplorer|NetcraftSurveyAgent|NetScoop|NetSeer|newscan\-online|nil|none|Nutch|ObjectsSearch|Occam|openstat.ru\/Bot|packrat|pageboy|ParaSite|patric|pegasus|perlcrawler|phpdig|piltdownman|Pimptrain|pingdom|pinterest|pjspider|PlumtreeWebAccessor|PortalBSpider|psbot|rambler|Raven|RHCS|RixBot|roadrunner|Robbie|robi|RoboCrawl|robofox|Scooter|Scrubby|Search\-AU|searchprocess|search|SemrushBot|Senrigan|seznambot|Shagseeker|sharp\-info\-agent|sift|SimBot|Site Valet|SiteSucker|skymob|SLCrawler\/2\.0|slurp|snooper|solbot|speedy|spider_monkey|SpiderBot\/1\.0|spiderline|spider|suke|tach_bw|TechBOT|TechnoratiSnoop|templeton|teoma|titin|topiclink|twitterbot|twitter|UdmSearch|Ukonline|UnwindFetchor|URL_Spider_SQL|urlck|urlresolver|Valkyrie libwww\-perl|verticrawl|Victoria|void\-bot|Voyager|VWbot_K|wapspider|WebBandit\/1\.0|webcatcher|WebCopier|WebFindBot|WebLeacher|WebMechanic|WebMoose|webquest|webreaper|webspider|webs|WebWalker|WebZip|wget|whowhere|winona|wlm|WOLP|woriobot|WWWC|XGET|xing|yahoo|YandexBot|YandexMobileBot|yandex|yeti|Zeus/i', $_SERVER[ 'HTTP_USER_AGENT' ] ) && ( ! empty( $_SERVER[ 'HTTP_REFERER' ] ) || $driver == 'discord' || $driver == 'tumblr' ) )
			{
				global $post;

				if ( isset( $post->ID ) && $feed_id > 0 )
				{
					$post_id = $post->ID;

					DB::DB()->query( DB::DB()->prepare( "UPDATE " . DB::table( 'feeds' ) . " SET visit_count=visit_count+1 WHERE id=%d AND post_id=%d AND status = 'ok'", [
						$feed_id,
						$post_id
					] ) );

					setcookie( 'fsp_last_visited_' . $feed_id, '1', Date::epoch( 'now', '+30 seconds' ), COOKIEPATH, COOKIE_DOMAIN );
				}
			}
		}
	}

	public function fetchAccessToken ()
	{
		if ( Request::get( 'fb_callback', '0', 'int' ) === 1 )
		{
			$res = Facebook::getAccessToken();
		}
		if ( Request::get( 'instagram_callback', '0', 'int' ) === 1 )
		{
			$res = InstagramAppMethod::getAccessToken();
		}
		else if ( Request::get( 'twitter_callback', '0', 'int' ) === 1 )
		{
			$res = Twitter::getAccessToken();
		}
		else if ( Request::get( 'linkedin_callback', '0', 'int' ) === 1 )
		{
			$res = Linkedin::getAccessToken();
		}
		else if ( Request::get( 'pinterest_callback', '0', 'int' ) === 1 || Request::get( 'state', '', 'str' ) === 'pinterest_callback' )
		{
			$res = Pinterest::getAccessToken();
		}

		if ( isset( $res[ 'status' ] ) && ! $res[ 'status' ] )
		{
			$esc_html = ! ( isset( $res[ 'esc_html' ] ) && $res[ 'esc_html' ] === FALSE );
			SocialNetwork::error( $res[ 'error_msg' ], $esc_html );
		}
		else if ( isset( $res ) )
		{
			SocialNetwork::closeWindow();
		}
	}

	public function fbRedirect ()
	{
		$appId = Request::get( 'fb_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . Facebook::getLoginURL( $appId ) );
			exit();
		}

	}

	public function instagramRedirect ()
	{
		$appId = Request::get( 'instagram_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . InstagramAppMethod::getLoginURL( $appId ) );
			exit();
		}

	}

	public function twitterRedirect ()
	{
		$appId = Request::get( 'twitter_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location:' . Twitter::getLoginURL( $appId ) );
			exit();
		}
	}

	public function linkedinRedirect ()
	{
		$appId = Request::get( 'linkedin_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . Linkedin::getLoginURL( $appId ) );
			exit();
		}
	}

	public function pinterestRedirect ()
	{
		$appId = Request::get( 'pinterest_app_redirect', '0', 'int' );

		if ( $appId > 0 )
		{
			header( 'Location: ' . Pinterest::getLoginURL( $appId ) );
			exit();
		}
	}

	public function standartFSApp ()
	{
		$supportedFSApps = [
			'fb',
			'instagram',
			'twitter',
			'linkedin',
			'pinterest',
		];

		$sn = Request::get( 'sn', '', 'string', $supportedFSApps );

		if ( empty( $sn ) )
		{
			return;
		}

		$callback  = Request::get( 'fs_app_redirect', '0', 'num', [ '1' ] );
		$proxy     = Request::get( 'proxy', '', 'string' );
		$slug      = Request::get( 'slug', '', 'string' );
		$name      = Request::get( 'name', '', 'string' );
		$appId     = Request::get( 'app_id', '', 'string' );
		$appKey    = Request::get( 'app_key', '', 'string' );
		$appSecret = Request::get( 'app_secret', '', 'string' );

		$appInf = DB::fetch( 'apps', [
			'driver' => $sn,
			'slug'   => $slug
		] );

		if ( empty( $appInf ) && ! empty( $slug ) )
		{
			$appInf = [
				'driver'     => $sn,
				'name'       => $name,
				'app_id'     => $appId,
				'app_key'    => $appKey,
				'app_secret' => $appSecret,
				'slug'       => $slug
			];

			DB::DB()->insert( DB::table( 'apps' ), $appInf );

			$appInf[ 'id' ] = DB::DB()->insert_id;
		}

		if ( ! empty( $proxy ) )
		{
			$proxy = strrev( $proxy );
		}

		if ( ! $callback )
		{
			return;
		}

		if ( $sn === 'fb' )
		{
			$access_token = Request::get( 'access_token', '', 'string' );

			if ( empty( $access_token ) )
			{
				return;
			}

			$fb  = new Facebook( $appInf, $access_token, $proxy );
			$res = $fb->authorize();
		}
		else if ( $sn === 'instagram' )
		{
			$access_token = Request::get( 'access_token', '', 'string' );

			if ( empty( $access_token ) )
			{
				return;
			}

			$res = InstagramAppMethod::authorize( $appInf[ 'id' ], $access_token, $proxy );
		}
		else if ( $sn === 'twitter' )
		{
			$oauth_token        = Request::get( 'oauth_token', '', 'string' );
			$oauth_token_secret = Request::get( 'oauth_token_secret', '', 'string' );

			if ( empty( $oauth_token ) || empty( $oauth_token_secret ) )
			{
				return;
			}

			$res = Twitter::authorize( $appInf, $oauth_token, $oauth_token_secret, $proxy );
		}
		else if ( $sn === 'linkedin' )
		{
			$access_token  = Request::get( 'access_token', '', 'string' );
			$expire_in     = Request::get( 'expire_in', '', 'string' );
			$refresh_token = Request::get( 'refresh_token', '', 'string' );

			if ( empty( $access_token ) || empty( $expire_in ) )
			{
				return;
			}

			$res = Linkedin::authorize( $appInf[ 'id' ], $access_token, $expire_in, $refresh_token, $proxy );
		}
		else if ( $sn === 'pinterest' )
		{
			$accessToken  = Request::get( 'access_token', '', 'string' );
			$refreshToken = Request::get( 'refresh_token', '', 'string' );
			$expiresIn    = Request::get( 'expires_in', '', 'string' );

			if ( empty( $accessToken ) || empty( $refreshToken ) )
			{
				return;
			}

			$refreshToken = urldecode( $refreshToken );

			$res = Pinterest::authorize( $appInf[ 'id' ], $accessToken, $refreshToken, $expiresIn, $proxy );
		}

		if ( isset( $res[ 'status' ] ) && $res[ 'status' ] == FALSE )
		{
			$esc_html = ! ( isset( $res[ 'esc_html' ] ) && $res[ 'esc_html' ] === FALSE );
			SocialNetwork::error( $res[ 'error_msg' ], $esc_html );
		}
		else if ( isset( $res ) )
		{
			SocialNetwork::closeWindow();
		}
	}
}
