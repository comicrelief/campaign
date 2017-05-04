source 'https://rubygems.org'

# Sass, Compass and extensions.
gem 'activesupport', '4.2.6'  # Pin version to stop higher version requirements
gem 'sass', '3.4.5'           # Sass.
gem 'sass-globbing'           # Import Sass files based on globbing pattern.
gem 'compass'                 # Framework built on Sass.
gem 'compass-validator'       # So you can `compass validate`.
gem 'compass-normalize'       # Compass version of normalize.css.
gem 'compass-rgbapng'         # Turns rgba() into .png's for backwards compatibility.
gem 'chunky_png', '1.3.6'
gem 'toolkit'                 # Compass utility from the fabulous Snugug.
gem 'breakpoint'              # Manages CSS media queries.
gem 'oily_png'                # Faster Compass sprite generation.
gem 'css_parser'              # Helps `compass stats` output statistics.
gem 'sass-rails', '~> 5.0.0'
gem 'susy'
gem 'compass-rails', '~> 2.0.0'

group :development do	# Guard
	gem 'guard'                   # Guard event handler.
	gem 'guard-compass'           # Compile on sass/scss change.
	gem 'guard-livereload'        # Browser reload.
	gem 'yajl-ruby'               # Faster JSON with LiveReload in the browser.

	gem 'listen', '~> 2.10'
end

# Dependency to prevent polling. Setup for multiple OS environments.
# Optionally remove the lines not specific to your OS.
# https://github.com/guard/guard#efficient-filesystem-handling
gem 'rb-inotify', '~> 0.9', :require => false      # Linux
gem 'rb-fsevent', :require => false                # Mac OSX
gem 'rb-fchange', :require => false                # Windows
