//Gruntfile
module.exports = function(grunt) {
  grunt.initConfig({
    concat: {
      app: {
        files: {
          './public/assets/js/app.js': [
            './public/packages/bower/jquery/dist/jquery.js',
            './public/packages/bower/bootstrap/dist/js/bootstrap.js',
            './public/packages/js/base.js'
          ],
          './public/assets/js/validate.js': [
            './public/packages/bower/bootstrapValidator/dist/js/bootstrapValidator.js',
          ],
          './public/assets/js/map.js': [
            './public/packages/bower/bower-jvectormap/jquery-jvectormap-1.2.2.min.js',
            './public/packages/bower/bower-jvectormap/jquery-jvectormap-world-mill-en.js'
          ],
          './public/assets/js/chart.js': [
            './public/packages/bower/highcharts/js/highcharts.src.js'
          ]
        }
      },
    },
    cssmin: {
      app: {
        files: {
          './public/assets/css/app.css': [
            './public/packages/bower/bootstrap/dist/css/bootstrap.css',
            './public/packages/bower/font-awesome/css/font-awesome.css',
            './public/packages/css/base.css'
          ],
          './public/assets/css/validate.css': [
            './public/packages/bower/bootstrapValidator/dist/css/bootstrapValidator.css',
          ],
          './public/assets/css/map.css': [
            './public/packages/bower/bower-jvectormap/jquery-jvectormap-1.2.2.css'
          ]
        }
      }
    },
    uglify: {
      app: {
        files: {
          './public/assets/js/app.js': './public/assets/js/app.js',
          './public/assets/js/validate.js': './public/assets/js/validate.js',
          './public/assets/js/map.js': './public/assets/js/map.js',
          './public/assets/js/chart.js': './public/assets/js/chart.js'
        }
      },
    },
    copy: {
      app: {
        files: [
          {
            expand: true,
            flatten: true,
            cwd: './public/packages/bower/font-awesome/fonts/',
            src: ['**'], 
            dest: './public/assets/fonts/', 
            filter: 'isFile'
          },
          // {
          //   expand: true,
          //   flatten: true,
          //   cwd: './public/packages/bower/bootstrap/fonts/',
          //   src: ['**'], 
          //   dest: './public/assets/fonts/', 
          //   filter: 'isFile'
          // },
        ]
      }
    },
    watch: {
      app: {
        files: [
        ],   
        tasks: ['concat:app','uglify:app','cssmin:app'],
        options: {
          livereload: true
        }
      },
    }
  });

  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-cssmin');

  grunt.registerTask('watch', ['watch']);
  grunt.registerTask('default', ['concat:app','uglify:app','cssmin:app']);

};