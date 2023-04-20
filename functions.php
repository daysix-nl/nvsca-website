<?php



/**

 * Day Six theme functions and definitions

 * 

 * @package Day Six theme

 */


/*
|--------------------------------------------------------------------------
| Front-end styles en scripts
|--------------------------------------------------------------------------
|
| 
| 
|
*/
function add_theme_scripts() {
    wp_enqueue_style( 'styles', get_template_directory_uri() . '/style.css', array(), '1.1', 'all');
    wp_enqueue_script( 'script', get_template_directory_uri() . '/script/index.js', array(), 1.1, true);
  }
  add_action( 'wp_enqueue_scripts', 'add_theme_scripts' );
/*
|--------------------------------------------------------------------------
| Back-end styles en scripts
|--------------------------------------------------------------------------
|
| 
| 
|
*/

function load_custom_wp_admin_style(){
    wp_enqueue_style( 'swiper',  'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css');
    wp_enqueue_style( 'styles', get_template_directory_uri() . '/style.css', array(), '1.1', 'all');
    wp_enqueue_script( 'script', get_template_directory_uri() . '/script/index.js', array(), 1.1, true);
}
add_action('admin_enqueue_scripts', 'load_custom_wp_admin_style');

/*
|--------------------------------------------------------------------------
| Menu
|--------------------------------------------------------------------------
|
| 
| 
|
*/

function day_six_config(){
    register_nav_menus (
        array(
            'day_six_main_menu' => 'Main Menu'
        )
    );
    add_theme_support( 'post-thumbnails' );
    add_image_size( 'preview', 100, 100, array( 'center', 'center' ) );
}

add_action( 'after_setup_theme', 'day_six_config', 0 );




/*
|--------------------------------------------------------------------------
| ACF blocks
|--------------------------------------------------------------------------
|
| 
| 
|
*/

/*
|--------------------------------------------------------------------------
| Categorie
|--------------------------------------------------------------------------
*/
add_filter('block_categories_all', function ($categories) {

    array_unshift($categories,           
    [
        'slug'  => 'styling',
        'title' => 'styling',
        'icon'  => null
    ],  
    [
        'slug'  => 'hero',
        'title' => 'hero',
        'icon'  => null
    ],
        [
        'slug'  => 'paginablokken',
        'title' => 'pagina blokken',
        'icon'  => null
    ],
    [
        'slug'  => 'containers',
        'title' => 'containers',
        'icon'  => null
    ],
    [
        'slug'  => 'blokken',
        'title' => 'blokken',
        'icon'  => null
    ],

    [
        'slug'  => 'cards',
        'title' => 'cards',
        'icon'  => null
    ],
    [
        'slug'  => 'navigatie',
        'title' => 'navigatie',
        'icon'  => null
    ],
    [
        'slug'  => 'innerblocks',
        'title' => 'inner blocks',
        'icon'  => null
    ],
    [
        'slug'  => 'elements',
        'title' => 'elements',
        'icon'  => null
    ],
    [
        'slug'  => 'page',
        'title' => 'page',
        'icon'  => null
    ],
);

return $categories;
}, 10, 1);


/*
|--------------------------------------------------------------------------
| All allowed blocks
|--------------------------------------------------------------------------
*/
add_filter('allowed_block_types_all', function($allowed_blocks, $editor_context) {
    $blocks = get_blocks();
    $acf_blocks = []; 
    foreach ($blocks as $block) { 
        $acf_blocks[] = 'acf/' . $block;
    }

    $core_blocks = [
        // 'core/paragraph',
        // 'core/heading',
    ];

    return array_merge($acf_blocks, $core_blocks);
}, 10, 2);


/*
|--------------------------------------------------------------------------
| Register blocks
|--------------------------------------------------------------------------
*/
add_action( 'init', 'register_acf_blocks', 5 );
function register_acf_blocks() {

    $blocks = get_blocks();
    foreach ($blocks as $block) {
            register_block_type( __DIR__ . '/blocks/'.$block );
    }
}

/*
|--------------------------------------------------------------------------
| Get all blocks name from the folder name
|--------------------------------------------------------------------------
*/
function get_blocks() {
	$theme   = wp_get_theme();
	$blocks  = get_option( 'cwp_blocks' );
	$version = get_option( 'cwp_blocks_version' );
	if ( empty( $blocks ) || version_compare( $theme->get( 'Version' ), $version ) || ( function_exists( 'wp_get_environment_type' ) && 'production' !== wp_get_environment_type() ) ) {
		$blocks = scandir( get_template_directory() . '/blocks/' );
		$blocks = array_values( array_diff( $blocks, array( '..', '.', '.DS_Store', '_base-block' ) ) );

		update_option( 'cwp_blocks', $blocks );
		update_option( 'cwp_blocks_version', $theme->get( 'Version' ) );
	}
	return $blocks;
}


/*
|--------------------------------------------------------------------------
| Script for one block
|--------------------------------------------------------------------------
*/
function cwp_register_block_script() {
    $blocks = get_blocks();
    foreach ($blocks as $block) {
        wp_register_script( $block, get_template_directory_uri() . '/blocks/'.$block.'/script.js' );
    }

}
add_action( 'init', 'cwp_register_block_script' );


/*
|--------------------------------------------------------------------------
| ACF json files
|--------------------------------------------------------------------------
|
| 
| 
|
*/

/**
 * Save the ACF fields as JSON in the specified folder.
 * 
 * @param string $path
 * @returns string
 */
add_filter('acf/settings/save_json', function ($path) {
    $path = get_stylesheet_directory() . '/acf-json';
    return $path;
});
/**
 * Load the ACF fields as JSON in the specified folder.
 *
 * @param array $paths
 * @returns array
 */
add_filter('acf/settings/load_json', function ($paths) {
    unset($paths[0]);
    $paths[] = get_stylesheet_directory() . '/acf-json';
    return $paths;
});
