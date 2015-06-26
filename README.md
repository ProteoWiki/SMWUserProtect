# SMWUserProtect

Extension for protecting specifically pages from users depending on their semantic properties.

## Parameters

// Groups that are allowed always

	$GLOBALS['wgSMWUserProtectGroups'] = array( 'sysop', 'team' );

// User Property

	$GLOBALS['wgSMWUserProtectProps'] = array( 'Has User' );

// Namespaces with protection

	$GLOBALS['wgSMWUserProtectNS'] = array( NS_REQUEST, NS_SAMPLE, NS_PROCESS );
	$GLOBALS['wgSMWUserProtectNSParent'] = array( NS_REQUEST );

// Edit prohibited depending on the value

	$GLOBALS['wgSMWUserProtectEditClose'] = array (
		NS_REQUEST => array(
			"Has Request Status" => array( "Accepted", "Closed", "Discarded" )
		)
	);

// If visiting user is not in the groups above, avoid reading User pages

	$GLOBALS['wgSMWUserProtectUserPages'] = true;

// Block edition of user pages by non-owning users. It actually makes sense if previous wgSMWUserProtectUserPages is false

	$GLOBALS['wgSMWUserProtectEditUserPages'] = true;