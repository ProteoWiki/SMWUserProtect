<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	echo 'Not a valid entry point';
	exit( 1 );
	}

if ( !defined( 'SMW_VERSION' ) ) {
	echo 'This extension requires Semantic MediaWiki to be installed.';
	exit( 1 );
}


/**
 * This class handles the permissions of access to User depending on Props.
 */
class SMWUserProtect {

	/**
	 * UserCan function
	 * @param $title Title
	 * @param $user User
	 * @param $action 
	 * @param $result
	 * @return boolean
	 */
	
	public static function checkIfUserCan( &$title, &$user, $action, &$result ) {

		global $wgSMWUserProtectGroups;
		global $wgSMWUserProtectProps;
		global $wgSMWUserProtectNS;
		global $wgSMWUserProtectNSParent;
		global $wgSMWUserProtectEditClose;
		global $wgSMWUserProtectUserPages;
		global $wgContLang;
	
		// Process user	
		$usergrps = $user->getEffectiveGroups();
		$username = $user->getName();
		
		foreach ( $usergrps as $usergrp ) {
		
			if ( in_array( $usergrp, $wgSMWUserProtectGroups ) ) {
				// If user is in allowed groups, we go ahead
				return true;
			}
		}
		
		// Let's get Namespace
		$ns = $title->getNamespace();
		$titleText = $title->getText();
		$fulltitleText = $title->getPrefixedText();
	
		//Check User
		if ( $ns == NS_USER ) {
			
			if ( $username == $titleText ) {
				
				return true;
			} else {
			
				if ( $wgSMWUserProtectUserPages ) { 
					return false;
				}
			}
			
		}

		// We check FormEdit -> This we might improve detection -> Do for formedit!!!!
		if ( ( $ns == -1  &&  strpos( $fulltitleText, "FormEdit" ) ) || $action == 'edit' ) {
			$titleparts = explode( "/", $fulltitleText );
			$newTitle = end( $titleparts );
			if ( $newTitle != '' ) {
				$titleCheck = Title::newFromText( $newTitle );
				$nsCheck = $titleCheck->getNamespace();

				if ( isset( $wgSMWUserProtectEditClose[$nsCheck] ) ) {

					foreach ( $wgSMWUserProtectEditClose[$nsCheck] as $propKey => $propValues ) {

						$values = self::getPropertyValues( $newTitle, $propKey );
						foreach ( $values as $value ) {

							if ( in_array( $value, $propValues ) ){
								return false; // If closing values found, close
							}

						}
					}
				}
			}
		}


		$check = true;

		// Check NS
		foreach ( $wgSMWUserProtectNS as $NStarget ) {
		
			if ( $ns == $NStarget ) {
		
				// Case of page containing the property
				foreach ( $wgSMWUserProtectProps as $prop ) {
					if ( ! self::getProperty( $fulltitleText, $prop, $username ) ) {
						$check = false;
					}
				}
				
				
				// Case of page not containing the property -> but parents
				if ( !in_array( $NStarget, $wgSMWUserProtectNSParent ) ) {
				
					// If not a parent page
					// We get the parent
					foreach ( $wgSMWUserProtectNSParent as $parent ) {
					
						$parentName = $wgContLang->getNsText( $parent );
					
						foreach ( $wgSMWUserProtectProps as $prop ) {
		
							$actualParent = SMWParent::getParent( $fulltitleText, $parentName );
							if ( ! self::getProperty( $actualParent, $prop, $username ) ) {
								$check = false;
							}
						}
					
					}
				}
				
			}
		}

		return $check; // We go ahead
	}
	
	/**
	 * getProperty function
	 * @param $titleText string
	 * @param $prop string
	 * @return boolean
	 */
	
	private static function getProperty( $titleText, $prop, $username ) {
	
		// Deal props with _
		$prop = str_replace(" ", "_", $prop); 
		$query_string = "[[".$titleText."]][[".$prop.":+]]";
		
		// Props to see
		$props = array();
		array_push( $props, $prop );
		
		// Get a list of usernames
		$usernames = self::processResults( $query_string, $props, false );

		if ( count( $usernames ) > 0 ) {
			
			if ( in_array( $username, $usernames ) ) {
				// If there -> we allow
				return true;
			} else {
				return false;
			}
		}
		return true; // If 0 matches, return true
	}
	

	/**
	 * getProperty function
	 * @param $titleText string
	 * @param $prop string
	 * @return Array
	 */
	
