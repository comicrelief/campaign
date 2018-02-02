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
        src: ['**/*.{png,jpg,gif}'],
        dest: 'images/patternlab'
      }
    },

    svgmin: {
      options: {
        plugins: [
          {removeViewBox: false},
          {removeUselessStrokeAndFill: false},
          {removeEmptyAttrs: false},
          {removeHiddenElems: false},
          {cleanupIDs: false}
        ]
      },
      dist: {
        files: [{
          expand: true,
          flatten: true,
          cwd: 'node_modules/@comicrelief/pattern-lab/sass/base/components',
          src: ['{,**/}*.svg'],
          dest: 'images'
        }]
      }
    },

    concat: {
      options: {
          separator: ';',
      },
      dist: {
          src: [
            // Import specific component js
            'node_modules/@comicrelief/pattern-lab/sass/base/components/navigation/js/main-nav.js',
            'node_modules/@comicrelief/pattern-lab/sass/base/components/media-block/js/media-block.js',
            // Drupal-specific custom js
            'scripts/{,**/}*.js'],
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
        tasks: ['sass','postcss:dist'],
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
    },

    postcss: {
      options: {
        map: true,
          processors: [
            require('autoprefixer')
          ]
      },
      
      dist: {
        src: ['css/styles.css', 'css/ie8.css', 'css/layout.css']
      }
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
    'imagemin',
    'postcss:dist'
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
