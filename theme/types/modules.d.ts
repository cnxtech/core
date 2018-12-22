/// <reference types="node" />
/// <reference types="jquery" />
declare module '*.styl';
declare module '*.scss';
declare module '*.less';
declare module '*.json';


declare const DEV: boolean
declare const ENV: any
declare const PROD: boolean
declare const TEST: boolean
declare const APP_VERSION: string

declare module "*.css" {
    interface IClassNames {
        [className: string]: string;
    }
    const classNames: IClassNames;
    export = classNames;
}

declare namespace NodeJS {
    interface ProcessEnv {
        NODE_ENV: 'development' | 'production' | 'test'
        PUBLIC_URL: string
    }
}

declare interface JQuery {
    forEach(callback: ($el: JQuery, index: number, element: TElement) => void | false): this;
}

declare module '*.bmp' {
    const src: string;
    export default src;
}

declare module '*.gif' {
    const src: string;
    export default src;
}

declare module '*.jpg' {
    const src: string;
    export default src;
}

declare module '*.jpeg' {
    const src: string;
    export default src;
}

declare module '*.png' {
    const src: string;
    export default src;
}

declare module '*.svg' {
    import * as React from 'react';

    export const ReactComponent: React.SFC<React.SVGProps<SVGSVGElement>>;

    const src: string;
    export default src;
}

declare module '*.module.css' {
    const classes: { [ key: string ]: string };
    export default classes;
}

declare module '*.module.scss' {
    const classes: { [ key: string ]: string };
    export default classes;
}

declare module '*.module.sass' {
    const classes: { [ key: string ]: string };
    export default classes;
}
