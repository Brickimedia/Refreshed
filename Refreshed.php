<?php
/**
 * Refreshed skin -- a new clean, modern MediaWiki skin used on Brickimedia.
 *
 * @file
 * @ingroup Skins
 * @version 3.0.0
 * @link https://www.mediawiki.org/wiki/Skin:Refreshed Documentation
 */

// Skin credits that will show up on Special:Version
$wgExtensionCredits['skin'][] = array(
	'path' => __FILE__,
	'name' => 'Refreshed',
	'version' => '3.1.0',
	'author' => array(
		'Adam Carter', 'George Barnick', 'MtMNC', 'ShermanTheMythran',
		'Jack Phoenix', 'Drew1200', 'SirComputer', 'Seaside98', 'Codyn329',
		'Lewis Cawte'
	),
	'description' => 'A clean, modern MediaWiki skin with extensive CSS customisability',
	'url' => 'https://www.mediawiki.org/wiki/Skin:Refreshed',
);

// The first instance must be strtolower()ed so that useskin=refreshed works and
// so that it does *not* force an initial capital (i.e. we do NOT want
// useskin=Refreshed) and the second instance is used to determine the name of
// *this* file.
$wgValidSkinNames['refreshed'] = 'Refreshed';

// Autoload the skin classes, set up i18n, set up CSS & JS (via ResourceLoader)
$wgAutoloadClasses['SkinRefreshed'] = __DIR__ . '/Refreshed.skin.php';
$wgAutoloadClasses['RefreshedTemplate'] = __DIR__ . '/Refreshed.skin.php';
$wgMessagesDirs['SkinRefreshed'] = __DIR__ . '/i18n';

$wgResourceModules['skins.refreshed'] = array(
	'styles' => array(
		# Styles custom to the Refreshed skin
		'skins/Refreshed/refreshed/main.css' => array( 'media' => 'screen' ),
		'skins/Refreshed/refreshed/small.css' => array( 'media' => '(max-width: 600px)' ),
		'skins/Refreshed/refreshed/medium.css' => array( 'media' => '(min-width: 601px) and (max-width: 1000px)' ),
		'skins/Refreshed/refreshed/big.css' => array( 'media' => '(min-width: 1001px)' ),
	),
	'position' => 'top'
);

$wgResourceModules['skins.refreshed.js'] = array(
	'scripts' => 'skins/Refreshed/refreshed/refreshed.js',
);

$wgHooks['BeforePageDisplay'][] = function( &$out, &$skin ) {
	// Add the viewport meta tag for users who are using this skin
	// The skin class check has to be present because hooks are global!
	if ( get_class( $skin ) == 'SkinRefreshed' ) {
		$out->addMeta( 'viewport', 'width=device-width' );
	}

	return true;
};

$wgRefreshedHeader = array(
	'img' => '<img src="http://meta.brickimedia.org/skins/Refreshed/refreshed/images/brickimedia.svg" width="144" alt="" />',
	'url' => 'http://meta.brickimedia.org/wiki/Main_Page',
	'dropdown' => array() // format: array( 'http://exampleurl.com' => '<img src="http://exampleimage.png" width="100" />', );
);