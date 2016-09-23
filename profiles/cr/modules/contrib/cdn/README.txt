
Description
-----------
This module provide easy Content Delivery Network integration for Drupal sites.
It alters file URLs, so that files (CSS, JS, images, fonts, videos …) are
downloaded from a CDN instead of your web server.

It does *not* put your entire website behind a CDN.

Only "Origin Pull" CDNs are supported. These are CDNs that only require you to
replace the domain name with another domain name. The CDN will then
automatically fetch (pull) the files from your server (the origin). Nowadays
pretty much every CDN is an Origin Pull CDN.

The CDN module aims to do only one thing and do it well: altering URLs to
point to files on CDNs. It supports:
    • Any sort of CDN mapping
    • DNS prefetching: lets browsers connect to the CDN faster
    • SEO: prevents CDN from serving HTML and REST responses, only allow files
    • Forever cacheable files (optimal far future expiration)
    • Auto-balancing files over multiple CDNs
    • … and many more details that are taken care of automatically

The "CDN UI" module is included, and can be used for configuring the CDN module.
Once set up, it can be uninstalled.


Installation
------------
1) Place this module directory in your "modules" folder

2) Install the module.

3) Go to your CDN provider's control panel and set up a "CDN instance" (Amazon
   CloudFront calls this a "distribution"). There, you will have to specify
   the origin server (Amazon CloudFront calls this a "custom origin"), which
   is simply the domain name of your Drupal site.
   The CDN will provide you with a "delivery address", this is the address
   that we'll use to download files from the CDN instead of the Drupal server.
   Suppose this is `http://d85nwn7m5gl3y.cloudfront.net`.
   (It acts like a globally distributed, super fast proxy server.)

   Relevant links:
   - Amazon CloudFront: http://docs.amazonwebservices.com/AmazonCloudFront/latest/DeveloperGuide/CreatingDistributions.html?r=4212

4) Optionally, you can create a CNAME alias to the delivery address on your
   DNS server. This way, it's not immediately obvious from the links in the
   HTMl that you're using an external service (that's why it's also called a
   vanity domain name).
   However, if you're going to use your CDN in HTTPS mode, then using vanity
   domains will break things (because SSL certificates are bound to domain
   names).

5) Configure the CDN module. Either modify and import the `cdn.settings.yml`
   configuration file manually or install the included CDN UI module to
   configure it through a UI. Provide the (vanity) domain name that your CDN
   has given you (`d85nwn7m5gl3y.cloudfront.net` in our example).

6) If your site is behind a reverse proxy such as Varnish, so that your stack
   looks like: CDN <-> reverse proxy <-> web server, then you need to take extra
   measures if you want to prevent duplicate content showing up on the CDN. See
   \Drupal\cdn\StackMiddleware\DuplicateContentPreventionMiddleware for details.


FAQ
---
Q: Is the CDN module compatible with Drupal's Page Cache?
A: Yes.

Q: Is the CDN module compatible with Drupal's "private files" functionality?
A: Yes. The CDN module won't break private files, they will continue to work
   the same way. However, it cannot serve private files from a CDN. Not every
   CDN supports protected/secured/authenticated file access, and those that do
   each have their own way of doing this (there is no standard). So private
   files will continue to be served by Drupal, which may or may not be
   acceptable for your use case.

Q: Does this module only work with Apache or also with nginx, lighttpd, etc.?
A: This module only affects HTML, so it doesn't matter which web server you use!


The "Forever cacheable files" (farfuture) setting
-------------------------------------------------
For small sites the 'Forever cacheable files' (farfuture) functionality works
fine out of the box. The CDN module serves all files through PHP with optimal
headers. Since the CDN only occasionally re-requests files, the far-from-great
performance of serving files through PHP is irrelevant.
For big sites, this can be problematic: if your site has so many files that the
CDN cannot cache them all, the CDN may continuously request files, amounting to
a constant load on your server of files being served through PHP. In that case,
you may want to let your web server take care of that for you.

