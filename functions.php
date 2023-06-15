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
function add_theme_scripts()
{
    wp_enqueue_style('styles', get_template_directory_uri() . '/style.css', array(), '1.1', 'all');
    wp_enqueue_script('script', get_template_directory_uri() . '/script/index.js', array(), 1.1, true);
}
add_action('wp_enqueue_scripts', 'add_theme_scripts');
/*
|--------------------------------------------------------------------------
| Back-end styles en scripts
|--------------------------------------------------------------------------
|
| 
| 
|
*/

function load_custom_wp_admin_style()
{
    wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css');
    wp_enqueue_style('styles', get_template_directory_uri() . '/style.css', array(), '1.1', 'all');
    wp_enqueue_script('script', get_template_directory_uri() . '/script/index.js', array(), 1.1, true);
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

function day_six_config()
{
    register_nav_menus(
        array(
            'day_six_main_menu' => 'Main Menu'
        )
    );
    add_theme_support('post-thumbnails');
    add_image_size('preview', 100, 100, array('center', 'center'));
}

add_action('after_setup_theme', 'day_six_config', 0);




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

    array_unshift(
        $categories,
        [
            'slug' => 'styling',
            'title' => 'styling',
            'icon' => null
        ],
        [
            'slug' => 'hero',
            'title' => 'hero',
            'icon' => null
        ],
        [
            'slug' => 'paginablokken',
            'title' => 'pagina blokken',
            'icon' => null
        ],
        [
            'slug' => 'containers',
            'title' => 'containers',
            'icon' => null
        ],
        [
            'slug' => 'blokken',
            'title' => 'blokken',
            'icon' => null
        ],

        [
            'slug' => 'cards',
            'title' => 'cards',
            'icon' => null
        ],
        [
            'slug' => 'navigatie',
            'title' => 'navigatie',
            'icon' => null
        ],
        [
            'slug' => 'innerblocks',
            'title' => 'inner blocks',
            'icon' => null
        ],
        [
            'slug' => 'elements',
            'title' => 'elements',
            'icon' => null
        ],
        [
            'slug' => 'page',
            'title' => 'page',
            'icon' => null
        ],
    );

    return $categories;
}, 10, 1);


/*
|--------------------------------------------------------------------------
| All allowed blocks
|--------------------------------------------------------------------------
*/
add_filter('allowed_block_types_all', function ($allowed_blocks, $editor_context) {
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
add_action('init', 'register_acf_blocks', 5);
function register_acf_blocks()
{

    $blocks = get_blocks();
    foreach ($blocks as $block) {
        register_block_type(__DIR__ . '/blocks/' . $block);
    }
}

/*
|--------------------------------------------------------------------------
| Get all blocks name from the folder name
|--------------------------------------------------------------------------
*/
function get_blocks()
{
    $theme = wp_get_theme();
    $blocks = get_option('cwp_blocks');
    $version = get_option('cwp_blocks_version');
    if (empty($blocks) || version_compare($theme->get('Version'), $version) || (function_exists('wp_get_environment_type') && 'production' !== wp_get_environment_type())) {
        $blocks = scandir(get_template_directory() . '/blocks/');
        $blocks = array_values(array_diff($blocks, array('..', '.', '.DS_Store', '_base-block')));

        update_option('cwp_blocks', $blocks);
        update_option('cwp_blocks_version', $theme->get('Version'));
    }
    return $blocks;
}


/*
|--------------------------------------------------------------------------
| Script for one block
|--------------------------------------------------------------------------
*/
function cwp_register_block_script()
{
    $blocks = get_blocks();
    foreach ($blocks as $block) {
        wp_register_script($block, get_template_directory_uri() . '/blocks/' . $block . '/script.js');
    }

}
add_action('init', 'cwp_register_block_script');


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



/*
|--------------------------------------------------------------------------
| Custom post type events
|--------------------------------------------------------------------------
|
| 
| 
|
*/

function create_event_post_type()
{
    // Labels for the post type
    $labels = array(
        'name' => __('Events'),
        'singular_name' => __('Event'),
        'menu_name' => __('Events'),
        'add_new' => __('Add New'),
        'add_new_item' => __('Add New Event'),
        'edit_item' => __('Edit Event'),
        'new_item' => __('New Event'),
        'view_item' => __('View Event'),
        'search_items' => __('Search Events'),
        'not_found' => __('No events found'),
        'not_found_in_trash' => __('No events found in Trash'),
        'all_items' => __('All Events'),
    );
    // Options for the post type
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'event'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author'),
        'taxonomies' => array('category', 'post_tag'),
        'menu_icon' => 'dashicons-calendar-alt',
    );

    // Register the post type
    register_post_type('event', $args);

}
add_action('init', 'create_event_post_type');
function add_event_rest_support()
{
    global $wp_post_types;
    $wp_post_types['event']->show_in_rest = true;
    $wp_post_types['event']->rest_base = 'events';
    $wp_post_types['event']->rest_controller_class = 'WP_REST_Posts_Controller';
}
add_action('init', 'add_event_rest_support', 25);

/*
|--------------------------------------------------------------------------
| Custom post type documents
|--------------------------------------------------------------------------
|
| 
| 
|
*/
function create_document_post_type()
{
    // Labels for the post type
    $labels = array(
        'name' => __('Documenten'),
        'singular_name' => __('Document'),
        'menu_name' => __('Documenten'),
        'add_new' => __('Add New'),
        'add_new_item' => __('Add New Document'),
        'edit_item' => __('Edit Document'),
        'new_item' => __('New Document'),
        'view_item' => __('View Document'),
        'search_items' => __('Search Documenten'),
        'not_found' => __('No documents found'),
        'not_found_in_trash' => __('No documents found in Trash'),
        'all_items' => __('All Documenten'),
    );
    // Options for the post type
    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'document'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author'),
        'taxonomies' => array('category', 'post_tag'),
        'menu_icon' => 'dashicons-media-text',
    );

    // Register the post type
    register_post_type('document', $args);
}
add_action('init', 'create_document_post_type');

