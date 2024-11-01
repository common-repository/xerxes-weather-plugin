<?php
/**
 * Plugin Name: Xerxes Weather 0.1b
 * Plugin URI: http://splusk.de/allgemein/5587-xerxes-weather
 * Description: Shows automaticly the weather of the current user location.
 * Version: 0.1b
 * Author: Nordvind Modified by Xerxes
 * Author URI: http://www.splusk.de
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

 add_action('wp_head', 'weather_css');
 
 function weather_css(){
 	include('xerxes-weather.css');
                
   
}
 
 add_action( 'widgets_init', 'load_sweather' );

 function load_sweather(){
	register_widget('simple_weather');
 }
 
 /* Core functions and classes */

class g_weather{
	private $w_xml;
	public function get_weather($cnt){
		libxml_use_internal_errors(true);
		$this->w_xml = new domDocument();
		$this->w_xml->loadXML($cnt);
		$test = $this->w_xml->getElementsByTagName('problem_cause');
		if ($test->length !== 0) return false;
	}
	public function get_loc(){
		if (empty($this->w_xml)) return -1;
		$cname = $this->w_xml->getElementsByTagName('city')->item(0)->getAttribute('data');	
		return $cname;
	}
	public function get_condition(){
		if (empty($this->w_xml)) return -1;
		$cnd = $this->w_xml->getElementsByTagName('condition')->item(0)->getAttribute('data');
		return $cnd;
	}
	public function get_icon(){
		if (empty($this->w_xml)) return -1;
		$icon = $this->w_xml->getElementsByTagName('icon')->item(0)->getAttribute('data');
		if (strpos($icon,'chance_of_snow') !== false) $path = 'cos.jpg';
		elseif (strpos($icon,'snow') !== false || strpos($icon,'flurries') !== false) $path = 'snow.jpg';
		elseif (strpos($icon,'mostly_sunny') !== false) $path = 'm_sunny.jpg';
		elseif (strpos($icon,'sunny') !== false) $path = 'sunny.jpg';
		elseif (strpos($icon,'mostly_cloudy') !== false || strpos($icon,'partly_cloudy') !== false) $path = 'm_cloudy.jpg';
		elseif (strpos($icon,'cloudy') !== false) $path = 'cloudy.jpg';
		elseif (strpos($icon,'haze') !== false) $path = 'haze.jpg';
		elseif (strpos($icon,'storm') !== false) $path = 'storm.jpg';
		elseif (strpos($icon, 'rain') !== false) $path = 'rain.jpg';
		elseif (strpos($icon, 'showers') !== false || strpos($icon, 'chance_of_rain') !== false) $path = 'cor.jpg';
		else $path = 'na.jpg';
		return $path;
	}
	public function get_hum(){
		if (empty($this->w_xml)) return -1;
		$hmd = $this->w_xml->getElementsByTagName('humidity')->item(0)->getAttribute('data');
		return $hmd;
	}
	public function get_temper(){
		if (empty($this->w_xml)) return -1;
		$t = $this->w_xml->getElementsByTagName('temp_c')->item(0)->getAttribute('data');
		return $t;
	}
	public function get_wind(){
		if (empty($this->w_xml)) return -1;
		$wind = $this->w_xml->getElementsByTagName('wind_condition')->item(0)->getAttribute('data');
		return $wind;
	}
}

function get_url_cnt($url){
        $crl = curl_init();
        $timeout = 5;
        curl_setopt ($crl, CURLOPT_URL,$url);
        curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $ret = curl_exec($crl);
        curl_close($crl);
        return $ret;
 }
 /* Core end */
 
 class simple_weather extends WP_Widget{
		public $url = 'http://www.meteo.lv/public/index.html';
		function simple_weather() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'weather-wdg', 'description' => 'Weather widget.' );

		/* Widget control settings. */
		$control_ops = array( 'width' => 200, 'height' => 300, 'id_base' => 'simple_weather' );

		/* Create the widget. */
		$this->WP_Widget( 'simple_weather', 'Xerxes weather', $widget_ops, $control_ops );
	}



	

