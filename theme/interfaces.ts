import { Hooks, LanguageDefinition, Languages, Token, Util } from 'prismjs';



export type Omit<T, K> = Pick<T, Exclude<keyof T, K>>


export interface PlatformIs {
    name: string
    platform: string
    version: string
    versionNumber: number
    android?: boolean
    blackberry?: boolean
    bb?: boolean
    desktop?: boolean
    cros?: boolean
    ios?: boolean
    ipad?: boolean
    iphone?: boolean
    ipod?: boolean
    kindle?: boolean
    linux?: boolean
    mac?: boolean
    playbook?: boolean
    silk?: boolean
    chrome?: boolean
    opera?: boolean
    safari?: boolean
    win?: boolean
    mobile?: boolean
    winphone?: boolean
    ssr?: boolean
    opr?: boolean
    vivaldi?: boolean
    webkit?: boolean
    rv?: boolean
    iemobile?: boolean
    ie?: boolean
    edge?: boolean
    electron?: boolean
    chromeExt?: boolean
    cordova?: boolean

}

export interface IPlatform {
    is?: PlatformIs
    has?: { touch: boolean, webStorage: boolean }
    within?: { iframe: boolean }
}


export interface MaterialColors {

    'red': string
    'red-1': string
    'red-2': string
    'red-3': string
    'red-4': string
    'red-5': string
    'red-6': string
    'red-7': string
    'red-8': string
    'red-9': string
    'red-10': string
    'red-11': string
    'red-12': string
    'red-13': string
    'red-14': string
    'pink': string
    'pink-1': string
    'pink-2': string
    'pink-3': string
    'pink-4': string
    'pink-5': string
    'pink-6': string
    'pink-7': string
    'pink-8': string
    'pink-9': string
    'pink-10': string
    'pink-11': string
    'pink-12': string
    'pink-13': string
    'pink-14': string
    'purple': string
    'purple-1': string
    'purple-2': string
    'purple-3': string
    'purple-4': string
    'purple-5': string
    'purple-6': string
    'purple-7': string
    'purple-8': string
    'purple-9': string
    'purple-10': string
    'purple-11': string
    'purple-12': string
    'purple-13': string
    'purple-14': string
    'deep-purple': string
    'deep-purple-1': string
    'deep-purple-2': string
    'deep-purple-3': string
    'deep-purple-4': string
    'deep-purple-5': string
    'deep-purple-6': string
    'deep-purple-7': string
    'deep-purple-8': string
    'deep-purple-9': string
    'deep-purple-10': string
    'deep-purple-11': string
    'deep-purple-12': string
    'deep-purple-13': string
    'deep-purple-14': string
    'indigo': string
    'indigo-1': string
    'indigo-2': string
    'indigo-3': string
    'indigo-4': string
    'indigo-5': string
    'indigo-6': string
    'indigo-7': string
    'indigo-8': string
    'indigo-9': string
    'indigo-10': string
    'indigo-11': string
    'indigo-12': string
    'indigo-13': string
    'indigo-14': string
    'blue': string
    'blue-1': string
    'blue-2': string
    'blue-3': string
    'blue-4': string
    'blue-5': string
    'blue-6': string
    'blue-7': string
    'blue-8': string
    'blue-9': string
    'blue-10': string
    'blue-11': string
    'blue-12': string
    'blue-13': string
    'blue-14': string
    'light-blue': string
    'light-blue-1': string
    'light-blue-2': string
    'light-blue-3': string
    'light-blue-4': string
    'light-blue-5': string
    'light-blue-6': string
    'light-blue-7': string
    'light-blue-8': string
    'light-blue-9': string
    'light-blue-10': string
    'light-blue-11': string
    'light-blue-12': string
    'light-blue-13': string
    'light-blue-14': string
    'cyan': string
    'cyan-1': string
    'cyan-2': string
    'cyan-3': string
    'cyan-4': string
    'cyan-5': string
    'cyan-6': string
    'cyan-7': string
    'cyan-8': string
    'cyan-9': string
    'cyan-10': string
    'cyan-11': string
    'cyan-12': string
    'cyan-13': string
    'cyan-14': string
    'teal': string
    'teal-1': string
    'teal-2': string
    'teal-3': string
    'teal-4': string
    'teal-5': string
    'teal-6': string
    'teal-7': string
    'teal-8': string
    'teal-9': string
    'teal-10': string
    'teal-11': string
    'teal-12': string
    'teal-13': string
    'teal-14': string
    'green': string
    'green-1': string
    'green-2': string
    'green-3': string
    'green-4': string
    'green-5': string
    'green-6': string
    'green-7': string
    'green-8': string
    'green-9': string
    'green-10': string
    'green-11': string
    'green-12': string
    'green-13': string
    'green-14': string
    'light-green': string
    'light-green-1': string
    'light-green-2': string
    'light-green-3': string
    'light-green-4': string
    'light-green-5': string
    'light-green-6': string
    'light-green-7': string
    'light-green-8': string
    'light-green-9': string
    'light-green-10': string
    'light-green-11': string
    'light-green-12': string
    'light-green-13': string
    'light-green-14': string
    'lime': string
    'lime-1': string
    'lime-2': string
    'lime-3': string
    'lime-4': string
    'lime-5': string
    'lime-6': string
    'lime-7': string
    'lime-8': string
    'lime-9': string
    'lime-10': string
    'lime-11': string
    'lime-12': string
    'lime-13': string
    'lime-14': string
    'yellow': string
    'yellow-1': string
    'yellow-2': string
    'yellow-3': string
    'yellow-4': string
    'yellow-5': string
    'yellow-6': string
    'yellow-7': string
    'yellow-8': string
    'yellow-9': string
    'yellow-10': string
    'yellow-11': string
    'yellow-12': string
    'yellow-13': string
    'yellow-14': string
    'amber': string
    'amber-1': string
    'amber-2': string
    'amber-3': string
    'amber-4': string
    'amber-5': string
    'amber-6': string
    'amber-7': string
    'amber-8': string
    'amber-9': string
    'amber-10': string
    'amber-11': string
    'amber-12': string
    'amber-13': string
    'amber-14': string
    'orange': string
    'orange-1': string
    'orange-2': string
    'orange-3': string
    'orange-4': string
    'orange-5': string
    'orange-6': string
    'orange-7': string
    'orange-8': string
    'orange-9': string
    'orange-10': string
    'orange-11': string
    'orange-12': string
    'orange-13': string
    'orange-14': string
    'deep-orange': string
    'deep-orange-1': string
    'deep-orange-2': string
    'deep-orange-3': string
    'deep-orange-4': string
    'deep-orange-5': string
    'deep-orange-6': string
    'deep-orange-7': string
    'deep-orange-8': string
    'deep-orange-9': string
    'deep-orange-10': string
    'deep-orange-11': string
    'deep-orange-12': string
    'deep-orange-13': string
    'deep-orange-14': string
    'brown': string
    'brown-1': string
    'brown-2': string
    'brown-3': string
    'brown-4': string
    'brown-5': string
    'brown-6': string
    'brown-7': string
    'brown-8': string
    'brown-9': string
    'brown-10': string
    'brown-11': string
    'brown-12': string
    'brown-13': string
    'brown-14': string
    'grey-1': string
    'grey-2': string
    'grey-3': string
    'grey-4': string
    'grey-5': string
    'grey-6': string
    'grey-7': string
    'grey-8': string
    'grey-9': string
    'grey-10': string
    'grey-11': string
    'grey-12': string
    'grey-13': string
    'grey-14': string
    'blue-grey': string
    'blue-grey-1': string
    'blue-grey-2': string
    'blue-grey-3': string
    'blue-grey-4': string
    'blue-grey-5': string
    'blue-grey-6': string
    'blue-grey-7': string
    'blue-grey-8': string
    'blue-grey-9': string
    'blue-grey-10': string
    'blue-grey-11': string
    'blue-grey-12': string
    'blue-grey-13': string
    'blue-grey-14': string


