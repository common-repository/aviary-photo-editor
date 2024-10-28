<?php 
/*
Plugin Name: aviary-photo-editor
Plugin URI: 
Author: Mineo Okuda
Version: 0.1
Description: A plugin that integrates The Awesome Aviary editor In the WordPress Media Library. 
Author URI: http://word-cat.com
*/ 

/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename (__FILE__)) {
  		die(__('Sorry, but you cannot access this page directly.'));
}


/**
*
*	@WP_aviary_photo_editor class 
*   @aviary_photo_editor
*	
**/ 
if(!(class_exists('WP_aviary_photo_editor'))){
	
class WP_aviary_photo_editor{

  	public $apc_options;
	public $lang = array(
						  'en' => 'English (default)',
						  'ca' => ' Catalan',
						  'zh_HANS' => 'Chinese (simplified)',
						  'zh_HANT' => 'Chinese (traditional)',
						  'nl' => 'Dutch',
						  'fr' => 'French',
						  'de' => 'German',
						  'he' => 'Hebrew',
						  'id' => 'Indonesian',
						  'it' => 'Italian',
						  'ja' => 'Japanese',
						  'ko' => 'Korean',
						  'lv' => 'Latvian',
						  'lt' => 'Lithuanian',
						  'pl' => 'Polish',
						  'pt' => ' Portuguese',
						  'pt_BR' => 'Portuguese (Brazilian)',
						  'ru' => 'Russian',
						  'es' => 'Spanish',
						  'vi' => 'Vietnamese'
      );
        
 	/**
	*
	*	Class Constructor
	*	@author Mineo Okuda
    *   @since 0.1
    *   @access public
	*
	**/	
 	function  __construct(){
		
			// Load translation files
		if ( ! load_plugin_textdomain( 'aviary-photo-editor' ) ) {
			
			load_plugin_textdomain( 'aviary-photo-editor', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}
		
		$this->apc_get_options();
		add_filter('admin_print_scripts', array($this,'aviary_photo_editor_scripts'), 20, 2);
 		add_filter('admin_print_styles', array($this,'aviary_photo_editor_styles'), 20, 2);
		add_action('admin_print_scripts', array($this,'ape_admin_enqueue_scripts'));
		add_action('admin_menu', array($this,'create_options_page'));
		
		add_filter( 'manage_media_columns', array($this,'wh_column' ));
		add_action( 'manage_media_custom_column', array($this,'wh_value'), 10, 2 );
		add_filter( 'manage_media_columns', array($this,'aviary_column') );
		add_action( 'manage_media_custom_column', array($this,'aviary'), 10, 2 );
		//options panel
    	add_action('admin_init', array($this,'register_and_build_fields'));
		add_action('wp_ajax_aviary_save_ajax', array($this,'aviary_ajax_save'));
		
	}
	
	/**
	*
	*	@apc_get_options function  is set $this->apc_options objects
	*	@author Mineo Okuda
    *   @since 0.1
    *   @access public
    *   @return void
	*
	**/	
	public function apc_get_options(){
	
			$this->apc_options = get_option('aviary_options');

	}
	
	/**
	*
	*	@ape_admin_enqueue_scripts function  is load js jquery,aviary-photo-editor-feather
	*	@author Mineo Okuda
    *   @since 0.1
    *   @access public
    *   @return void
	*
	**/	
	public function ape_admin_enqueue_scripts() {
			
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'aviary-photo-editor-feather', '/wp-content/plugins/aviary-photo-editor/js/feather.js');
			
	}	
	
	
	/**
	*
	*	@aviary_photo_editor_styles function is admin print styles css
	*	@author Mineo Okuda
    *   @since 0.1
    *   @access public
    *   @return void 
	*
	**/
	public function aviary_photo_editor_styles(){	
		echo "<style>
				#avpw_fullscreen_bg{ z-index:200000 !important;}
				#avpw_controls{ z-index:210000 !important;}
			</style>";
	}


	/**
	*
	*	@aviary_photo_editor_scripts function is admin print scripts js
	*	@author Mineo Okuda
	*   @since 0.1
	*   @access public
	*   @return void 
	*
	**/
	public function aviary_photo_editor_scripts(){	
	?>
	<!-- Instantiate Feather -->
	<script>	 
	;(function($){
		 $(function(){
			 var AVIARY_CURRENT_IMAGE ='';
		  function saved_new_image_aviary (imageID,newURL){
			  var data = {
				  action: 'aviary_save_ajax',
				  aviary_nonce: $('#'+imageID).prev().prev().prev().val(),
				  org_id: $('#'+imageID).prev().val(),
				  avaiary_url: newURL,
				  fileFormat: '<?php echo ($this->apc_options->fileFormat ) ? $this->apc_options->fileFormat : 'original';?>',
				  oriImageFormat: AVIARY_CURRENT_IMAGE
				};
				$.post(ajaxurl, data, function(response) {
								window.location.href = '<?php echo admin_url()."upload.php";?>';
							
				});
			}
						
	   var featherEditor = new Aviary.Feather({
		   apiKey:  '<?php echo ($this->apc_options['apikey'] ) ? $this->apc_options['apikey'] : 'en';?>',
		   language : '<?php echo ($this->apc_options['aviavry_language']) ? $this->apc_options['aviavry_language'] : 'en'; ?>',
		   apiVersion: 3,
		   theme: 'dark',
		   tools: "all",
		   cropPresets: <?php echo $this->cropPresets().",";?>
		   onSave: function(imageID, newURL) {
				saved_new_image_aviary (imageID,newURL);
			  
			   featherEditor.close();
		   },
		   onError: function(errorObj) {
			   alert(errorObj.message);
		   }
	   });
	 function launchEditor(id, src) {
		   featherEditor.launch({
			   image: id,
			   url: src
		   });
		  return false;
	   }  
	
			$(".aviary-launcher").live("click",function(){			
				
				var src = $(this).data("url");; 
				var id = $(this).data("id");
			
					 return launchEditor(id, src);
			
		
			});
			/* $(".wp_attachment_holder input[type=button]").removeAttr("onclick").live("click",function(){
				 $(".wp_attachment_holder .thumbnail").attr("id","ape_image");
				 var src = $(".wp_attachment_holder .thumbnail").attr("src");
				 var id = "ape_image"
				
				  return launchEditor(id, src);
				 });
			 */
			  });
	
			 })(jQuery);
		 
		 </script>
		 <?php 
	}
		
 
   /**
     * wh_column adds width and height column to media library
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @param  array $cols
     * @return array
     */
    public function wh_column( $cols ) {
          $cols["dimensions"] = __("Dimensions (w, h)",'aviary-photo-editor');
          return $cols;
    }

    /**
     * wh_value renders width and height column to media library
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @param  string $column_name
     * @param  int $id          
     * @return void
     */
    public function wh_value( $column_name, $id ) {
      if ( $column_name == "dimensions" ){
        $meta = wp_get_attachment_metadata($id);
               if(isset($meta['width']))
               echo $meta['width'].' x '.$meta['height'];
        }
    }

    /**
     * aviary_column adds Aviary column to media library
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @param  array $cols
     * @return array
     */
    public function aviary_column( $cols ) {
            $cols["aviary"] = __("advanced edit",'aviary-photo-editor');
            return $cols;
    }

    /**
     * aviary renders Aviary column to media library
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @param  string $column_name
     * @param  int $id          
     * @return void
     */
    public function aviary( $column_name, $id ) {
        if ( $column_name == "aviary" ){
          $image_attributes = wp_get_attachment_image_src( $id ,'full');
          wp_nonce_field('avaiary_saved'.$id,'aviary_nonce');
		  if($image_attributes ){
          ?>
            <input type="hidden" name="org_id" value="<?php echo $id; ?>">
            <img src="<?php echo $image_attributes[0]; ?>" style="display:none" id="aviary_<?php echo $id; ?>">
            <p><input type="button" class="button-primary aviary-launcher" value="<?php _e('Edit photo','aviary-photo-editor');?>" data-id='aviary_<?php echo $id; ?>' data-url='<?php echo $image_attributes[0]; ?>' /></p>
            <?php
		  }
        }
    }

    /**
     * aviary_ajax_save  function to save the new image
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @return void
     */
    public function aviary_ajax_save(){

        if (isset($_POST['org_id']))
          $action_name = 'avaiary_saved'.$_POST['org_id'];
        else{
          echo __('Sorry, your nonce did not verify.','aviary-photo-editor');
          die();
        }
        if ( empty($_POST) || !isset($_POST['avaiary_url']) )
        {
          echo __('Sorry, your nonce did not verify.','aviary-photo-editor');
           die();
        }
        else
        {
          $url = $_POST['avaiary_url'];
          $tmp = download_url( $url );
          $n = basename( $url );
          if (isset($_POST['fileFormat']) && $_POST['fileFormat'] != 'original'){
            $n = str_replace('.txt','.'.$_POST['fileFormat'],$n);
          }
          else{
            $n = str_replace('.txt','.'.$_POST['oriImageFormat'],$n);
          }
          $post_id  = 0 ;
          $desc = "";
		  //$postData = get_post($_POST['org_id']); 
          $file_array = array(
              'name' => $n,
              'tmp_name' => $tmp
          );

          // If error storing temporarily, unlink
          if ( is_wp_error( $tmp ) ) {
            @unlink($file_array['tmp_name']);
            $file_array['tmp_name'] = '';
          }

          if (!function_exists('media_handle_sideload')){
              require_once(ABSPATH . "wp-admin" . '/includes/image.php');
              require_once(ABSPATH . "wp-admin" . '/includes/file.php');
              require_once(ABSPATH . "wp-admin" . '/includes/media.php');
          }

          // do the validation and storage stuff
          $id = media_handle_sideload( $file_array, $post_id, $desc );
		  
          // If error storing permanently, unlink
          if ( is_wp_error($id) ) {
              @unlink($file_array['tmp_name']);
              echo __('Sorry, Unknown error when storing image localy.','aviary-photo-editor');
              die();
          }

          echo $id;
          
      }
    }
    /**
     * create_options_page
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @return void
     */
    public function create_options_page() {
       add_options_page(__('aviary photo editor options','aviary-photo-editor'), __('aviary photo editor options','aviary-photo-editor'), 'manage_options', __FILE__, array($this,'options_page_fn'));
    }

    /**
     * register_and_build_fields
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @return void
     */
    public function register_and_build_fields() {
       register_setting('aviary_options', 'aviary_options', array($this,'validate_setting'));
       add_settings_section('main_section', __('Main Settings','aviary-photo-editor'), array($this,'section_cb'), __FILE__);
       add_settings_field('apikey', __('API-Key:','aviary-photo-editor'), array($this,'api_key_filed'), __FILE__, 'main_section'); 
       add_settings_field('aviavry_language', __('Editor Language:','aviary-photo-editor'), array($this,'widget_lnguage'), __FILE__, 'main_section');
       add_settings_field('fileFormat', __('Saved File Format:','aviary-photo-editor'), array($this,'save_file_format'), __FILE__, 'main_section');
    }

    /**
     * options_page_fn
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @return void
     */
    public function options_page_fn() {
    ?>
    <style>
    #theme-options-wrap {
		border:solid 1px #ddd;
	  margin:20px 0;
      width: 60%;
	  min-width:700px;
      padding: 3em;}

    #theme-options-wrap #icon-tools {
      position: relative;
      top: -10px;
    }

    </style>

       <div id="theme-options-wrap" class="widefat">
          <div class="icon32" id="icon-tools"></div>

          <h2><?php _e('Aviary Options','aviary-photo-editor');?></h2>
          <p><?php _e('This will let you configure the Aviary image editor.','aviary-photo-editor');?></p>

          <form method="post" action="options.php" enctype="multipart/form-data">
             <?php settings_fields('aviary_options'); ?>
             <?php do_settings_sections(__FILE__); ?>
			
			
             <p class="submit">
                <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
             </p>
       </form>
    </div>
    <?php
    }

    /**
     * api_key_filed
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @return void
     */
    public function api_key_filed() {
     
       echo "<input name='aviary_options[apikey]' type='text' value='{$this->apc_options['apikey']}' />";
       echo "<br/>".__('To get your api key simply <a href="http://www.aviary.com/web-key" target="_blank">register here</a> for free','aviary-photo-editor');
    }

    
    /**
     * file formatfiled
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @return void
     */
    public function save_file_format() {
       $items = array(
          'png' => 'PNG',
          'jpg' => 'JPG', 
          'original' => 'original'
        );

       echo "<select name='aviary_options[fileFormat]'>";
       foreach ($items as $key => $val) {
          $selected = ( $this->apc_options['fileFormat'] === $key ) ? 'selected = "selected"' : '';
          echo "<option value='$key' $selected>$val</option>";
       }
       echo "</select>";
    }

    
    /**
     * language filed
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @return void
     */
    public  function widget_lnguage() {
      
      
      echo "<select name='aviary_options[aviavry_language]'>";
      foreach ($this->lang as $key => $val) {
        $selected = ( $this->apc_options['aviavry_language'] === $key ) ? 'selected = "selected"' : '';
        echo "<option value='$key' $selected>$val</option>";
      }
      echo "</select>";
    }

    
    /**
     * validate_setting
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @return array
     */
    public function validate_setting($aviary_options) {
      return $aviary_options;
    }
	
    /**
     * section_cb 
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @return Void
     */
    public function section_cb() {}

    /**
     * cropPresets
     * @author Ohad Raz
     * @since 0.1
     * @access public
     * @return string
     */
    public function cropPresets(){
      $image_sizes = get_intermediate_image_sizes();
      foreach ($image_sizes as $size_name => $size_attrs){
        $labled = "['".$size_name."', '".$size_name ."'],"."\n";
      }
      return "["."\n".$labled."'320x240','640x480','800x600','1280x1024','1600x1200',
        '240x320','480x640','600x800','1024x1280','1200x1600',
        'Original',
        ['Square', '1:1'],
        'Custom',
        '3:2', '3:5', '4:3', '4:6', '5:7', '8:10', '16:9'"."\n"."]";
    }
  }//end class
}//end if
$aviary = new WP_aviary_photo_editor;

