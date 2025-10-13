// webpack.config.js
const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // Chemins de sortie/public
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    // Entrées (ajoute d'autres .addEntry si besoin)
    .addEntry('app', './assets/app.js')
    .addEntry('affi', './assets/js/ptn_all.js')
    .addEntry('msg', './assets/js/aff_msg.js')
    .addEntry('secure', './assets/js/secure.js')
    .addEntry('codeacces', './assets/js/codeaccess.js')
    .addEntry('geolocate', './assets/js/aff_geolocate.js')
    .addEntry('placernotice', './assets/js/plug_placernotice.js')
    .addEntry('parameters', './assets/js/parameter_wb.js')
    .addEntry('msgwebsite', './assets/js/aff_msg_wb.js')
    .addEntry('resizor', './assets/js/aff_resizor.js')
    .addEntry('indexevent','./assets/elements/indexevent.js')
    .addEntry('indexeventpotin','./assets/elements/indexeventpotin.js')
    .addEntry('indexoffrepotin','./assets/elements/indexoffrepotin.js')
    .addEntry('indexnewmediaboard','./assets/elements/indexNewMediaBoard.js')
    .addEntry('indexwebsite','./assets/elements/indexwebsite.js')
    .addEntry('indexressources','./assets/elements/indexcatressources.js')
    .addEntry('addentity', './assets/elements/indexentity.js')
    .addEntry('adress', './assets/elements/indexadress.js')
    .addEntry('newboard', './assets/js/aff_newboard.js')
    .addEntry('openday', './assets/js/aff_openday.js')
    .addEntry('post', './assets/js/aff_post.js')
    .addEntry('article', './assets/js/aff_article.js')
    .addEntry('iframe', './assets/js/aff_iframe.js')
    .addEntry('link', './assets/js/aff_link.js')
    .addEntry('picgpreview', './assets/js/aff_pictgpreview.js')
    .addEntry('review', './assets/js/aff_review.js')
    .addEntry('rss', './assets/js/aff_ressource.js')
    .addEntry('add_doc', './assets/js/aff_adddoc.js')
    .addEntry('presents', './assets/js/aff_presents.js')
    .addEntry('addarticlefood', './assets/js/aff_addarticlefood.js')
    .addEntry('cargo', './assets/js/aff_cargo.js')
    .addEntry('calendara', './assets/js/aff_calendara.js')
    .addEntry('offre', './assets/js/aff_offre.js')
    .addEntry('workshop', './assets/js/aff_workshop.js')
    .addEntry('calendaraffi', './assets/js/plug_calendar.js')
    .addEntry('noel', './assets/js/noel.js')
    .addEntry('localitate', './assets/elements/indexlocate.js')
    .addEntry('wyswyg', './assets/js/scripts/bootstrap-wysiwyg.js')
    .addEntry('hotkeys', './assets/js/scripts/external/jquery.hotkeys.js')
    .addEntry('prettify', './assets/js/scripts/external/google-code-prettify/prettify.js')
    .addEntry('home', './assets/js/homegg.js')
    .addEntry('homemb', './assets/js/homemb.js')
    .addEntry('site', './assets/js/scriptgg.js')
    .addEntry('initplacer', './assets/js/init-placernotice.js')
    .addEntry('quiz', './assets/js/quiz.js')
    .addEntry('swipercarou', './assets/js/swipercarou.js')
    .addEntry('chatbotfull', './assets/js/chatbotfull.js')
    .addEntry('ia-animations', './assets/js/ia-animations.js')
    .addEntry('escape-garden', './assets/js/escape-garden.js')


    // Runtime séparé (recommandé)
    .enableSingleRuntimeChunk()

    // Stimulus (auto-chargement des contrôleurs)
    .enableStimulusBridge('./assets/controllers.json')

    // Confort dev/prod
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Sass & PostCSS (autoprefixer)
    .enableSassLoader()
    .enablePostCssLoader()

    // TypeScript si présent dans ton projet (sinon commente ces 2 lignes)
    //.enableTypeScriptLoader()
    //.enableForkedTypeScriptTypesChecking()

    // jQuery (si tu l’emploies encore dans certaines libs/plugins)
    .autoProvidejQuery()

// (Optionnel) Si tu veux copier des fichiers bruts d'un dossier à un autre
    .copyFiles({
        from: 'node_modules/tinymce',
        to: 'tinymce/[path][name].[ext]',
    })
    .copyFiles({
        from: 'node_modules/tinymce/skins',
        to: 'tinymce/skins/[path][name].[ext]',
    })
    .copyFiles({
        from: './node_modules/mind-ar/dist/',
        to: 'mindar/[path][name].[ext]'})

    .copyFiles({
        from: './public/models',
        to: 'models/[path][name].[ext]'})

    .copyFiles({
        from: './public/audio',
        to: 'audio/[path][name].[ext]'})
;

// --- Récupérer et ajuster la config Webpack brute
const config = Encore.getWebpackConfig();

// --- Asset Modules (remplace file-loader & url-loader) ---
// Images
config.module.rules.push({
    test: /\.(png|jpe?g|gif|svg|webp|avif)$/i,
    type: 'asset/resource',
    generator: { filename: 'images/[name][hash][ext]' }
});

// Fonts & icônes
config.module.rules.push({
    test: /\.(woff2?|eot|ttf|otf)$/i,
    type: 'asset/resource',
    generator: { filename: 'fonts/[name][hash][ext]' }
});

// (Optionnel) Inlines petits assets automatiquement (seuil par défaut ~8kb)
// Exemple pour forcer inline pour des petits SVGs :
// config.module.rules.push({
//   test: /\.svg$/i,
//   type: 'asset',
//   parser: { dataUrlCondition: { maxSize: 8 * 1024 } }
// });

// Si tu utilises html-webpack-plugin pour des pages HTML "pures":
// (Encore ne l'ajoute pas par défaut ; tu l'as en deps, donc tu peux l'activer
// dans un webpack.config.js custom, mais ce n’est pas nécessaire pour Symfony/Twig)

config.resolve = config.resolve || {};
config.resolve.alias = Object.assign({}, config.resolve.alias, {
    react: 'preact/compat',
    'react-dom': 'preact/compat',
    'react/jsx-runtime': 'preact/jsx-runtime'
});

module.exports = config;
