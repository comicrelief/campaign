
Description
-----------
This module provide easy Content Delivery Network integration for Drupal sites.
It alters file URLs, so that files/assets (CSS, JS, images, fonts, videos  …)
are downloaded from a CDN instead of your web server.

It does *not* put your entire website behind a CDN.

Only "Origin Pull" CDNs are supported. These are CDNs that only require you to
replace the domain name with another domain name. The CDN will then
automatically fetch (pull) the files from your server (the origin). Nowadays
pretty much every CDN is an Origin Pull CDN (2015 and later).

The CDN module aims to do only one thing and do it well: altering URLs to
point to files on CDNs. It supports:
    • Any sort of CDN mapping
    • DNS prefetching
    • CSS aggregation
    • auto-balance files over multiple CDNs (http://drupal.org/node/1452092)
    • … and many more details that are taken care of automatically

Not yet ported:
    • optimal Far Future expiration
    • SEO: prevent origin pull CDN from serving HTML content, only allow assets

Installation
------------
1) Place this module directory in your "modules" folder

2) Enable the module.

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


Cross-Origin Resource Sharing (CORS)
------------------------------------
By integrating a CDN, and depending on your actual configuration, resources
might be served from (a) domain(s) different than your site's domain. This
could cause browsers to refuse to use certain resources since they violate the
same-origin policy. This primarily affects font and JavaScript files.

To circumvent this, you can configure your server to serve those files with an
additional Access-Control-Allow-Origin header, containing a space-separated
list of domains that are allowed to make cross-domain use of a resource. Note
that this will only work if your CDN provider does not strip this header.

For server-specific instructions on adding this header, see
http://www.w3.org/wiki/CORS_Enabled#At_the_HTTP_Server_level...

If you are unable to add this header, or if your CDN provider ignores it, you
can add the files to the CDN module's blacklist to exclude them being served
by the CDN, or in the case of fonts, you can embed them in stylesheets via
data URIs (see https://developer.mozilla.org/en/data_URIs).

The Far Future expiration functionality takes care of this automatically!


FAQ
---
Q: Is the CDN module compatible with Drupal's page caching?
A: Yes.

Q: Is the CDN module compatible with Drupal's "private files" functionality?
A: Yes. The CDN module won't break private files, they will continue to work
   the same way. However, it cannot serve private files from a CDN. Not every
   CDN supports protected/secured/authenticated file access, and those that do
   each have their own way of doing this (there is no standard). So private
   files will continue to be served by Drupal, which may or may not be
   acceptable for your use case.

Q: Does this module only work with Apache or also with nginx, lighttpd, etc.?
A: This module only affects HTML, so it doesn't matter which web server you
   use!


No cookies should be sent to the CDN
------------------------------------
Please note though that you should ensure no cookies are sent to the CDN: this
would slow down HTTP requests to the CDN (since the requests become larger:
they piggyback the cookie data).
You can achieve this in two ways:
  1) When you are using cookies that are bound to your www subdomain only
     (i.e. not an example.com, but on www.example.com), you can safely use
     another subdomain for your CDN.
  2) When you are using cookies on your main domain (example.com), you'll have
     to use a completely different domain for the CDN if you don't want
     cookies to be sent.
     So then you should use the CDN's URL (e.g. myaccount.cdn.com). But now
     you should be careful to avoid JavaScript issues: you may run into "same
     origin policy" problems. See admin/config/development/cdn/other for
     details.

Drupal 7 no longer sets cookies for anonymous users.

If you just use the CDN's URL (e.g. myaccount.cdn.com), all cookie issues are
avoided automatically.


The "Far Future expiration" setting
-----------------------------------
For small sites, or sites with relatively few assets, the Far Future
expiration functionality should work just fine out of the box. The CDN module
serves all files through PHP with all headers configured perfectly. Since the
CDN only occasionally comes back to check on files, the far-from-great
performance of serving files through PHP is irrelevant.
However, if your site has a *lot* of images, for example, this can be
problematic, because even the occasional check by the CDN may amount to a near
constant load on your server, of files being served through PHP. In that case,
you may want to let your web server take care of that for you.

Apache users: add the following rules to <IfModule mod_rewrite.c> section of
your .htaccess file:

  ### CDN START ###
  # See http://drupal.org/node/1413156
  <IfModule mod_headers.c>
    # Transform /cdn/farfuture/[security token]/[ufi method]:[ufi]/sites/default/files
    # to /files and set environment variable for later Header rules.
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


    ###
    ### Default caching rules: no caching/immediate expiration.
    ###

    Header set Cache-Control "private, must-revalidate, proxy-revalidate" env=FARFUTURE_CDN
    Header set Expires "Wed, 20 Jan 1988 04:20:42 GMT" env=FARFUTURE_CDN


    ###
    ### Far future caching rules: only files with certain extensions.
    ###

    <FilesMatch "(\.css|\.css\.gz|\.js|\.js\.gz|\.svg|\.ico|\.gif|\.jpg|\.jpeg|\.png|\.otf|\.ttf|\.eot|\.woff|\.flv|\.swf)$">
      # Set a far future Cache-Control header (480 weeks), which prevents
      # intermediate caches from transforming the data and allows any
      # intermediate cache to cache it, since it's marked as a public resource.
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
    </FilesMatch>
  </IfModule>
  ### CDN END ###


Author
------
Wim Leers ~ http://wimleers.com/

Version 1 of this module (for Drupal 6) was written as part of the bachelor
thesis of Wim Leers at Hasselt University.

http://wimleers.com/tags/bachelor-thesis
http://uhasselt.be/