function widget($args,$instance){
		$loc = $instance['location'];


$ip = $_SERVER['REMOTE_ADDR'];

$file='http://api.ipinfodb.com/v3/ip-city/?key=de9e0eac62cc5b3d1fb79a9ef3daf812521501441cd3a338c03b6f77561f778c&ip='. $ip . '&format=xml';

$fp = fopen($file, "r"); 

$data = fread($fp, 80000); 


fclose($fp); 

preg_match_all("/<latitude>(.*?)<\/latitude>/s",$data,$arrTreffer); 
#
$lat = $arrTreffer[1][0];
preg_match_all("/<longitude>(.*?)<\/longitude>/s",$data,$arrTreffer);
$lng = $arrTreffer[1][0];	

$file='http://maps.googleapis.com/maps/api/geocode/xml?latlng='.$lat.','.$lng.'&sensor=false';
$fp = fopen($file, "r"); 
$data = fread($fp, 80000); 
fclose($fp);

preg_match_all("/<address_component>(.*?)<\/address_component>/s",$data,$arrTreffer);
preg_match_all("/<long_name>(.*?)<\/long_name>/s",$arrTreffer[1][3],$stadt);
$stadt = $stadt[1][0];

echo '<div id="s-weather"><div id="s-weather-data">';		

if (!$stadt) $stadt = 'Hannover';

$w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		$g = new g_weather();
		$wg = get_url_cnt($w_url);
		$x = $g->get_weather($wg);
		if ($x !== false){
		echo'<span id="widget-titel">Weather&nbsp;&nbsp;</span><span id="loc-name">'.$stadt.'</span>';
                echo '<div id="weather-img"><img src="'.get_bloginfo('wpurl').'/wp-content/plugins/xerxes-weather/img/'.$g->get_icon().'" alt="" style="margin-left: 13px;"/></div>';
		echo '<div id="weather-text-data">';
		echo '<span id="curr-t">'.$g->get_temper().'&deg;C</span><br />';
		#echo '<p>'.$g->get_condition().'</p>';
		#echo '<p id="w-humid">'.$g->get_hum().'</p>';
		#echo '<p id="w-wind">'.$g->get_wind().'</p>';
		echo '</div>';
		}
		else{
		  $stadt = "Berlin";
		  $w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		  $g = new g_weather();
		  $wg = get_url_cnt($w_url);
		  $x = $g->get_weather($wg);
		    
      if ($x == false) {
		      $stadt = "Frankfurt";
		      $w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		      $g = new g_weather();
		      $wg = get_url_cnt($w_url);
		      $x = $g->get_weather($wg);
      }                

      if ($x == false) {
		      $stadt = "Stuttgart";
		      $w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		      $g = new g_weather();
		      $wg = get_url_cnt($w_url);
		      $x = $g->get_weather($wg);
      }
     
      if ($x == false) {
		      $stadt = "Hamburg";
		      $w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		      $g = new g_weather();
		      $wg = get_url_cnt($w_url);
		      $x = $g->get_weather($wg);
      }        
        
      if ($x !== false){
		      echo'<span id="widget-titel">Wetter&nbsp;&nbsp;</span><span id="loc-name">'.$stadt.'</span>';
          echo '<div id="weather-img"><img src="'.get_bloginfo('wpurl').'/wp-content/plugins/xerxes-weather/img/'.$g->get_icon().'" alt="" style="margin-left: 13px;"/></div>';
		      echo '<div id="weather-text-data">';
		      echo '<span id="curr-t">'.$g->get_temper().'&deg;C</span><br /><span>Die Wetterdaten Ihres Standortes konnten leider nicht übermittelt werden. Alternativ wird das Wetter von '.$stadt.' angezeigt.</span>';
		#echo '<p>'.$g->get_condition().'</p>';
		#echo '<p id="w-humid">'.$g->get_hum().'</p>';
		#echo '<p id="w-wind">'.$g->get_wind().'</p>';
		      echo '</div>';
		    } else {

      $stadt = "Wien";
		  $w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		  $g = new g_weather();
		  $wg = get_url_cnt($w_url);
		  $x = $g->get_weather($wg);
		    
      if ($x == false) {
		      $stadt = "Prag";
		      $w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		      $g = new g_weather();
		      $wg = get_url_cnt($w_url);
		      $x = $g->get_weather($wg);
      }                

      if ($x == false) {
		      $stadt = "Paris";
		      $w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		      $g = new g_weather();
		      $wg = get_url_cnt($w_url);
		      $x = $g->get_weather($wg);
      }
     
      if ($x == false) {
		      $stadt = "Rom";
		      $w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		      $g = new g_weather();
		      $wg = get_url_cnt($w_url);
		      $x = $g->get_weather($wg);
      }        
        
      if ($x !== false){
		      echo'<span id="widget-titel">Wetter&nbsp;&nbsp;</span><span id="loc-name">'.$stadt.'</span>';
          echo '<div id="weather-img"><img src="'.get_bloginfo('wpurl').'/wp-content/plugins/xerxes-weather/img/'.$g->get_icon().'" alt="" style="margin-left: 13px;"/></div>';
		      echo '<div id="weather-text-data">';
		      echo '<span id="curr-t">'.$g->get_temper().'&deg;C</span><br /><span>Die Wetterdaten für Deutschland konnten leider nicht übermittelt werden. Alternativ wird das Wetter von '.$stadt.' angezeigt.</span>';
		#echo '<p>'.$g->get_condition().'</p>';
		#echo '<p id="w-humid">'.$g->get_hum().'</p>';
		#echo '<p id="w-wind">'.$g->get_wind().'</p>';
		      echo '</div>';		    
		    
		    } else {

      $stadt = "Miami";
		  $w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		  $g = new g_weather();
		  $wg = get_url_cnt($w_url);
		  $x = $g->get_weather($wg);
		  $sry = 'Die Wetterdaten Ihres Standortes konnten leider nicht übermittelt werden. Alternativ wird das Wetter von '.$stadt.' angezeigt.'; 
		    
      if ($x == false) {
		      $stadt = "Tokyo";
		      $w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		      $g = new g_weather();
		      $wg = get_url_cnt($w_url);
		      $x = $g->get_weather($wg);
      }                

      if ($x == false) {
		      $stadt = "Sydney";
		      $w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		      $g = new g_weather();
		      $wg = get_url_cnt($w_url);
		      $x = $g->get_weather($wg);
      }
     
      if ($x == false) {
		      $stadt = "Panama";
		      $w_url = 'http://www.google.com/ig/api?weather='. $stadt;
		      $g = new g_weather();
		      $wg = get_url_cnt($w_url);
		      $x = $g->get_weather($wg);
      }        
        
      if ($x !== false){
		      echo'<span id="widget-titel">Weather&nbsp;&nbsp;</span><span id="loc-name">'.$stadt.'</span>';
          echo '<div id="weather-img"><img src="'.get_bloginfo('wpurl').'/wp-content/plugins/xerxes-weather/img/'.$g->get_icon().'" alt="" style="margin-left: 13px;"/></div>';
		      echo '<div id="weather-text-data">';
		      echo '<span id="curr-t">'.$g->get_temper().'&deg;C</span><br /><span>Die Wetterdaten für Deutschland & Europa konnten leider nicht übermittelt werden. Alternativ wird das Wetter von '.$stadt.' angezeigt.</span>';
		#echo '<p>'.$g->get_condition().'</p>';
		#echo '<p id="w-humid">'.$g->get_hum().'</p>';
		#echo '<p id="w-wind">'.$g->get_wind().'</p>';
		      echo '</div>';		    
    
		    } else {
          
          echo '<img src="'.get_bloginfo('wpurl').'/wp-content/plugins/xerxes-weather/img/na.jpg" alt="" /><br />';
		      echo 'There are no weather data';        
        
        }

        
        }
		}
		}
		echo '</div></div>';
	}
	function update($new_instance,$old_instance){
	$instance = $old_instance;
	$instance['location'] = strip_tags( $new_instance['location'] );
	return $instance;
	}
	function form($instance){
	$def = array('location' => 'Riga');
	$instance = wp_parse_args((array)$instance,$def);
	?>
	
	<p style="color:#FF0000;">No need for changes her, just Drag&Drope the Widget where you want it to be. If you want a coustom CSS for the Widget, you can edit in the plugin editor the xerxes-weather.css</p>
	<?php
	}
 }
 ?>