'use strict';

module.exports = function (grunt) {

    grunt.initConfig({
        watch: {
            options: {
                livereload: true,
                nospawn: true
            },
            campaign_base: {
                files: ['templates/{,**/}*.{scss,sass}'],
                tasks: ['compass:dev']
            },
            templates: {
                files: ['templates/{,**/}*.html.twig', 'templates/components/{,**/}*.twig']
            },
            images: {
                files: ['images/**']
            },
            css: {
                files: ['css/{,**/}*.css']
            },
            js: {
                files: ['scripts/{,**/}*.js', '!js/{,**/}*.min.js'],
                tasks: ['concat:dist', 'uglify:dev'] //'jshint',
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

        shell: {
            styleguide: {
                command: 'node_modules/kss/bin/kss --builder kss --extend-drupal8 --source templates/ --destination styleguide --title "Campaign Styleguide" --homepage "styleguide.md"'
            },
        },

        compass: {
            options: {
                config: 'config.rb',
                bundleExec: true,
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
                    'out-dir': 'ie9-css/',
                    imports : false
                },
                files: {
                    'ie9-css/styles': 'css/styles.css'
                }
            }
        },

        jshint: {
            options: {
                jshintrc: '.jshintrc'
            },
            all: ['scripts/{,**/}*.js', '!scripts/{,**/}*.min.js']
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
                    cwd: 'js',
                    dest: 'js',
                    src: ['campaign_base.js', '!campaign_base.min.js'],
                    rename: function (dest, src) {
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
                    cwd: 'js',
                    dest: 'js',
                    src: ['campaign_base.js', '!campaign_base.min.js'],
                    rename: function (dest, src) {
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

    grunt.file.expand('node_modules/grunt-*/tasks').forEach(grunt.loadTasks);

    grunt.registerTask('style', ['shell:styleguide']);

    grunt.registerTask('build', [
        'shell:styleguide',
        'concat:dist',
        'uglify:dist',
        'compass:dist',
        'bless',
    ]);

};
