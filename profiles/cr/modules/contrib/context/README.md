# Context

The context module lets you define conditions for when certain reactions should take place.

An example of a condition could be when viewing a certain node type and blocks should be placed as a reaction when viewing a page with this node type.

###Good resources

- [Drupal 8 Plugin API](https://www.drupal.org/developing/api/8/plugins)

Conditions
---

Context for Drupal 8 uses the built in condition plugins supplied by Drupal trough the [Plugin API](https://www.drupal.org/developing/api/8/plugins). So any conditional plug-ins supplied by other modules can also be used with context.

Reactions
---

Reactions for the context module are defined trough the new Drupal 8 [Plugin API](https://www.drupal.org/developing/api/8/plugins).

The context module defines a plugin type named ContextReaction that you can extend when creating your own plugins.

A context reaction requires a configuration form and execute method. The execution of the plugin is also something that will have to be handled by the author of the reaction.