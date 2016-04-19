# -*- encoding: utf-8 -*-
# stub: toolkit 2.9.0 ruby lib

Gem::Specification.new do |s|
  s.name = "toolkit"
  s.version = "2.9.0"

  s.required_rubygems_version = Gem::Requirement.new(">= 1.2") if s.respond_to? :required_rubygems_version=
  s.require_paths = ["lib"]
  s.authors = ["Sam Richard", "Scott Kellum", "Mason Wendell"]
  s.date = "2015-03-04"
  s.description = "Toolkit full of bits and bobs of awesome aimed at assisting in advanced styling with Sass"
  s.email = ["sam@snug.ug", "scott@scottkellum.com", "mason@zivtech.com"]
  s.homepage = "https://github.com/Snugug/toolkit"
  s.licenses = ["MIT", "GPL-2.0"]
  s.rubyforge_project = "toolkit"
  s.rubygems_version = "2.5.1"
  s.summary = "Toolkit of awesome Sass stuff"

  s.installed_by_version = "2.5.1" if s.respond_to? :installed_by_version

  if s.respond_to? :specification_version then
    s.specification_version = 4

    if Gem::Version.new(Gem::VERSION) >= Gem::Version.new('1.2.0') then
      s.add_runtime_dependency(%q<sass>, ["~> 3.3"])
    else
      s.add_dependency(%q<sass>, ["~> 3.3"])
    end
  else
    s.add_dependency(%q<sass>, ["~> 3.3"])
  end
end
