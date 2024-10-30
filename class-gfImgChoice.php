<?php

GFForms::include_addon_framework();

class GFImgChoiceAddon extends GFAddOn {

	protected $_version = GF_PC_IMAGE_CHOICES_ADDON_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'image-choices-for-gravity-forms';
	protected $_path = 'image-choices-for-gravity-forms/gf-img-choices.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Image Choices For Gravity Forms';
	protected $_short_title = 'Image Picker';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFImgChoiceAddon
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFImgChoiceAddon();
		}

		return self::$_instance;
	}


	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();

    	add_filter(	'gform_tooltips', array($this, 'gfic_add_tooltips'));
		add_action( 'gform_editor_js', array($this,'gfic_editor_script') );
		add_action('gform_enqueue_scripts', array($this, 'add_frontend_enqueue_styles'), 10, 2);
		add_filter( 'gform_field_choice_markup_pre_render', array( $this, 'gfic_label_image_field'), 10, 4 );
		add_filter( 'gform_field_css_class', array( $this, 'gfic_custom_class' ), 10, 3 );
		if ( PC_IC_GF_MIN_2_5 ) {
			add_filter( 'gform_field_settings_tabs', array( $this, 'gfic_fields_settings_tab'), 10, 2 );
			add_action( 'gform_field_settings_tab_content_img_choice_tab', array($this, 'gfic_fields_settings_tab_content'), 10, 2 );
		} else {
			add_action('gform_field_advanced_settings', array($this, 'gfic_advanced_settings'), 10, 2);
		}
	}

	public function get_menu_icon() {
		return '<svg width="74" height="58" viewBox="0 0 74 58" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 42.3846L20.5195 23.8651C21.2695 23.1151 22.1599 22.5202 23.1398 22.1142C24.1198 21.7083 25.1701 21.4994 26.2308 21.4994C27.2915 21.4994 28.3418 21.7083 29.3217 22.1142C30.3016 22.5202 31.192 23.1151 31.9421 23.8651L50.4615 42.3846M45.0769 37L50.1349 31.9421C50.8849 31.192 51.7753 30.5971 52.7552 30.1912C53.7352 29.7853 54.7855 29.5763 55.8462 29.5763C56.9068 29.5763 57.9571 29.7853 58.9371 30.1912C59.917 30.5971 60.8074 31.192 61.5574 31.9421L72 42.3846M7.38462 55.8462H66.6154C68.0435 55.8462 69.4131 55.2788 70.4229 54.269C71.4327 53.2592 72 51.8896 72 50.4615V7.38462C72 5.95653 71.4327 4.58693 70.4229 3.57712C69.4131 2.56731 68.0435 2 66.6154 2H7.38462C5.95653 2 4.58693 2.56731 3.57712 3.57712C2.56731 4.58693 2 5.95653 2 7.38462V50.4615C2 51.8896 2.56731 53.2592 3.57712 54.269C4.58693 55.2788 5.95653 55.8462 7.38462 55.8462ZM45.0769 15.4615H45.1056V15.4903H45.0769V15.4615ZM46.4231 15.4615C46.4231 15.8186 46.2813 16.161 46.0288 16.4134C45.7763 16.6659 45.4339 16.8077 45.0769 16.8077C44.7199 16.8077 44.3775 16.6659 44.125 16.4134C43.8726 16.161 43.7308 15.8186 43.7308 15.4615C43.7308 15.1045 43.8726 14.7621 44.125 14.5097C44.3775 14.2572 44.7199 14.1154 45.0769 14.1154C45.4339 14.1154 45.7763 14.2572 46.0288 14.5097C46.2813 14.7621 46.4231 15.1045 46.4231 15.4615Z" stroke="#252748" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}

    /**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'gfic_admin_script',
				'src'     => $this->get_base_url() . '/assets/js/image-choice-end.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery', 'wp-color-picker' ),
				'enqueue'  => array(
						array( 'admin_page' => array( 'form_editor', 'plugin_settings' ) ),
				)
			)
		);

		return array_merge( parent::scripts(), $scripts );
	}

	public function styles() {
		$styles = array(
			array(
				'handle'  => 'gfic_admin_style',
				'src'     => $this->get_base_url() . '/assets/css/gfic_admin_style.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'admin_page' => array( 'form_editor', 'plugin_settings' ) ),
				)
			),
			array(
				'handle'  => 'gfic_front_style',
				'src'     => $this->get_base_url() . '/assets/css/gfic_front_style.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( 'radio', 'checkbox' ) )
				)
			)
		);

		return array_merge( parent::styles(), $styles );
	}


	public function init_admin() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		parent::init_admin();
	}

	public function admin_enqueue_scripts() {
		if ( $this->is_form_editor() ) {
			wp_enqueue_media();// For Media Library
		}
	}


	public function gfic_fields_settings_tab( $tabs, $form ) {
		$tabs[] = array(
			// Define the unique ID for your tab.
			'id'             => 'img_choice_tab',
			// Define the title to be displayed on the toggle button your tab.
			'title'          => 'Image Picker',
			// Define an array of classes to be added to the toggle button for your tab.
			'toggle_classes' => array( 'gfic_toggle_1', 'gfic_toggle_2' ),
			// Define an array of classes to be added to the body of your tab.
			'body_classes'   => array( 'gfic_toggle_class' ),
		);
	 
		return $tabs;
	}


    public function gfic_fields_settings_tab_content( $form) {
    	?>

        <li class="img_choice_field_setting field_setting">
            <ul>
                <li class="imgchoice_check" style="margin-bottom: 15px">
                    <input type="checkbox" id="gfic_enable_imgchoice" onclick="SetFieldProperty('initImageGField', this.checked);" />
                    <label for="gfic_enable_imgchoice" class="inline">
                        <?php _e("Enable Image Picker Options", "gravityforms"); ?> 
                        <?php gform_tooltip("enable_image_choices"); ?>
                    </label>
                </li>
				<li class="imgchoice_column" style="margin-bottom: 15px">
					<label for="gfic_imgcolumn_label" class="section_label">
						<?php _e("Choose Column", "gravityforms"); ?> 
						<?php gform_tooltip("img_column"); ?>
					</label>
					<select name="pcafe_imgp_column" id="pcafe_imgp_column" onChange="SetFieldProperty('pcafeImgpColumn', this.value);">
						<option value="">Choose</option>
						<option value="auto">Auto</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">9</option>
						<option value="9">9</option>
						<option value="10">10</option>
					</select>
				</li>
				<li class="pcafe_imgp_new_design" style="margin-bottom: 15px">
                    <input type="checkbox" id="pcafe_imgp_new_style" onclick="SetFieldProperty('pcafeNewStyle', this.checked);" />
                    <label for="pcafe_imgp_new_style" class="inline">
                        <?php _e("Use new design", "gravityforms"); ?> 
                        <?php gform_tooltip("pcafe_imgp_new_design"); ?>
                    </label>
                </li>
            </ul>
        </li>


    	<?php
    }


    public function gfic_advanced_settings( $position, $form_id ) {
		if ( $position == 550 ) {
		    $this->gfic_fields_settings_tab_content( GFAPI::get_form( $form_id ) );
		}
	}

	public function gfic_label_image_field( $choice_markup, $choice, $field, $value ) {

		if (  property_exists($field, 'initImageGField') && $field->initImageGField ) {
			
			$img = (isset($choice['imageUrl'])) ? $choice['imageUrl'] : '';
			$imgID = (isset($choice['imageId'])) ? $choice['imageId'] : '';

			if($img) {
				$img_markup = "<img src=". $img ." />";
			} else {
				$img_markup = "";
			}

			if( $field->pcafeNewStyle === true ) {
				return str_replace( $choice['text'] . "</label>", "<span class='pcafe_imgp_wrap'>".$img_markup."</span>" . "<span class='pcafe_imgp_text'>". $choice['text'] ."</span></label>", $choice_markup );
			}
		
			return str_replace( "</label>", $img_markup."</label>", $choice_markup );
		}

		return $choice_markup;
	}

	function gfic_custom_class( $classes, $field, $form ) {

		$color = $this->get_plugin_setting('pcafe_imgp_color');

		if ( $field->initImageGField === true && $field->type == 'radio' || $field->initImageGField === true && $field->type == 'checkbox' ) {
			if( is_admin() ) {
				$classes .= ' pcafe_imgp_admin';
			}

			$classes .= $field->pcafeNewStyle === true ? ' pcafe_image_picker' : ' pc_image_choice';

			$classes .= $field->pcafeImgpColumn != '' ? ' pcafe_imgp_col_'.$field->pcafeImgpColumn : ' pcafe_imgp_col_auto';

			if( $color != '' ) {
				$classes .= ' pcafe_imgp_color_'. ltrim($color, '#' );
			}
		}

		return $classes;
	}

	function add_frontend_enqueue_styles( $form, $is_ajax ) {
		$color = $this->get_plugin_setting('pcafe_imgp_color');

		$fields_data = [];

		$styles = '';

		foreach ($form['fields'] as $field) {

			if ($field->type === "radio" || $field->type === "checkbox") {
				$form = (array) GFFormsModel::get_form_meta($field->formId);
				$fields_data[] = GFFormsModel::get_field($form, $field->id);

				$styles .= $this->inline_styles( $color, $field->pcafeImgpColumn );
			}
		}

		if (count($fields_data) === 0) {
			return;
		}

		wp_add_inline_style("gfic_front_style", $styles);
	}

	function inline_styles( $color, $column ) {
		$css = '';

		if( $color != '' ) {
			$style_class = '.pcafe_imgp_color_'. ltrim($color, '#' );

			$css .= "
				.pcafe_image_picker$style_class .gfield_checkbox .gchoice input:checked+label,
				.pcafe_image_picker$style_class .gfield_radio .gchoice input:checked+label {
					border-color: $color;
				}
				.pcafe_image_picker$style_class .gfield_checkbox .gchoice input:checked+label .pcafe_imgp_text,
				.pcafe_image_picker$style_class .gfield_radio .gchoice input:checked+label .pcafe_imgp_text {
					color: $color;
				}
				.pcafe_image_picker .gfield_checkbox .gchoice .pcafe_imgp_wrap:before,
				.pcafe_image_picker .gfield_radio .gchoice .pcafe_imgp_wrap:before{
					background-color: $color;
				}
			";
		}

		if( $column != '' && $column != 'auto') {
			$column_class = '.pcafe_imgp_col_'.$column;

			$css .= "
				.gfield$column_class .gfield_radio, .gfield$column_class .gfield_checkbox{
					display: grid;
					grid-template-columns: repeat($column, 1fr);
					gap: 20px;
				}
				@media only screen and (min-width: 768px) and (max-width: 991px) { 
					.gfield$column_class .gfield_radio, .gfield$column_class .gfield_checkbox{
						grid-template-columns: 1fr 1fr 1fr;
					}
				}
				@media only screen and (max-width: 767px) {
					.gfield$column_class .gfield_radio, .gfield$column_class .gfield_checkbox{
						grid-template-columns: 1fr 1fr;
					}
				}
			";
		}

		return trim( preg_replace( '/\s\s+/', '', $css ) );
	}

    function gfic_editor_script() {
        ?>

		<script type='text/javascript'>
	        //adding setting to fields of type "date"
	        
	        fieldSettings.radio += ", .img_choice_field_setting";
	        fieldSettings.checkbox += ", .img_choice_field_setting";
			
	        //binding to the load field settings event to initialize the checkbox
	        
	       	jQuery(document).bind("gform_load_field_settings", function(event, field, form){
                jQuery("#gfic_enable_imgchoice").prop( 'checked', Boolean( rgar( field, 'initImageGField' ) ) );
                jQuery("#pcafe_imgp_new_style").prop( 'checked', Boolean( rgar( field, 'pcafeNewStyle' ) ) );
                jQuery("#pcafe_imgp_column").val(field["pcafeImgpColumn"]);
	        });

			jQuery('.choices_setting')
                .on('input propertychange', '.field-choice-image-id', function () {
                    var $this = jQuery(this);
                    var i = $this.closest('li.field-choice-row').data('index');
 
                    field = GetSelectedField();
                    field.choices[i].imageId= $this.val();
                });
			jQuery('.choices_setting')
					.on('input propertychange', '.field-choice-image-url', function () {
						var $this = jQuery(this);
						var i = $this.closest('li.field-choice-row').data('index');
	
						field = GetSelectedField();
						field.choices[i].imageUrl = $this.val();
					});
			gform.addFilter('gform_append_field_choice_option', function (str, field, i) {
				var inputType = GetInputType(field);
				var imageId = field.choices[i].imageId? field.choices[i].imageId: '';
				var imageurl = field.choices[i].imageUrl ? field.choices[i].imageUrl : '';
				if ( field['type'] === "radio" || field['type'] === "checkbox" ) {

					return "<input type='hidden' id='" + inputType + "_choice_image_id_" + i + "' value='" + imageId + "' class='field-choice-input field-choice-image-id' /><input type='hidden' id='" + inputType + "_choice_image_url_" + i + "' value='" + imageurl + "' class='field-choice-input field-choice-image-url' /><div class='show_hide_trigger'><button type='button' class='pc_image_media_upload'><i class='dashicons dashicons-format-image'></i></button><span class='image_preview_box' style='display:none'><span class='img_pick_preview'></span><span class='remove_pick_img'><i class='dashicons dashicons-no'></i></span></span></div>";
				}
	
				return "";
			});

	    </script>

	    <?php
    }


	public function gfic_add_tooltips() {
		$tooltips['enable_image_choices'] = esc_html__("Check this box to enable and show image choices options.", "gravityforms");
		$tooltips['pcafe_imgp_new_design'] = esc_html__("Check this box to enable new design.", "gravityforms");
		$tooltips['img_column'] = esc_html__("Choose column for showing on frontend form.", "gravityforms");

		return $tooltips;
	}


	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Image Picker Options', 'pdf-invoices-for-gravityforms' ),
				'fields' => array(
					array(
						'name'      => 'pcafe_imgp_color',
						'label'     => esc_html__( 'Image Picker Color', 'pdf-invoices-for-gravityforms' ),
						'tooltip'   => esc_html__( 'Choose your color', 'pdf-invoices-for-gravityforms' ),
						'type'      => 'text',
						'class'     => 'medium',
						'default_value' => '#0077FF',
						'required'  =>  true
					)
				),
			)
		);
	}

}



