module.exports = {
    plugins: [
        require('postcss-preset-env')({
            stage: 3,        // fonctions CSS raisonnablement stables
            autoprefixer: { grid: true }
        })
    ]
};
