<?php
/*
Plugin Name: List speakers
Plugin URI: http://yabo-concept.ch
Description: Shows the conference speakers in a widget
Version: 1.0
Author: Julien Ramel
Author URI: http://yabo-concept.ch
*/

define(LIST_SPEAKERS_WIDGET_ID, "widget_list_speakers");
define( 'LIST_SPEAKERS_PLUGIN_URL', WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) );



function list_speakers($atts=array(),$content=null,$code=''){
	$year = isset($atts['year']) ? $atts['year'] : date("Y"); // let the year be specified in the shortcode
	$speaker_page = $atts['speaker_page']; // This should be a URL to a page that includes the shortcode [the_conference_lineup year="my year"] with the addition of ?artist= (or &artist=) as the speaker id will get filled in below
	
	
	$Bootstrap = Bootstrap::getBootstrap(); // The Top Quark bootstrap;
	$Bootstrap->usePackage('FestivalApp'); // fire up The Conference Plugin
	$FestivalContainer = new FestivalContainer();
	$Conference = $FestivalContainer->getFestival($year);
	if (!is_a($Conference,'Festival') or !$Conference->getParameter('FestivalLineupIsPublished')){ 
		return 'Not published yet';
	} 
	$Speakers = $Conference->getLineup();



	$return.= '<div id="list-speakers">';
	foreach ($Speakers as $SpeakerID => $Speaker){
			error_log('Speakers  ' . $Speaker->getParameter('ArtistFullName') );
			$return.= '<div class="list-speaker-item">';
			$return.= '<div class="speaker-image">';
			$Speaker->parameterizeAssociatedMedia(); // prepares the images
			$Images = $Speaker->getParameter('ArtistAssociatedImages');
			if (count($Images)){
				$return.= '<img src="'.$Images[0]['Thumb'].'"/>';
			}
			else{
				$return.= '<img src="/wp-content/plugins/list-speakers/img/user_64.png" />';
			}
			$return.= '</div>';
		
			$return.= '	<div class="speaker-name"><a href="'.$speaker_page.'?subject=lineup&_year='.$year.'&speaker='.$SpeakerID.'">'.$Speaker->getParameter('ArtistFullName').'</a></div>';
		
		
			$return.= '<div class="speaker-description">'.$Speaker->getParameter('ArtistDescription');
		
			$return.= '</div> <!-- .speaker-description -->';
			$return.= '<a href="'.$speaker_page.'?subject=lineup&_year='.$year.'&speaker='.$SpeakerID.'" class="speaker-list-read-more"><img src="/wp-content/plugins/list-speakers/img/readmore.png" class="nolazyload"></a>';
			$return.= '</div> <!-- .speaker -->';
	}
	$return.= '</div> <!-- #list-speakers -->';
	return $return;
}

add_action('init', 'ls_enable_required_js_in_wordpress');
function ls_enable_required_js_in_wordpress() {
	
	if (!is_admin()) {

		//jQuery 
		wp_deregister_script('jquery');
		wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js');
		wp_enqueue_script('jquery');
		
		// content slider 
		// http://brenelz.com/blog/build-a-content-slider-with-jquery/
		wp_deregister_script('bxslider');
    	wp_register_script('bxslider',  'http://bxslider.com/sites/default/files/jquery.bxSlider.min.js');
		wp_enqueue_script('bxslider', 'http://bxslider.com/sites/default/files/jquery.bxSlider.min.js');

	}
}
add_action('init','ls_add_css_scripts');
function ls_add_css_scripts() {
	wp_enqueue_style( 'list-speakers-contentslider', '/wp-content/plugins/list-speakers/css/list-speakers.css');
}

add_action('wp_footer', 'add_ls_onload' );
function add_ls_onload() {
    ?>
   <script type="text/javascript">
        /***************************************************
                Nivo Slider
        ***************************************************/
        jQuery.noConflict()(function($){
            jQuery(document).ready(function($){
            $(window).load(function() {
					$('.list-speaker-item').css("display","block");
				
					// see http://bxslider.com/options
               	 	$('#list-speakers').bxSlider({
				     	auto: true,
				     	autoControls: false,
				 		controls: false,
				 		speed: 500,
				 		randomStart: true
				   });
            });
        });
        });
    </script>
    <?php
}





