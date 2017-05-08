const fse = require('fs-extra');

/**
 * Recursively drop down through directories until we find the drupal web directory.
 * @param directory {string} directory path to search
 * @returns {Promise}
 */
function getWebDirectoryFromPath(directory)
{
    return new Promise(function(resolve, reject) {

        let path = directory + '/web/';

        // Check to see if the web path exists in the current folder.
        fse.pathExists(path).then(function(pathExists) {

            // If it does not then drop down a folder, else if it does, then return the folder.
            if (pathExists === false) {
                resolve(getWebDirectoryFromPath(directory.substring(0, directory.lastIndexOf("/"))));
            } else {
                resolve(path);
            }

        }).catch(function(){
            reject();
        });
    });
}

/**
 * Module to detect the current web path.
 * @returns {{getWebBasePath: getWebBasePath}}
 */
module.exports = function () {

    return {
        getWebBasePath       : function() {
            return getWebDirectoryFromPath(__dirname);
        }
    };

};
