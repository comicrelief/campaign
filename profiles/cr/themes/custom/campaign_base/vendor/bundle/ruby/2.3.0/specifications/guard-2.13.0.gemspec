# -*- encoding: utf-8 -*-
# stub: guard 2.13.0 ruby lib

Gem::Specification.new do |s|
  s.name = "guard"
  s.version = "2.13.0"

  s.required_rubygems_version = Gem::Requirement.new(">= 0") if s.respond_to? :required_rubygems_version=
  s.require_paths = ["lib"]
  s.authors = ["Thibaud Guillaume-Gentil"]
  s.date = "2015-07-27"
  s.description = "Guard is a command line tool to easily handle events on file system modifications."
  s.email = ["thibaud@thibaud.gg"]
  s.executables = ["guard", "_guard-core"]
  s.files = ["bin/_guard-core", "bin/guard"]
  s.homepage = "http://guardgem.org"
  s.licenses = ["MIT"]
  s.required_ruby_version = Gem::Requirement.new(">= 1.9.3")
  s.rubygems_version = "2.5.1"
  s.summary = "Guard keeps an eye on your file modifications"

  s.installed_by_version = "2.5.1" if s.respond_to? :installed_by_version

  if s.respond_to? :specification_version then
    s.specification_version = 4

    if Gem::Version.new(Gem::VERSION) >= Gem::Version.new('1.2.0') then
      s.add_runtime_dependency(%q<thor>, [">= 0.18.1"])
      s.add_runtime_dependency(%q<listen>, ["<= 4.0", ">= 2.7"])
      s.add_runtime_dependency(%q<pry>, [">= 0.9.12"])
      s.add_runtime_dependency(%q<lumberjack>, ["~> 1.0"])
      s.add_runtime_dependency(%q<formatador>, [">= 0.2.4"])
      s.add_runtime_dependency(%q<nenv>, ["~> 0.1"])
      s.add_runtime_dependency(%q<shellany>, ["~> 0.0"])
      s.add_runtime_dependency(%q<notiffany>, ["~> 0.0"])
    else
      s.add_dependency(%q<thor>, [">= 0.18.1"])
      s.add_dependency(%q<listen>, ["<= 4.0", ">= 2.7"])
      s.add_dependency(%q<pry>, [">= 0.9.12"])
      s.add_dependency(%q<lumberjack>, ["~> 1.0"])
      s.add_dependency(%q<formatador>, [">= 0.2.4"])
      s.add_dependency(%q<nenv>, ["~> 0.1"])
      s.add_dependency(%q<shellany>, ["~> 0.0"])
      s.add_dependency(%q<notiffany>, ["~> 0.0"])
    end
  else
    s.add_dependency(%q<thor>, [">= 0.18.1"])
    s.add_dependency(%q<listen>, ["<= 4.0", ">= 2.7"])
    s.add_dependency(%q<pry>, [">= 0.9.12"])
    s.add_dependency(%q<lumberjack>, ["~> 1.0"])
    s.add_dependency(%q<formatador>, [">= 0.2.4"])
    s.add_dependency(%q<nenv>, ["~> 0.1"])
    s.add_dependency(%q<shellany>, ["~> 0.0"])
    s.add_dependency(%q<notiffany>, ["~> 0.0"])
  end
end
