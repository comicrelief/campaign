Maintainers Guide
----------------

Below are notes and references for current and future maintainers and contributors
to the YAML Form module.

### References

- [Create a project (module or theme) on Drupal.org](https://www.drupal.org/contribute-projects)


### Coding Standards

The YAML Form module follows all [coding standards](https://www.drupal.org/coding-standards) 
established by the Drupal community.

Below are the additional standards that should be followed.

- [[Policy, no patch] PHP 5.4 short array syntax coding standards](https://www.drupal.org/node/2135291)  
  The NOTES.md document includes a command to convert all arrays to use the
  shorthand syntax.


### Becoming a co-maintainer

Everyone is welcome to contribute to the YAML Form module by posting questions,
issues, feature requests, and patches to the YAML Form module's [issue queue](https://www.drupal.org/project/issues/yamlform).

Pleases read [best practices for co-maintaining projects](https://www.drupal.org/node/363367)
for information on how to become a co-maintainer of the YAML Form module.

Below are some specific tips and tasks for becoming a better contributor and 
co-maintainer of the YAML Form module:

- Review and edit the [test script](test.md) which documents every 
  feature provided by the YAML Form module.  

- Post issues and patches to help improve and/or fix any issues with the
  test script.
   
- Review, improve, and write self documenting SimpleTests and PHPUnit tests.
  
- Write a contrib module that extends the YAML Form module.


### Giving commit credit

> If others have contributed to the change you are committing, take the time to 
> give them credit. Each commit message should contain at least one contributor 
> name, even if it refers to yourself. Once a project has more than one maintainer, 
> or is taken over by a new maintainer, it's very valuable to know who actually 
> wrote or contributed a certain change.  
> -- [Commit messages - providing history and credit](https://www.drupal.org/node/52287)

Personally, I will give someone commit credit for simply taking the time out of 
their day to write a clear and concise ticket about a bug or issue. 

> I DO NOT give commit credit for people running automated code reviews and 
> then generating patches without ever installing, testing, or actually using 
> a module. It seems that certain organizations are just trying to 'game' the 
> commit credit system and I don't want to support this behavior.
