const manifestIcons = [
    {
        src: "/images/pwa-64x64.png",
        sizes: "64x64",
        type: "image/png"
    },
    {
        src: "/images/pwa-192x192.png",
        sizes: "192x192",
        type: "image/png"
    },
    {
        src: "/images/pwa-512x512.png",
        sizes: "512x512",
        type: "image/png",
        purpose: 'any'
    },
    {
        src: "/images/maskable-icon-512x512.png",
        sizes: "512x512",
        type: "image/png",
        purpose: "maskable"
    }
];

const publicIcons = [
    { src: '/images/favicon.ico' },
    // { src: '/favicon.svg' },
    { src: '/images/apple-touch-icon-180x180.png' }
]

const additionalImages = [
    { src: '/images/screenshot-1.webp' },
    { src: '/images/screenshot-2.webp' },
    { src: '/images/screenshot-3.webp' },
    { src: '/images/screenshot-4.webp' },
];

const pwaConfiguration = {
    // buildBase: '/build/',
    // scope: '/',
    // base: '/',
    // registerType: 'autoUpdate',
    registerType: 'prompt',
    devOptions: {
        enabled: false,
        // navigateFallbackAllowlist: [/^index.php$/]
    },
    includeAssets: [],
    workbox: {
        clientsClaim: true,
        skipWaiting: true,
        globPatterns: ['**\/*.{js,css,html,ico,jpg,png,svg,woff,woff2,ttf,eot}'],
        navigateFallback: '/',
        navigateFallbackDenylist: [/^\/telescope/],
        additionalManifestEntries: [
            { url: '/', revision: `${Date.now()}` },
            ...manifestIcons.map((i) => {
                return { url: i.src, revision: `${Date.now()}` }
            }),
            ...publicIcons.map((i) => {
                return { url: i.src, revision: `${Date.now()}` }
            }),
            ...additionalImages.map((i) => {
                return { url: i.src, revision: `${Date.now()}` }
            })
        ],
        maximumFileSizeToCacheInBytes: 3000000
    },
    manifest: {
        name: 'Potato Pal Cool Store',
        short_name: 'Potato Pal',
        description: 'Potato Pal cool store',
        theme_color: '#FFFFFF',
        background_color: '#FFFFFF',
        orientation: 'portrait',
        id: 'app.cherryhillcoolstores.net.au',
        start_url: '/',
        scope: '/',
        display_override: ["window-controls-overlay"],
        display: 'fullscreen',
        dir: "ltr",
        categories: [
            "food",
            "business",
            "productivity"
        ],
        icons: [...manifestIcons],
        launch_handler: {
            client_mode: "auto"
        },
        handle_links: "preferred",
        screenshots: [
            {
                "src": "/images/screenshot-1.webp",
                "sizes": "1080x2000",
                "type": "image/webp",
                "form_factor": "wide",
                "label": "Login"
            },
            {
                "src": "/images/screenshot-2.webp",
                "sizes": "1080x2000",
                "type": "image/webp",
                "form_factor": "wide",
                "label": "Home"
            },
            {
                "src": "/images/screenshot-3.webp",
                "sizes": "1080x2000",
                "type": "image/webp",
                "form_factor": "wide",
                "label": "User View"
            },
            {
                "src": "/images/screenshot-4.webp",
                "sizes": "1080x2000",
                "type": "image/webp",
                "form_factor": "wide",
                "label": "Receival List"
            },
            {
                "src": "/images/screenshot-1.webp",
                "sizes": "1080x2000",
                "type": "image/webp",
                "form_factor": "narrow",
                "label": "Login"
            },
            {
                "src": "/images/screenshot-2.webp",
                "sizes": "1080x2000",
                "type": "image/webp",
                "form_factor": "narrow",
                "label": "Home"
            },
            {
                "src": "/images/screenshot-3.webp",
                "sizes": "1080x2000",
                "type": "image/webp",
                "form_factor": "narrow",
                "label": "User View"
            },
            {
                "src": "/images/screenshot-4.webp",
                "sizes": "1080x2000",
                "type": "image/webp",
                "form_factor": "narrow",
                "label": "Receival List"
            },
        ],
        // url_handlers: [
        //     {
        //         origin: "http://localhost:8000"
        //     }
        // ]
    },
};

export default pwaConfiguration;
