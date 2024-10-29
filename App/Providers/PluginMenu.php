<?php

namespace FSPoster\App\Providers;

trait PluginMenu
{
	public function initMenu ()
	{
		add_action( 'init', function () {

			add_action( 'admin_menu', function () {
				add_menu_page( 'AItoSocial', 'AItoSocial', 'read', 'ai-poster', [
					Pages::class,
					'load_page'
				], Pages::asset( 'Base', 'img/logo_xs.png' ), 90 );

				add_submenu_page( 'ai-poster', fsp__( 'Dashboard' ), fsp__( 'Dashboard' ), 'read', 'ai-poster', [
					Pages::class,
					'load_page'
				] );

				add_submenu_page( 'ai-poster', fsp__( 'Accounts' ), fsp__( 'Accounts' ), 'read', 'ai-poster-accounts', [
					Pages::class,
					'load_page'
				] );
				
				// modified plugin code 
				$calendar_page = add_submenu_page( 'ai-poster', fsp__( 'Calendar' ), fsp__( 'Calendar' ), 'read', 'ai-poster-calendar', array( $this, 'func_load_calendar' ) );
				add_action( 'load-' . $calendar_page, array( $this, 'func_load_calendar_page_media_js' ) );
				// end

				add_submenu_page( 'ai-poster', fsp__( 'Logs' ), fsp__( 'Logs' ), 'read', 'ai-poster-logs', [
					Pages::class,
					'load_page'
				] );

				add_submenu_page( 'ai-poster', fsp__( 'Apps' ), fsp__( 'Apps' ), 'read', 'ai-poster-apps', [
					Pages::class,
					'load_page'
				] );
				
				// modified plugin code 
				add_submenu_page( 'ai-poster', fsp__( 'AI Prompts' ), fsp__( 'AI Prompts' ), 'read', 'ai-poster-prompts', array( $this, 'func_ai_content_generate_settings' ) );
				// end

				if ( ( current_user_can( 'administrator' ) || defined( 'AI_POSTER_IS_DEMO' ) ) )
				{
					add_submenu_page( 'ai-poster', fsp__( 'Settings' ), fsp__( 'Settings' ), 'read', 'ai-poster-settings', [
						Pages::class,
						'load_page'
					] );
				}
			} );
			// modified plugin code 
			add_action( 'admin_init', array( $this, 'func_add_settings_fields' ) );
		} );
	}
	
	// modified plugin code 
	
	public function func_load_calendar_page_media_js(){
		wp_enqueue_media();
		wp_enqueue_style( 'fsp-share', Pages::asset( 'Share', 'css/fsp-share.css' ), [ 'fsp-ui' ], NULL );
	}
	
	public function aits_load_header(){
		?>
		<div class="fsp-nav">
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Dashboard' ? 'active' : '' ); ?>" href="?page=ai-poster"><?php echo fsp__( 'Dashboard' ); ?></a>
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Accounts' ? 'active' : '' ); ?>" href="?page=ai-poster-accounts"><?php echo fsp__( 'Accounts' ); ?></a>
			<a class="fsp-nav-link <?php echo( $_GET[ 'page' ] === 'ai-poster-calendar' ? 'active' : '' ); ?>" href="?page=ai-poster-calendar"><?php echo fsp__( 'Calendar' ); ?></a>
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Logs' ? 'active' : '' ); ?>" href="?page=ai-poster-logs"><?php echo fsp__( 'Logs' ); ?></a>
			<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Apps' ? 'active' : '' ); ?>" href="?page=ai-poster-apps"><?php echo fsp__( 'Apps' ); ?></a>
			<a class="fsp-nav-link <?php echo( $_GET[ 'page' ] === 'ai-poster-prompts' ? 'active' : '' ); ?>" href="?page=ai-poster-prompts"><?php echo fsp__( 'Prompt Settings' ); ?></a>
			<?php if ( ( current_user_can( 'administrator' ) || defined( 'AI_POSTER_IS_DEMO' ) ) ) { ?>
				<a class="fsp-nav-link <?php echo( $fsp_params[ 'page_name' ] === 'Settings' ? 'active' : '' ); ?>" href="?page=ai-poster-settings"><?php echo fsp__( 'Settings' ); ?></a>
			<?php } ?>
		</div>
		<?php
	}
	
