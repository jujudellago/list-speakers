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

function list_speakers($atts=array(),$content=null,$code=''){
	$year = isset($atts['year']) ? $atts['year'] : date("Y"); // let the year be specified in the shortcode
	$speaker_page = $atts['speaker_page']; // This should be a URL to a page that includes the shortcode [the_conference_lineup year="my year"] with the addition of ?artist= (or &artist=) as the speaker id will get filled in below

	$nb_speakers = isset($atts['nb_speakers']) ? $atts['nb_speakers'] : 1 ; 
	
	
	$Bootstrap = Bootstrap::getBootstrap(); // The Top Quark bootstrap;
	$Bootstrap->usePackage('FestivalApp'); // fire up The Conference Plugin
	$ConferenceContainer = new FestivalContainer();
	$Conference = $FestivalContainer->getFestival($year);
	if (!is_a($Conference,'Festival') or !$Conference->getParameter('FestivalLineupIsPublished')){ 
		return 'Not published yet';
	} 
	$Speakers = $Conference->getLineup();

	// Here's where you make your markup
	$return = '';
	$i=0;
	foreach ($Speakers as $SpeakerID => $Speaker){
		if ($i<$nb_speakers){
			$return.= '<div class="speaker">';
			$return.= '	<div class="speaker-name"><a href="'.$speaker_page.$SpeakerID.'">'.$Speaker->getParameter('ArtistFullName').'</a></div>';
			$Speaker->parameterizeAssociatedMedia(); // prepares the images
			$Images = $Speaker->getParameter('ArtistAssociatedImages');
			if (count($Images)){
				$return.= '<div class="speaker-image"><img src="'.$Images[0]['Thumb'].'"/></div>';
			}
			$return.= '<div class="speaker-description">'.$Speaker->getParameter('ArtistDescription').'</div>';
			$return.= '</div> <!-- .speaker -->';
		}
		$i++;
	}
	return $return;
}





function widget_list_speakers($args) {
  extract($args, EXTR_SKIP);
  $options = get_option(LIST_SPEAKERS_WIDGET_ID);

  $num_speakers = $options["nb_speakers"];
  $show_excerpt = $options["show_excerpt"];
  $year = $options["year"];

  echo $before_widget;
  list_speakers($options);
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
    $options['num_speakers'] = $widget_data['num_speakers'];
    $options['year'] = $widget_data['year'];
    $options['show_excerpt'] = $widget_data['show_excerpt'];

    update_option(LIST_SPEAKERS_WIDGET_ID, $options);
  }

  // Render form
  $num_speakers = $options['num_speakers'];
  $year = $options['year'];
  $show_excerpt = $options['show_excerpt'];
  
?>
  <p>
    <label for="<?php echo LIST_SPEAKERS_WIDGET_ID;?>-num-posts">
      Number of speakes to show:
    </label>
    <input class="widefat" type="text" 
      name="<?php echo LIST_SPEAKERS_WIDGET_ID; ?>[num_speakers]" 
      id="<?php echo LIST_SPEAKERS_WIDGET_ID;?>-num-speakers" 
      value="<?php echo $num_speakers; ?>">
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
    	Show excerpt:
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