function add_document_rest_support()
{
    global $wp_post_types;
    $wp_post_types['document']->show_in_rest = true;
    $wp_post_types['document']->rest_base = 'documenten';
    $wp_post_types['document']->rest_controller_class = 'WP_REST_Posts_Controller';
}
add_action('init', 'add_document_rest_support', 25);

/*
|--------------------------------------------------------------------------
| Add more data in JWT
|--------------------------------------------------------------------------
|
| 
| 
|
*/


/**
 * Change the token's expire value.
 *
 * @param int $expire The default "exp" value in timestamp.
 * @param int $issued_at The "iat" value in timestamp.
 *
 * @return int The "nbf" value.
 */
add_filter(
    'jwt_auth_expire',
    function ( $expire, $issued_at ) {
        // Modify the "expire" here.
        return time() + (DAY_IN_SECONDS * 1);
    },
    10,
    2
);


add_filter(
    'jwt_auth_payload',
    function ( $payload, $user ) {
        $newData = array(
            'email' => $user->user_email,
            'role' => $user->roles[0],
        );
        $payload['data']['user'] = array_merge($payload['data']['user'], $newData);
        return $payload;
    },
    10,
    2
);


// add_filter('rest_pre_dispatch', 'jwt_authenticate_for_rest_requests', 10, 3);

// function jwt_authenticate_for_rest_requests($result, $server, $request) {
//     if (strpos($request->get_route(), '/wp/v2/documenten') !== false) {
//         $headers = $request->get_headers();

//         if (!isset($headers['wp-jwt'])) {
//             return new WP_Error(
//                 'jwt_auth_no_auth_header',
//                 'Authorization header not found.',
//                 array(
//                     'status' => 403,
//                 )
//             );
//         }

//         $token = $headers['wp-jwt'];

//         if (!$token) {
//             return new WP_Error(
//                 'jwt_auth_bad_auth_header',
//                 'Authorization header malformed.',
//                 array(
//                     'status' => 403,
//                 )
//             );
//         }

//         // Here replace this with your secret key. It's better to store this in your wp-config.php file.
//         $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false; 

//         try {
//             $user = JWT::decode($token, $secret_key, array('HS256'));

//             if (!isset($user->data->user->id)) {
//                 return new WP_Error(
//                     'jwt_auth_invalid_token',
//                     'Invalid token.',
//                     array(
//                         'status' => 403,
//                     )
//                 );
//             }
//         } catch(Exception $e) {
//             return new WP_Error(
//                 'jwt_auth_invalid_token',
//                 'Invalid token.',
//                 array(
//                     'status' => 403,
//                 )
//             );
//         }
//     header( 'Access-Control-Allow-Origin: *' );
//     header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
//     header( 'Access-Control-Allow-Credentials: true' );
//     header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
//     }

//     return $result;
// }



add_filter('rest_pre_dispatch', 'jwt_authenticate_for_rest_requests', 10, 3);

function jwt_authenticate_for_rest_requests($result, $server, $request) {
    if (strpos($request->get_route(), '/wp/v2/documenten') !== false) {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            return new WP_Error(
                'jwt_auth_no_auth_header',
                'Authorization header not found. Headers: ' . json_encode($headers),
                array(
                    'status' => 403,
                )
            );
        }

        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader); 

        if (!$token) {
            return new WP_Error(
                'jwt_auth_bad_auth_header',
                'Authorization cookie malformed.',
                array(
                    'status' => 403,
                )
            );
        }

        // // Here replace this with your secret key. It's better to store this in your wp-config.php file.
        // $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false; 

        // try {
        //     $user = JWT::decode($token, $secret_key, array('HS256'));

        //     if (!isset($user->data->user->id)) {
        //         return new WP_Error(
        //             'jwt_auth_invalid_token',
        //             'Invalid token.',
        //             array(
        //                 'status' => 403,
        //             )
        //         );
        //     }
        // } catch(Exception $e) {
        //     return new WP_Error(
        //         'jwt_auth_invalid_token',
        //         'Invalid token.',
        //         array(
        //             'status' => 403,
        //         )
        //     );
        // }

    // header( 'Access-Control-Allow-Origin: *' );
    // header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
    // header( 'Access-Control-Allow-Credentials: true' );
    // header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
    }

    return $result;
}
