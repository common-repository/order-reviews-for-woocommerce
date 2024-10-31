<?php
namespace ORFW\Admin;

class Settings
{
    public static $instance;
	public static $pageSlug  = 'orfw-settings';
	public static $optPrefix = 'orfw_';
	public static $tabs;
	public static $tab;

    public static function getInstance()
    {
        if ( !self::$instance instanceof self )
            self::$instance = new self();

        return self::$instance;
    }

    private function __construct()
    {
		self::$tabs = array(
			'general' => array(
				'name' 		  => esc_html__( 'General', 'order-reviews-for-woocommerce' ),
				'title' 	  => esc_html__( 'General Settings', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'This is where you change the default behavior of the popup and configure general settings.', 'order-reviews-for-woocommerce' ),
			),
			'styles'  => array(
				'name' 		  => esc_html__( 'Styles', 'order-reviews-for-woocommerce' ),
				'title' 	  => esc_html__( 'Style Settings', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'This is where you control the visual stuffs like colors, font, etc.', 'order-reviews-for-woocommerce' ),
			),
			'content'  => array(
				'name' 		  => esc_html__( 'Content', 'order-reviews-for-woocommerce' ),
				'title' 	  => esc_html__( 'Content Settings', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'This is where you control the default content like texts, etc.', 'order-reviews-for-woocommerce' ),
			),
		);
		$tab	   = isset($_GET['tab']) ? preg_replace('/[^A-Za-z0-9_-]/i', '', $_GET['tab']) : '';
		self::$tab = in_array($tab, array_keys(self::$tabs)) ? $tab : 'general';

		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'addSections' ) );
		add_action( 'admin_init', array( $this, 'addFields' ) );
    }

	public function menu() 
	{	
		$menuArguments = array(
			array(
				self::$pageSlug, 
				esc_html__( 'Order Settings', 'order-reviews-for-woocommerce' ),
				esc_html__( 'Settings', 'order-reviews-for-woocommerce' ),
				'manage_woocommerce', 
				self::$pageSlug, 
				array( $this, 'renderPage' ),
				0
			),
			array(
				'woocommerce',
				esc_html__( 'Order Reviews', 'order-reviews-for-woocommerce' ),
				esc_html__( 'Order Reviews', 'order-reviews-for-woocommerce' ),
				'manage_woocommerce',
				'orfw-reviews-link',
				function()
				{
					wp_safe_redirect( admin_url( 'edit.php?post_type=orfw_review' ), 301 ); 
  					exit;
				},
				1
			),
		);

		foreach( $menuArguments as $argument )
		{	
			add_submenu_page( ...$argument );
		}
	}

	public static function fields()
	{
		return array(

			array(
				'id'          => 'template_wait_period',
				'type'        => 'number',
				'section'     => 'general',
				'label'       => esc_html__( 'Wait Period', 'order-reviews-for-woocommerce' ),
				'placeholder' => esc_html__( '12', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'How many hours after the order is completed the popup will first be shown to the user? (0 = instantly)', 'order-reviews-for-woocommerce' ),
				'value' 	  => esc_html__( '3', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => true,
			),

			array(
				'id'          => 'template_again_period',
				'type'        => 'number',
				'section'     => 'general',
				'label'       => esc_html__( 'Again Period', 'order-reviews-for-woocommerce' ),
				'placeholder' => esc_html__( '12', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'After the popup is skipped, ask for review again after X hours. (0 = instantly)', 'order-reviews-for-woocommerce' ),
				'value' 	  => esc_html__( '6', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => true,
			),

			array(
				'id'          => 'template_view_frequency',
				'type'        => 'number',
				'section'     => 'general',
				'label'       => esc_html__( 'Frequency', 'order-reviews-for-woocommerce' ),
				'placeholder' => esc_html__( '5', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'How many times the popup will show to customer if skipped? (0 = unlimited)', 'order-reviews-for-woocommerce' ),
				'value' 	  => esc_html__( '3', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => true,
			),

			array(
				'id'          => 'template_force_feedback',
				'type'        => 'toggle',
				'section'     => 'general',
				'label'       => esc_html__( 'Force Write Feedback', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'If checked, users must have to write a feedback.', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => true,
			),
			
			array(
				'id'          => 'template_force_bad_feedback',
				'type'        => 'toggle',
				'section'     => 'general',
				'label'       => esc_html__( 'Force Feedback for Bad Rating', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'If checked, users must have to write a feedback if the rating is equal to or less than 3.', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => true,
			),

			array(
				'id'      	  => 'force_review',
				'type'    	  => 'toggle',
				'section' 	  => 'general',
				'label'   	  => esc_html__( 'Force Review', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Force customer to give a review and hide the skip button', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),
			
			array(
				'id'          => 'template_use_custom_colors',
				'type'        => 'toggle',
				'section'     => 'styles',
				'label'       => esc_html__( 'Use Custom Colors', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Turn on if you want to use custom colors', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'          => 'template_header_background_color',
				'type'        => 'color',
				'section'     => 'styles',
				'value'		  => '#f4b248',
				'label'       => esc_html__( 'Header Background', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Set background for the template header', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'          => 'template_body_background_color',
				'type'        => 'color',
				'section'     => 'styles',
				'value'		  => '#ecf0f1',
				'label'       => esc_html__( 'Body Background', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Set background for the template body', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'          => 'template_header_text_color',
				'type'        => 'color',
				'section'     => 'styles',
				'value'		  => '#442a00',
				'label'       => esc_html__( 'Header text color', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Set header text color', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'          => 'template_header_highlight_text_color',
				'type'        => 'color',
				'section'     => 'styles',
				'value'		  => '#442a00',
				'label'       => esc_html__( 'Header highlighted text color', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Set header highlighted text color', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'          => 'template_body_text_color',
				'type'        => 'color',
				'section'     => 'styles',
				'value'		  => '#442a00',
				'label'       => esc_html__( 'Body text color', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Set body text color', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'          => 'template_submit_background_color',
				'type'        => 'color',
				'section'     => 'styles',
				'value'		  => '#f4b248',
				'label'       => esc_html__( 'Submit Button Background', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Set submit button background color', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'          => 'template_submit_text_color',
				'type'        => 'color',
				'section'     => 'styles',
				'value'		  => '#ecf0f1',
				'label'       => esc_html__( 'Button text color', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Set button background color', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'          => 'template_small_text_color',
				'type'        => 'color',
				'section'     => 'styles',
				'value'		  => '#888888',
				'label'       => esc_html__( 'Small text color', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Set small text color', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'          => 'template_skip_text_color',
				'type'        => 'color',
				'section'     => 'styles',
				'value'		  => '#f4b248',
				'label'       => esc_html__( 'Skip/Close color', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Set skip/close color', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'      	  => 'sri_card_bg',
				'type'    	  => 'color',
				'section' 	  => 'styles',
				'label'   	  => esc_html__( 'Review Info Box', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Background color of the card', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'      	  => 'sri_font_color',
				'type'    	  => 'color',
				'section' 	  => 'styles',
				'label'   	  => esc_html__( 'Review Info Color', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Review info color of the text ', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'      	  => 'sri_link_color',
				'type'    	  => 'color',
				'section' 	  => 'styles',
				'label'   	  => esc_html__( 'Review Info Link Color', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Link color (products) of the text', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'      => 'sri_font_size',
				'type'    => 'select',
				'section' => 'styles',
				'label'   => esc_html__( 'Review Info Font Size', 'order-reviews-for-woocommerce' ),
				'options' => array(
					'large'  => esc_html__( 'Large',  'order-reviews-for-woocommerce' ),
					'medium' => esc_html__( 'Medium', 'order-reviews-for-woocommerce' ),
					'small'  => esc_html__( 'Small',  'order-reviews-for-woocommerce' ),
				),
				'description' => esc_html__( 'Choose font size.', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'      	  => 'sri_font_style',
				'type'    	  => 'select',
				'section' 	  => 'styles',
				'label'   	  => esc_html__( 'Review Info Font Style', 'order-reviews-for-woocommerce' ),
				'options'     => array(
					'normal'  => esc_html__( 'Normal',  'order-reviews-for-woocommerce' ),
					'italic'  => esc_html__( 'Italic', 'order-reviews-for-woocommerce' ),
					'oblique' => esc_html__( 'Oblique', 'order-reviews-for-woocommerce' ),
				),
				'description' => esc_html__( 'Choose font style.', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'      	  => 'template_show_time',
				'type'    	  => 'toggle',
				'section' 	  => 'content',
				'label'   	  => esc_html__( 'Show Order Time', 'order-reviews-for-woocommerce' ),
				'description' => esc_html__( 'Display the order time in popup.', 'order-reviews-for-woocommerce' ),
				'value'		  => 'yes',
				'show_in_js'  => false,
			),

			array(
				'id'          => 'text_last_order_heading',
				'type'        => 'text',
				'section'     => 'content',
				'label'       => esc_html__( 'Your Last Order', 'order-reviews-for-woocommerce' ),
				'value' 	  => esc_attr__( 'Your Last Order', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'          => 'text_rate_order_heading',
				'type'        => 'text',
				'section'     => 'content',
				'label'       => esc_html__( 'Rate the order', 'order-reviews-for-woocommerce' ),
				'value' 	  => esc_attr__( 'Rate the order', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'          => 'text_write_feedback',
				'type'        => 'text',
				'section'     => 'content',
				'label'       => esc_html__( 'Write feedback', 'order-reviews-for-woocommerce' ),
				'value' 	  => esc_attr__( 'Write feedback', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),

			array(
				'id'          => 'text_footer',
				'type'        => 'textarea',
				'section'     => 'content',
				'label'       => esc_html__( 'Footer Text', 'order-reviews-for-woocommerce' ),
				'value' 	  => esc_attr__( 'Please provide your honest feedback!', 'order-reviews-for-woocommerce' ),
				'show_in_js'  => false,
			),
		);
	}

	public static function renderPage()
	{
	?>
        <div class="wrap jmph-settings-container">
            <div class="jmph-sets">
				<h1><?php echo esc_html__( 'Order Reviews Settings', 'order-reviews-for-woocommerce' ); ?></h1>

				<?php settings_errors(); ?>

				<nav class="nav-tab-wrapper">
					<?php foreach ( self::$tabs as $tabID => $tab ) { ?>
					<a href="?page=<?php echo esc_attr(self::$pageSlug); ?>&tab=<?php echo esc_attr($tabID); ?>" class="nav-tab <?php if ($tabID == self::$tab) echo esc_attr('nav-tab-active'); ?>"><?php echo esc_html($tab['name']); ?></a>
					<?php } ?>
				</nav>

				<form method="POST" action="options.php">
					<?php 
					wp_nonce_field('update-options');
					
					settings_fields( self::$optPrefix . self::$tab );
					do_settings_sections( self::$pageSlug );
					submit_button();
					?>
				</form>
			</div>
        </div>
	<?php
	}

	public function addSections()
	{
		if (! isset(self::$tabs[ self::$tab ]))
			return;
		
		add_settings_section( self::$optPrefix . self::$tab, self::$tabs[ self::$tab ]['title'], function() { echo esc_html(self::$tabs[ self::$tab ]['description']); }, self::$pageSlug );
	}

	public function addFields()
	{
		foreach ( self::fields() as $field )
		{
			$uniqueID = self::$optPrefix . $field['id'];

			add_settings_field(
				$uniqueID,
				$field['label'],
				array( $this, 'createField' ),
				self::$pageSlug,
				self::$optPrefix . $field['section'],
				array_merge( $field, array( 'unique_id' => $uniqueID ) )
			);

			switch ( $field['type'] )
			{
				case 'toggle':
				case 'checkbox':
				case 'radio':
					register_setting( self::$optPrefix . $field['section'], $uniqueID);
					break;
				
				default:
					register_setting( self::$optPrefix . $field['section'], $uniqueID, array( 'sanitize_callback' => 'esc_attr' ) );
			}
		}

	}

	public function createField( $field )
	{
		$value = get_option( $field['unique_id'] );

		if ( !isset($field['value']) )
			$field['value'] = '';

		switch ( $field['type'] )
		{
			case 'textarea':
				echo sprintf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>',
					esc_attr( $field['unique_id'] ),
					isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '',
					( $value == false ) ? esc_attr( $field['value'] ) : esc_attr( $value )
				);
				break;

			case 'select':
				$isMultiple = ( isset($field['multiple']) && $field['multiple'] == true ) ? true : false;
				$multiple   = ( $isMultiple ) ? 'multiple' : '';
				$brackets   = ( $isMultiple ) ? '[]' : '';

				echo '<select name="' . esc_attr( $field['unique_id'] . $brackets )  . '" ' . esc_attr($multiple) . '>';
				foreach( $field['options'] as $optValue => $optText )
				{
					echo sprintf( '<option value="%1$s" %3$s>%2$s</option>',
						esc_attr( $optValue ),
						esc_html( $optText ),
						($value === $optValue) ? 'selected' : ''
					);
				}
				echo '</select>';

				break;

			case 'toggle':
				$checked = $value === false ? ( 'yes' == $field['value'] ? 'checked' : '' ) : ( 'yes' == $value ? 'checked' : '' );
				echo sprintf( '<div class="jmph-toggle">
						<input type="checkbox" id="%1$s" name="%1$s" value="yes" %2$s>
						<label for="%1$s"></label>
					</div>',
					esc_attr( $field['unique_id'] ),
					$checked
				);
				break;

			case 'checkbox':
				$options 	 = $field['options'];
				$isMultiple  = (count($options) > 1) ? true : false;
				$brackets 	 = ($isMultiple) ? '[]' : '';

				foreach( $options as $optValue => $optText )
				{
					echo sprintf( '<input type="checkbox" id="%5$s" name="%1$s" value="%2$s" %4$s> <label for="%5$s">%3$s</label> <br>',
						esc_attr( $field['unique_id'] . $brackets ),
						esc_attr( $optValue ),
						esc_html( $optText ),
						($isMultiple) ? (is_array($value) && in_array($optValue, $value) ? 'checked' : '') : ($optValue == $value ? 'checked' : ''),
						esc_attr($field['unique_id'] . '_' . strtolower(preg_replace('/[^A-Za-z0-9_-]/i', '', $optValue)))
					);
				}
				break;
			
			case 'radio':
				$options = $field['options'];

				foreach( $options as $optValue => $optText )
				{
					echo sprintf( '<input type="radio" id="%5$s" name="%1$s" value="%2$s" %4$s> <label for="%5$s">%3$s</label><br>',
						esc_attr( $field['unique_id'] ),
						esc_attr( $optValue ),
						esc_html( $optText ),
						( is_array($value) && in_array($optValue, $value) ) ? 'checked' : '',
						esc_attr($field['unique_id'] . '_' . strtolower(preg_replace('/[^A-Za-z0-9_-]/i', '', $optValue)))
					);
				}
				break;

			case 'icons':
				echo sprintf( '<div id="%1$s" class="jmph-icons">
					<ul class="jmph-icons-selector">
						<li><span class="icon-checkmark"></span></li>
						<li><span class="icon-loop"></span></li>
						<li><span class="icon-stop"></span></li>
						<li><span class="icon-pause"></span></li>
						<li><span class="icon-cross"></span></li>
						<li><span class="icon-warning"></span></li>
						<li><span class="icon-star-full"></span></li>
						<li><span class="icon-clipboard"></span></li>
						<li><span class="icon-power-cord"></span></li>
						<li><span class="icon-ticket"></span></li>
						<li><span class="icon-cart"></span></li>
						<li><span class="icon-coin-dollar"></span></li>
						<li><span class="icon-compass"></span></li>
						<li><span class="icon-clock"></span></li>
						<li><span class="icon-hour-glass"></span></li>
						<li><span class="icon-spinner"></span></li>
						<li><span class="icon-lock"></span></li>
						<li><span class="icon-gift"></span></li>
						<li><span class="icon-fire"></span></li>
						<li><span class="icon-briefcase"></span></li>
						<li><span class="icon-airplane"></span></li>
						<li><span class="icon-shield"></span></li>
						<li><span class="icon-power"></span></li>
						<li><span class="icon-cloud-fill"></span></li>
						<li><span class="icon-cloud-download"></span></li>
						<li><span class="icon-cloud-upload"></span></li>
						<li><span class="icon-cloud-check"></span></li>
						<li><span class="icon-bookmarks"></span></li>
					</ul>
					<input type="hidden" name="%1$s" value="%2$s">
				</div>', esc_attr( $field['unique_id'] ), esc_attr( $value ) );
				break;

			case 'number':
				echo sprintf( '<input type="number" id="%1$s" name="%1$s" placeholder="%2$s" value="%3$s" min="%4$s" max="%5$s">',
					esc_attr( $field['unique_id'] ),
					isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '',
					( !is_numeric($value) && empty($value) ) ? esc_attr( $field['value'] ) : esc_attr( $value ),
					( isset($field['min']) ) ? esc_attr( $field['min'] ) : 0,
					( isset($field['max']) ) ? esc_attr( $field['max'] ) : PHP_INT_MAX
				);
				break;
			
			default:
				echo sprintf( '<input id="%1$s" name="%1$s" type="%2$s" placeholder="%3$s" value="%4$s">',
					esc_attr( $field['unique_id'] ),
					esc_attr( $field['type'] ),
					isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '',
					( !is_numeric($value) && empty($value) ) ? esc_attr( $field['value'] ) : esc_attr( $value )
				);
		}

		if ( isset( $field['description'] ) )
			echo sprintf( '<p class="description">%s</p>', esc_html( $field['description'] ) );
	}
}
