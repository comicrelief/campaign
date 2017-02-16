'use strict';

module.exports = function (grunt) {

  grunt.initConfig({

    watch: {
      options: {
        livereload: true,
        nospawn : true
      },
      campaign_base: {
        files: ['themes/custom/campaign_base/sass/{,**/}*.{scss,sass}'],
        tasks: ['compass:dev']
      },
      templates: {
        files: ['themes/custom/campaign_base/templates/{,**/}*.html.twig', 'themes/custom/campaign_base/sass/components/{,**/}*.hbs']
      },
      images: {
        files: ['themes/custom/campaign_base/images/**']
      },
      css: {
        files: ['themes/custom/campaign_base/css/{,**/}*.css']
        },
      js: {
        files: ['themes/custom/campaign_base/scripts/{,**/}*.js', '!js/{,**/}*.min.js'],
        tasks: ['concat:dist','uglify:dev'] //'jshint',
      }
    },

    concat: {
      options: {
        separator: ';',
      },
      dist: {
        src: ['themes/custom/campaign_base/scripts/{,**/}*.js'],
        dest: 'themes/custom/campaign_base/js/campaign_base.js',
      },
    },

    shell: {
      styleguide: {
        command: '../node_modules/kss/bin/kss-node --source themes/custom/campaign_base/sass/ --destination themes/custom/campaign_base/styleguide --template themes/custom/campaign_base/kss/ --verbose'
      },
    },

    compass: {
      options: {
        config: 'themes/custom/campaign_base/config.rb',
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

    bless: {
      css: {
        options: {
          'out-dir': 'themes/custom/campaign_base/ie9-css/'
        },
        files: {
          'themes/custom/campaign_base/ie9-css/styles': 'themes/custom/campaign_base/css/styles.css'
        }
      }
    },

    jshint: {
      options: {
        jshintrc: '.jshintrc'
      },
      all: ['themes/custom/campaign_base/scripts/{,**/}*.js', '!themes/custom/campaign_base/scripts/{,**/}*.min.js']
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
          cwd: 'themes/custom/campaign_base/js',
          dest: 'themes/custom/campaign_base/js',
          src: ['campaign_base.js', '!campaign_base.min.js'],
          rename: function(dest, src) {
            var folder = src.substring(0, src.lastIndexOf('/'));
            var filename = src.substring(src.lastIndexOf('/'), src.length);
            filename = filename.substring(0, filename.lastIndexOf('.'));
            return dest + '/' + folder + filename + '.min.js';
          }
        },
        ]
      },
      dist: {
        options: {
          mangle: false,
          compress: {}
        },
        files: [{
          expand: true,
          flatten: true,
          cwd: 'themes/custom/campaign_base/js',
          dest: 'themes/custom/campaign_base/js',
          src: ['campaign_base.js', '!campaign_base.min.js'],
          rename: function(dest, src) {
            var folder = src.substring(0, src.lastIndexOf('/'));
            var filename = src.substring(src.lastIndexOf('/'), src.length);
            filename = filename.substring(0, filename.lastIndexOf('.'));
            return dest + '/' + folder + filename + '.min.js';
          }
        },
        ]
      }
    }
  });

  grunt.file.expand('../node_modules/grunt-*/tasks').forEach(grunt.loadTasks);

  grunt.registerTask('style', ['shell:styleguide']);

  grunt.registerTask('build', [
    'shell:styleguide',
    'concat:dist',
    'uglify:dist',
    'compass:dist',
    'bless',
  ]);

};
