<?php



/**
* Day Six theme functions and definitions
* 
* @package Day Six theme
*/
require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

function add_cors_http_header(){
    $origin = $_SERVER['HTTP_ORIGIN'];

    $allowed_domains = [
        'http://localhost:3000',
        // add any other domains you want to allow here
    ];

    if (in_array($origin, $allowed_domains)) {
        header("Access-Control-Allow-Origin: $origin");
    }

    header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE"); // Modify this line with your needed methods
    header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization"); // Modify this line with your needed headers
    header("Access-Control-Allow-Credentials: true");

    if ('OPTIONS' == $_SERVER['REQUEST_METHOD']) {
        exit(0);
    }
}
add_action('init','add_cors_http_header');

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







/*
|--------------------------------------------------------------------------
| Custom post type documents bescherm rest api
|--------------------------------------------------------------------------
|
| 
| 
|
*/
add_filter('rest_pre_dispatch', 'jwt_authenticate_for_rest_requests', 10, 3);

function jwt_authenticate_for_rest_requests($result, $server, $request) {
    if (strpos($request->get_route(), '/wp/v2/documenten') !== false) {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            return new WP_Error(
                'jwt_auth_no_auth_header',
                'Authorization header not found.',
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

        // Here replace this with your secret key. It's better to store this in your wp-config.php file.
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false; 

        try {
            $user = JWT::decode($token, new Key($secret_key, 'HS256'));
            
            if (!isset($user->data->user->id)) {
                return new WP_Error(
                    'jwt_auth_invalid_token',
                    'Invalid token.',
                    array(
                        'status' => 403,
                    )
                );
            }
            if (!isset($user->data->user->role) || $user->data->user->role !== 'admin') {
                return new WP_Error(
                    'jwt_auth_invalid_role',
                    'Invalid role.',
                    array(
                        'status' => 403,
                    )
                );
            }
        } catch (SignatureInvalidException $e) {
            return new WP_Error(
                'jwt_auth_invalid_token',
                'Invalid token.',
                array(
                    'status' => 403,
                )
            );
        }  catch (BeforeValidException $e) {
            return new WP_Error(
                'jwt_auth_invalid_token',
                'Invalid token.',
                array(
                    'status' => 403,
                )
            );
        } catch (ExpiredException $e) {
            return new WP_Error(
                    'jwt_auth_expired_token',
                    'Expired token.',
                    array(
                        'status' => 403,
                    )
                );
        }
        catch(Exception $e) {
            return new WP_Error(
                'jwt_auth_invalid_token',
                'Invalid token.',
                array(
                    'status' => 403,
                )
            );
        }

    }

    return $result;
}


/*
|--------------------------------------------------------------------------
| Add role to media
|--------------------------------------------------------------------------
|
| 
| 
|
*/
function add_role_field_to_response() {
    register_rest_field('attachment', 'role', array(
        'get_callback' => function($data) {
            return get_post_meta($data['id'], 'role'); // Remove the 'false' parameter to get an array
        },
        'update_callback' => function($value, $object) {
            // Delete all previous entries
            delete_post_meta($object->ID, 'role');

            // Sanitize each role and add them as separate metadata entries
            foreach ($value as $role) {
                add_post_meta($object->ID, 'role', sanitize_text_field($role));
            }

            // Retrieve the updated roles as an array
            return get_post_meta($object->ID, 'role');
        },
        'schema' => array(
            'description' => 'Role',
            'type' => 'array'
        ),
    ));
}
add_action('rest_api_init', 'add_role_field_to_response');

/*
|--------------------------------------------------------------------------
| Check role on media upload and JWT token for media POST request
|--------------------------------------------------------------------------
|
| 
| 
|
*/
add_filter('rest_pre_dispatch', 'my_rest_pre_dispatchb', 10, 3);

function my_rest_pre_dispatchb($response, $server, $request) {
    if ($request->get_method() === 'POST' && strpos($request->get_route(), '/wp/v2/media') !== false) {

        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            return new WP_Error(
                'jwt_auth_no_auth_header',
                'Authorization header not found.',
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

        // Here replace this with your secret key. It's better to store this in your wp-config.php file.
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false; 

        try {
            $user = JWT::decode($token, new Key($secret_key, 'HS256'));
            
            if (!isset($user->data->user->id)) {
                return new WP_Error(
                    'jwt_auth_invalid_token',
                    'Invalid token.',
                    array(
                        'status' => 403,
                    )
                );
            }
            if (!isset($user->data->user->role) || $user->data->user->role !== 'admin') {
                return new WP_Error(
                    'jwt_auth_invalid_role',
                    'Invalid role.',
                    array(
                        'status' => 403,
                    )
                );
            }

            // Check if role is provided in the request
            if (isset($_POST['role'])) {
                // Decode the JSON string to an array
                $roles = $_POST['role'];

                if (is_array($roles)) {
                    add_action('add_attachment', function($post_ID) use ($roles) {
                        // Delete all previous entries
                        delete_post_meta($post_ID, 'role');

                        foreach ($roles as $role) {
                            add_post_meta($post_ID, 'role', sanitize_text_field($role));
                        }
                    });
                }
            }

        } catch (SignatureInvalidException $e) {
            return new WP_Error(
                'jwt_auth_invalid_token',
                'Invalid token.',
                array(
                    'status' => 403,
                )
            );
        }  catch (BeforeValidException $e) {
            return new WP_Error(
                'jwt_auth_invalid_token',
                'Invalid token.',
                array(
                    'status' => 403,
                )
            );
        } catch (ExpiredException $e) {
            return new WP_Error(
                    'jwt_auth_expired_token',
                    'Expired token.',
                    array(
                        'status' => 403,
                    )
                );
        }
        catch(Exception $e) {
            return new WP_Error(
                'jwt_auth_invalid_token',
                'Invalid token.',
                array(
                    'status' => 403,
                )
            );
        }
    }

    return $response;
}

/*
|--------------------------------------------------------------------------
| Check role on media upload and GET request
|--------------------------------------------------------------------------
|
| 
| 
|
*/
add_filter('rest_request_before_callbacks', 'check_role_before_sending_media', 10, 3);

function check_role_before_sending_media($response, $handler, $request) {
    // Here $request is a WP_REST_Request object, not a WP_REST_Server
    if ($request->get_method() === 'GET' && strpos($request->get_route(), '/wp/v2/media') !== false) {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            return new WP_Error(
                'jwt_auth_invalid_token',
                'Invalid token.',
                array(
                    'status' => 403,
                )
            );
        }

        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader); 

        if (!$token) {
            return new WP_Error(
                'jwt_auth_invalid_token',
                'Invalid token.',
                array(
                    'status' => 403,
                )
            );
        }

        // Here replace this with your secret key. It's better to store this in your wp-config.php file.
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false; 

        try {
            $user = JWT::decode($token, new Key($secret_key, 'HS256'));

            if (!isset($user->data->user->id)) {
                return new WP_Error(
                    'jwt_auth_invalid_token',
                    'Invalid token.',
                    array(
                        'status' => 403,
                    )
                );
            }

            // Assuming that the user's role is stored in $user->data->user->role
            $user_role = $user->data->user->role;
            
            // Fetch the required roles from the 'role' property of the media object
            $media_id = $request->get_param('id');
            $media = get_post($media_id);
            $required_roles = get_post_meta($media->ID, 'role', false);

               // Check if user role is in required roles
            if (!in_array($user_role, $required_roles)) {
                return new WP_Error(
                    'jwt_auth_invalid_token',
                    'Invalid token.',
                    array(
                        'status' => 403,
                )
            );
            }
        } catch (SignatureInvalidException $e) {
            return new WP_Error(
                'jwt_auth_invalid_token',
                'Invalid token.',
                array(
                    'status' => 403,
                )
            );
        }  catch (BeforeValidException $e) {
            return new WP_Error(
                'jwt_auth_invalid_token',
                'Invalid token.',
                array(
                    'status' => 403,
                )
            );
        } catch (ExpiredException $e) {
            return new WP_Error(
                    'jwt_auth_expired_token',
                    'Expired token.',
                    array(
                        'status' => 403,
                    )
                );
        }
        catch(Exception $e) {
            return new WP_Error(
                'jwt_auth_invalid_token',
                'Invalid token.',
                array(
                    'status' => 403,
                )
            );
        }
    }

    return $response;
}

