<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	echo 'Not a valid entry point';
	exit( 1 );
}

if ( !defined( 'SMW_VERSION' ) ) {
	echo 'This extension requires Semantic MediaWiki to be installed.';
	exit( 1 );
}

#
# This is the path to your installation of SemanticTasks as
# seen from the web. Change it if required ($wgScriptPath is the
# path to the base directory of your wiki). No final slash.
# #
$spScriptPath = $wgScriptPath . '/extensions/SMWUserProtect';
#

# Extension credits
$wgExtensionCredits[defined( 'SEMANTIC_EXTENSION_TYPE' ) ? 'semantic' : 'other'][] = array(
	'path' => __FILE__,
	'name' => 'SMWUserProtect',
	'author' => array(
		'[https://www.mediawiki.org/wiki/User:Toniher Toni Hermoso]'
	),
	'version' => '0.1',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SMWUserProtect',
	'descriptionmsg' => 'SMWUserProtect-desc',
);


// i18n
$wgExtensionMessagesFiles['SMWUserProtect'] = dirname( __FILE__ ) . '/SMWUserProtect.i18n.php';

// Autoloading
$wgAutoloadClasses['SMWUserProtect'] = dirname( __FILE__ ) . '/SMWUserProtect.classes.php';

// Hooks
$wgHooks['userCan'][] = 'SMWUserProtect::checkIfUserCan';

// Allowed groups 
$wgSMWUserProtectGroups = array( 'sysop', 'team' );

// User Property
$wgSMWUserProtectProps = array( 'Has User' );

// Namespaces with protection
$wgSMWUserProtectNS = array( NS_REQUEST, NS_SAMPLE, NS_PROCESS );
$wgSMWUserProtectNSParent = array( NS_REQUEST );

# Edit prohibited depending on the value
$wgSMWUserProtectEditClose = array (
	NS_REQUEST => array(
		"Has Request Status" => array( "Accepted", "Closed", "Discarded" )
	)
);

# If visiting user is not in the groups above, avoid reading User pages
$wgSMWUserProtectUserPages = true; 

