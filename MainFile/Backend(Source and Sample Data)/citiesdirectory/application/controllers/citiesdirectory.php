<?php
class Citiesdirectory extends CI_Controller
{
	function __construct()
	{
		parent::__construct();

		// Load PayPal library
        $this->config->load('paypal');

        $config = array(
            'Sandbox' => $this->config->item('Sandbox'),            // Sandbox / testing mode option.
            'APIUsername' => $this->config->item('APIUsername'),    // PayPal API username of the API caller
            'APIPassword' => $this->config->item('APIPassword'),    // PayPal API password of the API caller
            'APISignature' => $this->config->item('APISignature'),    // PayPal API signature of the API caller
            'APISubject' => '',                                    // PayPal API subject (email address of 3rd party user that has granted API permission for your app)
            'APIVersion' => $this->config->item('APIVersion')        // API version you'd like to use for your call.  You can set a default version in the class and leave this blank if you want.
        );

        // Show Errors
        if ($config['Sandbox']) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        }

        $this->load->library('paypal/Paypal_pro', $config);

		$this->load->library('email',array(
										       	'mailtype'  => 'html',
										        	'newline'   => '\r\n'
												));
		$this->load->library('uploader');
	}
	
	function login()
	{
		if ( $this->user->is_logged_in() ) {
			redirect(site_url());
		} else {
			if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

				$user_name = $this->input->post('user_name');
				$user_password = $this->input->post('user_pass');

				if ( $this->user->login($user_name,$user_password )) {

					if ( ! $this->user->is_system_user() ) {

						$approved_cities = $this->city->get_all_by( array( 
							'admin_id' => $this->user->get_logged_in_user_info()->user_id, 
							'is_approved' => 1 
						))->result();

						if ( count( $approved_cities ) == 0 ){
							$this->session->set_flashdata('error', 'You could not login to system because your registered City is not yet approve' );
							redirect( site_url( 'login' ));
						} else {
							redirect(site_url() . "/dashboard/index/" . $this->session->userdata('allow_city_id'));	
						}
						
					} else {
						redirect(site_url());
					}
					
				} else {
					$this->session->set_flashdata('error','Username and password do not match.');
					redirect(site_url('login'));
				}
			} else {
				$this->load->view('login');	
			}
		}
	}

	function logout()
	{
		$this->user->logout();
	}
	
	function reset($code = false)
	{
		if (!$code || !$this->code->exists(array('code'=>$code))) {
			redirect(site_url('login'));
		}
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$code = $this->code->get_by_code($code);
			if ($code->is_systemuser == 1) {
				$data = array(
								'user_pass' => md5($this->input->post('password'))
							);
				if ($this->user->update_profile($data,$code->user_id)) {
					$this->code->delete($code->user_id);
					$this->session->set_flashdata('success','Password is successfully reset.');
					redirect(site_url('login'));
				}
			} else {
				$data = array(
								'password' => md5($this->input->post('password'))
							);
				if ($this->appuser->save($data,$code->user_id)) {
					$this->code->delete($code->user_id);
					$this->session->set_flashdata('success','Password is successfully reset.');
					redirect(site_url('login'));
				}
			}
		}
		
		$data['code'] = $code;
		$this->load->view('reset/reset',$data);
	}
	
	function forgot()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$email = $this->input->post('user_email');
			$user = $this->user->get_info_by_email($email);
			
			if ($user->user_id == "") {
				$this->session->set_flashdata('error','Email does not exist in the system.');
			} else {
				$code = md5(time().'teamps');
				$data = array(
								'user_id'=>$user->user_id,
								'code'=> $code,
								'is_systemuser'=>1
								);
				if ($this->code->save($data,$user->user_id)) {
					$sender_email = $this->config->item('sender_email');
					$sender_name = $this->config->item('sender_name');
					$to = $user->user_email;
				   $subject = 'Password Reset';
					$html = "<p>Hi,".$user->user_name."</p>".
								"<p>Please click the following link to reset your password<br/>".
								"<a href='".site_url('reset/'.$code)."'>Reset Password</a></p>".
								"<p>Best Regards,<br/>".$sender_name."</p>";
								
					$this->email->from($sender_email,$sender_name);
					$this->email->to($to); 
					$this->email->subject($subject);
					$this->email->message($html);	
					$this->email->send();
					
					$this->session->set_flashdata('success','Password reset email already sent!');
					redirect(site_url('login'));
				} else {
					$this->session->set_flashdata('error','System error occured. Please contact your system administrator.');
				}
			}
		}
		
		$this->load->view('reset/forgot');
	}

	function create_city()
	{
		if ( $this->user->is_logged_in() ) {
			redirect(site_url());
		}

		if ( $this->input->server('REQUEST_METHOD') == 'POST' ) {	

			$upload_data = $this->uploader->upload($_FILES);
			
			if ( ! isset( $upload_data['error'] )) {

				$city_data = $this->input->post();
				$user_pass = $city_data['user_password'];
				$img_desc = $city_data['image_desc'];
				
				#----------------------------------------
				# save user data
				#----------------------------------------
				// prepare user data
				$user_data = array(
					'user_name' => $city_data['user_name'], 
					'user_email' => $city_data['user_email'],
					'user_pass' => md5( $city_data['user_password']),
					'is_city_admin' => "1",
					'role_id' => 4
				);

				// prepare permision for new register user
				$permissions = array();
				foreach ( $this->module->get_all()->result() as $module ) {
					if ( $module->module_name != "users" ) {
						$permissions[] = $module->module_id;
					}
				}

				// save user data
				if ( ! $this->user->save( $user_data, $permissions )) {
					$this->session->set_flashdata( 'error', 'Database error occured in create new city');
					redirect( site_url( 'login' ));
				}

				#----------------------------------------
				# save city data
				#----------------------------------------
				// prepare data
				unset($city_data['user_name']);
				unset($city_data['user_email']);
				unset($city_data['user_password']);
				unset($city_data['conf_password']);
				unset($city_data['image_desc']);
				unset($city_data['images']);
				if(!$this->user->is_system_user()) {
					$city_data['is_approved'] = 0;
				} else {
					$city_data['is_approved'] = 1;
				}
				$city_data['admin_id'] = $user_data['user_id'];
					
				// save data
				if ( ! $this->city->save( $city_data )) {
					$this->session->set_flashdata( 'error', 'Database error occured in logging in to system');
					redirect( site_url( 'login' ));
				}

				#----------------------------------------
				# save cover photo data
				#----------------------------------------
				foreach ( $upload_data as $upload ) {
					$image = array(
						'parent_id'=>$city_data['id'],
						'type' => 'city',
						'description' => $img_desc,
						'path' => $upload['file_name'],
						'width'=>$upload['image_width'],
						'height'=>$upload['image_height']
					);
					$this->image->save($image);
				}

				#----------------------------------------
				# send email to administrator
				#----------------------------------------
				if ( ! $this->email_to_admin() ) {
					$this->session->set_flashdata( 'error', 'Error occured in email sending');
					redirect( site_url( 'login' ));
				}

				#----------------------------------------
				# do paypal transaction
				#----------------------------------------
				if(!$this->user->is_system_user()) {
					if ( $this->paypal_config->is_paypal_enable()) {
						if ( ! $this->set_express_checkout( $city_data )) {
							$this->session->set_flashdata( 'error', 'Error occured in paypal transaction');
							redirect( site_url( 'login' ));
						}	
					}
				}

				//$this->session->set_flashdata( 'success', 'Congratulation! Your City has been registered' );
				$this->load->view( 'message' );
				return;
			} else {
				$data['error'] = $upload_data['error'];
			}
		}
		
		$this->load->view( 'create_city' );
		
	}

	//is exist
	function exists($user_id=null)
	{
		$user_name = $_REQUEST['user_name'];
		
		if (strtolower($this->user->get_info($user_id)->user_name) == strtolower($user_name)) {
			echo "true";
		} else if($this->user->exists(array('user_name'=>$_REQUEST['user_name']))) {
			echo "false";
		} else {
			echo "true";
		}
	}

	function email_to_admin()
	{
		// get email configuration
		$sender_email = $this->config->item( 'sender_email' );
		$sender_name = $this->config->item( 'sender_name' );
		$admin_email = $this->config->item( 'admin_email' );

		// prepare subject and message
		$url = site_url();
	   	$subject = 'New Cities are waiting for approval';
		$html = <<<EOT
<p>Hi</p>

<p>Good day.</p>

<p>New Cities are waiting for approval.</p>
<a href='$url'>Go to City Directory</a>

<p>
	Best Regards,<br/>
	<em>Cities Directory</em>
<p>
EOT;
	
		// configure email		
		$this->email->from($sender_email,$sender_name);
		$this->email->to($admin_email); 
		$this->email->subject($subject);
		$this->email->message($html);	

		// send email
		if ( ! $this->email->send()) {
			return false;
		}

		return true;
	}

	function prep_paypal_data( $city_data, &$payments, &$fields, $is_callback = false )
	{
        // get paypal data
        $paypal_info = $this->paypal_config->get();

		// Payment Data
        $payments = array();
        $payment = array(
            'amt' => $paypal_info->price,
            'currencycode' => $paypal_info->currency_code, 
        );

		// prepare SECF and DECP data, items info
        if ( $is_callback ) {

        	$token = $this->input->get( 'token' );
	        $payer_id = $this->input->get( 'PayerID' );

	        $fields = array(
	            'token' => $token, 								// Required.  A timestamped token, the value of which was returned by a previous SetExpressCheckout call.
	            'payerid' => $payer_id,
	            'surveyquestion' => ''
            );
	        
        } else {
        	$fields = array(
	            'returnurl' => site_url( 'citiesdirectory/do_express_checkout_payment/'. $city_data['id'] ), 							// Required.  URL to which the customer will be returned after returning from PayPal.  2048 char max.
	            'cancelurl' => site_url( 'citiesdirectory/do_express_checkout_payment/'. $city_data['id'] ),
	            'surveyquestion' => ''
	        );

	        // Payment Item
	        $payment_order_items = array();
	        $item = array(
				'name' => $city_data['name'],
				'desc' => 'Registration fees for creating new city`',
				'amt' => $paypal_info->price,
				'qty' => '1'
	        );
	        array_push($payment_order_items, $item);

	        // Add payment item to payment data
	        $payment['order_items'] = $payment_order_items;
        }

        array_push($payments, $payment);
	}

	function set_express_checkout( $city_data = false )
	{
		// prepare transaction info
        $this->prep_paypal_data( $city_data, $Payments, $SECFields );

        $PayPalRequestData = array(
            'SECFields' => $SECFields,
            'Payments' => $Payments,
            'BuyerDetails' => array(),
            'ShippingOptions' => array(),
            'BillingAgreements' => array()
        );

        $PayPalResult = $this->paypal_pro->SetExpressCheckout( $PayPalRequestData );

        if ( ! $this->paypal_pro->APICallSuccessful( $PayPalResult['ACK'] )) {
        	//var_dump( $PayPalResult['ERRORS'] );
        	return false;
        } else {
            redirect( 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $PayPalResult['TOKEN'] );
            // Successful call.  Load view or whatever you need to do here.
        }
	}

	function do_express_checkout_payment( $city_id = false )
    {
    	$this->prep_paypal_data( array(), $Payments, $DECPFields, true );

    	$PayPalRequestData = array(
            'DECPFields' => $DECPFields,
            'Payments' => $Payments,
            'UserSelectedOptions' => array()
        );

        $PayPalResult = $this->paypal_pro->DoExpressCheckoutPayment($PayPalRequestData);

        if ( ! $this->paypal_pro->APICallSuccessful( $PayPalResult['ACK'] )) {

            //var_dump( $PayPalResult['ERRORS'] );
            $this->session->set_flashdata( 'error', 'Error occured in paypal transaction. Contact system administrator' );

        } else {

        	if ( $PayPalResult['PAYMENTINFO_0_ACK'] != 'Success' ) {
        		$this->session->set_flashdata( 'error', 'Error occured in paypal transaction');
        		redirect(site_url('login'));
        	}

        	$city_data = array( 'paypal_trans_id' => $PayPalResult['PAYMENTINFO_0_TRANSACTIONID'] );

        	if ( ! $this->city->save( $city_data, $city_id )) {
        		$this->session->set_flashdata( 'error', 'Error occured in paypal transaction');
        		redirect(site_url('login'));
        	}

        	//$this->session->set_flashdata( 'success', 'Congratulation! New City has been created' );
            // Successful call.  Load view or whatever you need to do here.
            $this->load->view( 'message' );
			return;
        }
            
        redirect( site_url( 'login' ));
    }
}
?>