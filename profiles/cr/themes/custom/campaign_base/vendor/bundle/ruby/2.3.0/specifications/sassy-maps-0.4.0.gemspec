# -*- encoding: utf-8 -*-
# stub: sassy-maps 0.4.0 ruby lib

Gem::Specification.new do |s|
  s.name = "sassy-maps"
  s.version = "0.4.0"

  s.required_rubygems_version = Gem::Requirement.new(">= 0") if s.respond_to? :required_rubygems_version=
  s.require_paths = ["lib"]
  s.authors = ["Sam Richard"]
  s.date = "2014-06-03"
  s.description = "Map helper functions for Sass 3.3 Maps including get-deep and set/set-deep"
  s.email = ["sam@snug.ug"]
  s.homepage = "https://github.com/Snugug/Sassy-Maps"
  s.licenses = ["MIT"]
  s.rubyforge_project = "sassy-maps"
  s.rubygems_version = "2.5.1"
  s.summary = "Map helper functions for Sass 3.3 Maps"

  s.installed_by_version = "2.5.1" if s.respond_to? :installed_by_version

  if s.respond_to? :specification_version then
    s.specification_version = 4

    if Gem::Version.new(Gem::VERSION) >= Gem::Version.new('1.2.0') then
      s.add_runtime_dependency(%q<sass>, ["~> 3.3"])
      s.add_development_dependency(%q<bundler>, [">= 0"])
      s.add_development_dependency(%q<rake>, [">= 0"])
      s.add_development_dependency(%q<minitest>, [">= 0"])
      s.add_development_dependency(%q<minitap>, [">= 0"])
      s.add_development_dependency(%q<tapout>, [">= 0"])
      s.add_development_dependency(%q<term-ansicolor>, [">= 0"])
      s.add_development_dependency(%q<colorize>, [">= 0"])
    else
      s.add_dependency(%q<sass>, ["~> 3.3"])
      s.add_dependency(%q<bundler>, [">= 0"])
      s.add_dependency(%q<rake>, [">= 0"])
      s.add_dependency(%q<minitest>, [">= 0"])
      s.add_dependency(%q<minitap>, [">= 0"])
      s.add_dependency(%q<tapout>, [">= 0"])
      s.add_dependency(%q<term-ansicolor>, [">= 0"])
      s.add_dependency(%q<colorize>, [">= 0"])
    end
  else
    s.add_dependency(%q<sass>, ["~> 3.3"])
    s.add_dependency(%q<bundler>, [">= 0"])
    s.add_dependency(%q<rake>, [">= 0"])
    s.add_dependency(%q<minitest>, [">= 0"])
    s.add_dependency(%q<minitap>, [">= 0"])
    s.add_dependency(%q<tapout>, [">= 0"])
    s.add_dependency(%q<term-ansicolor>, [">= 0"])
    s.add_dependency(%q<colorize>, [">= 0"])
  end
end