Apache users can add the following rules to <IfModule mod_rewrite.c> section of
your .htaccess file:

  ### CDN START ###
  # See http://drupal.org/node/1413156
  <IfModule mod_headers.c>
    # Transform /cdn/farfuture/[security token]/[mtime]/X/Y/Z to /X/Y/Z and set
    # environment variable for later Header rules.
    RewriteCond %{REQUEST_URI} ^/cdn/farfuture/[^/]+/[^/]+/(.+)$
    RewriteRule .* %1 [L,E=FARFUTURE_CDN:1]

    # Apache will change FARFUTURE_CDN to REDIRECT_FARFUTURE_CDN on internal
    # redirects, restore original environment variable.
    # See http://stackoverflow.com/q/3050444
    RewriteCond %{ENV:REDIRECT_FARFUTURE_CDN} =1
    RewriteRule .* - [E=FARFUTURE_CDN:1]


    ###
    ### Always reply "304 Not Modified" to "If-Modified-Since" header.
    ###

    # The redirect works only if URL was actually modified by rewrite rule
    # (probably, to prevent infinite loops). So, we rewrite the URL with
    # website root and this causes the webserver to return 304 status.
    RewriteCond %{ENV:FARFUTURE_CDN} =1
    RewriteCond %{HTTP:If-Modified-Since} !=""
    RewriteRule .* / [R=304,L]


    ###
    ### Generic headers that apply to all /cdn/farfuture/* requests.
    ###

    # Instead of being powered by Apache, tell the world this resource was
    # powered by the CDN module's .htaccess!
    Header set X-Powered-By "Drupal CDN module (.htaccess)" env=FARFUTURE_CDN

    # Instruct intermediate HTTP caches to store both a compressed (gzipped) and
    # uncompressed version of the resource.
    Header set Vary "Accept-Encoding" env=FARFUTURE_CDN

    # Support partial content requests.
    Header set Accept-Ranges "bytes" env=FARFUTURE_CDN

    # Do not use ETags for cache validation.
    Header unset ETag env=FARFUTURE_CDN

    # Browsers that implement the W3C Access Control specification might refuse
    # to use certain resources such as fonts if those resources violate the
    # same-origin policy. Send a header to explicitly allow cross-domain use of
    # those resources. (This is called Cross-Origin Resource Sharing, or CORS.)
    Header set Access-Control-Allow-Origin "*" env=FARFUTURE_CDN
    Header set Access-Control-Allow-Methods "GET, HEAD" env=FARFUTURE_CDN

    # Set a far future Cache-Control header (480 weeks), which prevents
    # intermediate caches from transforming the data and allows any intermediate
    # cache to cache it, since it's marked as a public resource.
    Header set Cache-Control "max-age=290304000, no-transform, public" env=FARFUTURE_CDN

    # Set a far future Expires header. The maximum UNIX timestamp is somewhere
    # in 2038. Set it to a date in 2037, just to be safe.
    Header set Expires "Tue, 20 Jan 2037 04:20:42 GMT" env=FARFUTURE_CDN

    # Pretend the file was last modified a long time ago in the past, this will
    # prevent browsers that don't support Cache-Control nor Expires headers to
    # still request a new version too soon (these browsers calculate a
    # heuristic to determine when to request a new version, based on the last
    # time the resource has been modified).
    # Also see http://code.google.com/speed/page-speed/docs/caching.html.
    Header set Last-Modified "Wed, 20 Jan 1988 04:20:42 GMT" env=FARFUTURE_CDN
  </IfModule>
  ### CDN END ###


Author
------
Wim Leers ~ http://wimleers.com/

Version 1 of this module (for Drupal 6) was written as part of the bachelor
thesis of Wim Leers at Hasselt University.

http://wimleers.com/tags/bachelor-thesis
http://uhasselt.be/
