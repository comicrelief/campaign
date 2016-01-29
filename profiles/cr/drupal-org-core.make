api = 2
core = 8.x

; Warning: for now Drupal core versions are managed by Pantheon and upstream repositories
; @see https://pantheon.io/docs/articles/sites/code/applying-upstream-updates/
;
; Applying upstream updates:
; 	git pull -Xtheirs git://github.com/pantheon-systems/drops-8.git master
;	# resolve conflicts

projects[drupal][version] = 8.0.2

; Fix the permissions issue on sites folders.
projects[drupal][patch][] = https://www.drupal.org/files/issues/1232572-57.drupal.disable-file-permissions-fix.patch
