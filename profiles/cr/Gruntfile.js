'use strict';

module.exports = function (grunt) {

  var target = grunt.option('target') || 'themes/custom/campaign_base/config.rb';
  // var theme = grunt.option('target') || 'profiles/cr/themes/custom/campaign_base/';

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
        files: ['themes/custom/campaign_base/sass/{,**/}*.{scss,sass}'],
        tasks: ['compass:dev','shell:campaign_styleguide','shell:ie9_styles']
      },
      rnd17: {
        files: ['../../themes/rnd17/sass/{,**/}*.{scss,sass}'],
        tasks: ['compass:dev','shell:rnd17_styleguide']
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
        tasks: ['concat:campaign_base','uglify:dev'] //'jshint',
      }
    },

    concat: {
      options: {
        separator: ';',
      },
      campaign_base: {
        src: ['themes/custom/campaign_base/scripts/{,**/}*.js'],
        dest: 'themes/custom/campaign_base/js/campaign_base.js',
      },
      rnd17: {
        src: ['../../themes/rnd17/scripts/{,**/}*.js'],
        dest: '../../themes/rnd17/js/rnd17.js',
      },
    },

    shell: {
        campaign_styleguide: {
            command: '../../node_modules/kss/bin/kss-node --source themes/custom/campaign_base/sass/ --destination themes/custom/campaign_base/styleguide --template themes/custom/campaign_base/kss/ --verbose'
        },
        rnd17_styleguide: {
            command: '../../node_modules/kss/bin/kss-node --source ../../themes/rnd17/sass/ --destination ../../themes/rnd17/styleguide --css ../css/styles.css --verbose --title "Red Nose Day PatternLab"'
        },
        ie9_styles: {
            command: 'blessc chunk themes/custom/campaign_base/css/styles.css --out-dir themes/custom/campaign_base/ie9-css/'
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
              config: 'themes/custom/campaign_base/config.rb'
            },
            {
              config: '../../themes/rnd17/config.rb'
            }
          ]
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
        {
          expand: true,
          flatten: true,
          cwd: '../../themes/rnd17/js',
          dest: '../../themes/rnd17/js',
          src: ['rnd17.js', '!rnd17.min.js'],
          rename: function(dest, src) {
            var folder = src.substring(0, src.lastIndexOf('/'));
            var filename = src.substring(src.lastIndexOf('/'), src.length);
            filename = filename.substring(0, filename.lastIndexOf('.'));
            return dest + '/' + folder + filename + '.min.js';
          }
        }
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
        {
          expand: true,
          flatten: true,
          cwd: '../../themes/rnd17/js',
          dest: '../../themes/rnd17/js',
          src: ['rnd17.js', '!rnd17.min.js'],
          rename: function(dest, src) {
            var folder = src.substring(0, src.lastIndexOf('/'));
            var filename = src.substring(src.lastIndexOf('/'), src.length);
            filename = filename.substring(0, filename.lastIndexOf('.'));
            return dest + '/' + folder + filename + '.min.js';
          }
        }
        ]
      }
    }
  });

  grunt.file.expand('../../node_modules/grunt-*/tasks').forEach(grunt.loadTasks);

  grunt.registerTask('style', ['shell:styleguide']);
  grunt.registerTask('campaign_base', ['shell:campaign_styleguide', 'uglify:dev', 'focus:campaign_base']);
  grunt.registerTask('rnd17', ['shell:rnd17_styleguide', 'uglify:dev', 'focus:rnd17']);

  grunt.registerTask('build', [
    'shell:campaign_styleguide',
    'concat:campaign_base',
    'uglify:dist',
    'compass:dist',
    'shell:ie9_styles'
  ]);

  grunt.registerTask('build_all', [
    'shell:campaign_styleguide',
    'shell:rnd17_styleguide',
    'concat',
    'uglify:dist',
    'compassMultiple',
    'shell:ie9_styles'
  ]);

};
