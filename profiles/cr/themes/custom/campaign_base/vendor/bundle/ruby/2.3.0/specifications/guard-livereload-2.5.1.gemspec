# -*- encoding: utf-8 -*-
# stub: guard-livereload 2.5.1 ruby lib

Gem::Specification.new do |s|
  s.name = "guard-livereload"
  s.version = "2.5.1"

  s.required_rubygems_version = Gem::Requirement.new(">= 0") if s.respond_to? :required_rubygems_version=
  s.require_paths = ["lib"]
  s.authors = ["Thibaud Guillaume-Gentil"]
  s.date = "2015-10-26"
  s.description = "Guard::LiveReload automatically reloads your browser when 'view' files are modified."
  s.email = "thibaud@thibaud.me"
  s.homepage = "https://rubygems.org/gems/guard-livereload"
  s.licenses = ["MIT"]
  s.rubygems_version = "2.5.1"
  s.summary = "Guard plugin for livereload"

  s.installed_by_version = "2.5.1" if s.respond_to? :installed_by_version

  if s.respond_to? :specification_version then
    s.specification_version = 4

    if Gem::Version.new(Gem::VERSION) >= Gem::Version.new('1.2.0') then
      s.add_runtime_dependency(%q<guard>, ["~> 2.8"])
      s.add_runtime_dependency(%q<guard-compat>, ["~> 1.0"])
      s.add_runtime_dependency(%q<em-websocket>, ["~> 0.5"])
      s.add_runtime_dependency(%q<multi_json>, ["~> 1.8"])
      s.add_development_dependency(%q<bundler>, [">= 1.3.5"])
    else
      s.add_dependency(%q<guard>, ["~> 2.8"])
      s.add_dependency(%q<guard-compat>, ["~> 1.0"])
      s.add_dependency(%q<em-websocket>, ["~> 0.5"])
      s.add_dependency(%q<multi_json>, ["~> 1.8"])
      s.add_dependency(%q<bundler>, [">= 1.3.5"])
    end
  else
    s.add_dependency(%q<guard>, ["~> 2.8"])
    s.add_dependency(%q<guard-compat>, ["~> 1.0"])
    s.add_dependency(%q<em-websocket>, ["~> 0.5"])
    s.add_dependency(%q<multi_json>, ["~> 1.8"])
    s.add_dependency(%q<bundler>, [">= 1.3.5"])
  end
end
