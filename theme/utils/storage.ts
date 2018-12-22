var hasOwn     = Object.prototype.hasOwnProperty;
var class2type = {};

// if(window === undefined){
//     const window = {}
// }

'Boolean Number String Function Array Date RegExp Object'.split(' ').forEach(function (name) {
    class2type[ '[object ' + name + ']' ] = name.toLowerCase();
});

function type(obj) {
    return obj === null ? String(obj) : class2type[ toString.call(obj) ] || 'object'
}

function isPlainObject(obj) {
    if ( ! obj || type(obj) !== 'object' ) {
        return false
    }

    if ( obj.constructor &&
        ! hasOwn.call(obj, 'constructor') &&
        ! hasOwn.call(obj.constructor.prototype, 'isPrototypeOf') ) {
        return false
    }

    var key;
    for ( key in obj ) {}

    return key === undefined || hasOwn.call(obj, key)
}

function extend(...args) {
    var arguments$1 = args;

    var
        options, name, src, copy, copyIsArray, clone,
        target = arguments[ 0 ] || {},
        i      = 1,
        length = arguments.length,
        deep   = false;

    if ( typeof target === 'boolean' ) {
        deep   = target;
        target = arguments[ 1 ] || {};
        i      = 2;
    }

    if ( Object(target) !== target && type(target) !== 'function' ) {
        target = {};
    }

    if ( length === i ) {
        target = this;
        i --;
    }

    for ( ; i < length; i ++ ) {
        if ( (options = arguments$1[ i ]) !== null ) {
            for ( name in options ) {
                src  = target[ name ];
                copy = options[ name ];

                if ( target === copy ) {
                    continue
                }

                if ( deep && copy && (isPlainObject(copy) || (copyIsArray = type(copy) === 'array')) ) {
                    if ( copyIsArray ) {
                        copyIsArray = false;
                        clone       = src && type(src) === 'array' ? src : [];
                    }
                    else {
                        clone = src && isPlainObject(src) ? src : {};
                    }

                    target[ name ] = extend(deep, clone, copy);
                }
                else if ( copy !== undefined ) {
                    target[ name ] = copy;
                }
            }
        }
    }

    return target
}

function encode(string) {
    return encodeURIComponent(string)
}

function decode(string) {
    return decodeURIComponent(string)
}

function stringifyCookieValue(value) {
    return encode(value === Object(value) ? JSON.stringify(value) : '' + value)
}

