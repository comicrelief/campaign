# -*- encoding: utf-8 -*-
# stub: yajl-ruby 1.2.1 ruby lib
# stub: ext/yajl/extconf.rb

Gem::Specification.new do |s|
  s.name = "yajl-ruby"
  s.version = "1.2.1"

  s.required_rubygems_version = Gem::Requirement.new(">= 0") if s.respond_to? :required_rubygems_version=
  s.require_paths = ["lib"]
  s.authors = ["Brian Lopez", "Lloyd Hilaiel"]
  s.date = "2014-06-05"
  s.email = "seniorlopez@gmail.com"
  s.extensions = ["ext/yajl/extconf.rb"]
  s.files = ["ext/yajl/extconf.rb"]
  s.homepage = "http://github.com/brianmario/yajl-ruby"
  s.licenses = ["MIT"]
  s.required_ruby_version = Gem::Requirement.new(">= 1.8.6")
  s.rubygems_version = "2.5.1"
  s.summary = "Ruby C bindings to the excellent Yajl JSON stream-based parser library."

  s.installed_by_version = "2.5.1" if s.respond_to? :installed_by_version

  if s.respond_to? :specification_version then
    s.specification_version = 4

    if Gem::Version.new(Gem::VERSION) >= Gem::Version.new('1.2.0') then
      s.add_development_dependency(%q<rake-compiler>, [">= 0.7.5"])
      s.add_development_dependency(%q<rspec>, ["~> 2.14"])
      s.add_development_dependency(%q<activesupport>, ["~> 3.1.2"])
      s.add_development_dependency(%q<json>, [">= 0"])
    else
      s.add_dependency(%q<rake-compiler>, [">= 0.7.5"])
      s.add_dependency(%q<rspec>, ["~> 2.14"])
      s.add_dependency(%q<activesupport>, ["~> 3.1.2"])
      s.add_dependency(%q<json>, [">= 0"])
    end
  else
    s.add_dependency(%q<rake-compiler>, [">= 0.7.5"])
    s.add_dependency(%q<rspec>, ["~> 2.14"])
    s.add_dependency(%q<activesupport>, ["~> 3.1.2"])
    s.add_dependency(%q<json>, [">= 0"])
  end
end
