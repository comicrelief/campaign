'use strict';

var tilde_importer = require('grunt-sass-tilde-importer');

module.exports = function (grunt) {

  grunt.initConfig({

    sass: {
      dist: {
        options: {
            outputStyle: 'compressed',
            sourceMap: false,
            includePaths: ['node_modules'],
            importer: tilde_importer
        },
        files: [{
            expand: true,
            cwd: 'sass',
            src: ['*.scss'],
            dest: 'css',
            ext: '.css'
        }]
      }
    },

    sass_globbing: {
      your_target: {
        files: {
          'sass/pattern-lab/_components.scss': 'sass/pattern-lab/components/**/*.scss',
          'sass/pattern-lab/_variables.scss': 'sass/pattern-lab/variables/*.scss',
          'sass/pattern-lab/_core.scss': 'sass/pattern-lab/core/*.scss'
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
    // Get images from pattern-lab
    imagemin: {
      files: {
        expand: true,
        flatten: true,
        cwd: 'node_modules/@comicrelief/pattern-lab/sass/base/components',
        src: ['**/*.{png,jpg,gif,svg}'],
        dest: 'images/patternlab'
      }
    },

    concat: {
      options: {
          separator: ';',
      },
      dist: {
          src: ['node_modules/smartmenus/src/jquery.smartmenus.js', 'scripts/{,**/}*.js'],
          dest: 'js/campaign_base.js',
      },
    },

    uglify: {
	    my_target: {
	      files: {
	        'js/campaign_base.min.js': 'js/campaign_base.js'
	      }
	    }
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
        files: ['sass/sass/{,**/}*.{scss,sass}'],
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
        builder: 'node_modules/@comicrelief/pattern-lab/kss'
      },
      all: {
        options: {
          verbose: true,
          builder: 'node_modules/@comicrelief/pattern-lab/kss',
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
    // only needed if you have your own components
    // 'sass_globbing',
    'sass',
    'concat',
    'uglify',
    'modernizr',
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
