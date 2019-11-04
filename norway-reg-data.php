<?php
/**
 * Plugin Name: sjekk EU-kontrollfrist
 * Plugin URI: http://tabishrana.blogspot.com
 * Description: Check EU-deadline date you can place anywhere ** [kontrollfrist_form] ** shortcode and get form there to submit registration number. 
 * Version: 1.0.1
 * Author: Hello Dear Code
 * Author URI: http://facebook.com/hellodearcode
 * License: GPL2
 */


function foobar_func( $atts ){
	ob_start();
	$html = "
	<style>
.loaderAjax {
  border: 8px solid #f3f3f3;
  border-radius: 50%;
  border-top: 8px solid #f58a1d;
  width: 30px;
  height: 30px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
		".get_option('hdc_control_style')."
 	</style>
		 	";

	$html .= '<form id="hdc_styler_form" method="POST" autocomplete="off" onsubmit="return false;">
			<input type="text" id="REG_ID" placeholder="Please Enter Registration ID" required="">
			<button type="submit" onclick="submitRegIdI()">sjekk</button>
		</form><br>
		<div id="responseData" class="hdc_response"></div>
		<script>
			function submitRegIdI(){
				var regid = jQuery("#REG_ID").val();
				jQuery("#responseData").html(\'<div class="loaderAjax"></div>\');
				if(regid.length>1){
					jQuery.post("'.admin_url('admin-ajax.php').'", {action: "kontrolfrist_action",REG_ID: regid}, function(result){
					    jQuery("#responseData").html(result);
					  }); 
					}else{
						alert("Please Fill Registration ID");
					}
			}
		</script>
		';
	return $html;
	ob_clean();
}
add_shortcode( 'kontrollfrist_form', 'foobar_func' );


/*
admin ajax action control
*/
add_action( 'wp_ajax_nopriv_kontrolfrist_action','kontrollfrist_processing' );
add_action( 'wp_ajax_kontrolfrist_action','kontrollfrist_processing' );
function kontrollfrist_processing(){

	if(isset($_POST['REG_ID'])){

		$requestURL = "https://www.vegvesen.no/ws/no/vegvesen/kjoretoy/kjoretoyoppslag/v1/kjennemerkeoppslag/kjoretoy/".$_POST['REG_ID'];
		$agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_URL,$requestURL);
		$err=curl_error($ch);
		$response = curl_exec($ch);

		curl_close($ch);

		if ($err) {
		  	echo "cURL Error #:" . $err;
		} else {
	        $dataRow = json_decode($response);
	        echo "kontrollfrist Details For <big>".strip_tags($_POST['REG_ID'])."</big><br>";
	        if($dataRow->periodiskKjoretoykontroll->sistKontrollert){
	            echo "SistKontrollert: <b>".$dataRow->periodiskKjoretoykontroll->sistKontrollert."</b><br>";
	        }else{ echo "SistKontrollert Not Found<br>";}
	        if($dataRow->periodiskKjoretoykontroll->nesteKontroll){
	            echo "NesteKontroll: <b>".$dataRow->periodiskKjoretoykontroll->nesteKontroll."</b>";
	        }else{ echo "NesteKontroll Not Found";}
		}
	}
  //CODE
  die();
}

/*
Settings page For CSS Styling
*/

function hdc_kontrollfrist_settings_options() {
  add_options_page('Sjekk kontrollfrist', 'Sjekk kontrollfrist', 'manage_options', 'hdc_kontrollfrist', 'hdc_kontrollfrist_settings');
}
add_action('admin_menu', 'hdc_kontrollfrist_settings_options');


function hdc_kontrollfrist_settings()
{
?>
  <div>
  <h2>Sjekk EU-kontrollfrist (FORM CUSTOMIZATION STYLING)</h2>
  <form method="post">
  <?php settings_fields( 'hdc_sjekk_settings' ); ?>
  <p>Place Custom Design For Elements</p>
  <table>
	  <tr valign="middle">
		  <th scope="row"><label for="hdc_control_style">Text Input Style</label></th>
		  <td><textarea rows="15" cols="100" id="hdc_control_style" name="hdc_control_style" placeholder="Please Input Css Code Here..."><?php echo get_option('hdc_control_style'); ?></textarea></td>
	  </tr>
  </table>
  <?php  submit_button(); ?>
  </form>
  </div>
<?php
}

/*update Options Values*/
if(isset($_POST['option_page']) && $_POST['option_page'] == "hdc_sjekk_settings"){

	update_option("hdc_control_style",esc_html($_POST['hdc_control_style']));

}
?>