/*
|--------------------------------------------------------------------------
| Options ACF categorieen
|--------------------------------------------------------------------------
|
| 
| 
|
*/

// if( function_exists('acf_add_options_page') ) {
    
//     acf_add_options_page(array(
//         'page_title'    => 'Theme General Settings',
//         'menu_title'    => 'Theme Settings',
//         'menu_slug'     => 'theme-general-settings',
//         'capability'    => 'edit_posts',
//         'redirect'      => false
//     ));
    
//     acf_add_options_sub_page(array(
//         'page_title'    => 'Document categorieen',
//         'menu_title'    => 'Categorieen',
//         'parent_slug'   => 'theme-general-settings',
//     ));
// }


// add_action('rest_api_init', function() {
//     register_rest_route('acf/v1', '/theme-settings/', array(
//         'methods' => 'GET',
//         'callback' => 'get_theme_settings',
//     ));
// });

// function get_theme_settings() {
//     // Check if ACF is active and function exists
//     if(function_exists('acf_add_options_page')) {
//         // Get fields from both option pages
//         $categories = the_field('categories', 'option');

//         // Return data as JSON
//         return array(
//             'categories' => $categories,
//         );
//     }

//     // Return an empty object if the function does not exist
//     return new WP_Error('acf_not_found', 'ACF Plugin not found', array('status' => 404));
// }

function my_acf_op_init() {
    if( function_exists('acf_add_options_page') ) {
        $option_page = acf_add_options_page(array(
            'page_title'    => __('Theme General Settings'),
            'menu_title'    => __('Theme Settings'),
            'menu_slug'     => 'theme-general-settings',
            'capability'    => 'edit_posts',
            'redirect'      => false
        ));

        acf_add_options_sub_page(array(
            'page_title'    => 'Document categorieen',
            'menu_title'    => 'Categorieen',
            'parent_slug'   => 'theme-general-settings',
        ));
    }
}
add_action('acf/init', 'my_acf_op_init');

add_action('rest_api_init', function () {
    register_rest_route('myplugin/v1', '/theme-settings/', array(
        'methods' => 'GET',
        'callback' => 'get_theme_settings',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        }
    ));
});

function get_theme_settings() {
    // Get all ACF fields associated with the options page
    $fields = get_fields('option');
    if (!$fields) {
        return new WP_Error('no_fields', 'No fields were found', array('status' => 404));
    }
    return $fields;
}
