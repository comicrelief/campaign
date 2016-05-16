##
## This file is only needed for Compass/Sass integration. If you are not using
## Compass, you may safely ignore or delete this file.
##
## If you'd like to learn more about Sass and Compass, see the sass/README.txt
## file for more information.
##

# Default to development if environment is not set.
# Because we're committing CSS, we always want to be commiting clean CSS code without debug info
# environment = :production
saved = environment
if (environment.nil?)
  environment = :production
else
  environment = saved
end

# Location of the theme's resources.
css_dir = "css"
sass_dir = "sass"
images_dir = "images"
generated_images_dir = images_dir + "/generated"
javascripts_dir = "js"

# Require any additional compass plugins installed on your system.
require 'compass-normalize'
require 'rgbapng'
require 'toolkit'
require 'sass-globbing'
require 'breakpoint'
require 'susy'

##
## You probably don't need to edit anything below this.
##

# You can select your preferred output style here (:expanded, :nested, :compact
# or :compressed).
output_style = (environment == :production) ? :compressed : :nested

# To enable relative paths to assets via compass helper functions.
relative_assets = true

# Conditionally enable line comments when in development mode.
line_comments = (environment == :production) ? false : true

# Conditionally enable sourcemap when in development mode.
sourcemap = (environment == :production) ? false : true

# Output debugging info in development mode.
sass_options = (environment == :production) ? {} : {:debug_info => true}

# Add the 'sass' directory itself as an import path to ease imports.
add_import_path 'sass'

# Disable the asset cache buster
asset_cache_buster :none
