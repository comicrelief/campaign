source 'https://rubygems.org'

# Sass, Compass and extensions.
gem 'activesupport', '4.2.6'  # Pin version to stop higher version requirements
gem 'chunky_png', '1.3.6'
gem 'oily_png'                # Faster Compass sprite generation.
gem 'css_parser'              # Helps `compass stats` output statistics.

# Dependency to prevent polling. Setup for multiple OS environments.
# Optionally remove the lines not specific to your OS.
# https://github.com/guard/guard#efficient-filesystem-handling
gem 'rb-inotify', '~> 0.9', :require => false      # Linux
gem 'rb-fsevent', :require => false                # Mac OSX
gem 'rb-fchange', :require => false                # Windows

group :craft do
  # Craft DB backup
  gem 'craft-drush', '0.0.7' ,:git => 'git@github.com:comicrelief/craft-drush.git'
end
