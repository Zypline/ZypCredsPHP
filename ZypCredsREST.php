<?php
/**
 * Class ZypCredsREST
 * Summary: A libary which handles requests for ZypCreds REST API. 
 * Version: 0.7
 * Authors: Greg Kasbarian
 * Release Date Version 1.0: n/a
 *
 * Methods: 
 * 
 * 	request_verification( $index, $ip )
 * 	attempt_verification( $index, $code, $ip )
 *
 *	check_token( $index, $token, $ip )
 * 
 *	get_whitelist()
 *	add_to_whitelist( $index )
 *	delete_from_whitelist( $index )
 *
 *	get_blacklist()
 *	add_to_blacklist( $index )
 *	delete_from_blacklist( $index )
 *
 *  --- privates ---
 *  
 *	call_uri()
 *	parse_data()
 *	parse_xml()
 *	parse_json()
 *	create_hash()
 *	
 **/

class ZypCredsREST{
	
	# Your API Credentials
	private $api_id  = ''; # Place your api ID  here <---		** ATTENTION **
	private $api_key = ''; # Place your api KEY here <---		** ATTENTION **

	# ---

	# URIs
	private $verify_uri		= 'http://api.zypcreds.com/verify/';
	private $token_uri 		= 'http://api.zypcreds.com/token/';
	private $whitelist_uri 	= 'http://api.zypcreds.com/whitelist/';
	private $blacklist_uri 	= 'http://api.zypcreds.com/blacklist/';

	# Values
	public $raw_index;
	public $raw_country;
	public $raw_ip;
	
	public $prepped_index;
	public $friendly_index;
	public $secret_code;
	
	public $token;
	public $token_expiration;
	public $token_bool;
	
	public $result_desc;
	public $result_bool;
	public $error_no;	
	public $error_desc;
	
	# Settings
	private $timeout = 30;
	private $accept_type = 'json';	# Default return type

	
	# ----------------------------------------------
	# ----------------------------------------------


