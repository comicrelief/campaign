'use strict';

module.exports = function (grunt) {

  grunt.initConfig({
    watch: {
      options: {
        livereload: true
      },
      sass: {
        files: ['sass/{,**/}*.{scss,sass}'],
        tasks: ['compass:dev'],
        options: {
          livereload: false
        }
      },
      templates: {
        files: ['templates/{,**/}*.html.twig']
      },
      images: {
        files: ['images/**']
      },
      css: {
        files: ['css/{,**/}*.css']
        },
      js: {
        files: ['scripts/{,**/}*.js', '!js/{,**/}*.min.js'],
        tasks: ['uglify:dev'] //'jshint',
      }
    },

    concat: {
      options: {
        separator: ';',
      },
      basic: {
        src: ['scripts/{,**/}*.js'],
        dest: 'js/basic.js',
      },
      vendor: {
        src: ['vendor/{,**/}*.js'],
        dest: 'js/vendor.js',
      },
    },

    accessibility: { // todo!!!
      options : {
        accessibilityLevel: 'WCAG2A',
        domElement: true,
        force: true
      },
      test : {
        src: ['templates/**/*.html.twig']
      }
    },

    // browserSync: {
    //     dev: {
    //         bsFiles: {
    //             src : ['index.html','views/{,**/}*.html','css/{,**/}*.css']
    //         },
    //         proxy: 'localhost',
    //         options: {
    //             port: 4567,
    //             watchTask: true,
    //             server: './'
    //         }
    //     }
    // },

    compass: {
      options: {
        config: 'config.rb',
        bundleExec: false,
        force: true
      },
      dev: {
        options: {
          environment: 'development'
        }
      },
      dist: {
        options: {
          environment: 'production'
        }
      }
    },

    jshint: {
      options: {
        jshintrc: '.jshintrc'
      },
      all: ['js/{,**/}*.js', '!js/{,**/}*.min.js']
    },

    uglify: {
      dev: {
        options: {
          mangle: false,
          compress: false,
          beautify: true
        },
        files: [{
          expand: true,
          flatten: true,
          cwd: 'scripts',
          dest: 'js',
          src: ['**/*.js', '!**/*.min.js'],
          rename: function(dest, src) {
            var folder = src.substring(0, src.lastIndexOf('/'));
            var filename = src.substring(src.lastIndexOf('/'), src.length);
            filename = filename.substring(0, filename.lastIndexOf('.'));
            return dest + '/' + folder + filename + '.min.js';
          }
        }]
      },
      dist: {
        options: {
          mangle: false,
          compress: {} // conpress: {}
        },
        files: [{
          expand: true,
          flatten: true,
          cwd: 'scripts',
          dest: 'js',
          src: ['**/*.js', '!**/*.min.js'],
          rename: function(dest, src) {
            var folder = src.substring(0, src.lastIndexOf('/'));
            var filename = src.substring(src.lastIndexOf('/'), src.length);
            filename = filename.substring(0, filename.lastIndexOf('.'));
            return dest + '/' + folder + filename + '.min.js';
          }
        }]
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-browser-sync');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-accessibility');

  // grunt.registerTask('test',  [
  //   // 'jshint', 
  //   'browserSync:dev'//, 
  //   // 'nodeunit'
  //   ]);
  grunt.registerTask('default', ['uglify:dev', 'watch']);

  grunt.registerTask('build', [
    // 'uglify:dist',
    'concat',
    'uglify:dist', // todo error when compress
    'compass:dist' //,
    // 'jshint'
  ]);

  grunt.registerTask('test', ['accessibility']);

};
