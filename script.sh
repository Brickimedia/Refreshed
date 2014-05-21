#! /bin/bash

# TODO: Clean this up and make sure it works. This is really dirty right now.

set -x

originalDirectory=$(pwd)

function installMediaWiki {
	cd ..

	wget https://github.com/wikimedia/mediawiki-core/archive/$MW.tar.gz
	tar -zxf $MW.tar.gz
	mv mediawiki-core-$MW mediawiki

	cd mediawiki
	
	mysql -e 'create database its_a_mw;'
	php maintenance/install.php --dbtype $DBTYPE --dbuser root --dbname its_a_mw --dbpath $(pwd) --pass $DBPASS Refreshed admin
}

function installRefreshed {

	composer require 'phpunit/phpunit=3.7.*' --prefer-source
	composer require 'mediawiki/refreshed-skin=@dev' --prefer-source

	echo 'require_once( "$IP/skins/Refreshed/Refreshed.php" );' >> LocalSettings.php
	echo '$wgDefaultSkin = "refreshed";' >> LocalSettings.php

	php maintenance/update.php --quick
}

installMediaWiki
installRefreshed