	/**
	 * __construct()
	 * Summary: Requires $api_id and $api_key to begin.
	 * @param 	str 	$api_id 	Your API ID
	 * @param 	str 	$api_key 	Your API KEY
	 * @return 	bool 	True 		Constructor successful.
	 * @return 	bool 	False 		Constructor failed.
	 **/
	function __construct( $api_id, $api_key ){

		if( !$api_id || !$api_key ){
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
	 * request_verification()
	 * Summary: Requests a verification code be sent to the index provided.
	 *	Preps parameters, makes curl call, and then parses data into class variables.
	 * @param 	str 	$index 	Index requesting verification for.
	 * @return 	bool 	False 	Error occurred - error number and description set.
	 * @return 	obj 	$data 	Raw-content returned from ->call_uri() call.
	 **/
	function request_verification( $index, $ip ){

		# Check for required values
		// if( !$index || !$ip ){
		// 	$this->error_no = '1';
		// 	$this->error = 'Missing required parameters.';
		// 	return false;
		// }

		# Prep params
		$params = array( 'index' => $index, 'ip' => $ip );

		# Initiate request and gather response.
		$data = $this->call_uri( $params, $this->verify_uri, 'post' );

		# Parse the returned content and place values within class.
		$this->parse_data( $data );
		
		return $data;
	}

	/**
	 * attempt_verification()
	 * Summary: Check if $code provided is a valid verification code for the $index provided.
	 * @param 	str 	$index 		The index you wish to verify.
	 * @param 	str 	$code 		Code entered by user.
	 * @return 	str 	$response 	Returned content from uri call.
	 **/
	function attempt_verification( $index, $code, $ip ){

		# Check for required parameters.
		if( !$index || !$code ){
			$this->error_no = '1';
			$this->error_desc = 'Missing required parameters';
			return false;
		}

		# Prep params
		$params = array( 'index' => $index, 'code' => $code, 'ip' => $ip );

		# Attempt to verify the code entered by the user
		$data = $this->call_uri( $params, $this->verify_uri, 'put' );
		
		# Parse the result
		$this->parse_data( $data );

		# Return raw response
		return $data;
	}

	# ------------------
	## TOKEN API METHODS
	# ------------------
	
	/**
	 * check_token()
	 * @param  str $index Index who's session we're checking.
	 * @param  str $token Session token that we're checking.
	 * @return bool / response data
	 **/
	function check_token( $index, $token, $ip ){

		# Check for required params
		if( !$index || !$token ){
			$this->error_no = '1';
			$this->error_desc = 'Missing required paramaters';
			return false;
		}

		# Prep params 
		$params = array( 'index' => $index, 'token' => $token, 'ip' => $ip );

		# Check if index/token (along with API ID and timestamp) is valid.
		$data = $this->call_uri( $params, $this->token_uri, 'post');

		# Parse the result
		$this->parse_data( $data );

		# Retun raw response
		return $data;
	}
	
	# ----------------------
	## WHITELIST API METHODS
	# ----------------------

	/**
	 * get_whitelist()
	 * Summary: Fetch the whitelist for the current api_id
	 * @return 	str 	$response 	Content from uri call.
	 **/
	function get_whitelist(){

		# No parameters. - Pass empty array
		$params = array();

		# Attempt to fetch whitelist
		$response = $this->call_uri( $params, $this->whitelist_uri, 'get' );

		# Parse result and place values in variables.
		$this->parse_data( $response );

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
		$this->parse_data( $data );

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
		$this->parse_data( $data );

		return $data;
	}


	# ----------------------
	## BLACKLIST API METHODS
	# ----------------------

	/**
	 * get_blacklist()
	 * Summary: Fetch the blacklist for the current api_id
	 * @return 	str 	$response 	Content from uri call.
	 **/
	function get_blacklist(){

		# No parameters. - Pass empty array
		$params = array();

		# Attempt to fetch whitelist
		$response = $this->call_uri( $params, $this->blacklist_uri, 'get' );

		# Parse result and place values in variables.
		$this->parse_data( $response );

		return $response;
	}

	/**
	 * add_index_to_blacklist( )
	 * Summary: Attempts to add the provided index to the api_ids blacklist.
	 * @param 	str 	$index 	The index wished to be added to the blacklist.
	 * @return 	str 	$data 	The result data from the ->call_uri()
	 **/
	function add_index_to_blacklist( $index ){

		# Check for required parameters.
		if( !$index ){
			$this->error_no = '1';
			$this->error_desc = 'Missing required parameters.';
			return false;
		}

		# Prep params
		$params = array( 'index' => $index );
		
		# Attempt to add index to whitelist
		$data = $this->call_uri( $params, $this->blacklist_uri, 'post' );

		# Parse data and assign values to variables
		$this->parse_data( $data );

		# Returning incase they want to see raw xml/json
		return $data;
	}

	/**
	 * delete_index_from_blacklist()
	 * Summary: Attempts to delete the provided index from the api_ids blacklist.
	 * @param 	str 	$index 	The index wished to be deleted from the blacklist.
	 * @return 	str 	$data  	The resulting data from the ->call_uri()
	 **/
	function delete_index_from_blacklist( $index ){

		# Check for required parameters
		if( !$index ){
			$this->error_no = '0';
			$this->error_desc = 'Index required';
			return false;
		}

		# Prep params
		$params = array('index' => $index);

		# Attempt to delete index from whitelist
		$data = $this->call_uri( $params, $this->blacklist_uri, 'delete' );

		# Parse data and assign values to variables
		$this->parse_data( $data );

		return $data;
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
			curl_setopt($ch, CURLOPT_HTTPHEADER, array ( "Accept: application/json" ) );
		}else{
			curl_setopt($ch, CURLOPT_HTTPHEADER, array ( "Accept: text/xml" ) );
		}

		# Execute curl
		$data = curl_exec( $ch );
		curl_close ( $ch );
		return $data;
	}

	/**
	 * parse_data()
	 * Summary: Calls either JSON or XML parse depending on what the accepted content type is.
	 * @param 	str 	$data 	Returned content from the ->call_uri() call.
	 * @return 	void 
	 **/
	private function parse_data( $data ){
		
		if( $this->accept_type == 'json' ){
			$this->parse_json( $data );
		}else{
			$this->parse_xml( $data );
		}
	}

	/**
	 * parse_xml( $data )
	 * Summary: Parses through the XML result and places all the values in the class variables.
	 * @param 	str 	$data 	 The resulting XML from the CURL call.
	 * @return 	void
	 **/
	private function parse_xml( $xml ){

		if(!$xml){ return false; }

		$obj = new SimpleXMLElement( $xml );
		# Use (string) or (int) or SimpleXMLElement passes an object and causes SESSION fail.
		$this->raw_index 		= (string)$obj->params['index'];
		$this->raw_country 		= (string)$obj->params['country'];
		$this->raw_ip 			= (string)$obj->params['ip'];
		
		$this->prepped_index 	= (string)$obj->index['prepped'];
		$this->friendly_index 	= (string)$obj->index['friendly'];
		
		$this->token 			= (string)$obj->token['token'];
		$this->token_expiration = (string)$obj->token['expiration'];
		$this->token_bool 		= (string)$obj->token['bool'];

		$this->result_bool 		= (string)$obj->result['bool'];
		$this->result_desc 		= (string)$obj->result['description'];
		$this->error_no 		= (string)$obj->error['number'];
		$this->error_desc 		= (string)$obj->error['description'];
		return true;
	}

	/**
	 * parse_json
	 * Summary: Parses JSON and places values in class variables.
	 * @param 	str 	$json 	The resulting JSON content.
	 * @return 	void
	 **/
	private function parse_json( $json ){

		if( !$json ){ return false; }

		$x = json_decode($json, true);
		$x = $x['zypcreds'];

		$this->raw_index 	= (string)$x['params']['index'];
		$this->raw_country 	= (string)$x['params']['country'];
		$this->raw_ip 		= (string)$x['params']['ip'];

		$this->prepped_index 	= (string)$x['index']['prepped'];
		$this->friendly_index 	= (string)$x['index']['friendly'];

		$this->token 			= (string)$x['token']['token'];
		$this->token_expiration = (string)$x['token']['expiration'];
		$this->token_bool 		= (string)$x['token']['bool'];

		$this->result_bool 	= (string)$x['result']['bool'];
		$this->result_desc 	= (string)$x['result']['description'];
		$this->error_no 	= (string)$x['error']['number'];
		$this->error_desc 	= (string)$x['error']['description'];

		return true;
	}
	
	/**
	 * create_hash()
	 * Summary: Creates a hash value from the params passed to it.
	 * @param 	arr 	$params Parameters being passed to API.
	 * @return 	str 	$hash 	The properly hashed value from using params and the api_key
	 **/
	private function create_hash( $params ){
		$str = '';
		foreach( $params as $k => $v ){
			if($k !='hash' && $k !='siteurl'){
				$str .= $k.$v;
			}
		}
		return hash_hmac('sha1', $str, $this->api_key);
	}



}# End Class
?>