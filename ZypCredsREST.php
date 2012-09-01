<?php
/**
 * Class ZypCredsREST
 * Summary: A libary which handles requests for ZypCreds REST API. 
 * Version: 0.5 pre-release
 * Authors: Greg Kasbarian
 * Initial Release Date: n/a
 *
 *
 * 	request_verification( $index )
 * 	attempt_verification( $index, $code )
 *
 *	get_whitelist()
 *	add_to_whitelist( $index )
 *	delete_from_whitelist( $index )
 *
 *	get_blacklist()
 *	add_to_blacklist( $index )
 *	delete_from_blacklist( $index )
 *
 *	call_uri()
 *	parse_xml()
 *	parse_json()
 *	
 **/

class ZypCredsREST{
	
	# Your API Credentials
	private $api_id  = '';
	private $api_key = '';

	# URIs
	private $verify_uri		= 'http://api.zypcreds.com/verify/';
	private $whitelist_uri 	= 'http://api.zypcreds.com/whitelist/';
	private $blacklist_uri 	= 'http://api.zypcreds.com/blacklist/'

	# Values
	public $prepped_index;
	public $result_desc;
	public $result_bool;
	public $error_no;	
	public $error_desc;
	public $secret_code;

	# Settings
	private $timeout = 30;
	private $accept_type = 'json';	# JSON XML

	
	# ----------------------------------------------
	# ----------------------------------------------


	/**
	 * __construct( $api_id, $api_key )
	 * Summary: Constructor requires $api_id and $api_key to complete. If both passed, places values in class variables.
	 * @param 	str 	$api_id 	User's API ID
	 * @param 	str 	$api_key 	User's API KEY
	 * @return 	bool 	True 		Constructor successful.
	 * @return 	bool 	False 		Constructor failed.
	 **/
	function __construct( $api_id, $api_key ){

		if( !$api_id || !$api_key ){
			$this->error_desc = "Api ID and Api KEY are both required.";
			return false;
		}else{
			$this->api_id = $api_id;
			$this->api_key = $api_key;
			return true;
		}
	}


	# -------------------------
	## VERIFICATION API METHODS
	# -------------------------

	/**
	 * request_verification( $index )
	 * Summary: Request a verification code be sent to the $index provided.
	 * @param 	str 	$index 	Index to request verification for.
	 * @return 	bool 	False 	Error occurred.
	 * @return 	obj 	$data 	Data returned from ->call_uri() call.
	 **/
	function request_verification( $index ){

		# Check for required values
		if( !$index ){
			$this->error_no = '1';
			$this->error = 'Missing required parameters.';
			return false;
		}

		# Prep params
		$params = array( 'index' => $index );

		# Initiate request and gather response.
		$data = $this->call_uri( $params, $this->verify_uri, 'post' );

		# Parse the returned content and place values within class.
		$this->parse_xml( $data );
		
		return $data;
	}


	/**
	 * attempt_verification( $index, $code )
	 * Summary: Check if $code provided is a valid verification code for the $index provided.
	 * @param 	str 	$index 		The index you wish to verify.
	 * @param 	str 	$code 		Code entered by user.
	 * @return 	str 	$response 	Returned content from uri call.
	 **/
	function attempt_verification( $index, $code ){

		# Check for required parameters.
		if( !$index || !$code ){
			$this->error_no = '1';
			$this->error_desc = 'Missing required parameters';
			return false;
		}

		# Prep params
		$params = array( 'index' => $index, 'code' => $code );

		# Attempt to verify the code entered by the user
		$data = $this->call_uri( $params, $this->verify_uri, 'put' );
		
		# Parse the result
		$this->parse_xml( $data );

		# Return raw response
		return $data;
	}


	# ----------------------
	## WHITELIST API METHODS
	# ----------------------

	/**
	 * get_whitelist()
	 * Summary: Fetch the whitelist for the current api_id
	 * @return 	str 	$response 	Content from uri call.
	 */
	function get_whitelist(){

		# No parameters. - Pass empty array
		$params = array();

		# Attempt to fetch whitelist
		$response = $this->call_uri( $params, $this->whitelist_uri, 'get' );

		# Parse result and place values in variables.
		$this->parse_xml( $response );

		return $response;
	}


	/**
	 * add_index_to_whitelist( )
	 * Summary: Attempts to add the provided index to the api_ids whitelist.
	 * @param 	str 	$index 	The index wished to be added to the whitelist.
	 * @return 	str 	$data 	The result data from the ->call_uri()
	 **/
	function add_index_to_whitelist( $index ){

		# Check for required parameters.
		if( !$index ){
			$this->error_no = '1';
			$this->error_desc = 'Missing required parameters.';
			return false;
		}

		# Prep params
		$params = array( 'index' => $index );
		
		# Attempt to add index to whitelist
		$data = $this->call_uri( $params, $this->whitelist_uri, 'post' );

		# Parse data and assign values to variables
		$this->parse_xml( $data );

		# Returning incase they want to see raw xml/json
		return $data;
	}


