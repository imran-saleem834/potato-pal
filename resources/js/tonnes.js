export var binSizes = [
    { value: 0.5, label: 'Half Tone' },
    { value: 1, label: 'One Tone' },
    { value: 2, label: 'Two Tone' },
];

export function getBinSizesValue(binSize) {
    return binSizes.find(bin => bin.key === binSize).value;
}
