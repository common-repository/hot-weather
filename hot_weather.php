<?php
/**
 * Plugin Name: Hot Weather
 * Plugin URI: http://hot-themes.com/wordpress/plugins/weather/
 * Description: Hot Weather widget helps you to inform your visitors about weather conditions for the selected city. Just enter your location WOEID and select units (Fahrenheit or Celsius).
 * Version: 1.2
 * Author: HotThemes
 * Author URI: http://hot-themes.com/
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'hot_weather_load_widgets' );
add_action('admin_init', 'hot_weather_textdomain');
/**
 * Register our widget.
 * 'HotWeather' is the widget class used below.
 *
 * @since 0.1
 */
function hot_weather_load_widgets() {
	register_widget( 'Weather' );
}

function hot_weather_textdomain() {
	load_plugin_textdomain('hot_weather', false, dirname(plugin_basename(__FILE__) ) . '/languages');
}
	
/**
 * Weather Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
 
class Weather extends WP_Widget {
     
	/**
	 * Widget setup.
	 */
	 
	function __construct() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'Hot_weather', 'description' => __('Hot Weather', 'hot_weather') );

		/* Widget control settings. */
		$control_ops = array(  'id_base' => 'hot-weather' );

		/* Create the widget. */
		parent::__construct( 'hot-weather', __('Hot Weather', 'hot_weather'), $widget_ops, $control_ops );
		
		add_action('wp_enqueue_scripts', array( $this, 'HotWeather_style'), 12);
		add_action('admin_init', array( $this,'admin_utils'));
    }
	
	function HotWeather_style(){
		wp_enqueue_style( 'hot-weather-style', plugins_url('/css/style.css', __FILE__));
	}
	
	function admin_utils(){
	}

	function GetDefaults()
	{
		return array( 
			'units' => 'f'
			,'woeid' => '28218'
		);
	}
	
	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {

		extract( $args );

		echo $before_widget;

        $defaults = $this->GetDefaults();
		$instance = wp_parse_args( (array) $instance, $defaults );  
		
		//-------------------------RENDER START----------------------------------------------?>

		<div id="weather"></div>

		<?php
		wp_enqueue_script( 'hot-weather-script', plugins_url('/js/jquery.simpleWeather.min.js', __FILE__), array('jquery'));
		wp_add_inline_script( 'hot-weather-script', '
			jQuery(document).ready(function() {
				jQuery.simpleWeather({
					woeid: '.$instance['woeid'].',
					unit: "'.$instance['units'].'",
					success: function(weather) {
						html = \'<div class="city">\'+weather.city+\'</div><div class="country">\'+weather.country+\'</div><i class="icon-\'+weather.code+\'"></i><div class="temperature">\'+weather.temp+\'&deg;\'+weather.units.temp+\' \'+\'</div>\';
						/* This string can be edited for different weather format. Check simpleweatherjs.com for details */

						jQuery("#weather").html(html);
					},
					error: function(error) {
						jQuery("#weather").html("<p>"+error+"</p>");
					}
				});
			});
		' );
		//-------------------------RENDER END-------------------------------------------------

		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
    	
		foreach($new_instance as $key => $option) {
			$instance[$key] = $new_instance[$key];
		} 

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
	    $defaults = $this->GetDefaults();
		$instance = wp_parse_args( (array) $instance, $defaults );  ?>

		<!-- Widget Title: Text Input -->

		<p>
			<label for="<?php echo $this->get_field_id( 'units' ); ?>"><?php _e('Units:','hot_weather'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'units' ); ?>" name="<?php echo $this->get_field_name( 'units' ); ?>" >
				<option value="f"><?php _e('Fahrenheit', 'hot_weather'); ?></option>
				<option value="c"><?php _e('Celsius', 'hot_weather'); ?></option>
			</select>
			<script>
				document.getElementById('<?php echo $this->get_field_id( 'units' ); ?>').value = "<?php echo $instance['units']; ?>";
			</script>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'woeid' ); ?>"><?php _e('WOEID:','hot_weather'); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'woeid' ); ?>" id="<?php echo $this->get_field_id( 'woeid' ); ?>" value="<?php echo $instance['woeid']; ?>" class="widefat" />
		</p>
		<p>You can find WOEID for your location here: <a href="http://woeid.rosselliot.co.nz/" target="_blank">woeid.rosselliot.co.nz</a></p>

	<?php  
	}
}

?>