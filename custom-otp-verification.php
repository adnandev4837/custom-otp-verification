<?php
/*
Plugin Name: Custom OTP Verification
*/

// Include the Twilio PHP SDK autoloader
require_once __DIR__ . '/vendor/autoload.php';
include __DIR__ . "/vendor/twilio/sdk/src/Twilio/Rest/Client.php";
use Twilio\Rest\Client;

// Enqueue necessary scripts and styles
function custom_otp_verification_enqueue_scripts() {
    // Enqueue Twilio SDK script
    wp_enqueue_script('twilio-sdk', 'https://sdk.twilio.com/js/v1/twilio.min.js', array(), '1.0', true);
}
add_action('wp_enqueue_scripts', 'custom_otp_verification_enqueue_scripts');
// Plugin activation hook
function custom_otp_verification_activate() {
 // Check if the database table already exists
global $wpdb;
$table_name = $wpdb->prefix . 'otp_verfication_status';
$charset_collate = $wpdb->get_charset_collate();

if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

    // Table does not exist, so create it
    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        phonenumber VARCHAR(255) NOT NULL,
        verification_status INT(11) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

}
register_activation_hook(__FILE__, 'custom_otp_verification_activate');
function custom_OTP_verification() {
    if (!session_id()) {
        session_start();
    }    
    try {
    $otp_number = ''; 
    $otp_verify = ''; 
    ?>
  
    <p class="otpHeading">Verify your number first!</p>
    <form method="POST" action="" class="otpFrom">
   
    <input type="text" id="otpNumber" name="otpNumber" placeholder="XXX-XXX-XXXX"  class="otpInput" min="1" maxlength="15" value="<?php echo $_SESSION['otp_number']; ?>"  oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
    <button type="submit" name="send" class="otpButton" style="height: 50px;">Send OTP</button>
    </form>
    <form method="POST" action="" class="otpFrom">
    <!-- <label for="otp"><b>Enter OTP</b></label><br> -->
    <input type="text" id="otp" name="otp" placeholder="Verify OTP" class="otpInput" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '')" min="1" required>
    <button type="submit" class="otpButton" name="verify" style="height: 50px;">Verify OTP</button>
    </form>
  <p class="popupNote"><b>Note: </b>Enter your number with country code without plus e.g: 12123456789 and verify only once by OTP after that just enter number and login.</p>

	<script>
	jQuery(document).ready(function() {
	  // Hide the message after 9 seconds
	  setTimeout(function() {
		jQuery('.message').hide();
	  }, 9000); // 3000 milliseconds = 3 seconds
	});</script>
    <?php
    add_action('wp_enqueue_scripts', 'custom_otp_verification_enqueue_scripts');
	if(!empty(get_option( 'twilio_account_sid' ))){$twilio_account_sid = get_option( 'twilio_account_sid' );}else{ $twilio_account_sid = 'ACf76214335b36021f1d5809f4f0f4a9a4';}	
	if(!empty(get_option( 'twilio_auth_token' ))){ $twilio_auth_token = get_option( 'twilio_auth_token' );}else{$twilio_auth_token = '65ef324cfe6f8446a3304921d6ff8ef4';}
	
	//$cookie_expiration_time=60;
		$cookie_expiration_time=7 * 24 * 60 * 60;

	if (!isset($_SESSION['count'])) {
    $_SESSION['count'] = 0; // Initialize count if it doesn't exist
}
    if(isset( $_POST['send'])){
		
	
	global $wpdb;
	$table_name = $wpdb->prefix . 'otp_verfication_status';

		$verification_record = $wpdb->get_row("SELECT * FROM $table_name WHERE phonenumber = ". $_POST['otpNumber']." AND verification_status = 1");
		if($verification_record){

			setcookie('popup_status_cookie', 1, time() + $cookie_expiration_time, '/');
			setcookie('verification_alert', 1, time() + 3, '/');
			unset($_SESSION['otp_number']);	
			// Reload the current page
			$url = esc_url_raw($_SERVER['REQUEST_URI']);
			wp_safe_redirect($url);
			exit();

        

		}
	else{

	$_SESSION['count'] = 0; 
    if(isset( $_POST['otpNumber']) && !empty( $_POST['otpNumber'])){
        $otp_number = $_POST['otpNumber'];
    }
    $twilio_phone_number = '+'.$otp_number;
    
	if(!empty(get_option( 'twilio_number' ))){ $twilio_number = '+'.get_option( 'twilio_number' );}else{$twilio_number = "+15005550006";}
		
    $otp_code = mt_rand(100000, 999999); // Generate a random OTP code
    $client = new Client($twilio_account_sid, $twilio_auth_token);
    $message = $client->messages->create(
        $twilio_phone_number,
        [
            'from' => $twilio_number,
            'body' => 'Your OTP code is: ' . $otp_code,
        ]
    );
    if ($message) {
		
        $_SESSION['otp_code'] =  $otp_code;
		$_SESSION['otp_number'] =  $_POST['otpNumber'];
		
			$data = array('phonenumber' =>  $_POST['otpNumber'] ,'verification_status' => 0);
	// Check if the record already exists
	$existing_record = $wpdb->get_row("SELECT * FROM $table_name WHERE phonenumber = ". $_POST['otpNumber']);
	if (!$existing_record) {
    $wpdb->insert($table_name, $data);

	}
			
	    header("Location: " . $_SERVER['REQUEST_URI']);
		setcookie('otp_send_alert', 1, time() + 3, '/');

		return '<p class="message"><b style="color:blue;">Information: </b>OTP sent to your phone number!</p>';
    }
		
	}
		
    header("Location: " . $_SERVER['REQUEST_URI']);
}
	
		 
if(isset( $_POST['verify'])){
// 	$count++;
// 	echo $count.'count';
// 	
// 
	if(!($_SESSION['count']>=2))
	{
	
 	$_SESSION['count']++;
    if(isset( $_POST['otp']) && !empty( $_POST['otp'])){
        $otp_verify = $_POST['otp'];
    }
    if (isset($_SESSION['otp_code'])) {
    $otp_code_gen = $_SESSION['otp_code'];
    if($otp_code_gen==$otp_verify){
		global $wpdb;
		$table_name = $wpdb->prefix . 'otp_verfication_status';
		$data = array('verification_status' => 1);
		$where = array('phonenumber' => $_SESSION['otp_number']);
		$wpdb->update($table_name, $data, $where);
		
		unset($_SESSION['count']);
        unset($_SESSION['otp_code']);
		unset($_SESSION['otp_number']);
		//$_SESSION['popup_status'] = 1; 
		setcookie('popup_status_cookie', 1, time() + $cookie_expiration_time, '/');
        echo '<p class="message"><b style="color:green;">Success: </b>OTP verified!</p>';
		setcookie('verification_now_alert', 1, time() + 3, '/');
		$url = esc_url_raw($_SERVER['REQUEST_URI']);
			wp_safe_redirect($url);
		return	exit();
    }
    else if($otp_code_gen!=$otp_verify){
		
         return '<p class="message"><b style="color:#E22C38;">Error: </b> OTP not verified! Sent to '.$_SESSION['otp_number'].' number enter again</p>';
 
    }
        }
	
	}
	else{
		   unset($_SESSION['count']);
		   unset($_SESSION['otp_code']);
		   unset($_SESSION['otp_number']);
		   header("Location: " . $_SERVER['REQUEST_URI']);
		   setcookie('limit_reached_alert', 1, time() + 3, '/');
		   return '<p class="message"><b style="color:#E22C38;">Error: </b> Limit Reached!Again Enter Number</b></p>';
	}
	}

    }
    catch (Twilio\Exceptions\RestException $e) {
      
		 return '<p class="message"><b style="color:#E22C38;">Error: </b> ' . $e->getMessage().'</p>';
    } catch (Exception $e) {
        return '<p class="message"><b style="color:#E22C38;">Error: </b> ' . $e->getMessage().'</p>';
    }

}
add_shortcode('customOTPVerification', 'custom_OTP_verification');
function my_plugin_enqueue_styles() {
	
    wp_enqueue_style('my-plugin-styles', plugin_dir_url(__FILE__) . 'styles.css', array(), '1.0.0');
  wp_enqueue_script('custom-script',  plugin_dir_url(__FILE__) .'scripts.js', array('jquery'), '1.4', false);
	
	
		if (isset($_COOKIE['popup_status_cookie'])) {
		$popup_current_status=$_COOKIE['popup_status_cookie'];
	}
	  wp_localize_script('custom-script', 'popupStatus', array(
        'value' => $popup_current_status,
		 'verification_alert'=>$_COOKIE['verification_alert'],
		 'verification_now_alert'=>$_COOKIE['verification_now_alert'],
		  'otp_send_alert'=>$_COOKIE['otp_send_alert'],
		  'limit_reached_alert'=>$_COOKIE['limit_reached_alert'],
    ));


}
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_styles');
function enqueue_admin_scripts() {
	
    // Enqueue DataTables CSS
    wp_enqueue_style('datatables', 'https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css');

    // Enqueue DataTables JavaScript
    wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js', array('jquery'), '1.10.25', true);
	 wp_enqueue_style('my-plugin-admin-styles', plugin_dir_url(__FILE__) . 'styles-admin.css', array(), '1.0.0');
}
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');

