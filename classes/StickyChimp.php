<?php
/*
 * REF: https://rudrastyh.com/wordpress/using-mailchimp-api.html
 * https://developer.mailchimp.com/documentation/mailchimp/reference/reports/advice/
 *
 * Extra Fields
 *  use in $data. 'merge_fields' => array( "FNAME"=> "Justy", "LNAME"=> "Geez" )
 *  unique fields need to be created first before assigning after
 *
 * $api_key = "6b852b63044c927a9a1210c379978960-us4";
 * $list_id = "efa48a35b3";
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
			$this->base_url    = "https://{$this->data_center}.api.mailchimp.com/3.0/lists/". $this->list_id ."/";
		}
	}
	private function _build_request() {
		$this->request_params = array(
			"method" => $this->method,
			"headers" => array(
				"Authorization" => "Basic  ". base64_encode('user:'. $this->api_key)
			),
			"body" => json_encode($this->body)
		);

		return $this;
	}
	private function _build_request_url() {
		// IE: /lists/{list_id}/merge-fields/{merge_id}
		$this->request_url = $this->base_url . $this->on ."/". $this->id . $this->path;
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

		$this->response = wp_remote_post($this->request_url, $this->request_params);
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
	public function hash($value) {
		return md5(strtolower($value));
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

		if($response->status === 200 && $response->status == $status) {
			return $response;
		} else {
			$this->response_errors['create'] = $response;
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


$chimp = new StickyChimp("6b852b63044c927a9a1210c379978960-us4", "efa48a35b3");

if($_SERVER['REMOTE_ADDR'] === "50.98.107.120") {
	#$response = $chimp->create_subscriber('lala@test.com');
	$response = $chimp->remove_subscriber('lala@test.com');

	echo $chimp->request_url;
	echo "<pre>";
	print_R($response);
	echo "</pre>";
}

