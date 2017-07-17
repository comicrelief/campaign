## Release

You can execute the script inside of the folder /.github
to get the logs since the last release and the right format
(have in mind that you probably need to update/change some values of the next release)

```
. .github/release_script.sh
```
alternatively you can do it manually from your IDE, terminal, etc..
as long as follow the standard.

Then you need to create a PR from develop to master
and copy the output/changelog to the description.

Once is merge you need to create a tag from master
and paste again the changelog.