function read(string) {
    if ( string === '' ) {
        return string
    }

    if ( string.indexOf('"') === 0 ) {
        // This is a quoted cookie as according to RFC2068, unescape...
        string = string.slice(1, - 1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
    }

    // Replace server-side written pluses with spaces.
    // If we can't decode the cookie, ignore it, it's unusable.
    // If we can't parse the cookie, ignore it, it's unusable.
    string = decode(string.replace(/\+/g, ' '));

    try {
        string = JSON.parse(string);
    }
    catch ( e ) {}

    return string
}

function set$1(key, val, opts?: { expires?: number | Date, path?: string, domain?: string, secure?: boolean }) {
    if ( opts === void 0 ) opts = {};

    var time = opts.expires;

    if ( typeof opts.expires === 'number' ) {
        time = new Date();
        time.setMilliseconds(time.getMilliseconds() + opts.expires * 864e+5);
    }

    document.cookie = [
        encode(key), '=', stringifyCookieValue(val),
        time ? '; expires=' + (time as Date).toUTCString() : '', // use expires attribute, max-age is not supported by IE
        opts.path ? '; path=' + opts.path : '',
        opts.domain ? '; domain=' + opts.domain : '',
        opts.secure ? '; secure' : ''
    ].join('');
}

function get(key?) {
    var
        result  = key ? undefined : {},
        cookies = document.cookie ? document.cookie.split('; ') : [],
        i       = 0,
        l       = cookies.length,
        parts,
        name,
        cookie;

    for ( ; i < l; i ++ ) {
        parts  = cookies[ i ].split('=');
        name   = decode(parts.shift());
        cookie = parts.join('=');

        if ( ! key ) {
            result[ name ] = cookie;
        }
        else if ( key === name ) {
            result = read(cookie);
            break
        }
    }

    return result
}

function remove(key, options) {
    set$1(key, '', extend(true, {}, options, {
        expires: - 1
    }));
}

function has(key) {
    return get(key) !== undefined
}

export interface ICookieStorage {
    set(key, val, opts?: { expires?: number | Date, path?: string, domain?: string, secure?: boolean })

    get(key?: string): any

    has(key?: string): boolean

    remove(key, options?: any)

    all(): any

}

export var CookieStorage:ICookieStorage = {
    get   : get,
    set   : set$1,
    has   : has,
    remove: remove,
    all   : function () { return get(); }
};

function encode$1(value) {
    if ( Object.prototype.toString.call(value) === '[object Date]' ) {
        return '__q_date|' + value.toUTCString()
    }
    if ( Object.prototype.toString.call(value) === '[object RegExp]' ) {
        return '__q_expr|' + value.source
    }
    if ( typeof value === 'number' ) {
        return '__q_numb|' + value
    }
    if ( typeof value === 'boolean' ) {
        return '__q_bool|' + (value ? '1' : '0')
    }
    if ( typeof value === 'string' ) {
        return '__q_strn|' + value
    }
    if ( typeof value === 'function' ) {
        return '__q_strn|' + value.toString()
    }
    if ( value === Object(value) ) {
        return '__q_objt|' + JSON.stringify(value)
    }

    // hmm, we don't know what to do with it,
    // so just return it as is
    return value
}

function decode$1(value) {
    var type, length, source;

    length = value.length;
    if ( length < 10 ) {
        // then it wasn't encoded by us
        return value
    }

    type   = value.substr(0, 8);
    source = value.substring(9);

    switch ( type ) {
        case '__q_date':
            return new Date(source)

        case '__q_expr':
            return new RegExp(source)

        case '__q_numb':
            return Number(source)

        case '__q_bool':
            return Boolean(source === '1')

        case '__q_strn':
            return '' + source

        case '__q_objt':
            return JSON.parse(source)

        default:
            // hmm, we reached here, we don't know the type,
            // then it means it wasn't encoded by us, so just
            // return whatever value it is
            return value
    }
}

function generateFunctions(fn) {
    return {
        local  : fn('local'),
        session: fn('session')
    }
}

var hasStorageItem     = generateFunctions(
    function (type) { return function (key) { return window[ type + 'Storage' ].getItem(key) !== null; }; }
);
var getStorageLength   = generateFunctions(
    function (type) { return function () { return window[ type + 'Storage' ].length; }; }
);
var getStorageItem     = generateFunctions(function (type) {
    var
        hasFn   = hasStorageItem[ type ],
        storage = window[ type + 'Storage' ];

    return function (key) {
        if ( hasFn(key) ) {
            return decode$1(storage.getItem(key))
        }
        return null
    }
});
var getStorageAtIndex  = generateFunctions(function (type) {
    var
        lengthFn  = getStorageLength[ type ],
        getItemFn = getStorageItem[ type ],
        storage   = window[ type + 'Storage' ];

    return function (index) {
        if ( index < lengthFn() ) {
            return getItemFn(storage.key(index))
        }
    }
});
var getAllStorageItems = generateFunctions(function (type) {
    var
        lengthFn  = getStorageLength[ type ],
        storage   = window[ type + 'Storage' ],
        getItemFn = getStorageItem[ type ];

    return function () {
        var
            result = {},
            key,
            length = lengthFn();

        for ( var i = 0; i < length; i ++ ) {
            key           = storage.key(i);
            result[ key ] = getItemFn(key);
        }

        return result
    }
});
var setStorageItem     = generateFunctions(function (type) {
    var storage = window[ type + 'Storage' ];
    return function (key, value) { storage.setItem(key, encode$1(value)); }
});
var removeStorageItem  = generateFunctions(function (type) {
    var storage = window[ type + 'Storage' ];
    return function (key) { storage.removeItem(key); }
});
var clearStorage       = generateFunctions(function (type) {
    var storage = window[ type + 'Storage' ];
    return function () { storage.clear(); }
});
var storageIsEmpty     = generateFunctions(function (type) {
    var getLengthFn = getStorageLength[ type ];
    return function () { return getLengthFn() === 0; }
});

export var LocalStorage = {
    has    : hasStorageItem.local,
    get    : {
        length: getStorageLength.local,
        item  : getStorageItem.local,
        index : getStorageAtIndex.local,
        all   : getAllStorageItems.local
    },
    set    : setStorageItem.local,
    remove : removeStorageItem.local,
    clear  : clearStorage.local,
    isEmpty: storageIsEmpty.local
};

export var SessionStorage = { // eslint-disable-line one-var
    has    : hasStorageItem.session,
    get    : {
        length: getStorageLength.session,
        item  : getStorageItem.session,
        index : getStorageAtIndex.session,
        all   : getAllStorageItems.session
    },
    set    : setStorageItem.session,
    remove : removeStorageItem.session,
    clear  : clearStorage.session,
    isEmpty: storageIsEmpty.session
};