    'red1': string
    'red2': string
    'red3': string
    'red4': string
    'red5': string
    'red6': string
    'red7': string
    'red8': string
    'red9': string
    'red10': string
    'red11': string
    'red12': string
    'red13': string
    'red14': string
    'pink1': string
    'pink2': string
    'pink3': string
    'pink4': string
    'pink5': string
    'pink6': string
    'pink7': string
    'pink8': string
    'pink9': string
    'pink10': string
    'pink11': string
    'pink12': string
    'pink13': string
    'pink14': string
    'purple1': string
    'purple2': string
    'purple3': string
    'purple4': string
    'purple5': string
    'purple6': string
    'purple7': string
    'purple8': string
    'purple9': string
    'purple10': string
    'purple11': string
    'purple12': string
    'purple13': string
    'purple14': string
    'deepPurple': string
    'deepPurple1': string
    'deepPurple2': string
    'deepPurple3': string
    'deepPurple4': string
    'deepPurple5': string
    'deepPurple6': string
    'deepPurple7': string
    'deepPurple8': string
    'deepPurple9': string
    'deepPurple10': string
    'deepPurple11': string
    'deepPurple12': string
    'deepPurple13': string
    'deepPurple14': string
    'indigo1': string
    'indigo2': string
    'indigo3': string
    'indigo4': string
    'indigo5': string
    'indigo6': string
    'indigo7': string
    'indigo8': string
    'indigo9': string
    'indigo10': string
    'indigo11': string
    'indigo12': string
    'indigo13': string
    'indigo14': string
    'blue1': string
    'blue2': string
    'blue3': string
    'blue4': string
    'blue5': string
    'blue6': string
    'blue7': string
    'blue8': string
    'blue9': string
    'blue10': string
    'blue11': string
    'blue12': string
    'blue13': string
    'blue14': string
    'lightBlue': string
    'lightBlue1': string
    'lightBlue2': string
    'lightBlue3': string
    'lightBlue4': string
    'lightBlue5': string
    'lightBlue6': string
    'lightBlue7': string
    'lightBlue8': string
    'lightBlue9': string
    'lightBlue10': string
    'lightBlue11': string
    'lightBlue12': string
    'lightBlue13': string
    'lightBlue14': string
    'cyan1': string
    'cyan2': string
    'cyan3': string
    'cyan4': string
    'cyan5': string
    'cyan6': string
    'cyan7': string
    'cyan8': string
    'cyan9': string
    'cyan10': string
    'cyan11': string
    'cyan12': string
    'cyan13': string
    'cyan14': string
    'teal1': string
    'teal2': string
    'teal3': string
    'teal4': string
    'teal5': string
    'teal6': string
    'teal7': string
    'teal8': string
    'teal9': string
    'teal10': string
    'teal11': string
    'teal12': string
    'teal13': string
    'teal14': string
    'green1': string
    'green2': string
    'green3': string
    'green4': string
    'green5': string
    'green6': string
    'green7': string
    'green8': string
    'green9': string
    'green10': string
    'green11': string
    'green12': string
    'green13': string
    'green14': string
    'lightGreen': string
    'lightGreen1': string
    'lightGreen2': string
    'lightGreen3': string
    'lightGreen4': string
    'lightGreen5': string
    'lightGreen6': string
    'lightGreen7': string
    'lightGreen8': string
    'lightGreen9': string
    'lightGreen10': string
    'lightGreen11': string
    'lightGreen12': string
    'lightGreen13': string
    'lightGreen14': string
    'lime1': string
    'lime2': string
    'lime3': string
    'lime4': string
    'lime5': string
    'lime6': string
    'lime7': string
    'lime8': string
    'lime9': string
    'lime10': string
    'lime11': string
    'lime12': string
    'lime13': string
    'lime14': string
    'yellow1': string
    'yellow2': string
    'yellow3': string
    'yellow4': string
    'yellow5': string
    'yellow6': string
    'yellow7': string
    'yellow8': string
    'yellow9': string
    'yellow10': string
    'yellow11': string
    'yellow12': string
    'yellow13': string
    'yellow14': string
    'amber1': string
    'amber2': string
    'amber3': string
    'amber4': string
    'amber5': string
    'amber6': string
    'amber7': string
    'amber8': string
    'amber9': string
    'amber10': string
    'amber11': string
    'amber12': string
    'amber13': string
    'amber14': string
    'orange1': string
    'orange2': string
    'orange3': string
    'orange4': string
    'orange5': string
    'orange6': string
    'orange7': string
    'orange8': string
    'orange9': string
    'orange10': string
    'orange11': string
    'orange12': string
    'orange13': string
    'orange14': string
    'deepOrange': string
    'deepOrange1': string
    'deepOrange2': string
    'deepOrange3': string
    'deepOrange4': string
    'deepOrange5': string
    'deepOrange6': string
    'deepOrange7': string
    'deepOrange8': string
    'deepOrange9': string
    'deepOrange10': string
    'deepOrange11': string
    'deepOrange12': string
    'deepOrange13': string
    'deepOrange14': string
    'brown1': string
    'brown2': string
    'brown3': string
    'brown4': string
    'brown5': string
    'brown6': string
    'brown7': string
    'brown8': string
    'brown9': string
    'brown10': string
    'brown11': string
    'brown12': string
    'brown13': string
    'brown14': string
    'grey': string
    'grey1': string
    'grey2': string
    'grey3': string
    'grey4': string
    'grey5': string
    'grey6': string
    'grey7': string
    'grey8': string
    'grey9': string
    'grey10': string
    'grey11': string
    'grey12': string
    'grey13': string
    'grey14': string
    'blueGrey': string
    'blueGrey1': string
    'blueGrey2': string
    'blueGrey3': string
    'blueGrey4': string
    'blueGrey5': string
    'blueGrey6': string
    'blueGrey7': string
    'blueGrey8': string
    'blueGrey9': string
    'blueGrey10': string
    'blueGrey11': string
    'blueGrey12': string
    'blueGrey13': string
    'blueGrey14': string
}