function widget_list_speakers($args, $instance) {
  extract($args, EXTR_SKIP);
  $options = get_option(LIST_SPEAKERS_WIDGET_ID);

  $show_excerpt = $options["show_excerpt"];
  $year = $options["year"];
  $speaker_page= $options["speaker_page"];

  echo $before_widget;
  echo $before_title;
  echo $options["title"];
  echo $after_title;
  echo list_speakers($options);
  echo $after_widget;
}

function widget_list_speakers_init() {
  wp_register_sidebar_widget(LIST_SPEAKERS_WIDGET_ID, 
  	__('List speakers'), 'widget_list_speakers');
  wp_register_widget_control(LIST_SPEAKERS_WIDGET_ID, __('List speakers'), 'widget_list_speakers_control');
}

function widget_list_speakers_control() {
  $options = get_option(LIST_SPEAKERS_WIDGET_ID);
  if (!is_array($options)) {
    $options = array();
  }

  $widget_data = $_POST[LIST_SPEAKERS_WIDGET_ID];
  if ($widget_data['submit']) {
    $options['year'] = $widget_data['year'];
    $options['title'] = $widget_data['title'];
    $options['show_excerpt'] = $widget_data['show_excerpt'];
    $options['speaker_page'] = $widget_data['speaker_page'];

    update_option(LIST_SPEAKERS_WIDGET_ID, $options);
  }

  // Render form
  $year = $options['year'];
  $title = $options['title'];
  $show_excerpt = $options['show_excerpt'];
  $speaker_page = $options['speaker_page'];
  
?>

<p>
   <label for="<?php echo LIST_SPEAKERS_WIDGET_ID;?>-title">
    Title
   </label>
   <input class="widefat" type="text" 
     name="<?php echo LIST_SPEAKERS_WIDGET_ID; ?>[title]" 
     id="<?php echo LIST_SPEAKERS_WIDGET_ID;?>-title" 
     value="<?php echo $title; ?>">
 </p>


 
  <p>
    <label for="<?php echo LIST_SPEAKERS_WIDGET_ID;?>-num-posts">
    Speaker page
    </label>
    <input class="widefat" type="text" 
      name="<?php echo LIST_SPEAKERS_WIDGET_ID; ?>[speaker_page]" 
      id="<?php echo LIST_SPEAKERS_WIDGET_ID;?>-speaker-page" 
      value="<?php echo $speaker_page; ?>">
  </p>

  <p>
    <label for="<?php echo LIST_SPEAKERS_WIDGET_ID;?>-year">
 	Year:
    </label>
    <input class="widefat" type="text" 
      name="<?php echo LIST_SPEAKERS_WIDGET_ID; ?>[year]" 
      id="<?php echo LIST_SPEAKERS_WIDGET_ID;?>-year" 
      value="<?php echo $year; ?>">
  </p>
  <p>
    <label for="<?php echo LIST_SPEAKERS_WIDGET_ID;?>-show-excerpt">
    	Show description:
    </label>
    <select class="widefat" 
      name="<?php echo LIST_SPEAKERS_WIDGET_ID; ?>[show_excerpt]" 
      id="<?php echo LIST_SPEAKERS_WIDGET_ID;?>-show-exceprt">
        <option value="1"  <?php if ($show_excerpt=="1") : echo "selected"; endif ?>>
            Yes
        </option>
        <option value="0" <?php if ($show_excerpt=="0") : echo "selected"; endif ?> >No</option>
      </select>
    </p>

    <input type="hidden" name="<?php echo LIST_SPEAKERS_WIDGET_ID; ?>[submit]" value="1">
<?php
}

// Register widget to WordPress

add_action("plugins_loaded", "widget_list_speakers_init");


?>