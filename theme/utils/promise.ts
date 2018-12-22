
/*
 * Copyright (c) 2018. Codex Project
 *
 * The license can be found in the package and online at https://codex-project.mit-license.org.
 *
 * @copyright 2018 Codex Project
 * @author Robin Radic
 * @license https://codex-project.mit-license.org MIT License
 */

export const defer = <T>() => new Deferred<T>();
export interface Thenable <R> {
    then <U> (onFulfilled?: (value: R) => U | Thenable<U>, onRejected?: (error: any) => U | Thenable<U>): Thenable<U>;
    then <U> (onFulfilled?: (value: R) => U | Thenable<U>, onRejected?: (error: any) => void): Thenable<U>;
}

export class Deferred<T> {
    promise: Promise<T>
    resolve: (value?: T | Thenable<T>) => Promise<T>
    reject: (error: T) => Promise<T>
    then: <U>(onFulfilled?: (value: T) => U | Thenable<U>, onRejected?: (error: any) => U | Thenable<U>) => Promise<U>;
    catch: <U>(onRejected?: (error: any) => U | Thenable<U>) => Promise<U>;

    constructor() {
        this.promise = new Promise((resolve: (value?: T | Thenable<T>) => void, reject: (error?: any) => void) => {
            this.resolve = resolve as any;
            this.reject  = reject as any;
        })

        this.then  = this.promise.then.bind(this.promise);
        this.catch = this.promise.catch.bind(this.promise)
    }
}
export const resolve = Promise.resolve.bind(Promise)
export const reject  = Promise.reject.bind(Promise)
