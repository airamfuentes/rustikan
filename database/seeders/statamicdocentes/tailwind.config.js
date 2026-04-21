export default {
    content: [
        './resources/**/*.antlers.html',
        './resources/**/*.antlers.php',
        './resources/**/*.blade.php',
        './resources/**/*.vue',
        './content/**/*.md',
    ],
    safelist: [
        'grid-cols-2',
        'object-cover',
        'shrink-0',
        'flex-1',
        'items-start',
        'gap-6',
        'gap-4',
        'gap-2',
    ],
    theme: {
        extend: {},
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
};
