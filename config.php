<?php

/*
 *		URL AND REPERTORIES
 */

// Absolute URL of the website
//define( '_ROOT_URL', 'http://localhost/Flea/' );
//define( '_ROOT_URL', 'http://flea.namide.com/' );
define( '_ROOT_URL', 'http://localhost/damien/github/Flea/' );


// Directory name of the system's files
// It is better to be outside of the www directory
define( '_SYSTEM_DIRECTORY', 'system/' );

// Directory name of the content's files
define( '_CONTENT_DIRECTORY', 'content/' );

// Directory name of the template's files
define( '_TEMPLATE_DIRECTORY', 'template/' );

// Directory name of the cache's files
// It is better to be outside of the www directory
define( '_CACHE_DIRECTORY', 'cache/' );

// Directory name of the temporaries files generated
define( '_TEMP_DIRECTORY', 'temp-files/' );


/*
 *		PARAMETERS
 */

// URL rewriting activated => TRUE, deactivated => FALSE
define( '_URL_REWRITING', false );

// Debug mode activated => TRUE, deactivated => FALSE
define( '_DEBUG', true );

// Cache activated => TRUE, deactivated => FALSE
define( '_CACHE', true );

// Maximum number of files in the cache directory
// It is better to have 5 beyond the maximum number of cachable pages
define( '_MAX_PAGE_CACHE', 50 );


/*
 *		DATA BASE
 */

define( '_DB_DSN_CACHE', 'sqlite:'._CONTENT_DIRECTORY.'cache.sqlite' );
define( '_DB_DSN_PAGES', 'sqlite:'._CONTENT_DIRECTORY.'pages.sqlite' );
define( '_DB_USER', null );
define( '_DB_PASS', null );
define( '_DB_OPTIONS', null );
