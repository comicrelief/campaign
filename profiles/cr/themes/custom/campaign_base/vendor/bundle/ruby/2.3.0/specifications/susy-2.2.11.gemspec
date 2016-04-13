# -*- encoding: utf-8 -*-
# stub: susy 2.2.11 ruby lib

Gem::Specification.new do |s|
  s.name = "susy"
  s.version = "2.2.11"

  s.required_rubygems_version = Gem::Requirement.new(">= 1.2") if s.respond_to? :required_rubygems_version=
  s.require_paths = ["lib"]
  s.authors = ["Miriam Eric Suzanne"]
  s.date = "2015-09-01"
  s.description = "Susy is a toolkit for building layouts of all types with a simple, natural syntax."
  s.email = ["miriam@oddbird.net"]
  s.extra_rdoc_files = ["docs/changelog.rst", "LICENSE.txt", "README.md", "lib/susy.rb"]
  s.files = ["LICENSE.txt", "README.md", "docs/changelog.rst", "lib/susy.rb"]
  s.homepage = "http://susy.oddbird.net/"
  s.licenses = ["BSD-3-Clause"]
  s.rdoc_options = ["--line-numbers", "--inline-source", "--title", "Susy", "--main", "README.md"]
  s.rubyforge_project = "susy"
  s.rubygems_version = "2.5.1"
  s.summary = "Responsive layout toolkit for Sass."

  s.installed_by_version = "2.5.1" if s.respond_to? :installed_by_version

  if s.respond_to? :specification_version then
    s.specification_version = 3

    if Gem::Version.new(Gem::VERSION) >= Gem::Version.new('1.2.0') then
      s.add_runtime_dependency(%q<sass>, ["< 3.5", ">= 3.3.0"])
    else
      s.add_dependency(%q<sass>, ["< 3.5", ">= 3.3.0"])
    end
  else
    s.add_dependency(%q<sass>, ["< 3.5", ">= 3.3.0"])
  end
end
