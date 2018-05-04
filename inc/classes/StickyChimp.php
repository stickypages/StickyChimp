<?php
/*
 * REF: https://rudrastyh.com/wordpress/using-mailchimp-api.html
 * https://developer.mailchimp.com/documentation/mailchimp/reference/reports/advice/
 *
 * Extra Fields
 *  use in $data. 'merge_fields' => array( "FNAME"=> "Justin", "LNAME"=> "Geez" )
 *  unique fields need to be created first before assigning after
 *
 * $api_key = "xxxxxxxxxxxxxxxxxxxxx79978960-us4";
 * $list_id = "xxxxxa35b3";
*/
class StickyChimp
{
	public $api_key;
	public $list_id;
	public $id;
	public $data_center;
	public $base_url;
	public $request_url;
	public $request_params;
	public $method;
	public $on;
	public $body;
	public $path;
	public $response;
	public $response_headers;
	public $response_body;
	public $response_errors;
	public $response_code;
	public $status;
	private $retrieve = "limited";

	public function __construct($api_key=null, $list_id=null) {
		if(!empty($api_key)) {
			$this->api_key = $api_key;
		}
		if(!empty($list_id)) {
			$this->list_id = $list_id;
		}

		$this->_base_url();
		return $this;
	}

	public function debug() {
		$debug = array(
			 'api_key' => $this->api_key
			,'list_id' => $this->list_id
			,'id'      => $this->id
			,'method'  => $this->method
			,'on'      => $this->on
			,'path'    => $this->path
			,'body'    => $this->body
			,'data_center'     => $this->data_center
			,'base_url'        => $this->base_url
			,'request_url'     => $this->request_url
			,'request_params'  => $this->request_params
			,'response_code'   => $this->response_code
			,'response_errors' => $this->response_errors
			,'response'        => $this->response
		);
		return $debug;
	}
	public function set_api_key($api_key) {
		if(!empty($api_key)) {
			$this->api_key = $api_key;
		}
		return $this;
	}
	public function set_list_id($list_id) {
		if(!empty($list_id)) {
			$this->list_id = $list_id;
		}
		return $this;
	}
	public function hash($value) {
		return md5(strtolower($value));
	}
	public function method($method) {
		if(!empty($method)) {
			$this->method = $method;
		}
		return $this;
	}
	// What are we taking action against? Members, Reports, etc?
	// https://developer.mailchimp.com/documentation/mailchimp/reference/reports/advice/
	public function on($on) {
		if(!empty($on)) {
			$this->on = $on;
		}
		return $this;
	}
	private function _base_url() {
		if(!empty($this->api_key)) {
			$this->data_center = substr($this->api_key,strpos($this->api_key,'-')+1);
			$list_id = null;
			if(!empty($this->list_id)) {
				$list_id = "/". $this->list_id ."/";
			}
			$this->base_url    = "https://{$this->data_center}.api.mailchimp.com/3.0/lists". $list_id;
		}
	}
	private function _build_request() {
		$this->request_params = array(
			"method" => $this->method,
			"headers" => array(
				"Authorization" => "Basic ". base64_encode('user:'. $this->api_key)
			)
		);
		if(!empty($this->body)) {
			$this->request_params['body'] = json_encode($this->body);
		}

		return $this;
	}
	private function _build_request_url() {
		// IE: /lists/{list_id}/merge-fields/{merge_id}
		$on = null;
		if(!empty($this->on)) {
			$on = $this->on ."/";
		}
		$this->request_url = $this->base_url . $on . $this->id . $this->path;
		return $this;
	}
	public function body($body) {
		$this->body = $body;
		return $this;
	}
	public function path($path) {
		$this->path = $path;
		return $this;
	}
	public function request($body=null) {
		if(!empty($body)) {
			$this->body($body);
		}
		$this->_build_request_url();
		$this->_build_request();

		$this->response      = wp_remote_post($this->request_url, $this->request_params);
		$this->response_code = wp_remote_retrieve_response_code($this->response);
		$this->parse_response();

		if($this->retrieve === "limited") {
			$on = $this->on;
			if(isset($this->response_body->$on)) {
				return $this->response_body->$on;
			}
		}
		return $this->response;

	}
	public function parse_response() {
		if ( ! empty( $this->response ) ) {
			$this->response_headers = wp_remote_retrieve_headers($this->response);
			$this->response_body    = json_decode(wp_remote_retrieve_body($this->response));
			return $this->response_body;
		}
	}

	/* Base Functions */
	public function id($id) {
		$this->id = $id;
		return $this;
	}
	public function read() {
		$response = $this->method( "GET" )->request();
		return $response;
	}
	public function create($more=null) {
		$response = $this->method( "POST" )->request($more);
		if($response->status === 200) {
			return $response;
		} else {
			$this->response_errors['create'] = $response;
		}
	}
	public function delete($what) {
		if(is_string($what)) {
			$what = $this->hash($what);
		}
		$response = $this->method( "DELETE" )
		                 ->path( $what )
		                 ->request();

		if($response->status === 200) {
			return $response;
		} else {
			$this->response_errors['remove'] = $response;
			return $response->status;
		}
	}


	/* Helper Functions */
	public function create_subscriber($email, $more = array(), $status="subscribed", $method="PUT") {
		$more_plus = $more + array( 'email_address' => $email, 'status' => $status );

		$response = $this->method( $method )
		                 ->on('members')
		                 ->path( $this->hash($email) )
		                 ->request($more_plus);

		if($this->response_code === 200) {
			return $response;
		} else {
			$this->response_errors['create_subscriber'] = $response;
			return $this->response;
		}
	}
	public function create_field($name, $type="text") {
		$response = $this->method( "POST" )
		                 ->on('merge-fields')
		                 ->request(array("name"=>$name, "type"=>$type));

		if($this->response_code === 200) {
			return $response;
		} else {
			$this->response_errors['create_field'] = $response;
			return $this->response;
		}
	}
	public function remove_subscriber($email, $rod = "DELETE") {
		// Do you want to completely remove them, or just unsubscribe them?
		if($rod === "DELETE") {
			return $this->create_subscriber($email, array(), "unsubscribed", "DELETE");
		} else {
			return $this->create_subscriber($email, array(), "unsubscribed");
		}
	}
}