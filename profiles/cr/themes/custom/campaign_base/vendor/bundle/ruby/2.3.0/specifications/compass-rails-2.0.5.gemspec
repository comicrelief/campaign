# -*- encoding: utf-8 -*-
# stub: compass-rails 2.0.5 ruby lib

Gem::Specification.new do |s|
  s.name = "compass-rails"
  s.version = "2.0.5"

  s.required_rubygems_version = Gem::Requirement.new(">= 0") if s.respond_to? :required_rubygems_version=
  s.require_paths = ["lib"]
  s.authors = ["Scott Davis", "Chris Eppstein", "Craig McNamara"]
  s.date = "2015-08-15"
  s.description = "Integrate Compass into Rails 3.0 and up."
  s.email = ["jetviper21@gmail.com", "chris@eppsteins.net", "craig.mcnamara@gmail.com"]
  s.homepage = "https://github.com/Compass/compass-rails"
  s.licenses = ["MIT"]
  s.rubygems_version = "2.5.1"
  s.summary = "Integrate Compass into Rails 3.0 and up."

  s.installed_by_version = "2.5.1" if s.respond_to? :installed_by_version

  if s.respond_to? :specification_version then
    s.specification_version = 4

    if Gem::Version.new(Gem::VERSION) >= Gem::Version.new('1.2.0') then
      s.add_runtime_dependency(%q<compass>, ["~> 1.0.0"])
      s.add_runtime_dependency(%q<sprockets>, ["< 2.13"])
      s.add_runtime_dependency(%q<sass-rails>, ["< 5.1"])
    else
      s.add_dependency(%q<compass>, ["~> 1.0.0"])
      s.add_dependency(%q<sprockets>, ["< 2.13"])
      s.add_dependency(%q<sass-rails>, ["< 5.1"])
    end
  else
    s.add_dependency(%q<compass>, ["~> 1.0.0"])
    s.add_dependency(%q<sprockets>, ["< 2.13"])
    s.add_dependency(%q<sass-rails>, ["< 5.1"])
  end
end
