'use strict';

module.exports = function (grunt) {

  grunt.initConfig({

    sass: {
      dist: {
        options: {
            outputStyle: 'compressed',
            sourceMap: false,
            includePaths: ['node_modules']
        },
        files: [{
            expand: true,
            cwd: 'templates',
            src: ['{,**/}*.scss'],
            dest: 'css',
            ext: '.css'
        }]
      }
    },

    sass_globbing: {
      your_target: {
        files: {
          'templates/pattern-lab/_components.scss': 'templates/pattern-lab/components/**/*.scss',
          'templates/pattern-lab/_variables.scss': 'templates/pattern-lab/variables/*.scss',
          'templates/pattern-lab/_core.scss': 'templates/pattern-lab/core/*.scss'
        },
        options: {
          useSingleQuotes: false,
          signature: '/* generated with grunt-sass-globbing */\n\n'
        }
      }
    },

    modernizr: {
      dist: {
        "crawl": false,
        "customTests": [],
        "dest": "libraries/modernizr/modernizr-custom.js",
        "tests": [
          "svg",
          "touchevents",
          "flexbox",
          "cssmask",
          "mediaqueries",
          "objectfit",
          "details"
        ],
        "options": [
          "setClasses"
        ],
        "uglify": true
      }
    },
    // this can be used for theme images as well .. now only moving pattern lab images | we need to move to images/patternlabb (this change has to be done in patternlab repo as well)
    imagemin: {
      dynamic: {
        files: [{
          expand: true,
          cwd: 'node_modules/@comicrelief/pattern-lab/images/',
          src: ['**/*.{png,jpg,gif}'],
          // using this path to match with patterlab images path | we can review this dest
          dest: '../../../../../../images'
        }]
      }
    },

    concat: {
        options: {
            separator: ';',
        },
        dist: {
            src: ['scripts/{,**/}*.js'],
            dest: 'js/campaign_base.js',
        },
    },

    bless: {
        css: {
            options: {
                'out-dir': 'ie9-css/',
                imports : false
            },
            files: {
                'ie9-css/styles': 'css/styles.css'
            }
        }
    },

    watch: {
      options: {
        livereload: true
      },
      sass: {
        files: ['templates/sass/{,**/}*.{scss,sass}'],
        tasks: ['sass'],
        options: {
          // Start a live reload server on the default port 35729
          livereload: true
        }
      },
      // images: {
      //   files: ['images/**']
      // },
      css: {
        files: ['css/{,**/}*.css']
      }
    },

    kss: {
      options: {
        verbose: true,
        css: 'node_modules/@comicrelief/pattern-lab/kss/kss-assets/kss.css',
        // builder from npm package can be used here
        builder: 'kss'
      },
      all: {
        options: {
          verbose: true,
          builder: 'kss',
          title: 'PatternLab',
          css: '../css/styles.css'
        },
        // you can choose the path to your own components here
        src: 'node_modules/@comicrelief/pattern-lab/sass/base/components',
        dest: 'styleguide'
      }
    },

    concurrent: {
      target: {
        tasks: ['connect', 'watch'],
        options: {
          logConcurrentOutput: true
        }
      }
    },

    connect: {
      dev: {
        port: 1337,
        base: 'dist'
      }
    },
    // * add backstopjs
    clean: {
      build: ['tests/visual/reference']
    }
  });

  grunt.file.expand('node_modules/grunt-*/tasks').forEach(grunt.loadTasks);

  grunt.registerTask('build', [
    // 'sass_globbing',
    'sass',
    // we need to remove modernizr downloading via composer first so we can enable it and let grunt build it
    // 'modernizr',
    'kss',
    'imagemin'
  ]);

  grunt.registerTask('watch:dev', [
    'concurrent:target'
  ]);

  grunt.registerTask('devserver', [
    'connect:dev'
  ]);
  
  grunt.registerTask('clean:test', [
    'clean'
  ]);
};
