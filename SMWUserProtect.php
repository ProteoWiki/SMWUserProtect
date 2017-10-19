<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	echo 'Not a valid entry point';
	exit( 1 );
}

if ( !defined( 'SMW_VERSION' ) ) {
	echo 'This extension requires Semantic MediaWiki to be installed.';
	exit( 1 );
}


call_user_func( function () {

	# Extension credits
	$GLOBALS['wgExtensionCredits'][defined( 'SEMANTIC_EXTENSION_TYPE' ) ? 'semantic' : 'other'][] = array(
		'path' => __FILE__,
		'name' => 'SMWUserProtect',
		'author' => array(
			'[https://www.mediawiki.org/wiki/User:Toniher Toni Hermoso]'
		),
		'version' => '0.1.1',
		'url' => 'https://www.mediawiki.org/wiki/Extension:SMWUserProtect',
		'descriptionmsg' => 'SMWUserProtect-desc',
	);
	
	
	// i18n
	$GLOBALS['$wgExtensionMessagesFiles']['SMWUserProtect'] = dirname( __FILE__ ) . '/SMWUserProtect.i18n.php';
	
	// Autoloading
	$GLOBALS['wgAutoloadClasses']['SMWUserProtect'] = dirname( __FILE__ ) . '/SMWUserProtect.classes.php';
	
	// Allowed groups 
	$GLOBALS['wgSMWUserProtectGroups'] = array( 'sysop', 'team' );
	
	// User Property
	$GLOBALS['wgSMWUserProtectProps'] = array( 'Has User' );
	
	// Namespaces with protection
	$GLOBALS['wgSMWUserProtectNS'] = array( NS_REQUEST, NS_SAMPLE, NS_PROCESS );
	$GLOBALS['wgSMWUserProtectNSParent'] = array( NS_REQUEST );
	
	# Edit prohibited depending on the value
	$GLOBALS['wgSMWUserProtectEditClose'] = array (
		NS_REQUEST => array(
			"Has Request Status" => array( "Accepted", "Closed", "Discarded" )
		)
	);
	
	# If visiting user is not in the groups above, avoid reading User pages
	$GLOBALS['wgSMWUserProtectUserPages'] = true;

	# Block edition of user pages by non-owning users. It actually makes sense if previous wgSMWUserProtectUserPages is false
	$GLOBALS['wgSMWUserProtectEditUserPages'] = true;

	// Hooks
	$GLOBALS['wgHooks']['userCan'][] = 'SMWUserProtectfunc';

});

function SMWUserProtectfunc( $title, $user, $action, &$result ) {

	$object = new SMWUserProtect;
	$result = $object->checkIfUserCan( $title, $user );
	return($result);

}

