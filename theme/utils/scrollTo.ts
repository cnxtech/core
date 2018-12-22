/*
 * Copyright (c) 2018. Codex Project
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author Robin Radic
 * @license https://codex-project.mit-license.org MIT License
 */

//
// Smooth scroll-to inspired by:
// http://stackoverflow.com/a/24559613/728480
//

import { defer } from './promise';

export function scrollTo(scrollTo:string|number|HTMLElement, scrollOffset:number=0, scrollDuration:number = 1000) : Promise<any> {
    let def = defer();
    // polyfill
    if ("performance" in window == false) {
        (window as any)['performance'] = {};
    }

    if ("now" in window.performance == false) {
        Date.now = (Date.now || function () {  // thanks IE8
            return new Date().getTime();
        });


        let nowOffset = Date.now();

        if (performance.timing && performance.timing.navigationStart) {
            nowOffset = performance.timing.navigationStart
        }

        window.performance.now = function now() {
            return Date.now() - nowOffset;
        }
    }
    //
    // Set a default for where we're scrolling to
    //

    if (typeof scrollTo === 'string') {

        // Assuming this is a selector we can use to find an element
        let scrollToObj = $(scrollTo as string).get(0)

        if (scrollToObj && typeof scrollToObj.getBoundingClientRect === 'function') {
            scrollTo = window.pageYOffset + scrollToObj.getBoundingClientRect().top;
        } else {
            throw 'error: No element found with the selector "' + scrollTo + '"';
        }
    } else if (typeof scrollTo !== 'number') {

        // If it's nothing above and not an integer, we assume top of the window
        scrollTo = 0;
    } else {
        let scrollToObj = $(scrollTo as any).get(0)
        if (scrollToObj && typeof scrollToObj.getBoundingClientRect === 'function') {
            scrollTo = window.pageYOffset + scrollToObj.getBoundingClientRect().top;
        } else {
            throw 'error: No element found with the selector "' + scrollTo + '"';
        }
    }

    scrollTo += scrollOffset
    // Set this a bit higher

    const anchorHeightAdjust = 30;
    if (scrollTo > anchorHeightAdjust) {
        scrollTo = scrollTo - anchorHeightAdjust;
    }

    // Declarations

    const cosParameter = (window.pageYOffset - scrollTo) / 2;
    let scrollCount    = 0,
          oldTimestamp = window.performance.now();

    function step(newTimestamp) {

        let tsDiff = newTimestamp - oldTimestamp;

        // Performance.now() polyfill loads late so passed-in timestamp is a larger offset
        // on the first go-through than we want so I'm adjusting the difference down here.
        // Regardless, we would rather have a slightly slower animation than a big jump so a good
        // safeguard, even if we're not using the polyfill.

        if (tsDiff > 100) {
            tsDiff = 30;
        }

        scrollCount += Math.PI / (scrollDuration / tsDiff);

        // As soon as we cross over Pi, we're about where we need to be

        if (scrollCount >= Math.PI) {
            return def.resolve();
        }

        const moveStep = Math.round((scrollTo as number) + cosParameter + cosParameter * Math.cos(scrollCount));
        window.scrollTo(0, moveStep);
        oldTimestamp = newTimestamp;
        window.requestAnimationFrame(step);
    }

    window.requestAnimationFrame(step);
    return def.promise;
};
export default scrollTo
