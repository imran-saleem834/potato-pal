export var binSizes = [
    { value: 500, label: 'Half Tone' },
    { value: 1000, label: 'One Tone' },
    { value: 2000, label: 'Two Tone' },
];

export function getBinSizesValue(binSize) {
    return binSizes.find(bin => bin.value === binSize).label;
}
