// babel.config.js
module.exports = function (api) {
    api.cache(true);

    return {
        presets: [
            ['@babel/preset-env', {
                useBuiltIns: 'usage',
                corejs: 3,
                targets: '>0.5%, not dead'
            }],
            ['@babel/preset-react', {
                runtime: 'automatic',
                importSource: 'preact'
            }]
        ],
        plugins: [
            '@babel/plugin-transform-class-properties',
            ['@babel/plugin-transform-runtime', { helpers: true, regenerator: true }]
        ]
    };
};
