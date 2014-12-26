<?php

/*
 *		URL AND DIRECTORIES
 */

// Absolute URL of the website
//define( '_ROOT_URL', 'http://localhost/damien/github/Flea/' );
//define( '_ROOT_URL', 'http://flea.namide.com/' );
define( '_ROOT_URL', 'http://localhost/Flea/' );


// Directory name of the system's files
// It is better to be outside of the www directory
define( '_SYSTEM_DIRECTORY', 'system/' );

// Directory name of the content's files
define( '_CONTENT_DIRECTORY', 'content/' );

// Directory name of the template's files
define( '_TEMPLATE_DIRECTORY', 'template/' );

// Directory name of the caches files generated
define( '_CACHE_DIRECTORY', 'cache/' );


/*
 *		PARAMETERS
 */

// URL rewriting activated => true, deactivated => false
define( '_URL_REWRITING', false );

// Debug mode activated => true, deactivated => false
define( '_DEBUG', true );

// Cache activated => true, deactivated => false
define( '_CACHE', false );

// Maximum number of files in the cache directory
// It is better to have 5 beyond the maximum number of cachable pages
define( '_MAX_PAGE_CACHE', 50 );


/*
 *		DATA BASE
 */

// All the pages datas (without content)
define( '_DB_DSN_PAGE', 'sqlite:'._CACHE_DIRECTORY.'pages.sqlite' );

// Cache of pages
define( '_DB_DSN_CACHE', 'sqlite:'._CACHE_DIRECTORY.'cache.sqlite' );

// Tool to manage your content (optional)
define( '_DB_DSN_CONTENT', 'sqlite:'._CONTENT_DIRECTORY.'content.sqlite' );

// Database identifiants if it isn't SQLite
define( '_DB_USER', null );
define( '_DB_PASS', null );
define( '_DB_OPTIONS', null );