export interface BreakpointDictionary<T> {
    xs: T
    sm: T
    md: T
    lg: T
    xl: T
    xxl: T
}

export type Breakpoint = keyof BreakpointDictionary<any>


export interface Prism {
    util: Util;
    languages: Languages;
    plugins: any;
    hooks: Hooks;

    /**
     * This is the most high-level function in Prism’s API. It fetches all the elements that have a .language-xxxx class and
     * then calls Prism.highlightElement() on each one of them.
     *
     * @param async Whether to use Web Workers to improve performance and avoid blocking the UI when highlighting
     * very large chunks of code. False by default.
     * @param callback An optional callback to be invoked after the highlighting is done. Mostly useful when async
     * is true, since in that case, the highlighting is done asynchronously.
     */
    highlightAll(async: boolean, callback?: (element: Element) => void): void;

    /**
     * Highlights the code inside a single element.
     *
     * @param element The element containing the code. It must have a class of language-xxxx to be processed,
     * where xxxx is a valid language identifier.
     * @param async Whether to use Web Workers to improve performance and avoid blocking the UI when
     * highlighting very large chunks of code. False by default.
     * @param callback An optional callback to be invoked after the highlighting is done.
     * Mostly useful when async is true, since in that case, the highlighting is done asynchronously.
     */
    highlightElement(element: Element, async: boolean, callback?: (element: Element) => void): void;

    /**
     * Low-level function, only use if you know what you’re doing. It accepts a string of text as input and the language
     * definitions to use, and returns a string with the HTML produced.
     *
     * @param text A string with the code to be highlighted.
     * @param grammer - An object containing the tokens to use. Usually a language definition like
     * Prism.languages.markup
     * @returns The highlighted HTML
     */
    highlight(text: string, grammer: LanguageDefinition, language?: LanguageDefinition): string;

    /**
     * This is the heart of Prism, and the most low-level function you can use. It accepts a string of text as input and the
     * language definitions to use, and returns an array with the tokenized code. When the language definition includes
     * nested tokens, the function is called recursively on each of these tokens. This method could be useful in other
     * contexts as well, as a very crude parser.
     *
     * @param  text A string with the code to be highlighted.
     * @param  grammar An object containing the tokens to use. Usually a language definition like
     * Prism.languages.markup
     * @returns An array of strings, tokens (class Prism.Token) and other arrays.
     */
    tokenize(text: string, grammar: LanguageDefinition): Array<Token | string>;

    fileHighlight(): void;
}
