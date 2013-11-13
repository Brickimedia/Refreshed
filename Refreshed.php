<?php
/**
 * Refreshed skin -- a new clean, modern MediaWiki skin used on Brickimedia.
 *
 * @file
 * @ingroup Skins
 * @version 1.0
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

//Hide ToC, as refreshed create's it's own
$wgExtensionCredits['parserhook'][] = array(
		'name' => 'NoTOC',
		'author' =>'[http://swiftlytilting.com Andrew Fitzgerald]',
		'url' => 'http://www.mediawiki.org/wiki/Extension:NoTOC',
		'description' => 'Turns off TOC by default on all pages',
		'descriptionmsg' => "notoc-desc",
		'version' => '0.1.0',
		'path' => __FILE__,
);

$wgHooks['ParserClearState'][] = 'efMWNoTOC';

function efMWNoTOC($parser) {
	$parser->mShowToc = false;
	return true;
}

// Skin credits that will show up on Special:Version
$wgExtensionCredits['skin'][] = array(
	'path' => __FILE__,
	'name' => 'Refreshed',
	'version' => '2.0',
	'author' => 'Brickimedia',
	'description' => 'A new clean, modern MediaWiki skin used on Brickimedia',
	'url' => 'https://www.mediawiki.org/wiki/Skin:Refreshed',
);

// Autoload the skin class, make it a valid skin, set up i18n, set up CSS & JS
// (via ResourceLoader)
$skinID = basename( dirname( __FILE__ ) );
$dir = dirname( __FILE__ ) . '/';

// The first instance must be strtolower()ed so that useskin=refreshed works and
// so that it does *not* force an initial capital (i.e. we do NOT want
// useskin=Refreshed) and the second instance is used to determine the name of
// *this* file.
$wgValidSkinNames[strtolower( $skinID )] = 'Refreshed';

$wgAutoloadClasses['SkinRefreshed'] = $dir . 'Refreshed.skin.php';
$wgAutoloadClasses['RefreshedTemplate'] = $dir . 'Refreshed.skin.php'; // needed for the hooked func below
$wgExtensionMessagesFiles['SkinRefreshed'] = $dir . 'Refreshed.i18n.php';
$wgResourceModules['skins.refreshed'] = array(
	'styles' => array(
		// MonoBook also loads these
		'skins/common/commonElements.css' => array( 'media' => 'screen' ),
		'skins/common/commonContent.css' => array( 'media' => 'screen' ),
		'skins/common/commonInterface.css' => array( 'media' => 'screen' ),
		# Styles custom to the Refreshed skin
		'skins/Refreshed/refreshed/main.css' => array( 'media' => 'screen' ),
		'skins/Refreshed/refreshed/small.css' => array( 'media' => '(max-width: 600px)' ),
		'skins/Refreshed/refreshed/medium.css' => array( 'media' => '(min-width: 601px) and (max-width: 1000px)' ),
		'skins/Refreshed/refreshed/big.css' => array( 'media' => '(min-width: 1001px)' ),
	),
	'scripts' => 'skins/Refreshed/refreshed/refreshed.js',
	'position' => 'top'
);

$wgHooks['OutputPageParserOutput'][] = 'RefreshedTemplate::onOutputPageParserOutput';

$wgHooks['BeforePageDisplay'][] = function( &$out, &$skin ) {
	global $wgUser;

	// Add the viewport meta tag for users who are using this skin
	// The skin class check has to be present because hooks are global!
	if ( get_class( $wgUser->getSkin() ) == 'SkinRefreshed' ) {
		$out->addMeta( 'viewport', 'width=device-width' );
	}

	return true;
};

// Don't leak variables to global scope.
unset( $skinID, $dir );