	public function aits_load_default_style(){
		echo '<style>
			#wpcontent {
				padding-left: 0 !important;
			}
			
			[dir="rtl"] #wpcontent {
				padding-right: 0 !important;
			}

			body {
				background: #f3f7fa !important;
			}
		</style>';
	}
	
	public function func_load_calendar(){
		$this->aits_load_default_style();
		?>
		<div class="fsp-container">
			<div class="fsp-header">
				<?php $this->aits_load_header();?>
			</div>
			<div class="fsp-body calendar-container">
				<div class="fsp-row">
					<div class="fsp-col-12 fsp-title">
						<div class="fsp-title-text">
							<?php echo fsp__( 'Calendar' ); ?>
						</div>
					</div>
					<div class="fsp-col-12 fsp-row">
						<div class="fsp-col-12 fsp-col-md-12 fsp-calendar-left">
							<div class="fsp-card fsp-calendar-card">
								<div class="fsp-card-body">
									<div id="prev_month" class="fsp-calendar-arrow">
										<i class="fas fa-chevron-left"></i>
									</div>
									<div id="calendar_area" class="fsp-calendar-area"></div>
									<div id="next_month" class="fsp-calendar-arrow">
										<i class="fas fa-chevron-right"></i>
									</div>
								</div>
							</div>
						</div>
						<div id="schedulePopup" class="fsp-col-12 fsp-col-md-12 aits_schedule_list popup">
							<div class="popup-content">
								<div class="fsp-card">
									<div class="fsp-card-title">
										<?php echo fsp__( 'SCHEDULED POSTS' ); ?>
									</div>
									<div class="fsp-card-body fsp-calendar-posts plan_posts_list"></div>
									<div class="fsp-calendar-emptiness">
										<img src="<?php echo Pages::asset( 'Schedules', 'img/empty-calendar.svg' ); ?>">
									</div>
								</div>
								<a id="closePopup"><span class="dashicons dashicons-no-alt"></span></a>
							</div>
						</div>
					</div>
				</div>

				<script>
					jQuery( document ).ready( function () {
						FSPoster.load_script( '<?php echo Pages::asset( 'Base', 'js/fsp-custom-calendar.js' ); ?>' );
					} );
				</script>
			</div>
		</div>
		<?php
		wp_enqueue_style( 'fsp-schedules', Pages::asset( 'Schedules', 'css/fsp-schedules.css' ), [ 'fsp-ui' ], NULL );
	}
	
	public function func_ai_content_generate_settings(){
		$this->aits_load_default_style();
		?>
		<div class="fsp-container">
			<div class="fsp-header">
				<?php $this->aits_load_header();?>
			</div>
			<div class="fsp-body">
				<?php
				// show message when updated
				if ( isset( $_GET['settings-updated'] ) ) {
					add_settings_error( 'aip_messages', 'aip_message', esc_html__( 'Settings Saved', 'aitosocial' ), 'success' );
				}
			 
				// show error/update messages
				settings_errors( 'aip_messages' );
				
				?>
				<form method="post" action="options.php" class="ai-poster_form">
					<?php
					settings_fields( 'ai-poster-prompt-setting' ); // $option_group( A settings group name. This must match the group name used in register_setting(), which is the page slug name on which the form is to appear. ). To display the hidden fields and handle security of your options form
					do_settings_sections( 'ai-poster-prompt-setting' ); // $page The slug name of the page whose settings sections you want to output. This should match the page name used in add_settings_section(). 
					submit_button();
					?>
				</form>
			</div>
		</div>
		<?php
	}
	
	/**
	 *  Function that fills the section with the desired content. The function should echo its output.
	 *
	 * @since    1.0.0
	 */
	public function func_print_sm_d_prompt_section_info() {
		echo '<p>'.esc_html_e( 'Social Media default prompts', 'aitosocial' ).'</p>';
	}
	
	/**
	 * Generate option page settings sections and fields.
	 *
	 * @since    1.0.0
	 */
	public function func_add_settings_fields(){
        add_settings_section(
			'ai-poster-prompt_setting_section', // $id - String for use in the 'id' attribute of tags.
			'', // $title - Title of the section.
			'', // Function that fills the section with the desired content. The function should echo its output.
			'ai-poster-prompt-setting' // $page - The type of settings page on which to show the section (general, reading, writing, media etc.)
		);
		
		$ai_content_enable = 'ai-poster-prompt_enable';
		$_enable_options = array(
			'yes' => __( 'Enable', 'aitosocial' ),
		);
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		    $ai_content_enable, // $id - String for use in the 'id' attribute of tags.
			__( 'Status', 'aitosocial' ), // Title of the field.
		    array($this, 'func_create_checkbox_input'), //callback function for checkbox input
		    'ai-poster-prompt-setting', //settings page on which to show the field 
		    'ai-poster-prompt_setting_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'checkbox',
				"id"           => $ai_content_enable,
				"value"           => $_enable_options,
				'desc'  => __( 'Enable for AI generted content.', 'aitosocial' ),
            )
		);
		
		$_select_ai_engine = 'ai-poster-prompt_select_ai_engine';
		$ai_engine_options = array(
			'openai' => __( 'OpenAI', 'aitosocial' ),
		);
		
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		    $_select_ai_engine, // $id - String for use in the 'id' attribute of tags.
			__( 'Select AI Engine', 'aitosocial' ), // Title of the field.
		    array($this, 'func_create_select_input'), //callback function for select input
		    'ai-poster-prompt-setting', //settings page on which to show the field 
		    'ai-poster-prompt_setting_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'select',
				"id"           => $_select_ai_engine,
				"value"           => $ai_engine_options,
				'desc'  => __( 'Select AI Engine.', 'aitosocial' ),
            )
		);
		
		$_select_model = 'ai-poster-prompt_select_model';
		
		$ai_model_options = [
			'gpt-3.5-turbo' => __( 'gpt 3.5 turbo', 'aitosocial' ),
			'gpt-3.5-turbo-0613' => __( 'gpt 3.5 turbo 0613', 'aitosocial' ),
			'gpt-3.5-turbo-16k' => __( 'gpt 3.5 turbo 16k', 'aitosocial' ),
			'gpt-3.5-turbo-16k-0613' => __( 'gpt 3.5 turbo 16k 0613', 'aitosocial' ),
			'gpt-4' => __( 'gpt 4', 'aitosocial' ),
			'gpt-4-0613' => __( 'gpt 4 0613', 'aitosocial' ),
		];
		
		
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		    $_select_model, // $id - String for use in the 'id' attribute of tags.
			__( 'Select AI Model', 'aitosocial' ), // Title of the field.
		    array($this, 'func_create_select_input'), //callback function for select input
		    'ai-poster-prompt-setting', //settings page on which to show the field 
		    'ai-poster-prompt_setting_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'select',
				"id"           => $_select_model,
				"value"           => $ai_model_options,
				'desc'  => __( 'Select AI Model.', 'aitosocial' ),
            )
		);
		
		$args = array(
            'public'   => true,
            '_builtin' => true
        );
 
        $output = 'names'; // 'names' or 'objects' (default: 'names')
        $operator = 'and'; // 'and' or 'or' (default: 'and')
 
        $post_types = get_post_types( $args, $output, $operator );
		
		if(!empty($post_types)){
			if(isset($post_types['attachment'])){
				unset($post_types['attachment']);
			}
			$_select_post_types = 'ai-poster-prompt_select_post_types';
		
		    add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		        $_select_post_types, // $id - String for use in the 'id' attribute of tags.
			    __( 'Select Post Types', 'aitosocial' ), // Title of the field.
		        array($this, 'func_create_checkbox_input'), //callback function for checkbox input
		        'ai-poster-prompt-setting', //settings page on which to show the field 
		        'ai-poster-prompt_setting_section',// The section of the settings page in which to show the box
			    array( // The array of arguments to pass to the callback.
				    'type'         => 'checkbox',
				    "id"           => $_select_post_types,
				    "value"           => $post_types,
				    'desc'  => __( 'Select post types for generate AI content.', 'aitosocial' ),
                )
		    );
		
		}
		
		$social_media_options = [
			'fb' => __( 'Facebook', 'aitosocial' ),
			'twitter' => __( 'Twitter', 'aitosocial' ),
			'linkedin' => __( 'LinkedIn', 'aitosocial' ),
			'threads' => __( 'Threads', 'aitosocial' ),
			'instagram' => __( 'Instagram', 'aitosocial' ),
			'tiktok' => __( 'TikTok', 'aitosocial' ),
			'pinterest' => __( 'Pinterest', 'aitosocial' ),
		];
		
		$sm_options = 'ai-poster-social_media_options';
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
			$sm_options, // $id - String for use in the 'id' attribute of tags.
			__( 'Generate AI Content', 'aitosocial' ), // Title of the field.
			array($this, 'func_create_checkbox_input'), //callback function for checkbox input
			'ai-poster-prompt-setting', //settings page on which to show the field 
			'ai-poster-prompt_setting_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'checkbox',
				"id"           => $sm_options,
				"value"           => $social_media_options,
				'desc'  => __( 'Select social media\'s for generate AI content.', 'aitosocial' ),
			)
		);
		
		$content_publish_after = 'ai-poster-content_publish_after';
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		    $content_publish_after, // $id - String for use in the 'id' attribute of tags.
			__( 'Publish After( in minutes )', 'aitosocial' ), // Title of the field.
		    array($this, 'func_create_number_input'), //callback function for number input
		    'ai-poster-prompt-setting', //settings page on which to show the field 
		    'ai-poster-prompt_setting_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'number',
				"id"           => $content_publish_after,
				'desc'  => __( 'Content publish to social media\'s after ( x ) minutes', 'aitosocial' ),
            )
		);
		
		$_api_key = 'ai-poster-prompt_api_key';
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		    $_api_key, // $id - String for use in the 'id' attribute of tags.
			__( 'API Key', 'aitosocial' ), // Title of the field.
		    array($this, 'func_create_text_input'), //callback function for text input
		    'ai-poster-prompt-setting', //settings page on which to show the field 
		    'ai-poster-prompt_setting_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'text',
				"id"           => $_api_key,
				'desc'  => __( 'Enter your API Key here.', 'aitosocial' ),
            )
		);
		
		add_settings_section(
			'ai-poster-prompt_sm_default_prompt_section', // $id - String for use in the 'id' attribute of tags.
			__( 'Default Prompts', 'aitosocial' ), // $title - Title of the section.
			array($this, 'func_print_sm_d_prompt_section_info'), // Function that fills the section with the desired content. The function should echo its output.
			'ai-poster-prompt-setting' // $page - The type of settings page on which to show the section (general, reading, writing, media etc.)
		);
		
		$_fb_prompt = 'ai-poster-prompt_fb_prompt';
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		    $_fb_prompt, // $id - String for use in the 'id' attribute of tags.
			__( 'Facebook', 'aitosocial' ), // Title of the field.
		    array($this, 'func_create_text_input'), //callback function for text input
		    'ai-poster-prompt-setting', //settings page on which to show the field 
		    'ai-poster-prompt_sm_default_prompt_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'text',
				"id"           => $_fb_prompt,
				"class"           => 'widefat',
				'desc'  => __( 'Enter default prompt for Facebook.', 'aitosocial' ),
				'default'  => __( 'Write one engaging Facebook post on the below article of maximum 220 characters, including two social hashtags', 'aitosocial' ),
            )
		);
		
		$_twitter_prompt = 'ai-poster-prompt_twitter_prompt';
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		    $_twitter_prompt, // $id - String for use in the 'id' attribute of tags.
			__( 'Twitter', 'aitosocial' ), // Title of the field.
		    array($this, 'func_create_text_input'), //callback function for text input
		    'ai-poster-prompt-setting', //settings page on which to show the field 
		    'ai-poster-prompt_sm_default_prompt_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'text',
				"id"           => $_twitter_prompt,
				"class"           => 'widefat',
				'desc'  => __( 'Enter default prompt for Twitter.', 'aitosocial' ),
				'default'  => __( 'Write one engaging Twitter post on the below article of maximum 220 characters, including two social hashtags', 'aitosocial' ),
            )
		);
		
		$_linkedin_prompt = 'ai-poster-prompt_linkedin_prompt';
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		    $_linkedin_prompt, // $id - String for use in the 'id' attribute of tags.
			__( 'LinkedIn', 'aitosocial' ), // Title of the field.
		    array($this, 'func_create_text_input'), //callback function for text input
		    'ai-poster-prompt-setting', //settings page on which to show the field 
		    'ai-poster-prompt_sm_default_prompt_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'text',
				"id"           => $_linkedin_prompt,
				"class"           => 'widefat',
				'desc'  => __( 'Enter default prompt for LinkedIn.', 'aitosocial' ),
				'default'  => __( 'Write one engaging LinkedIn posts, including two hashtags, consisting of max 100 words', 'aitosocial' ),
            )
		);
		
		$_threads_prompt = 'ai-poster-prompt_threads_prompt';
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		    $_threads_prompt, // $id - String for use in the 'id' attribute of tags.
			__( 'Threads', 'aitosocial' ), // Title of the field.
		    array($this, 'func_create_text_input'), //callback function for text input
		    'ai-poster-prompt-setting', //settings page on which to show the field 
		    'ai-poster-prompt_sm_default_prompt_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'text',
				"id"           => $_threads_prompt,
				"class"           => 'widefat',
				'desc'  => __( 'Enter default prompt for Threads.', 'aitosocial' ),
				'default'  => __( 'Write one engaging Threads social media posts on the below article of max 450 characters and include two hashtags', 'aitosocial' ),
            )
		);
		
		$_instagram_prompt = 'ai-poster-prompt_instagram_prompt';
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		    $_instagram_prompt, // $id - String for use in the 'id' attribute of tags.
			__( 'Instagram', 'aitosocial' ), // Title of the field.
		    array($this, 'func_create_text_input'), //callback function for text input
		    'ai-poster-prompt-setting', //settings page on which to show the field 
		    'ai-poster-prompt_sm_default_prompt_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'text',
				"id"           => $_instagram_prompt,
				"class"           => 'widefat',
				'desc'  => __( 'Enter default prompt for Instagram.', 'aitosocial' ),
				'default'  => __( 'Write one engaging Instagram posts on the below article of max 450 characters and include two hashtags', 'aitosocial' ),
            )
		);
		
		$_tiktok_prompt = 'ai-poster-prompt_tiktok_prompt';
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		    $_tiktok_prompt, // $id - String for use in the 'id' attribute of tags.
			__( 'TikTok', 'aitosocial' ), // Title of the field.
		    array($this, 'func_create_text_input'), //callback function for text input
		    'ai-poster-prompt-setting', //settings page on which to show the field 
		    'ai-poster-prompt_sm_default_prompt_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'text',
				"id"           => $_tiktok_prompt,
				"class"           => 'widefat',
				'desc'  => __( 'Enter default prompt for TikTok.', 'aitosocial' ),
				'default'  => __( 'Write one engaging TikTok text posts on the below article of max 450 characters and include two hashtags', 'aitosocial' ),
            )
		);
		
		$_pinterest_prompt = 'ai-poster-prompt_pinterest_prompt';
		add_settings_field( //You MUST register any options you use with add_settings_field() or they won't be saved and updated automatically. 
		    $_pinterest_prompt, // $id - String for use in the 'id' attribute of tags.
			__( 'Pinterest', 'aitosocial' ), // Title of the field.
		    array($this, 'func_create_text_input'), //callback function for text input
		    'ai-poster-prompt-setting', //settings page on which to show the field 
		    'ai-poster-prompt_sm_default_prompt_section',// The section of the settings page in which to show the box
			array( // The array of arguments to pass to the callback.
				'type'         => 'text',
				"id"           => $_pinterest_prompt,
				"class"           => 'widefat',
				'desc'  => __( 'Enter default prompt for Pinterest.', 'aitosocial' ),
				'default'  => __( 'Write one engaging Pinterest text posts on the below article of max 450 characters and include two hashtags', 'aitosocial' ),
            )
		);
		
		register_setting( 'ai-poster-prompt-setting', $ai_content_enable );
		register_setting( 'ai-poster-prompt-setting', $_select_ai_engine );
		register_setting( 'ai-poster-prompt-setting', $_select_model );
		register_setting( 'ai-poster-prompt-setting', $_select_post_types );
		register_setting( 'ai-poster-prompt-setting', $sm_options );
		register_setting( 'ai-poster-prompt-setting', $content_publish_after );
		register_setting( 'ai-poster-prompt-setting', $_api_key );
		register_setting( 'ai-poster-prompt-setting', $_twitter_prompt );
		register_setting( 'ai-poster-prompt-setting', $_fb_prompt );
		register_setting( 'ai-poster-prompt-setting', $_linkedin_prompt );
		register_setting( 'ai-poster-prompt-setting', $_threads_prompt );
		register_setting( 'ai-poster-prompt-setting', $_instagram_prompt );
		register_setting( 'ai-poster-prompt-setting', $_tiktok_prompt );
		register_setting( 'ai-poster-prompt-setting', $_pinterest_prompt );
    }
	
	/**
	 * Function that fills the field with the desired inputs as part of the larger form. Name and id of the input should match the $id given to this function. The function should echo its output.
	 *
	 * @since    1.0.0
	 */
	public function func_create_select_input($args){
		$ai_options = ( $args['value'] ) ? $args['value'] : array();
		$option = (get_option($args['id'])) ? get_option($args['id']) : array();
		$html = '';
		if(!empty($ai_options)){
			$html .= '<select name="'  . esc_attr($args["id"]) . '" id="'  . esc_attr($args["id"]) . '">';
			foreach($ai_options as $key => $ai_option):
				$selected = ($key == $option) ? 'selected' : '';
				$html .= '<option value="'.esc_attr($key).'"  '.$selected.'>' .esc_html($ai_option). '</option>';
			endforeach;
			$html .= '</select>';
		}
		if($args["desc"]) {
			$html .= '<p class="description">'.esc_html($args["desc"]).'</p>';
		}
		echo wp_kses_post($html);
	}
	
	/**
	 * Function that fills the field with the desired inputs as part of the larger form. Name and id of the input should match the $id given to this function. The function should echo its output.
	 *
	 * @since    1.0.0
	 */
	public function func_create_checkbox_input($args){
		if($args['id'] == 'ai-poster-social_media_options'){
			global $aits_fs;
			if($aits_fs->is_plan('free', true)){
				unset($args['value']['twitter']);
				unset($args['value']['linkedin']);
				unset($args['value']['threads']);
				unset($args['value']['instagram']);
				unset($args['value']['tiktok']);
				unset($args['value']['pinterest']);
			}
		}
		$_options = ( $args['value'] ) ? $args['value'] : array();
		$options = (get_option($args['id'])) ? get_option($args['id']) : array();
		if(!empty($_options)){
			foreach($_options as $key => $_option):
				$checked = in_array($key, $options) ? 'checked="checked"' : '';
				$html .= '<input type="checkbox" name="'  . esc_attr($args["id"]) . '[]" value="'.esc_attr($key).'" '.$checked.'/> '.ucfirst(esc_html($_option)).'&nbsp;&nbsp;<br/>';
			endforeach;
		}
		if($args["desc"]) {
			$html .= '<p class="description">'.esc_html($args["desc"]).'</p>';
		}
		echo wp_kses_post($html);
	}
	
	/**
	 * Function that fills the field with the desired inputs as part of the larger form. Name and id of the input should match the $id given to this function. The function should echo its output.
	 *
	 * @since    1.0.0
	 */
	public function func_create_text_input($args) {

		if(isset($args["default"])) {
			$default = $args["default"];
		}else{
			$default = false;
		}
		
		echo '<input type="text" class="'  . esc_attr($args["class"]) . '" id="'  . esc_attr($args["id"]) . '" name="'  . esc_attr($args["id"]) . '" value="' . esc_attr(get_option($args["id"], $default)) . '" />';
		if($args["desc"]) {
			echo "<p class='description'>".esc_html($args["desc"])."</p>";
		}
		
	}
	
	/**
	 * Function that fills the field with the desired inputs as part of the larger form. Name and id of the input should match the $id given to this function. The function should echo its output.
	 *
	 * @since    1.0.0
	 */
	public function func_create_number_input($args) {

		if(isset($args["default"])) {
			$default = $args["default"];
		}else{
			$default = false;
		}
		
		echo '<input type="number" class="'  . esc_attr($args["class"]) . '" id="'  . esc_attr($args["id"]) . '" name="'  . esc_attr($args["id"]) . '" value="' . esc_attr(get_option($args["id"], $default)) . '" />';
		if($args["desc"]) {
			echo "<p class='description'>".esc_html($args["desc"])."</p>";
		}
		
	}
	
	// end

	public function app_disable ()
	{
		register_uninstall_hook( FS_ROOT_DIR . '/init.php', [ Helper::class, 'removePlugin' ] );

		Helper::deleteOption( 'poster_plugin_installed', TRUE );

		Pages::controller( 'Base', 'App', 'disable' );
	}
	
}
