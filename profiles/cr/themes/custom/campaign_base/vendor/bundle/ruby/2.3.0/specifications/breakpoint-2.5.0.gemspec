# -*- encoding: utf-8 -*-
# stub: breakpoint 2.5.0 ruby lib

Gem::Specification.new do |s|
  s.name = "breakpoint"
  s.version = "2.5.0"

  s.required_rubygems_version = Gem::Requirement.new(">= 1.3.6") if s.respond_to? :required_rubygems_version=
  s.require_paths = ["lib"]
  s.authors = ["Mason Wendell", "Sam Richard"]
  s.date = "2014-08-05"
  s.description = "Really simple media queries in Sass"
  s.email = ["mason@thecodingdesigner.com", "sam@snug.ug"]
  s.homepage = "https://github.com/Team-Sass/breakpoint"
  s.licenses = ["MIT", "GPL-2.0"]
  s.rubyforge_project = "breakpoint"
  s.rubygems_version = "2.5.1"
  s.summary = "An easy to use system for writing and managing media queries."

  s.installed_by_version = "2.5.1" if s.respond_to? :installed_by_version

  if s.respond_to? :specification_version then
    s.specification_version = 4

    if Gem::Version.new(Gem::VERSION) >= Gem::Version.new('1.2.0') then
      s.add_runtime_dependency(%q<sass>, ["~> 3.3"])
      s.add_runtime_dependency(%q<sassy-maps>, ["< 1.0.0"])
    else
      s.add_dependency(%q<sass>, ["~> 3.3"])
      s.add_dependency(%q<sassy-maps>, ["< 1.0.0"])
    end
  else
    s.add_dependency(%q<sass>, ["~> 3.3"])
    s.add_dependency(%q<sassy-maps>, ["< 1.0.0"])
  end
end