	private static function getPropertyValues( $titleText, $prop ) {
	
		// Deal props with _
		$prop = str_replace(" ", "_", $prop); 
		$query_string = "[[".$titleText."]][[".$prop.":+]]";
		
		// Props to see
		$props = array();
		array_push( $props, $prop );
		
		// Get a list of usernames
		$values = self::processResultsGeneric( $query_string, $props, false );

		return $values; // Return values
	}
	
	
	/**
	 * Function for processing query results
	 * @param $query_string String : the query
	 * @param $properties_to_display array(String): array of property names to display
	 * @param $display_title Boolean : add the page title in the result
	 * @return usernames array
	 */	
	
	private static function processResults ( $query_string, $properties_to_display, $display_title ) {
		
		// By default -> empty
		$usernames = array();
		
		$results = self::getQueryResults( $query_string, $properties_to_display, $display_title );
		
		// In theory, there is only one row
		while ( $row = $results->getNext() ) {
		
			$userContainer = $row[1];
		
			if ( !empty( $userContainer ) ) {
		                    
		                while ( $obj = $userContainer->getNextObject() ) {
		                            
					$pagevalue = $obj->getWikiValue();
					
					$userobj = User::newFromName( $pagevalue );
					
					
					if ( $userobj && $userobj->getId() > 0 ) {
						
						array_push( $usernames, $userobj->getName() );
						
					}
					
		                }   
		        } 
		
		}
		
		// Return list of usernames
		return $usernames;
		
	}

	/**
	 * Function for processing query results
	 * @param $query_string String : the query
	 * @param $properties_to_display array(String): array of property names to display
	 * @param $display_title Boolean : add the page title in the result
	 * @return usernames array
	 */	
	
	private static function processResultsGeneric( $query_string, $properties_to_display, $display_title ) {
		
		// By default -> empty
		$values = array();
		
		$results = self::getQueryResults( $query_string, $properties_to_display, $display_title );
		
		// In theory, there is only one row
		while ( $row = $results->getNext() ) {
		
			$Container = $row[1];
		
			if ( !empty( $Container ) ) {
						
				while ( $obj = $Container->getNextObject() ) {
					
					$value = $obj->getWikiValue();
					
					array_push( $values, $value );
						
					
				}
			} 
		
		}
		
		// Return list of usernames
		return $values;
		
	}

	
	/**
	 * This function returns to results of a certain query
	 * Thank you Yaron Koren for advices concerning this code
	 * @param $query_string String : the query
	 * @param $properties_to_display array(String): array of property names to display
	 * @param $display_title Boolean : add the page title in the result
	 * @return TODO
	 */
	static function getQueryResults( $query_string, $properties_to_display, $display_title ) {
		
		// We use the Semantic MediaWiki Processor
		// $smwgIP is defined by Semantic MediaWiki, and we don't allow
		// this file to be sourced unless Semantic MediaWiki is included.
		global $smwgIP;
		include_once( $smwgIP . "/includes/SMW_QueryProcessor.php" );
		
		$params = array();
		$inline = true;
		$printlabel = "";
		$printouts = array();
		
		// add the page name to the printouts
		if ( $display_title ) {
			$to_push = new SMWPrintRequest( SMWPrintRequest::PRINT_THIS, $printlabel );
			array_push( $printouts, $to_push );
		}
		
		// Push the properties to display in the printout array.
		foreach ( $properties_to_display as $property ) {
			if ( class_exists( 'SMWPropertyValue' ) ) { // SMW 1.4
				$to_push = new SMWPrintRequest( SMWPrintRequest::PRINT_PROP, $printlabel, SMWPropertyValue::makeProperty( $property ) );
			} else {
				$to_push = new SMWPrintRequest( SMWPrintRequest::PRINT_PROP, $printlabel, Title::newFromText( $property, SMW_NS_PROPERTY ) );
			}
			array_push( $printouts, $to_push );
		}
		
		if ( version_compare( SMW_VERSION, '1.6.1', '>' ) ) {
			SMWQueryProcessor::addThisPrintout( $printouts, $params );
			$params = SMWQueryProcessor::getProcessedParams( $params, $printouts );
			$format = null;
		}
		else {
			$format = 'auto';
		}
		
		$query = SMWQueryProcessor::createQuery( $query_string, $params, $inline, $format, $printouts );
		$results = smwfGetStore()->getQueryResult( $query );
		
		return $results;
	}
	
}
