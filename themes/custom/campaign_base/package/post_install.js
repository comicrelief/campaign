const EnvironmentDetection = require('./class/EnvironmentDetection.js')(),
      LibraryManager       = require('./class/LibraryManager.js')();

// Get the web base path for the current environment.
EnvironmentDetection.getWebBasePath().then(function(basePath) {

   // Ensure that all of the libraries exist in the libraries folder.
   return LibraryManager.ensureLibs(basePath);

});

