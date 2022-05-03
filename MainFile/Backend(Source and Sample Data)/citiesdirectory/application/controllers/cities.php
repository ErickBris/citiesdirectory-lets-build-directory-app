<?php
require_once('main.php');
class Cities extends Main
{
	function __construct()
	{
		parent::__construct('cities');

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
	
	function index()
	{
		$this->session->unset_userdata('city_id');
	
		$cities = array();

		if ($this->input->server('REQUEST_METHOD')=='POST') {
			$searchterm = $this->input->post('searchterm');

			if ( $this->user->is_system_user() ) {

				$cities = $this->city->get_all_by( array( 
					'searchterm' => $searchterm,
					'is_approved' => APPROVE 
				))->result();
			} else {

				$cities = $this->city->get_all_by( array( 
					'searchterm' => $searchterm, 
					'admin_id' => $this->user->get_logged_in_user_info()->user_id,
					'is_approved' => APPROVE 
				))->result();	
			}
			
			$data['searchterm'] = $searchterm;
		} else {
			if ( $this->user->is_system_user() ) {
				$cities = $this->city->get_all( array() )->result();
			} else {
				$cities = $this->city->get_all_by( array( 
					'admin_id' => $this->user->get_logged_in_user_info()->user_id, 
					'is_approved' => APPROVE 
				))->result();
			}
		}
		
		$temp_cities_arr = array();
		foreach ($cities as $city) {
			$img = $this->image->get_all_by_type($city->id, 'city')->result();

			if ( count( $img ) > 0 ) {
				$city->image = $img[0]->path;
			} else {
				$city->image = "";
			}
			$temp_cities_arr[] = $city;
		}
		$data['cities'] = $temp_cities_arr;
		$this->load->view('cities/list', $data);
	}
	
	function create()
	{
		if(!$this->session->userdata('is_city_admin')) {
		      $this->check_access('add');
		}
		if ($this->input->server('REQUEST_METHOD')=='POST') {			
			$upload_data = $this->uploader->upload($_FILES);
			
			if (!isset($upload_data['error'])) {
				$city_data = $this->input->post();
				
				$img_desc = $city_data['image_desc'];
				unset($city_data['image_desc']);
				unset($city_data['images']);

				$city_data['admin_id'] = $this->user->get_logged_in_user_info()->user_id;
				
				if(!$this->user->is_system_user()) {
					$city_data['is_approved'] = 0;
				} else {
					$city_data['is_approved'] = 1;
				}
				
				if ( ! $this->city->save($city_data)) {
					$this->session->set_flashdata('error','Database error occured.Please contact your system administrator.');
				}

				foreach ($upload_data as $upload) {
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
					
				// send email to admin
				if ( ! $this->email_to_admin() ) {
					$this->session->set_flashdata( 'error', 'Error occured in email sending');
				}
				
				if(!$this->user->is_system_user()) {
					if ( $this->paypal_config->is_paypal_enable()) {
						if ( ! $this->set_express_checkout( $city_data )) {
							$this->session->set_flashdata( 'error', 'Error occured in paypal transaction');
						}
					}
				}
						
				$this->session->set_flashdata( 'success', 'Congratulation! New City has been created' );
				redirect(site_url('cities/create'));
			} else {
				$data['error'] = $upload_data['error'];
			}
		}
		
		$content['content'] = $this->load->view('cities/create',array(),true);
		$this->load_template($content,false);
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

	function approval()
	{
		if( $this->session->userdata('is_city_admin')) {
		    return;
		}

		$pag = $this->config->item('pagination');
		$pag['base_url'] = site_url('cities/index');
		$pag['total_rows'] = count( $this->city->get_all_by( array( 'is_approved' => 0 ))->result());

		$data['cities'] = $this->city->get_all_by( array( 'is_approved' => 0 ), $pag['per_page'], $this->uri->segment(3));
		$data['pag'] = $pag;

		$content['content'] = $this->load->view( 'cities/approval', $data, true);
		$this->load_template( $content, false);
	}

	function paypal_config()
	{
		if( $this->session->userdata('is_city_admin')) {
		    return;
		}

		if ( $this->input->server( 'REQUEST_METHOD' ) == 'POST' ) {
			$config = array(
				'status' => $this->input->post( 'status' ),
				'price' => $this->input->post( 'price' ),
				'currency_code' => $this->input->post( 'currency' )
			);

			if ( ! $this->paypal_config->save( $config )) {
				$this->session->set_flashdata('error','Database error occured.Please contact your system administrator.');
			}

			$this->session->set_flashdata('success','Paypal Configuration is successfully updated.');
			redirect( site_url( 'cities/paypal_config' ));
		}

		$data['paypal_config'] = $this->paypal_config->get_paypal_config();
		$content['content'] = $this->load->view( 'cities/paypal_config', $data, true);
		$this->load_template( $content, false);
	}
	
	function send_gcm() 
	{
		if( $this->session->userdata('is_city_admin')) {
		    return;
		}
		$content['content'] = $this->load->view( 'gcm/form', array(), true );
		$this->load_template($content, false);
	}
	
	function push_message() 
	{
		if ( $this->input->server( 'REQUEST_METHOD' ) == "POST" ) {
			$message = $this->input->post( 'message' );

			$devices = $this->gcm_token->get_all()->result();;

			$reg_ids = array();
			if ( count( $devices ) > 0 ) {
				foreach ( $devices as $device ) {
					$reg_ids[] = $device->reg_id;
				}
			}

			$status = $this->sendMessageThroughGCM( $reg_ids, array( "m" => $message ));

			$this->session->set_flashdata( 'success', "Successfully Sent Push Notification" );

			redirect( 'cities/send_gcm' );
		}

		$content['content'] = $this->load->view( 'gcm/form', array(), true );
		
		$this->load_template($content, false);
	}

	//Generic php function to send GCM push notification
   	function sendMessageThroughGCM( $registatoin_ids, $message) 
   	{
		//Google cloud messaging GCM-API url
		$url = 'https://android.googleapis.com/gcm/send';
		$fields = array(
		    'registration_ids' => $registatoin_ids,
		    'data' => $message,
		);
		// Update your Google Cloud Messaging API Key
		//define("GOOGLE_API_KEY", "AIzaSyCCwa8O4IeMG-r_M9EJI_ZqyybIawbufgg");
		define("GOOGLE_API_KEY", $this->config->item( 'gcm_api_key' ));  	
			
		$headers = array(
		    'Authorization: key=' . GOOGLE_API_KEY,
		    'Content-Type: application/json'
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$result = curl_exec($ch);				
		if ($result === FALSE) {
		    die('Curl failed: ' . curl_error($ch));
		}
		curl_close($ch);
		return $result;
    }
	

	function approve( $city_id = 0 )
	{
		$city_data = array( 'is_approved'=> APPROVE );

		$city = $this->city->get_info( $city_id );

		$user_email = $this->user->get_info( $city->admin_id )->user_email;
			
		if ( $this->city->save( $city_data, $city_id )) {
			if ( $this->email_to_user( $user_email )) {
				echo 'true';
			} else {
				echo 'email-error';
			}
		} else {
			echo 'false';
		}
	}

	function reject( $city_id = 0 )
	{
		$city_data = array( 'is_approved'=> REJECT );

		$city = $this->city->get_info( $city_id );

		$user_email = $this->user->get_info( $city->admin_id )->user_email;
			
		if ( $this->city->save( $city_data, $city_id )) {
			if ( $this->email_to_user( $user_email, false )) {
				echo 'true';
			} else {
				echo 'email-error';
			}
		} else {
			echo 'false';
		}
	}

	function detail( $city_id = 0 )
	{
		$data['city'] = $this->city->get_info( $city_id );

		$content['content'] = $this->load->view( 'cities/detail', $data, true );
		$this->load_template( $content, false, true );
	}
	
	function edit( $city_id = 0 )
	{
		if(!$this->session->userdata('is_city_admin')) {
			$this->check_access('edit');
		}
		
		$this->session->set_userdata('city_id', $city_id);
		$this->session->set_userdata('action', 'city_edit');
		
		if ($this->input->server('REQUEST_METHOD')=='POST') {
			if ($this->input->post('status')!= 1) {
				$_POST['status'] = 0;
			}
			
			$city_data = $this->input->post();
			
			if ($this->city->save($city_data, $city_id)) {
				$this->session->set_flashdata('success','City Information is successfully updated.');
			} else {
				$this->session->set_flashdata('error','Database error occured.Please contact your system administrator.');
			}
			redirect(site_url('cities/edit/' . $city_id));
		}
		
		$data['city'] = $this->city->get_info($city_id);
		
		$content['content'] = $this->load->view('cities/edit',$data,true);
		$this->load_template($content,false,true);
	}
	
	function upload($city_id=0)
	{
		if(!$this->session->userdata('is_city_admin')) {
		    $this->check_access('edit');
		}
		
		$upload_data = $this->uploader->upload($_FILES);
		
		if (!isset($upload_data['error'])) {
			unlink('./uploads/'.$this->image->get_info_parent_type($city_id,'city')->path);
			unlink('./uploads/thumbnail/'.$this->image->get_info_parent_type($city_id,'city')->path);
			$this->image->delete_by_parent($city_id,'city');
			
			foreach ($upload_data as $upload) {
				$image = array(
					'parent_id'=> $city_id,
					'type' => 'city',
					'description' => $this->input->post('image_desc'),
					'path' => $upload['file_name'],
					'width'=>$upload['image_width'],
					'height'=>$upload['image_height']
				);
				$this->image->save($image);
				redirect(site_url('cities/edit/' . $city_id));
			}
			
		} else {
			$data['error'] = $upload_data['error'];
		}
		
		$data['city'] = $this->city->get_info($city_id);
		
		$content['content'] = $this->load->view('cities/edit',$data,true);
		$this->load_template($content);
	}
	
	function edit_image($city_id, $image_id)
	{
		if(!$this->session->userdata('is_city_admin')) {
		    $this->check_access('edit');
		}
		
		$image = array(
			'description' => $this->input->post('image_desc')
		);
			
		if ($this->image->save($image, $image_id)) {
			$this->session->set_flashdata('success','City cover photo description is successfully updated.');
		} else {
			$this->session->set_flashdata('error','Database error occured.Please contact your system administrator.');
		}
		redirect(site_url('cities/edit/' . $city_id));
	}

	function delete_image($city_id,$image_id,$image_name)
	{
		if(!$this->session->userdata('is_city_admin')) {
		    $this->check_access('edit');
		}
		
		if ($this->image->delete($image_id)) {
			unlink('./uploads/'.$image_name);
			unlink('./uploads/thumbnail/'.$image_name);
			$this->session->set_flashdata('success','City cover photo is successfully deleted.');
		} else {
			$this->session->set_flashdata('error','Database error occured.Please contact your system administrator.');
		}
		redirect(site_url('cities/edit/' . $city_id));
	}	
	
	function delete_city($city_id)
	{
		if(!$this->session->userdata('is_city_admin')) {
		     $this->check_access('delete');
		}
		
		$city_images = $this->image->get_item_images_by_city(2);
		foreach($city_images->result() as $img):	
			if ($this->image->delete($img->id)) {
				unlink('./uploads/'.$img->path);		
			}	
		endforeach;
		
		
		$this->category->delete_by_city($city_id);
		$this->favourite->delete_by_city($city_id);
		$this->feed->delete_by_city($city_id);
		
		
		$this->image->delete_city_image_by_parent($city_id); //still need to delete physical images
		
		
		$this->like->delete_by_city($city_id);
		$this->review->delete_by_city($city_id);
		$this->city->delete_by_city($city_id);
		$this->follow->delete_by_city($city_id);
		$this->sub_category->delete_by_city($city_id);
		$this->touch->delete_by_city($city_id);
		
		$this->item->delete_by_city($city_id);
		$this->inquiry->delete_by_city($city_id);
		$this->rating->delete_by_city($city_id);
		$this->user->delete_by_city($city_id);
		
		$this->session->set_flashdata('success','City is successfully deleted.');
		redirect(site_url('cities'));
		
		
	}	
	
	function email_to_user( $to = false, $is_approved = true )
	{
		// if there is no receptient email
		if ( ! $to ) return false;

		// get email configuration
		$sender_email = $this->config->item( 'sender_email' );
		$sender_name = $this->config->item( 'sender_name' );

		// prepare subject and message
		$url = site_url();

		$action = "approved";
		if ( ! $is_approved ) {
			$action = "rejected";
		}

	   	$subject = 'You city has been '. $action;
		$html = <<<EOT
<p>Hi</p>

<p>Good day.</p>

<p>Your city has been $action.</p>
<a href='$url'>Go to City Directory</a>

<p>
	Best Regards,<br/>
	<em>Cities Directory</em>
<p>
EOT;
	
		// configure email		
		$this->email->from( $sender_email, $sender_name );
		$this->email->to( $to ); 
		$this->email->subject( $subject );
		$this->email->message( $html );	

		// send email
		if ( ! $this->email->send() ) {
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
	            'returnurl' => site_url( 'cities/do_express_checkout_payment/'. $city_data['id'] ), 							// Required.  URL to which the customer will be returned after returning from PayPal.  2048 char max.
	            'cancelurl' => site_url( 'cities/do_express_checkout_payment/'. $city_data['id'] ),
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
        		redirect(site_url('cities/create'));
        	}

        	$city_data = array( 'paypal_trans_id' => $PayPalResult['PAYMENTINFO_0_TRANSACTIONID'] );

        	if ( ! $this->city->save( $city_data, $city_id )) {
        		$this->session->set_flashdata( 'error', 'Error occured in paypal transaction');
        		redirect(site_url('cities/create'));
        	}

        	$this->session->set_flashdata( 'success', 'Congratulation! New City has been created' );
            // Successful call.  Load view or whatever you need to do here.
        }

		redirect(site_url('cities/create'));
    }
}
?>