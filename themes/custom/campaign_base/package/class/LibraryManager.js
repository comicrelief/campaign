const fse = require('fs-extra');

let packages = ['blazy'];

/**
 * Library manager to make sure libraries exist in the web root.
 */
module.exports = function () {

    /**
     * Ensure that libraries exist
     * @param webPath
     */
    let ensureLibs = function (webPath)
    {
        let librariesPath = webPath + 'libraries';

        // Ensure that the libraries path exists.
        fse.ensureDir(librariesPath).then(function() {

            // Loop through the packages and copy from node modules to web/libraries.
            packages.forEach(function(item) {
                fse.copy(__dirname + '/../../node_modules/' + item, librariesPath + '/' + item);
            });

        });
    };

    return {
        ensureLibs : ensureLibs
    };

};
