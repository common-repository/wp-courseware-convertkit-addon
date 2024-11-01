const mix = require( 'laravel-mix' )
const wpPot = require( 'wp-pot' )

// SASS Configuration.
var sassConfig = {
	outputStyle: 'expanded',
	indentType: 'tab',
	indentWidth: 1
}

// CSS Configuration.
var cssConfig = {
	postCss: [ require( 'postcss-preset-env' )() ],
	processCssUrls: false,
	autoprefixer: false
}

// Sets the path to the generated assets. By default, this is the `/assets` folder.
mix.setPublicPath( 'assets' )

// Autoload.
mix.autoload( {
	jquery: [ '$', 'window.jQuery', 'jQuery' ]
} )

// Compile Scripts.
mix.js( 'assets/src/js/course.js', 'js' )
mix.js( 'assets/src/js/settings.js', 'js' )

// Compile Styles.
mix.sass( 'assets/src/scss/course.scss', 'css', sassConfig ).options( cssConfig )

// Version.
mix.version()

// Language.
if ( mix.inProduction() ) {
	wpPot( {
		destFile: './languages/wpcw-convertkit.pot',
		domain: 'wpcw-convertkit',
		bugReport: 'https://flyplugins.com/questions/',
		package: 'WP Courseware - ConvertKit',
		lastTranslator: 'Fly Plugins <info@flyplugins.com>',
		team: 'Fly Plugins <info@flyplugins.com>',
		src: [
			'!vendor/**/*.php',
			'assets/**/*.php',
			'includes/**/*.php',
		]
	} )
}

// Add custom Webpack configuration.
mix.webpackConfig( {
	stats: 'minimal',
	performance: { hints: false },
	externals: {
		'jquery': 'jQuery',
	}
} );

// Disable processing asset URLs in Sass files.
mix.options( { processCssUrls: false } );
