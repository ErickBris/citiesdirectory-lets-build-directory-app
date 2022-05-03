<?php	
require_once(APPPATH.'/libraries/REST_Controller.php');

class Gcm extends REST_Controller
{

	function __construct()
	{
		parent::__construct();
	}

	function register_post()
	{
		$data = $this->post();

		if ( $data == null ) {
			$this->response(array(
				'status'=>'error',
				'data'	=> 'Invalid JSON')
			);
		}

		if ( ! array_key_exists( 'reg_id', $data )) {
			$this->response(array(
				'status'=>'error',
				'data'	=> 'Required Token ID')
			);
		}
		
		$user_data = array(
			'reg_id' => $data['reg_id']
		);

		if ( $this->gcm_token->exists( $user_data )) {
			$this->response(array(
				'status'=>'error',
				'data'	=> 'Token already exist')
			);
		} else {
			$this->gcm_token->save( $user_data );
			$this->response(array(
				'status'=>'success',
				'data'	=> 'Token successfully added.')
			);
		}
	}

	function unregister_post()
	{
		$data = $this->post();

		if ( $data == null ) {
			$this->response(array(
				'status'=>'error',
				'data'	=> 'Invalid JSON')
			);
		}

		if ( ! array_key_exists( 'reg_id', $data )) {
			$this->response(array(
				'status'=>'error',
				'data'	=> 'Required Token ID')
			);
		}
		
		$user_data = array(
			'reg_id' => $data['reg_id']
		);

		if ( $this->gcm_token->exists( $user_data )) {
			$this->gcm_token->delete_by( $user_data );
			$this->response(array(
				'status'=>'success',
				'data'	=> 'Token successfully removed.')
			);
		} else {
			$this->response(array(
				'status'=>'error',
				'data'	=> 'Token already exist')
			);
		}
	}

}

?>