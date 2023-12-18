export var binSizes = [
    { key: 0.5, value: 'Half Tone' },
    { key: 1, value: 'One Tone' },
    { key: 2, value: 'Two Tone' },
];

export function getBinSizesValue(binSize) {
    return binSizes.find(bin => bin.key === binSize).value;
}
