'use strict';

module.exports = function (grunt) {

  var target = grunt.option('target') || 'profiles/cr/themes/custom/campaign_base/config.rb';

  grunt.initConfig({

    focus: {
      campaign_base: {
        exclude: ['rnd17']
      },
      rnd17: {
        exclude: ['campaign_base']
      }
    },
    watch: {
      options: {
        livereload: true,
        nospawn : true
      },
      campaign_base: {
        files: ['profiles/cr/themes/custom/campaign_base/sass/{,**/}*.{scss,sass}'],
        tasks: ['compass:dev','shell:styleguide']
      },
      rnd17: {
        files: ['themes/rnd17/sass/{,**/}*.{scss,sass}'],
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
            command: 'node_modules/kss/bin/kss-node --source profiles/cr/themes/custom/campaign_base/sass/ --destination profiles/cr/themes/custom/campaign_base/styleguide --css ../css/styles.css --verbose --title "Comic Relief PatternLab"'
        }
    },

    compass: {
      options: {
        config: target,
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

    compassMultiple: {
      all: {
        options: {
          multiple: [
            {
              config: 'profiles/cr/themes/custom/campaign_base/config.rb',
              sassDir: 'profiles/cr/themes/custom/campaign_base/sass',
              cssDir: 'profiles/cr/themes/custom/campaign_base/css'
            },
            {
              config: 'themes/rnd17/config.rb',
              sassDir: 'themes/rnd17/sass',
              cssDir: 'themes/rnd17/css'
            }
          ]
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
          compress: {}
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

  grunt.loadNpmTasks('grunt-compass-multiple');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-browser-sync');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-kss');
  grunt.loadNpmTasks('grunt-shell');
  grunt.loadNpmTasks('grunt-focus');

  grunt.registerTask('style', ['shell:styleguide']);
  grunt.registerTask('campaign_base', ['shell:styleguide', 'uglify:dev', 'focus:campaign_base']);
  grunt.registerTask('rnd17', ['shell:styleguide', 'uglify:dev', 'focus:rnd17']);

  grunt.registerTask('build', [
    'shell:styleguide',
    'concat',
    'uglify:dist',
    'compassMultiple'
  ]);

};
