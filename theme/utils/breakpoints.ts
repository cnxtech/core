import { BreakpointDictionary } from 'interfaces';

export const breakpoints: BreakpointDictionary<number> = {
    xs : 480,
    sm : 576,
    md : 768,
    lg : 992,
    xl : 1200,
    xxl: 1600
}

export const breakpointsPx: BreakpointDictionary<string> = {
    xs : '480px',
    sm : '576px',
    md : '768px',
    lg : '992px',
    xl : '1200px',
    xxl: '1600px'
}