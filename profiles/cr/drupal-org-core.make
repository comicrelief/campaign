api = 2
core = 8.x

; Warning: for now Drupal core versions are managed by Pantheon and upstream repositories
; @see https://pantheon.io/docs/articles/sites/code/applying-upstream-updates/
;

; Applying upstream updates:
; 	git pull -Xtheirs git://github.com/pantheon-systems/drops-8.git master
;	# resolve conflicts

projects[drupal][version] = 8.0.3

; Fix the permissions issue on sites folders.
; @see https://www.drupal.org/node/1232572
; This is committed and will be part of 8.1.0
projects[drupal][patch][] = https://www.drupal.org/files/issues/1232572-57.drupal.disable-file-permissions-fix.patch
