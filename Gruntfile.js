module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        concat: {
            options: {
                separator: ';\n'
            },
            project: {
                src: ['js/**/*.js'],
                dest: 'web/js/<%= pkg.name %>.js'
            },
            vendors: {
                src: [
                    'components/jquery/dist/jquery.min.js',
                    'components/lodash/dist/lodash.min.js',
                    'components/angular/angular.min.js',
                    'components/angular-ui-utils/ui-utils.min.js',
                    'components/angular-bootstrap/ui-bootstrap.min.js',
                    'components/angular-bootstrap/ui-bootstrap-tpls.min.js',
                    'components/angular-animate/angular-animate.min.js'
                ],
                dest: 'web/js/vendors.js'
            }
        },
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n',
                mangle: false
            },
            dist: {
                files: {
                    'web/js/<%= pkg.name %>.min.js': ['<%= concat.project.dest %>'],
                    'web/js/vendors.min.js': ['<%= concat.vendors.dest %>']
                }
            }
        },
        jshint: {
            files: ['Gruntfile.js', 'js/**/*.js'],
            options: {
                // options here to override JSHint defaults
                globals: {
                    jQuery: true,
                    console: true,
                    module: true,
                    document: true
                }
            }
        },
        less: {
            dev: {
                options: {
                    compress: true,
                    yuicompress: true,
                    optimization: 2
                },
                files: {
                    "web/css/<%= pkg.name %>.css": "less/main.less"
                }
            }
        },
        copy: {
            bootstrap_fonts: {
                files: [
                    {expand: true, cwd: 'components/bootstrap/fonts', src: ['**'], dest: 'web/fonts/'}
                ]
            },
            dev_js: {
                files: {
                    'web/js/<%= pkg.name %>.min.js': ['<%= concat.project.dest %>'],
                    'web/js/vendors.min.js': ['<%= concat.vendors.dest %>']
                }
            }
        },
        watch: {
            css: {
                files: ['less/**/*.less'],
                tasks: ['less'],
                options: {
                    nospawn: true
                }
            },
            js: {
                files: ['<%= jshint.files %>'],
                tasks: ['jshint', 'concat', 'copy:dev_js']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-copy');

    grunt.registerTask('test', ['jshint']);

    grunt.registerTask('prod', ['concat', 'uglify', 'less', 'copy:bootstrap_fonts']);
    grunt.registerTask('dev', ['jshint', 'concat', 'copy:dev_js', 'less', 'copy:bootstrap_fonts']);

    grunt.registerTask('default', ['dev', 'watch']);

};
