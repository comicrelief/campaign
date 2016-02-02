api = 2
core = 8.x

; Warning: for now Drupal core versions are managed by Pantheon and upstream repositories
; @see https://pantheon.io/docs/articles/sites/code/applying-upstream-updates/
;
<<<<<<< 3b011e0f6b126d7b7d66d4ccfd3537a4450b3986
; Applying upstream updates:  
=======
; Applying upstream updates:
>>>>>>> PLAT-15 Add patch
; 	git pull -Xtheirs git://github.com/pantheon-systems/drops-8.git master
;	# resolve conflicts

projects[drupal][version] = 8.0.2
<<<<<<< 3b011e0f6b126d7b7d66d4ccfd3537a4450b3986
=======

; Fix the permissions issue on sites folders.
projects[drupal][patch][] = https://www.drupal.org/files/issues/1232572-57.drupal.disable-file-permissions-fix.patch
>>>>>>> PLAT-15 Add patch
