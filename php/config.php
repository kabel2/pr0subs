<?php

// Datenbank Konfiguration
	define( 'DB_HOST', 'localhost' );
	define( 'DB_USER', 'root' );
	define( 'DB_PASSWORD', '' );
	define( 'DB_DATABASE', 'pr0verter' );

// Verzeichnisse der Webseite
	define( 'CLASSES', DIRECTORY . '/classes/' );
	define( 'PHP', DIRECTORY . '/php/' );
	define( 'CSS', DIRECTORY . '/css/' );
	define( 'JS', DIRECTORY . '/js/' );
	define( 'SMARTY', DIRECTORY . '/smarty/' );

// Projekt Konfiguration
	define( 'SESSION_NAME', 'pr0verter' );
	define( 'TITLE', 'Pr0verter' );
	define( 'BASE_URL', '' );
	define( 'TIME_TO_WAIT', 60 );
	define( 'DOWNLOAD_PATH', '/var/www/html/data/' );
	define( 'LOG_PATH', '/var/www/html/data/' );
        
        define('DEFAULT_MB', 6);
        define('MAX_DURATION_IN_SEC', 179); // ffmpeg cuts not exactly on 2 min
        define('BITS_IN_KILOBYTE', 1024 * 8);
        define('AUDIO_BITRATE', 130);


	require( PHP . 'class_loader.php' );
	require( PHP . 'map.php' );

