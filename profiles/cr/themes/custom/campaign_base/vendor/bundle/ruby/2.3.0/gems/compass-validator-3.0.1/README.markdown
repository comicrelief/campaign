# compass-validator

This is a library dependency for [compass](http://compass-style.org/) that provides a css
validator for compass projects. It is only useful during development and should be installed
separately. Very little is original code. Java is required and must be on the $PATH.

# Validating a CSS Folder

The W3C Validator can be challenging to install and get working. The Compass Validator
makes it much easier. On a system with java installed all you have do is this:

    $ gem install compass-validator
    $ compass-validate <css_folder>

In your compass project all you have to do is:

    $ compass validate

== Copyright

Copyright (c) 2009 Chris Eppstein. See LICENSE for details.
Additional copyrights for included software can be found in lib/java_validator.