// Add an admin menu page
function otp_verification_add_admin_menu() {
    add_menu_page(
        'OTP Verification',    // Page title
        'OTP Verification',    // Menu title
        'manage_options',// Capability required to access the menu
        'otp-verification',    // Menu slug
        'otp_verification_settings_admin_page',   // Callback function to render the page
        'dashicons-smartphone',   // Icon URL or Dashicons class
        10  // Menu position
    );
	 add_submenu_page(
        'otp-verification',              // Parent slug
        'Numbers List',      // Page title
        'Numbers List',                // Menu title
        'manage_options',         // Capability required to access the submenu
        'numbers-list',      // Menu slug
        'numbers_list_page'  // Callback function to render the submenu page
    );

}
add_action('admin_menu', 'otp_verification_add_admin_menu');
function otp_verification_settings_admin_page() {

    echo '<h1>Twilio Settings</h1>';?>
	<form action="" id="form-admin" method="POST" >
  <div class="conn-row">
      <div class="col">
      <lable class="form-lable">Twilio Account SID</lable><br>
      <input type="text" value="<?php echo get_option( 'twilio_account_sid' );?>" id="twilio_account_sid" name="twilio_account_sid" class="adminInputField" placeholder="Twilio Account SID" required>
      </div><br>
        <div class="col">
      <lable class="form-lable">Twilio Auth Token</lable><br>
      <input type="text" value="<?php echo get_option( 'twilio_auth_token' );?>" id="twilio_auth_token" name="twilio_auth_token" class="adminInputField" placeholder="Twilio Auth Token" required>
      </div><br>
	     <div class="col">
      <lable class="form-lable">Twilio Number</lable><br>
      <input type="text" value="<?php echo get_option( 'twilio_number' );?>" id="twilio_number" name="twilio_number" class="adminInputField" oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="1XXXXXXXXXX" required>
      </div><br>
	  
      <div class="col">
        <input type="submit" class="button" name="submit" value="Save Settings" class="form-btn" >
        <input type="submit" class="button" name="submit-delete" value="Delete Settings" class="form-btn" >
       
  </div> </div>
</form>
		<?php
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
  if(isset($_POST["twilio_account_sid"])){
    $twilioAccountSid = sanitize_text_field($_POST["twilio_account_sid"]);
    update_option( 'twilio_account_sid', $twilioAccountSid ); 
  }
  if(isset($_POST["twilio_auth_token"])){
    $twilioAuthToken = sanitize_text_field($_POST["twilio_auth_token"]);
    update_option( 'twilio_auth_token', $twilioAuthToken ); 
  }
   if(isset($_POST["twilio_number"])){
    $twilioNumber = sanitize_text_field($_POST["twilio_number"]);
    update_option( 'twilio_number', $twilioNumber ); 
  }
  if(isset($_POST["submit-delete"]))
  {
    delete_option( 'twilio_account_sid');
	delete_option( 'twilio_auth_token');
	delete_option( 'twilio_number');
  }
	    header("Location: " . $_SERVER['REQUEST_URI']);

}

}
// Callback function to render the top-level menu page
function numbers_list_page() {
	$counter = 1;
    global $wpdb;
	$table_name = $wpdb->prefix . 'otp_verfication_status';
	$results = $wpdb->get_results("SELECT * FROM $table_name");
	if ($results) {
		?>
	<h1>Numbers List</h1>
		<table id="numberListTable">
			<thead>
				<tr>
					<th>Sr. No.</th>
					<th>Phone Numbers</th>
					<th>Verification Status</th>
				</tr>
			</thead>
			<tbody>
				<?php
				// Loop through the results and output the data
				foreach ($results as $row) {
					?>
					<tr>
						<td><?php echo $counter ?></td>
						<td><?php echo $row->phonenumber; ?></td>
						<td><?php if($row->verification_status==1) echo "<span style='color:green;'><b>Veified</b></span>"; else echo "<span style='color:red;'><b>Not Verfied</b></span>" ?></td>
					</tr>
					<?php
					 $counter++;
				}
				?>
			</tbody>
		</table>
  <script>
	  
        jQuery(document).ready(function($) {
            $('#numberListTable').DataTable();
        });
    </script>
		<?php
	} else {
		echo 'No data found.';
	}

}



