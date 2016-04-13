# -*- encoding: utf-8 -*-
# stub: rb-fchange 0.0.6 ruby lib

Gem::Specification.new do |s|
  s.name = "rb-fchange"
  s.version = "0.0.6"

  s.required_rubygems_version = Gem::Requirement.new(">= 0") if s.respond_to? :required_rubygems_version=
  s.require_paths = ["lib"]
  s.authors = ["stereobooster"]
  s.date = "2011-05-15"
  s.description = "A Ruby wrapper for Windows Kernel functions for monitoring the specified directory or subtree"
  s.email = ["stereobooster@gmail.com"]
  s.extra_rdoc_files = ["README.md"]
  s.files = ["README.md"]
  s.homepage = "http://github.com/stereobooster/rb-fchange"
  s.rubygems_version = "2.5.1"
  s.summary = "A Ruby wrapper for Windows Kernel functions for monitoring the specified directory or subtree"

  s.installed_by_version = "2.5.1" if s.respond_to? :installed_by_version

  if s.respond_to? :specification_version then
    s.specification_version = 3

    if Gem::Version.new(Gem::VERSION) >= Gem::Version.new('1.2.0') then
      s.add_runtime_dependency(%q<ffi>, [">= 0"])
      s.add_development_dependency(%q<bundler>, [">= 0"])
      s.add_development_dependency(%q<rspec>, [">= 0"])
    else
      s.add_dependency(%q<ffi>, [">= 0"])
      s.add_dependency(%q<bundler>, [">= 0"])
      s.add_dependency(%q<rspec>, [">= 0"])
    end
  else
    s.add_dependency(%q<ffi>, [">= 0"])
    s.add_dependency(%q<bundler>, [">= 0"])
    s.add_dependency(%q<rspec>, [">= 0"])
  end
end
