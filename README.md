# ZypCredsPHP

**ZypCredsPHP**, a PHP library for the *ZypCreds REST API*.

## Methods
The following methods are organized in to categories.
For more information, visit <http://zypcreds.com/docs>.

### Verification
	request_verification( $index, $users_ip )
	attempt_verification( $index, $code, $users_ip )
	check_token( $index, $token, $users_ip )
	
### Whitelist/Blacklist
	get_whitelist()
	add_to_whitelist( $index )
	delete_from_whitelist( $index )
	get_blacklist()
	add_to_blacklist( $index )
	delete_from_blacklist( $index )


## Brief Examples
### Verification Process
	# Step 1 of 3 - Request Verification
	# Summary: A code will be sent to the index to specify. Either via SMS or email.
	require_once('ZypCredsREST.php');
	$x = new ZypCredsREST( $your_api_id, $your_api_key );
	$x->request_verification( $index );
	$x->result_bool; # Whether or not message was sent.
	
	# Step 2 of 3 - Attempt Verification
	# Summary: Checks to see if code provided is valid for this index.
	require_once('ZypCredsREST.php');
	$x = new ZypCredsREST( $your_api_id, $your_api_key );
	$x->verify_code( $index, $code_from_user, $users_ip );
	$x->result_bool; # Whether or not users code is correct.
	$x->token; # The token used to identify this correct verification.

	# Step 3 of 3 - Check token validity
	# Summary: Checks to see if token is valid for this index.
	require_once('ZypCredsREST.php');
	$x = new ZypCredsREST( $your_api_id, $your_api_key );
	$x->check_token( $index, $token, $users_ip );
	$x->result_bool; # Whether or not token is valid


