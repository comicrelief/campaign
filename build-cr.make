api = 2
core = 8.x
; Include the definition for how to build Drupal core directly, including patches:
includes[] = drupal-org-core.make
; Download the aGov install profile and recursively build all its dependencies:
projects[cr][version] = 1.x-dev
