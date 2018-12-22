/*
 * Copyright (c) 2018. Codex Project
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author Robin Radic
 * @license https://codex-project.mit-license.org MIT License
 */

import { defer, resolve } from './promise';
import { Prism } from '../interfaces';


const log = require('debug')('core:get-prism')

let prismLoaded    = false;
let prismIsLoading = false
let prismPromise;

declare const Prism: Prism


export async function getPrism(): Promise<null> {
    await import(/* webpackChunkName: "prismjs" */'./prism');
    await import(/* webpackChunkName: "prismjs" */'../styles/prismjs.scss');
    return null
}

export function fixCodeBlocks(context: Element | Document | DocumentFragment) {
    $('code', context).forEach($code => {
        if ( $code.get(0).parentElement.tagName !== 'PRE' ) {
            return
        }
        let $pre  = $(this.parentElement);
        let lines = this.textContent.split('\n').length;

        if ( lines > 3 && $code.hasClass('language-bash') ) {
            if ( ! $code.hasClass('command-line') ) {
                $code.addClass('command-line')
            }
        }

        if ( $pre.hasClass('command-line') && $pre.hasClass('line-numbers') ) {
            $pre.removeClass('line-numbers')
        }
    })
}

function checkCodeBlocks(context: Element | Document | DocumentFragment): Element[] {
    let fix = [];
    $('pre:not(.line-numbers) code', context).forEach($code => {
        let $pre = $code.get(0).parentElement;
        if ( false === $pre.className.includes('language-') ) {
            fix.push($pre);
        }
    })
    $('.line-numbers', context).forEach(el => {
        if ( $('.line-numbers-rows', el).length === 0 ) {
            fix.push(el);
        }
    })
    return fix;
}

export function highlight(context: Element | Document | DocumentFragment): Promise<any> {
    let promise = defer()
    log('highlight', { context })
    getPrism()
        .then((prism: Prism) => {
            log('highlight getPrism then', prism, $('code', context))
            fixCodeBlocks(context)
            // prism.highlightAll(false)
            return resolve(prism);
        })
        .then((prism: Prism) => {
            log('highlight getPrism then 2', prism, $('code', context))
            let promise = defer();

            let interval = setInterval(() => {
                let fix = checkCodeBlocks(context);
                log('highlight getPrism then 2 fix', fix)
                if ( fix.length > 0 ) {
                    fixCodeBlocks(context)
                    return fix.forEach(el => {
                        prism.highlightElement(el, false)
                    })
                }
                clearInterval(interval);
                promise.resolve()
            }, 1000)

            return promise.promise;
        })
        .then(() => {
            promise.resolve()
        })
    return promise.promise;
}
