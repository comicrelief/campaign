<?php

/**
 * @file
 * Constants used by the CDN module.
 */

// Variables and values.
define('CDN_DRUPAL_ROOT_VARIABLE', 'cdn_drupal_root');

// Variables for SEO duplicate content prevention.
define('CDN_SEO_REDIRECT_VARIABLE', 'cdn_seo_redirect');
define('CDN_SEO_REDIRECT_DEFAULT', TRUE);
define('CDN_SEO_USER_AGENTS_VARIABLE', 'cdn_seo_user_agents');
define('CDN_SEO_USER_AGENTS_DEFAULT', "Amazon Cloudfront\nAkamai");
define('CDN_SEO_FORBIDDEN_EXTENSIONS_VARIABLE', "cdn_seo_forbidden_extensions");
define('CDN_SEO_FORBIDDEN_EXTENSIONS_DEFAULT', "html\nhtm\nphp");

// Variables for basic mode.
define('CDN_BASIC_FARFUTURE_VARIABLE', 'cdn_farfuture_status');
define('CDN_BASIC_FARFUTURE_DEFAULT', FALSE);
define('CDN_BASIC_FARFUTURE_EXTENSIONS_VARIABLE', "cdn_farfuture_extensions");
define('CDN_BASIC_FARFUTURE_EXTENSIONS_DEFAULT', "css\njs\nsvg\nico\ngif\njpg\njpeg\npng\notf\nttf\neot\nwoff\nflv\nswf");
define('CDN_BASIC_FARFUTURE_GZIP_EXTENSIONS', "css\njs\nico\nsvg\neot\notf\nttf");
define('CDN_BASIC_FARFUTURE_GZIP_DIRECTORY', "cdn/farfuture/gzip");
define('CDN_BASIC_FARFUTURE_REMOVE_HEADERS', "Set-Cookie\nETag");
define('CDN_BASIC_FARFUTURE_UNIQUE_IDENTIFIER_MAPPING_VARIABLE', 'cdn_farfuture_unique_identifier_mapping');
define('CDN_BASIC_FARFUTURE_UNIQUE_IDENTIFIER_MAPPING_DEFAULT', "misc/*:modules/*:themes/*|drupal_version\nsites/*|mtime\nsites/*|.avi .m4v .mov .mp4 .wmv .flv|perpetual");
define('CDN_BASIC_FARFUTURE_UNIQUE_IDENTIFIER_DEFAULT', 'mtime');
