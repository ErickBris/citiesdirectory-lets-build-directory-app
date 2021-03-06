<?php 
require APPPATH.'/libraries/REST_Controller.php';

class Images extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('uploader');
	}
	
	function upload_post()
	{
		$user_id = $this->post('userId');
		if ( !$user_id ) {
			$this->response(array(
				'status'=>'error',
				'data'	=> 'Required User ID')
			);
		}

	    // Get file name posted from Android App
	    $filename = $this->post('filename');

		// Decode Image
		$base = $this->post('image');
	    $binary = base64_decode($base);
	    header( 'Content-Type: bitmap; charset=utf-8' );

	    // Images will be saved under 'uploads' folder
	    $path_parts = pathinfo( $filename );
	    $filename = $path_parts['filename'] . date( 'YmdHis' ) .'.'. $path_parts['extension'];
	    $file = fopen( 'uploads/'. $filename, 'wb' );
	    fwrite( $file, $binary );
	    fclose( $file );

	    $user_data = array( 'profile_photo' => $filename );

	    if ( $this->appuser->save( $user_data, $user_id ) ) {
			$this->response(array(
				'status'=>'success',
				'data'	=> $filename)
			);
			
		} else {
			$this->response(array(
				'status'=>'error',
				'data'	=> 'na')
			);
		}
	}
}
?>



