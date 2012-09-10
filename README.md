# ZypCredsPHP

**ZypCredsPHP**, a PHP library for the *ZypCreds REST API*.

## Methods
The following methods are organized in to categories.
For more information, visit <http://zypcreds.com/docs>.
### Verification
	request_verification( $index_to_verify )
	attempt_verification( $index, $code )
### Whitelist/Blacklist
	get_whitelist()
	add_to_whitelist( $index )
	delete_from_whitelist( $index )
	get_blacklist()
	add_to_blacklist( $index )
	delete_from_blacklist( $index )


## How to use
	# Request Verification - A code will be sent to the index to specify.
	require_once('ZypCredsREST.php');
	$x = new ZypCredsREST( $your_api_id, $your_api_key );
	$x->request_verification( $index_to_verify );
	return $x->result_bool; # Whether or not message was sent.
	
	
	# Attempt Verification - See if user provided code matches code in database.
	require_once('ZypCredsREST.php');
	$x = new ZypCredsREST( $your_api_id, $your_api_key );
	$x->verify_code( $index_to_verify, $code_from_user );
	return $x->result_bool; # Whether or not users code is correct.

