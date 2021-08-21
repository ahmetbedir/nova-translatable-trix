let mix = require('laravel-mix')
let path = require('path')

mix
  .setPublicPath('dist')
  .js('resources/js/field.js', 'js').vue()
  .sass('resources/sass/field.scss', 'css')
  .webpackConfig({
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js/'),
            },
        },
    })
