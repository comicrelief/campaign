/**
 * @file
 * Grunt task to build form documentation.
 */

module.exports = function (grunt) {

  grunt.initConfig({
    mkdocs: {
      dist: {
        src: '.',
        options: {
          clean: true
        }
      }
    },
    'gh-pages': {
      options: {
        base: 'site',
        repo: 'https://github.com/jrockowitz/yamlform.git',
        clone: 'gh-pages',
        message: 'Deploying changes to GitHub'
      },
      src: ['**']
    },
    shell: {
      'mkdocs-serve': {
        command: 'mkdocs serve'
      },
      'mkdocs-cleanup': {
        command: '[ -d site ] && rm -Rf site'
      },
      'gh-pages-cleanup': {
        command: '[ -d gh-pages ] && rm -Rf gh-pages'
      },
      'docs-open-local': {
        command: '(sleep 5 && open http://127.0.0.1:8000/)&'
      },
      'docs-open-remote': {
        command: 'open http://thebigbluehouse.com/yamlform'
      }
    }
  });

  // Load tasks.
  grunt.loadNpmTasks('grunt-shell');
  grunt.loadNpmTasks('grunt-mkdocs');
  grunt.loadNpmTasks('grunt-gh-pages');

  // Register tasks.
  grunt.registerTask('docs-serve', ['shell:docs-open-local', 'shell:mkdocs-serve', 'shell:mkdocs-cleanup']);
  grunt.registerTask('docs-deploy', ['mkdocs', 'gh-pages', 'shell:mkdocs-cleanup', 'shell:gh-pages-cleanup', 'shell:docs-open-remote']);
};