	/**
	 * delete_index_from_whitelist()
	 * Summary: Attempts to delete the provided index from the api_ids whitelist.
	 * @param 	str 	$index 	The index wished to be deleted from the whitelist.
	 * @return 	str 	$data  	The resulting data from the ->call_uri()
	 **/
	function delete_index_from_whitelist( $index ){

		# Check for required parameters
		if( !$index ){
			$this->error_no = '0';
			$this->error_desc = 'Index required';
			return false;
		}

		# Prep params
		$params = array('index' => $index);

		# Attempt to delete index from whitelist
		$data = $this->call_uri( $params, $this->whitelist_uri, 'delete' );

		# Parse data and assign values to variables
		$this->parse_xml( $data );

		return $data;
	}


	# ----------------------
	## BLACKLIST API METHODS
	# ----------------------

	/**
	 * get_blacklist()
	 * Summary: Fetch the blacklist for the current api_id
	 * @return 	str 	$response 	Content from uri call.
	 */
	function get_blacklist(){

		# No parameters. - Pass empty array
		$params = array();

		# Attempt to fetch whitelist
		$response = $this->call_uri( $params, $this->blacklist_uri, 'get' );

		# Parse result and place values in variables.
		$this->parse_xml( $response );

		return $response;
	}


	# ---------------------------------
	## PRIVATE METHODS / HELPER METHODS
	# ---------------------------------

	/**
	 * call_uri( $data, $uri, $verb )
	 * Summary: Makes a CURL call to the $uri provided and returns the resulting content.
	 * @param 	array 	$data 	Parameters passed by user.
	 * @param 	str 	$uri 	URI to call.
	 * @param 	str 	$verb 	Form submission method - GET POST PUT DELETE
	 * @return 	str 	$data 	Resulting content (XML or JSON)
	 **/
	private function call_uri( $data, $uri, $verb ){

		# Affix api id to params list
		$data = array_merge($data, array('api_id' => $this->api_id));
		
		# Create hash
		$hash = $this->create_hash( $data );
		
		# Affix hash to params list
		$data = array_merge($data, array('hash' => $hash));

		# Start curl
		$ch = curl_init();
		
		# Decide verb
		switch( $verb ){
			
			case 'get' :
				# Affix params to the uri for GET
				$uri = $uri . '?' . http_build_query($data);
				break;

			case 'post' :	
				# Place params in post and enable post.
				curl_setopt($ch, CURLOPT_POST, true);  			
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));  
    			break;

			case 'put':
				# CUSTOM REQUEST FOR PUT - USE SAME POSTFIELDS
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
				break;

			case 'delete':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
				break;
		}

		# SET CURL OPTIONS
		curl_setopt( $ch, CURLOPT_URL, $uri );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		
		# Set accept header
		if( $this->accept_type == 'json' ){
			curl_setopt($cURL, CURLOPT_HTTPHEADER, array ( "Accept: application/json" ) );
		}else{
			curl_setopt($cURL, CURLOPT_HTTPHEADER, array ( "Accept: text/xml" ) );
		}

		# Execute curl
		$data = curl_exec( $ch );
		curl_close ( $ch );
		return $data;
	}


	/**
	 * parse_xml( $data )
	 * Summary: Parses through the XML result and places all the values in the class variables.
	 * @param 	str 	$data 	 The resulting XML from the CURL call.
	 * @return 	void
	 **/
	private function parse_xml( $xml ){

		if( !$xml ){
			return false;
		}
		$obj = new SimpleXMLElement( $xml );
		
		# Use (string) or (int) or SimpleXMLElement passes an object and causes SESSION fail.
		$this->result_bool = 	(string)$obj->result['bool'];
		$this->result_desc = 	(string)$obj->result[0];
		$this->prepped_index = 	(string)$obj->index['prepped'];
		$this->error_no = 		(string)$obj->error['number'];
		$this->error_desc = 	(string)$obj->error['description'];

	}


	/**
	 * parse_json
	 * Summary:
	 * @param 	str 	$json 	The resulting JSON content.
	 * @return 	void
	 **/
	private function parse_json( $json ){

		# PARSE THE JSON
		$x = json_decode($json, true);
		
		foreach( $json as $k => $v ){
			error_log( $k.'-'.$v );
		}

		//$x['result']['bool'];

		// $this->result_bool = 	(string)$x['result']['bool'];
		// $this->result_desc = 	(string)$x['result']['description'];
		// $this->prepped_index = 	(string)$x['index']['prepped'];
		// $this->error_no = 		(string)$x['error']['number'];
		// $this->error_desc = 	(string)$x['error']['description'];


		// foreach ($json_a as $person_name => $person_a) {
  //   		echo $person_a['status'];
		// }
	}

	
	/**
	 * create_hash( $data )
	 * Summary: Creates a hash value from the params passed to it.
	 * @param 	arr 	$data 	Parameters from user.
	 * @return 	str 	$hash 	The properly hashed value from using params and the api_key
	 **/
	private function create_hash( $data ){

		$str = '';
		foreach( $data as $k => $v ){
			if($k !='hash' && $k !='siteurl'){ $str .= $k.$v; }
		}
		return hash_hmac('sha1', $str, $this->api_key);
	}



}# End Class
?>