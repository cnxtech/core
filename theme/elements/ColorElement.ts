import { _colors } from '../utils/colors';

const colorKeys = Object.keys(_colors);
const log       = require('debug')('elements:ColorElement');

export class ColorElement extends HTMLElement {
    static TAG = 'c-c';

    // static observedAttributes: string[] = []

    constructor() {
        super();
        Array.from(this.attributes).forEach(attr => {
            // log('attr', attr.name, attr.value, attr.value === '', colorKeys.includes(attr.name))
            if ( attr.value === '' && colorKeys.includes(attr.name) ) {
                // this.setAttribute('style', `color: ${_colors[ attr.name ]}`)
                this.style.color = _colors[attr.name];
                this.removeAttribute(attr.name);
            }
        })
    }

    connectedCallback() {
    }

    disconnectedCallback() {
    }

    attributeChangedCallback() {
    }

}
