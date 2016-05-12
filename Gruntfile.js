'use strict';

module.exports = function (grunt) {

  grunt.initConfig({
    watch: {
      options: {
        livereload: true,
        nospawn : true
      },
      sass: {
        files: ['profiles/cr/themes/custom/campaign_base/sass/{,**/}*.{scss,sass}'],
        tasks: ['compass:dev','shell:styleguide']
      },
      templates: {
        files: ['profiles/cr/themes/custom/campaign_base/templates/{,**/}*.html.twig', 'profiles/cr/themes/custom/campaign_base/sass/components/{,**/}*.hbs']
      },
      images: {
        files: ['profiles/cr/themes/custom/campaign_base/images/**']
      },
      css: {
        files: ['profiles/cr/themes/custom/campaign_base/css/{,**/}*.css']
        },
      js: {
        files: ['profiles/cr/themes/custom/campaign_base/scripts/{,**/}*.js', '!js/{,**/}*.min.js'],
        tasks: ['uglify:dev'] //'jshint',
      }
    },

    concat: {
      options: {
        separator: ';',
      },
      basic: {
        src: ['profiles/cr/themes/custom/campaign_base/scripts/{,**/}*.js'],
        dest: 'profiles/cr/themes/custom/campaign_base/js/basic.js',
      },
      vendor: {
        src: ['vendor/{,**/}*.js'],
        dest: 'profiles/cr/themes/custom/campaign_base/js/vendor.js',
      },
    },

    shell: {
        styleguide: {
            command: 'node_modules/kss/bin/kss-node --source profiles/cr/themes/custom/campaign_base/sass/ --css ../css/styles.css --verbose --title "Comic Relief PatternLab"'
        }
    },

    // accessibility: { // todo!!!
    //   options : {
    //     accessibilityLevel: 'WCAG2A',
    //     domElement: true,
    //     force: true
    //   },
    //   test : {
    //     src: ['index.html','views/**/*.html']
    //   }
    // },

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
        config: 'profiles/cr/themes/custom/campaign_base/config.rb',
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
  grunt.loadNpmTasks('grunt-kss');
  grunt.loadNpmTasks('grunt-shell');

  // grunt.loadNpmTasks('grunt-accessibility');

  // grunt.registerTask('test',  [
  //   // 'jshint',
  //   'browserSync:dev'//,
  //   // 'nodeunit'
  //   ]);
  grunt.registerTask('style', ['shell:styleguide']);
  grunt.registerTask('default', ['shell:styleguide', 'uglify:dev', 'watch']);

  grunt.registerTask('build', [
    // 'uglify:dist',
    'shell:styleguide',
    'concat',
    'uglify:dist', // todo error when compress
    'compass:dist' //,
    // 'jshint'
  ]);

};
