# SMWUserProtect

Extension erProtectGroups = array( 'sysop', 'team' );

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
or protecting user pages and pages associated to users depending on the roles and associated properties.


