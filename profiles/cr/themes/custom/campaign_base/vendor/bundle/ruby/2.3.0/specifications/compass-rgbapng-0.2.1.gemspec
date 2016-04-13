# -*- encoding: utf-8 -*-
# stub: compass-rgbapng 0.2.1 ruby lib

Gem::Specification.new do |s|
  s.name = "compass-rgbapng"
  s.version = "0.2.1"

  s.required_rubygems_version = Gem::Requirement.new(">= 0") if s.respond_to? :required_rubygems_version=
  s.require_paths = ["lib"]
  s.authors = ["Aaron Russell", "Benjamin Doherty"]
  s.date = "2013-06-26"
  s.description = "Compass plugin for providing cross-browser compatible RGBA support by creating transparent PNGs on the fly for browsers that don't support RGBA. Uses the pure Ruby ChunkyPNG library for hassle free install and deployment."
  s.email = "aaron@gc4.co.uk"
  s.homepage = "http://github.com/aaronrussell/compass-rgbapng"
  s.rubygems_version = "2.5.1"
  s.summary = "Compass plugin for providing cross-browser compatible RGBA support by creating transparent PNGs on the fly for browsers that don't support RGBA."

  s.installed_by_version = "2.5.1" if s.respond_to? :installed_by_version

  if s.respond_to? :specification_version then
    s.specification_version = 3

    if Gem::Version.new(Gem::VERSION) >= Gem::Version.new('1.2.0') then
      s.add_runtime_dependency(%q<compass>, [">= 0.10.0"])
      s.add_runtime_dependency(%q<chunky_png>, [">= 0.8.0"])
    else
      s.add_dependency(%q<compass>, [">= 0.10.0"])
      s.add_dependency(%q<chunky_png>, [">= 0.8.0"])
    end
  else
    s.add_dependency(%q<compass>, [">= 0.10.0"])
    s.add_dependency(%q<chunky_png>, [">= 0.8.0"])
  end
end
