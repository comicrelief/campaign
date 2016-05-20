## Rules of the Road

This section intends to lay down some guiding principles and best-practice approaches for the campaign build. Additions should be submitted as a PR for team discussion.

### Table of Contents

1. [Node-based](rules_of_the_road.md#node-based)
2. [Susy grid](rules_of_the_road.md#susy-grid)
3. [Theme inheritance](rules_of_the_road.md#theme-inheritance)
4. [Only essential markup](rules_of_the_road.md#only-essential-markup)
5. [Component-based sass](rules_of_the_road.md#component-based-sass)
6. [Feature provides default content](rules_of_the_road.md#feature-provides-default-content)
7. [Contrib projects](rules_of_the_road.md#contrib-projects)
8. [Behat tests, where useful](rules_of_the_road.md#behat-tests-where-useful)
9. [Theme JS in footer](rules_of_the_road.md#theme-js-in-footer)
10. [Everything should be within the profile](rules_of_the_road.md#everything-should-be-within-the-profile)
11. [Views view modes](rules_of_the_road.md#views-view-modes)
12. [Document module fields](rules_of_the_road.md#document-module-fields)

### Node-based

Everything should be a node. This falls more inline with how Drupal __wants__ things to be done. This approach will also help with Solr indexing implementations later on.

### Susy grid

The grid system for the theme should be generated with the Susy mixins - http://susy.oddbird.net/. So no more Bootstrap grid.

### Theme inheritance

To be discussed.

### Only essential markup

Drupal has a long history of generating bloated markup. For performance reasons, efforts should me made to keep all module generated markup to a minimum.

### Component-based sass

SASS should be written with a component approach in mind, with a view to being able to easily extend a given component. BEM has worked well in the past and probably should be adopted for the campaign theme SASS.

[http://alwaystwisted.com/articles/2014-02-27-even-easier-bem-ing-with-sass-33](http://alwaystwisted.com/articles/2014-02-27-even-easier-bem-ing-with-sass-33)

### Feature provides default content

When writing a custom module - say a Blog article with a node type, fields, views etc. - default content should be provided in code so the feature (don't confuse with the drupal feature module!) as a whole can be developer-reviewed and QA'd as it moves upstream. Default content should be exported as part of `cr_default_content`.

See [https://www.drupal.org/project/default_content](See https://www.drupal.org/project/default_content) (already included in the CR profile).

### Contrib projects

Because of the high-performance nature of the campaign sites, the use of contirb projects should be carefully considered. Adding a contrib module because it gets the job done can sometimes cause issues further down the line.

### Behat tests, where useful

Bear in mind that testing for the existence of certain links on certain pages may not be sustainable, due to ever-changing content. But Behat tests should still be written and provided in your feature where it makes sense to.

[http://docs.behat.org/en/v3.0/](http://docs.behat.org/en/v3.0/)
[https://knpuniversity.com/screencast/behat/using-behat](https://knpuniversity.com/screencast/behat/using-behat)

### Theme JS in footer

As far as I know, Drupal 8 adds JS to the footer by default but it's good one to keep an eye on.

[https://www.drupal.org/theme-guide/8/assets](https://www.drupal.org/theme-guide/8/assets)

### Everything should be within the profile

Use Drupal's recommended guidelines on how to write a profile:
[https://www.drupal.org/node/2210443](https://www.drupal.org/node/2210443)

### Views view modes

Views should always use view modes, never field-based views for example.

[https://drupalize.me/blog/201403/exploring-new-drupal-8-display-modes](https://drupalize.me/blog/201403/exploring-new-drupal-8-display-modes)

### Document module fields

Modules defining an entity type should document their fields.

See [https://github.com/comicrelief/campaign/tree/develop/profiles/cr/modules/custom/cr_article](https://github.com/comicrelief/campaign/tree/develop/profiles/cr/modules/custom/cr_article)
