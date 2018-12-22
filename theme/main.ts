///<reference path="types/index.d.ts"/>
import 'reflect-metadata'
import 'jquery';
import 'popper.js';
import 'bootstrap';
import 'metismenu';
import './styles/stylesheet.scss'
import './styles/prismjs.scss'
import { app } from './classes/Application';
import { ColorElement } from './elements/ColorElement';

const log = require('debug')('main');

$.fn.forEach = function (this: JQuery, callback) {
    return this.each(function (index, element) {
        callback($(this), index, element)
    });
}

$('div').forEach($el => {
    log('div foreach', { $el })
})


log('application', app);

window[ 'app' ]    = app;
window[ 'jQuery' ] = window[ '$' ] = $;


customElements.define(ColorElement.TAG, ColorElement)
