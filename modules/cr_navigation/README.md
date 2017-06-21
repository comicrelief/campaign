# CR Navigation module

## Overview

Previously this custom module provided the JS for the sites' standard main site navigation. It *now* provides theme suggestion hooks and associated JS to form the 'feature nav' variation to override the standard nav templates, style and functionality, which now live in the main campaign_base theme.

Drupal still requires that all templates live within the theme folder (rather than living within this module), so you'll find the overriding templates here:

```/themes/custom/campaign_base/templates/layout/Navigation/menu--main--feature.html.twig```
```/themes/custom/campaign_base/templates/layout/Navigation/block--system-menu-block--feature.html.twig```
