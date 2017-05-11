## Exporting default content

Every new feature should have default content associated with it in order to:

- demonstrate the feature on a clean install and without any manual actions
- provide fixture content for automated testing (in particular, behat testing)

Default content can be exported as part of the `cr_default_content` module.

To export content, you need to create a node, taxonomy term, paragraph, or generally any type of entity first via the UI.

### Exporting via Drush (more involved)

Once created, grab the entity id and entity type, and run

	drush dcer node 12 --folder=profiles/cr/modules/custom/cr_default_content/content

This will export the node plus all references (such as images, files, taxonomy terms, etc.) and structure this in the `content` folder of `cr_default_content`.

You will need to remove the automatically exported user as this generates problems for the installation.

	rm -fr profiles/cr/modules/custom/cr_default_content/content/user/

This sometimes creates problems with serial identifiers (e.g. file id 7), as they might have been reused from previous installations. The site install will fail but the problem is easily solved by changing

	"self": {
	  "href": "http:\/\/default\/file\/7?_format=hal_json"
	},

to

	"self": {
	  "href": "http:\/\/default\/file\/7777?_format=hal_json"
	},

### Troubleshooting

#### Error: PHP Fatal Error: Nesting level too deep

	PHP Fatal error:  Nesting level too deep - recursive dependency? in /Users/pvhee/Sites/comicrelief/campaign/profiles/cr/modules/contrib/default_content/src/DefaultContentManager.php on line 34

The following error typically occurs on big landing pages that have references to many entities. In order to solve this, you need to decompose this landing page and do not export *recursively*. For this, we can use the command `drush dce` (instead of `drush dcer`).

	drush dce node 12 --file=profiles/cr/modules/custom/cr_default_content/content/node/e2f2ca58-d03a-4fe1-8616-2222cda201d7.json

Now, embedded entities (such as the paragraph entity that's being used in this landing page node) will *not* be exported automatically, so check the diff of that file  (`e2f2ca58-d03a-4fe1-8616-2222cda201d7.json`) and note a new paragraph entity. We can then export this manually using

	drush dcer paragraph 6 --folder=profiles/cr/modules/custom/cr_default_content/content

(note that we use `dcer` again instead of `dce` since the paragraph entity does have nested entities like files, but doesn't have the problem of "nesting level too deep")

We need to continue this for every new entity that is being referenced from the main landing page node.

#### Error: PHP Fatal error:  Call to a member function access() on null

	PHP Fatal error:  Call to a member function access() on null in /Users/pvhee/Sites/comicrelief/campaign/core/lib/Drupal/Core/Field/Plugin/Field/FieldFormatter/EntityReferenceFormatterBase.php on line 187`

or also

	Recoverable fatal error: Argument 1 passed to Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase::checkAccess() must implement interface Drupal\Core\Entity\EntityInterface, null given, called in /home/travis/build/comicrelief/campaign/core/lib/Drupal/Core/Field/Plugin/Field/FieldFormatter/EntityReferenceFormatterBase.php on line 55 and defined in /home/travis/build/comicrelief/campaign/core/lib/Drupal/Core/Field/Plugin/Field/FieldFormatter/EntityReferenceFormatterBase.php on line 182

This occurs when importing the default content (i.e. on re-installing the site, or enabling `cr_default_content`).

This happened before because of a revision_id issue in the paragraph entity.

The solution is to look at the JSON file of the newly exported paragraph entity, and make sure `revision_id` is the same as `id`:

	"id": [
	    {
	        "value": "8"
	    }
	],
	"revision_id": [
	    {
	        "value": "8"
	    }
	],

