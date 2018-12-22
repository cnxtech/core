import { mixin } from '@radic/build-tools';
import { gulp, Gulpclass, GulpEnvMixin, Task } from '@radic/build-tools-gulp';
import { build, GulpWebpackMixin } from '@radic/build-tools-webpack';
import webpack from 'webpack';


interface Gulpfile extends GulpEnvMixin, GulpWebpackMixin {}

@Gulpclass(gulp)
@mixin(GulpEnvMixin)
@mixin(GulpWebpackMixin)
class Gulpfile {

    get webpack() { return require('./webpack.config') }

    @Task('default') default() {

    }

    @Task('dev:watch')
    async devWatch(cb) {
        this.dev();
        let chain = this.webpack.chain;
        chain.watch(true);
        webpack(chain.toConfig()).watch({}, (err, stats) => {
            if ( err ) return cb(err);
        })
    }
}
