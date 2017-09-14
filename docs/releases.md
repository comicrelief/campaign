## Release management

You can execute the release script inside the folder `/.github` to get the logs since the last release, formatted correctly for the [release notes](https://github.com/comicrelief/campaign/releases). Have in mind that this script doesn't predict your next release version, so you'll need to update manually the version number of the release that you are doing.

```
sh .github/releaser.sh
```

Alternatively, you can do it manually from your IDE, terminal, etc.. as long as follow the standard.

Then you need to create a PR from `develop` to `master` and copy the output/changelog to the description.

Once this is reviewed and merged, you need to [create a tag from `master`](https://github.com/comicrelief/campaign/releases) and paste the changelog.
