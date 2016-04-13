# -*- encoding: utf-8 -*-
# stub: guard-compass 1.1.0 ruby lib

Gem::Specification.new do |s|
  s.name = "guard-compass"
  s.version = "1.1.0"

  s.required_rubygems_version = Gem::Requirement.new(">= 0") if s.respond_to? :required_rubygems_version=
  s.require_paths = ["lib"]
  s.authors = ["Olivier Amblet", "R\u{e9}my Coutable"]
  s.date = "2014-03-07"
  s.description = "Guard::Compass automatically rebuilds scss|sass files when a modification occurs taking in account your compass configuration."
  s.email = ["remy@rymai.me"]
  s.homepage = "https://rubygems.org/gems/guard-compass"
  s.licenses = ["MIT"]
  s.required_ruby_version = Gem::Requirement.new(">= 1.9.2")
  s.rubygems_version = "2.5.1"
  s.summary = "Guard plugin for Compass"

  s.installed_by_version = "2.5.1" if s.respond_to? :installed_by_version

  if s.respond_to? :specification_version then
    s.specification_version = 4

    if Gem::Version.new(Gem::VERSION) >= Gem::Version.new('1.2.0') then
      s.add_runtime_dependency(%q<guard>, ["~> 2.0"])
      s.add_runtime_dependency(%q<compass>, [">= 0.10.5"])
    else
      s.add_dependency(%q<guard>, ["~> 2.0"])
      s.add_dependency(%q<compass>, [">= 0.10.5"])
    end
  else
    s.add_dependency(%q<guard>, ["~> 2.0"])
    s.add_dependency(%q<compass>, [">= 0.10.5"])
  end
end
