<?php

error_reporting(0); // Set E_ALL for debuging

define('ACP', 1);

define('BASE_DIR', str_replace("\\", "/", dirname(dirname(dirname(dirname(dirname(__FILE__)))))));

include_once BASE_DIR . '/inc/init.php';

if (! check_permission('mediapool_finder'))
{
	exit;
}

// load composer autoload before load elFinder autoload If you need composer
//require './vendor/autoload.php';

// elFinder autoload
require './autoload.php';
// ===============================================

// Enable FTP connector netmount
elFinder::$netDrivers['ftp'] = 'FTP';
// ===============================================

// // Required for Dropbox network mount
// // Installation by composer
// // `composer require kunalvarma05/dropbox-php-sdk`
// // Enable network mount
// elFinder::$netDrivers['dropbox2'] = 'Dropbox2';
// // Dropbox2 Netmount driver need next two settings. You can get at https://www.dropbox.com/developers/apps
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=dropbox2&host=1"
// define('ELFINDER_DROPBOX_APPKEY',    '');
// define('ELFINDER_DROPBOX_APPSECRET', '');
// ===============================================

// // Required for Google Drive network mount
// // Installation by composer
// // `composer require google/apiclient:^2.0`
// // Enable network mount
// elFinder::$netDrivers['googledrive'] = 'GoogleDrive';
// // GoogleDrive Netmount driver need next two settings. You can get at https://console.developers.google.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=googledrive&host=1"
// define('ELFINDER_GOOGLEDRIVE_CLIENTID',     '');
// define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', '');
// // Required case of without composer
// define('ELFINDER_GOOGLEDRIVE_GOOGLEAPICLIENT', '/path/to/google-api-php-client/vendor/autoload.php');
// ===============================================

// // Required for Google Drive network mount with Flysystem
// // Installation by composer
// // `composer require nao-pon/flysystem-google-drive:~1.1 nao-pon/elfinder-flysystem-driver-ext`
// // Enable network mount
// elFinder::$netDrivers['googledrive'] = 'FlysystemGoogleDriveNetmount';
// // GoogleDrive Netmount driver need next two settings. You can get at https://console.developers.google.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=googledrive&host=1"
// define('ELFINDER_GOOGLEDRIVE_CLIENTID',     '');
// define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', '');
// ===============================================

// // Required for One Drive network mount
// //  * cURL PHP extension required
// //  * HTTP server PATH_INFO supports required
// // Enable network mount
// elFinder::$netDrivers['onedrive'] = 'OneDrive';
// // GoogleDrive Netmount driver need next two settings. You can get at https://dev.onedrive.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL/netmount/onedrive/1"
// define('ELFINDER_ONEDRIVE_CLIENTID',     '');
// define('ELFINDER_ONEDRIVE_CLIENTSECRET', '');
// ===============================================

// // Required for Box network mount
// //  * cURL PHP extension required
// // Enable network mount
// elFinder::$netDrivers['box'] = 'Box';
// // Box Netmount driver need next two settings. You can get at https://developer.box.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL"
// define('ELFINDER_BOX_CLIENTID',     '');
// define('ELFINDER_BOX_CLIENTSECRET', '');
// ===============================================

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string    $attr    attribute name (read|write|locked|hidden)
 * @param  string    $path    absolute file path
 * @param  string    $data    value of volume option `accessControlData`
 * @param  object    $volume  elFinder volume driver object
 * @param  bool|null $isDir   path is directory (true: directory, false: file, null: unknown)
 * @param  string    $relpath file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume, $isDir, $relpath) {
	$basename = basename($path);
	return $basename[0] === '.'                  // if file/folder begins with '.' (dot)
			 && strlen($relpath) !== 1           // but with out volume root
		? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
		:  null;                                 // else elFinder decide it itself
}


// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$opts = array(
	// 'debug' => true,
	'roots' => array(
		array(
			'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
			'path'          => '../../../../templates/' . DEFAULT_THEME_FOLDER,                 // path to files (REQUIRED)
			'URL'           => '/templates/' . DEFAULT_THEME_FOLDER . '/', // URL to files (REQUIRED)
			'accessControl' => 'access',                    // disable and hide dot starting files (OPTIONAL)
			'disabled' => array(),
			'acceptedName' => 'validName',
			'uploadAllow' => array('all'),
			'uploadDeny'  => array('all'),
			'uploadOrder' => 'deny,allow',
			'uploadOverwrite' => false,
			'uploadMaxSize' => '256m',
			'copyOverwrite' => false,
			'copyJoin' => true,
			'mimeDetect' => 'internal',
			'tmbCrop' => false,
			'imgLib' => 'gd',
			'utf8fix' => true,
			'attributes' => array(
				array(
					'pattern' => '/^\/\./',
					'read' => false,
					'write' => false,
					'hidden' => true,
					'locked' => true
				),
				 array(
					 'pattern' => '/.tmb/',
					 'read' => false,
					 'write' => false,
					 'hidden' => true,
					 'locked' => false
				 ),
				 array(
					 'pattern' => '/\.php$/',
					 'read' => false,
					 'write' => false,
					 'hidden' => true,
					 'locked' => false
				 ),
				array(
					 'pattern' => '/.quarantine/',
					 'read' => false,
					 'write' => false,
					 'hidden' => true,
					 'locked' => false
				 ),
				 array(
					 'pattern' => '/\.htaccess$/',
					 'write' => false,
					 'locked' => false,
					 'hidden' => true
				 ),
				 array(
					 'pattern' => '/.uploader/',
					 'read' => false,
					 'write' => false,
					 'hidden' => true,
					 'locked' => false
				 ),
				 array(
					 'pattern' => '/.temp/',
					 'read' => false,
					 'write' => false,
					 'hidden' => true,
					 'locked' => false
				 )
			)
		)
	)
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();