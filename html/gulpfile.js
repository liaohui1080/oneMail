/**
 * Created by liaohui1080 on 15/12/27.
 */
var gulp = require('gulp');
// 引入组件
var htmlmin = require('gulp-htmlmin'), //html压缩
    imagemin = require('gulp-imagemin'),//图片压缩
    pngcrush = require('imagemin-pngcrush'),
    minifycss = require('gulp-minify-css'),//css压缩
    jshint = require('gulp-jshint'),//js检测
    uglify = require('gulp-uglify'),//js压缩
    concat = require('gulp-concat'),//文件合并
    rename = require('gulp-rename'),//文件更名
    notify = require('gulp-notify');//提示信息



var cssArr = [
    'src/css/chushihua.css',
    'node_modules/angular-loading-bar/build/loading-bar.css',
    'bower_components/bootstrap/dist/css/bootstrap.css',
    'bower_components/bootstrap/dist/css/bootstrap-theme.css',
    'siren/dropify/dist/css/dropify.min.css',
    'src/css/css.css'
];

var jsArr=[

    'bower_components/jquery/dist/jquery.min.js',
    'bower_components/angular/angular.js',
    'bower_components/bootstrap/dist/js/bootstrap.min.js',
    'bower_components/store/dist/store2.min.js',
    'siren/dropify/dist/js/dropify.min.js',
    'bower_components/angular-cookies/angular-cookies.min.js',
    'bower_components/ng-file-upload/ng-file-upload.min.js',
    'bower_components/angular-ui-router/release/angular-ui-router.js',
    //'bower_components/textAngular/dist/textAngular-sanitize.min.js',
    'siren/wangEditor/dist/js/wangEditor-1.3.12.min.js',
    'node_modules/angular-loading-bar/build/loading-bar.min.js'
];






// 合并、压缩、重命名css
gulp.task('css', function() {
    return gulp.src(cssArr)
        .pipe(concat('main.css'))
        .pipe(gulp.dest('build/css'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(minifycss())
        .pipe(gulp.dest('build/css'))
        .pipe(notify({ message: 'css 压缩成功' }))
});

// 检查js
gulp.task('lint', function() {
    return gulp.src(jsArr)
        .pipe(jshint())
        .pipe(jshint.reporter('default'))
        .pipe(notify({ message: 'lint js检测通过' }));
});

// 合并、压缩js文件
gulp.task('js', function() {
    return gulp.src(jsArr)
        .pipe(concat('main.js'))
        .pipe(gulp.dest('build/js'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(uglify())
        .pipe(gulp.dest('build/js'))
        .pipe(notify({ message: 'js 压缩成功' }));
});

// 把index.js复制到 bulid 文件夹
gulp.task('indexjs', function() {
    return gulp.src('src/js/index.js')

        .pipe(gulp.dest('build/js'))
        .pipe(notify({ message: 'index.js复制成功' }));
});


// 压缩图片
gulp.task('img', function() {
    return gulp.src('src/image/*')
        .pipe(imagemin({
            progressive: true,
            svgoPlugins: [{removeViewBox: false}],
            use: [pngcrush({quality: '10-50'})]
        }))
        .pipe(gulp.dest('./build/image/'))
        .pipe(notify({ message: 'img 压缩成功' }));
});

// 压缩html
gulp.task('html', function() {
    return gulp.src('src/**/*.html')
        .pipe(htmlmin({collapseWhitespace: true}))
        .pipe(gulp.dest('./build'))
        .pipe(notify({ message: 'html 压缩成功' }));

});



// 默认任务
gulp.task('default', function(){
    gulp.run('css','lint','js','indexjs','img','html');

    // 监听html文件变化
    gulp.watch('src/**/*.html', ['html']);

    // Watch .css files
    gulp.watch('src/css/*.css', ['css']);

    // Watch .js files
    gulp.watch('src/js/*.js', ['lint', 'js','indexjs']);

    // Watch image files
    gulp.watch('src/image/*', ['img']);
});
