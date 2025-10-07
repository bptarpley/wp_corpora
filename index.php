<?php
/**
* Plugin Name: Corpora
* Plugin URI: https://github.com/bptarpley/wp_corpora
* Description: A plugin for allowing a Wordpress frontend to interface with Corpora as a backend
* Author: Bryan Tarpley
* Author URI: https://codhr.tamu.edu
* Version: 1.0.2
* License: GPL2+
* License URI: https://www.gnu.org/licenses/gpl-2.0.txt
*
* @package CGB
*/

// Exit if accessed directly.
	if (! defined( 'ABSPATH' ) ) 
	{
		exit;
	}


    // -------------------------- //
    // FRONT FACING SITE          //
    // -------------------------- //


    function corpora_enqueue_scripts()
    {
        // Get plugin version for cache busting
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $plugin_data = get_plugin_data(__FILE__);
        $plugin_version = $plugin_data['Version'];

        // Register Javascript
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-mark', plugin_dir_url(__FILE__).'js/jquery.mark.min.js');
        wp_enqueue_script('corpora-openseadragon', plugin_dir_url(__FILE__).'js/openseadragon/openseadragon.min.js');
        wp_enqueue_script('corpora-leaflet', plugin_dir_url(__FILE__).'js/leaflet/leaflet.js');
        wp_enqueue_script('corpora-leaflet-markercluster', plugin_dir_url(__FILE__).'js/leaflet/leaflet.markercluster.js');
        wp_enqueue_script('rangeslider-marks', plugin_dir_url(__FILE__).'js/tcrs-marks.min.js');
        wp_enqueue_script('rangeslider', plugin_dir_url(__FILE__).'js/toolcool-range-slider.min.js');
        wp_enqueue_script(
            'corpora-script',
            plugin_dir_url( __FILE__ ).'js/corpora.js',
            array(
                'jquery',
                'jquery-mark',
                'corpora-openseadragon'
            ),
            $plugin_version
        ); //your javascript library

        // Register CSS
        wp_enqueue_style('dashicons');
        wp_enqueue_style('corpora-css', plugin_dir_url( __FILE__ ).'css/corpora.css', $plugin_version);
        wp_enqueue_style('leaflet-css', plugin_dir_url( __FILE__ ).'js/leaflet/leaflet.css');
    }
    add_action('wp_enqueue_scripts','corpora_enqueue_scripts');


    // create and register shortcode for searches
    function corpora_search_shortcode($raw_attrs) {
        $attrs = shortcode_atts(array(
            'id' => ''
        ), $raw_attrs);

        $saved_searches = get_option( 'corpora_searches_field' );
        if (empty($saved_searches)) {
            $saved_searches = "[]";
        } else {
            $saved_searches = str_replace('"', '\"', $saved_searches);
        }
        
        $saved_colors = get_option( 'corpora_colors_field' );
        if (empty($saved_colors)) {
            $saved_colors = "[]";
        } else {
            $saved_colors = str_replace('"', '\"', $saved_colors);
        }

        ob_start();
        ?>
            <div id="corpora-search-<?php echo $attrs['id'] ?>" class="corpora-search-div"></div>
            <script type="application/javascript">
                jQuery(document).ready(function() {
                    // this strange method of initializing variables is due to the potential
                    // of having multiple corpora search shortcodes on the same page
                    if (typeof corpora_host === 'undefined') {
                        var corpora_host = "<?php echo get_option('corpora_host_field'); ?>"
                        var corpora_corpus_id = "<?php echo get_option('corpora_corpus_field'); ?>"
                        var corpora = null
                        var corpus_obj = null
                        var corpus = null
                        var detail_page = "<?php echo get_option('corpora_detail_page_field'); ?>"
                        var searches_json = "<?php echo $saved_searches ?>"
                        var searches = null
                        var colors_json = "<?php echo $saved_colors ?>"
                        var colors = null
                        var search_init_function = null
                        var corpora_initialized = false
                    } else {
                        corpora_initialized = true
                    }

                    if (!corpora_initialized) {
                        try {
                            searches = JSON.parse(searches_json)
                            colors = JSON.parse(colors_json)
                        } catch (e) {
                            console.log("An error occurred reading the settings for your Corpora searches.")
                            searches = []
                        }
                    }

                    if (corpora_host && corpora_corpus_id) {
                        if (!corpora_initialized) {
                            search_init_function = (search) => {
                                let corpora_search = new CorpusSearch(
                                    corpora,
                                    corpus,
                                    search,
                                    detail_page
                                )
                            }

                            corpora = new Corpora(corpora_host)
                            corpora.set_css_colors(colors)
                            corpus_obj = new Corpus(corpora, corpora_corpus_id)
                            corpus_obj.load(function (corpus_data) {
                                corpus = corpus_data
                                if (searches.length) {
                                    searches.forEach(s => {
                                        if (s.slug === "<?php echo $attrs['id'] ?>") {
                                            search_init_function(s)
                                        }
                                    });
                                }
                            });
                        } else {
                            if (searches.length) {
                                searches.forEach(s => {
                                    if (s.slug === "<?php echo $attrs['id'] ?>") {
                                        search_init_function(s)
                                    }
                                });
                            }
                        }
                    }
                });
            </script>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
    add_shortcode('corpora_search', 'corpora_search_shortcode');


    // create and register shortcode for maps
    function corpora_map_shortcode($raw_attrs) {
        $attrs = shortcode_atts(array(
            'id' => ''
        ), $raw_attrs);
        $saved_maps_json = get_option( 'corpora_maps_field' );

        ob_start();
        if (!empty($saved_maps_json)) {
            $saved_maps = json_decode($saved_maps_json, true);
            foreach ($saved_maps as $map) {
                if ($map['slug'] == $attrs['id']) {
                    ?>
                    <corpora-aggregation-map
                        corpora_host="<?php echo get_option('corpora_host_field'); ?>"
                        corpora_corpus="<?php echo get_option('corpora_corpus_field'); ?>"
                        corpora_content_type="<?php echo $map['content_type'] ?>"
                        corpora_geopoint_field="<?php echo $map['geopoint_field'] ?>"
                        corpora_location_content_type="<?php echo $map['location_ct'] ?>"
                        corpora_location_id_field="<?php echo $map['location_id_field'] ?>"
                        detail_page_url="/<?php echo get_option('corpora_detail_page_field'); ?>"
                        plugin_dir_url="<?php echo plugin_dir_url( __FILE__ ) ?>"
                        basemap_uri="<?php echo $map['basemap_uri'] ?>"
                        location_icon_url="<?php echo $map['location_icon_url'] ?>"
                        icon_background_color="<?php echo $map['location_icon_color'] ?>"
                        icon_font_color="<?php echo $map['location_font_color'] ?>"
                    ></corpora-aggregation-map>
                    <?php
                }
            }
        }
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
    add_shortcode('corpora_map', 'corpora_map_shortcode');


    // add configurable detail page rewrite rules
    function add_corpora_detail_page_rewrite_rules() {
        $detail_page_name = get_option( 'corpora_detail_page_field' );
        if (!empty($detail_page_name)) {
            $page = get_page_by_path($detail_page_name);
            $page_id = $page->ID;
            add_rewrite_rule('^' . $detail_page_name . '/([^/]*)/([^/]*)/?', 'index.php?page_id=' . $page_id . '&content_type=$matches[1]&content_id=$matches[2]', 'top');
        }
    }
    add_action('init', 'add_corpora_detail_page_rewrite_rules', 10, 0);


    // overwrite detail page title
    function corpora_overwrite_detail_page_title($title, $id = null) {
        if (get_option('permalink_structure') == "/%postname%/") {
            if (!empty($id) && !is_admin()) {
                $page = get_page($id);
                $detail_page_name = get_option('corpora_detail_page_field');
                if (!empty($detail_page_name)) {
                    if ($page->post_name == $detail_page_name) {
                        return '<span id="corpora-detail-content-label"></span>';
                    }
                }
            }
        }

        return $title;
    }
    add_filter('the_title', 'corpora_overwrite_detail_page_title', 10, 2);


    // render the detail page
    function corpora_render_detail_page($content) {
        // make sure permalink structure configured properly
        if (is_page() && get_option('permalink_structure') == "/%postname%/")
        {
            // make sure this is the corpora details page
            $current_page = basename(get_permalink());
            $detail_page = get_option('corpora_detail_page_field');
            if ($current_page == $detail_page) {
                $saved_content_type_templates = get_option( 'corpora_content_type_templates_field' );
                if (empty($saved_content_type_templates)) {
                    $saved_content_type_templates = "{}";
                } else {
                    $saved_content_type_templates = str_replace('"', '\"', $saved_content_type_templates);
                }

                $saved_colors = get_option( 'corpora_colors_field' );
                if (empty($saved_colors)) {
                    $saved_colors = "[]";
                } else {
                    $saved_colors = str_replace('"', '\"', $saved_colors);
                }

                ob_start();
                ?>
                    <div id="corpora-detail-content-div"></div>
                    <script type="application/javascript">
                        let corpora_host = "<?php echo get_option( 'corpora_host_field' ); ?>"
                        let corpora_corpus_id = "<?php echo get_option( 'corpora_corpus_field' ); ?>"
                        let corpora_plugin_url = "<?php echo plugin_dir_url( __FILE__ ); ?>"
                        let detail_page = "<?php echo get_option( 'corpora_detail_page_field' ); ?>"
                        let content_type_templates_json = "<?php echo $saved_content_type_templates ?>"
                        let content_type_templates = null
                        let colors_json = "<?php echo $saved_colors ?>"
                        let colors = null
                        let content_type = null
                        let content_id = null
                        let highlight = null
                        let content = null

                        jQuery(document).ready(function() {
                            try {
                                content_type_templates = JSON.parse(content_type_templates_json)
                                colors = JSON.parse(colors_json)
                            } catch(e) {
                                console.log("An error occurred reading the settings for your Corpora content type templates.")
                                content_type_templates = {}
                                colors = {}
                            }

                            // check for url path parameters
                            let path_parts = window.location.pathname.split('/')

                            if (path_parts.length === 5) {
                                content_type = path_parts[2]
                                content_id = path_parts[3]

                                if (window.location.search) {
                                    let get_params = new URLSearchParams(window.location.search)
                                    highlight = get_params.get('highlight')
                                }
                            }

                            if (corpora_host && corpora_corpus_id && content_type && content_id) {
                                let corpora = new Corpora(corpora_host)
                                corpora.set_css_colors(colors)
                                let corpus_obj = new Corpus(corpora, corpora_corpus_id)
                                corpus_obj.load(function(corpus) {
                                    content = new CorpusContent(
                                        corpora,
                                        corpus,
                                        content_type,
                                        content_id,
                                        detail_page,
                                        corpora_plugin_url,
                                        content_type_templates,
                                        highlight
                                    )
                                })
                            }
                        });
                    </script>
                <?php
                $output = ob_get_contents();
                ob_end_clean();
                return $content . $output;
            }
        }

        return $content;
    }
    add_filter('the_content', 'corpora_render_detail_page');


    // -------------------------- //
    // ADMIN INTERFACE            //
    // -------------------------- //

    // Load corpora.js
    function corpora_enqueue_admin_scripts($hook) {
        if ($hook != 'toplevel_page_corpora-config') {
            return;
        }

        wp_enqueue_script('corpora-script', plugin_dir_url( __FILE__ ).'js/corpora.js');
        wp_enqueue_style('corpora-css', plugin_dir_url( __FILE__ ).'css/corpora.css');

        if (get_option('permalink_structure') != "/%postname%/") {
            add_action('admin_notices', 'corpora_show_admin_permalink_error');
        }
    }
    add_action('admin_enqueue_scripts', 'corpora_enqueue_admin_scripts');

    // Show error about permalink structure if necessary
    function corpora_show_admin_permalink_error() {
        ?>
        <div class="error notice">
            <p><?php _e( 'In order for the Corpora plugin to show detail pages for content, "Post name" must be selected under "Common Settings" on the "Permalink Settings" page!', 'corpora-textdomain' ); ?></p>
        </div>
        <?php
    }

    // Add Corpora page to WP settings in Dashboard
    function corpora_setup_config_menu() {
        add_menu_page(
            'Corpora Configuration',
            'Corpora',
            'manage_options',
            'corpora-config',
            'corpora_render_config_page',
            plugin_dir_url( __FILE__ ).'img/corpora-config.png'
        );
    }
    add_action('admin_menu', 'corpora_setup_config_menu');

    // Render settings page
    function corpora_render_config_page() {
        ?>
            <h1> <?php esc_html_e( 'Corpora Settings', 'corpora-textdomain' ); ?> </h1>
            <form method="POST" action="options.php">
            <?php
            settings_fields( 'corpora-config' );
            do_settings_sections( 'corpora-config' );
            submit_button();
            ?>
            </form>
        <?php
    }

    // Initialize the various settings on the settings page so they can be saved to the WP database
    function corpora_setup_config_settings() {
        add_settings_section(
            'corpora_config_host_section',
            '',
            'corpora_render_config_host_section',
            'corpora-config'
        );

        add_settings_field(
            'corpora_host_field',
            __('Corpora Host', 'corpora-textdomain'),
            'corpora_render_config_host_field',
            'corpora-config',
            'corpora_config_host_section'
        );
        register_setting('corpora-config', 'corpora_host_field');

        add_settings_field(
            'corpora_corpus_field',
            __('Corpus', 'corpora-textdomain'),
            'corpora_render_config_corpus_field',
            'corpora-config',
            'corpora_config_host_section'
        );
        register_setting('corpora-config', 'corpora_corpus_field');

        add_settings_field(
            'corpora_detail_page_field',
            __('Detail Page', 'corpora-textdomain'),
            'corpora_render_config_detail_page_field',
            'corpora-config',
            'corpora_config_host_section'
        );
        register_setting('corpora-config', 'corpora_detail_page_field');

        add_settings_field(
            'corpora_content_type_templates_field',
            __('Detail Configuration', 'corpora-textdomain'),
            'corpora_render_config_content_type_templates_field',
            'corpora-config',
            'corpora_config_host_section'
        );
        register_setting('corpora-config', 'corpora_content_type_templates_field');

        add_settings_field(
            'corpora_searches_field',
            __('Searches', 'corpora-textdomain'),
            'corpora_render_config_searches_field',
            'corpora-config',
            'corpora_config_host_section'
        );
        register_setting('corpora-config', 'corpora_searches_field');

        add_settings_field(
            'corpora_maps_field',
            __('Maps', 'corpora-textdomain'),
            'corpora_render_config_maps_field',
            'corpora-config',
            'corpora_config_host_section'
        );
        register_setting('corpora-config', 'corpora_maps_field');

        add_settings_field(
            'corpora_colors_field',
            __('Colors', 'corpora-textdomain'),
            'corpora_render_config_colors_field',
            'corpora-config',
            'corpora_config_host_section'
        );
        register_setting('corpora-config', 'corpora_colors_field');

    }
    add_action( 'admin_init', 'corpora_setup_config_settings' );

    // Render host config section
    function corpora_render_config_host_section() {
        ?><?php
    }

    // Render host field
    function corpora_render_config_host_field () {
        ?><input type="text" id="corpora_host_field" name="corpora_host_field" value="<?php echo get_option( 'corpora_host_field' ); ?>"><?php
    }

    // Render corpus field
    function corpora_render_config_corpus_field () {
        ?><select id="corpora_corpus_field" name="corpora_corpus_field" disabled></select><?php
    }

    // Render detail page field
    function corpora_render_config_detail_page_field () {
        $selected_page = get_option('corpora_detail_page_field');
        ?>
        <select id="corpora_detail_page_field" name="corpora_detail_page_field">
            <?php
            if ($pages = get_pages()) {
                foreach($pages as $page) {
                    $selection_status = '';
                    if ($page->post_name == $selected_page) {
                        $selection_status = 'selected';
                    }
                    echo '<option value="' . $page->post_name . '" ' . $selection_status . '>' . $page->post_title . '</option>';
                }
            }
            ?>
        </select>
        <?php
    }
    
    // render content type templates field
    function corpora_render_config_content_type_templates_field() {
        ?>
        <div id="corpora_content_type_templates_div"></div>
        <input type="hidden" id="corpora_content_type_templates_field" name="corpora_content_type_templates_field" value="">
        <?php
    }

    // Render colors field
    function corpora_render_config_colors_field() {
        ?>
        <div>
            <input type="color" id="corpora_color_primary_box" class="corpora-color-box" data-color="primary">
            <label for="corpora_color_primary_box">Primary</label>
        </div>
        <div>
            <input type="color" id="corpora_color_secondary_box" class="corpora-color-box" data-color="secondary">
            <label for="corpora_color_secondary_box">Secondary</label>
        </div>
        <div>
            <input type="color" id="corpora_color_foreground_box" class="corpora-color-box" data-color="foreground">
            <label for="corpora_color_foreground_box">Foreground</label>
        </div>
        <div>
            <input type="color" id="corpora_color_background_box" class="corpora-color-box" data-color="background">
            <label for="corpora_color_background_box">Background</label>
        </div>
        <input type="hidden" id="corpora_colors_field" name="corpora_colors_field" />
        <?php
    }

    // Render colors field
    function corpora_render_config_maps_field() {
        ?>
        <div id="corpora_maps_div"></div>
        <input type="hidden" id="corpora_maps_field" name="corpora_maps_field" value="">
        <?php
    }

    // Render searches field
    function corpora_render_config_searches_field() {
        $saved_content_type_templates = get_option( 'corpora_content_type_templates_field' );
        if (empty($saved_content_type_templates)) {
            $saved_content_type_templates = "{}";
        } else {
            $saved_content_type_templates = str_replace('"', '\"', $saved_content_type_templates);
        }
        
        $saved_searches = get_option( 'corpora_searches_field' );
        if (empty($saved_searches)) {
            $saved_searches = "[]";
        } else {
            $saved_searches = str_replace('"', '\"', $saved_searches);
        }

        $saved_colors = get_option( 'corpora_colors_field' );
        if (empty($saved_colors)) {
            $saved_colors = "{}";
        } else {
            $saved_colors = str_replace('"', '\"', $saved_colors);
        }

        $saved_maps = get_option( 'corpora_maps_field' );
        if (empty($saved_maps)) {
            $saved_maps = "[]";
        } else {
            $saved_maps = str_replace('"', '\"', $saved_maps);
        }

        ?>
        <div id="corpora_searches_div"></div>
        <input type="hidden" id="corpora_searches_field" name="corpora_searches_field" value="">

        <!-- search configuration modal -->
        <div id="corpora_search_modal" style="display: none;">
            <h2>New Search</h2>
            <hr />
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="corpora_search_name_box">Search Name</label>
                        </th>
                        <td>
                            <input type="text" id="corpora_search_name_box" placeholder="My Search" style="width: 100%; margin-bottom: 10px;" /><br />
                            <input type="checkbox" id="corpora_search_display_name_box"> <label for="corpora_search_display_name_box">Display search name?</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="corpora_search_ct_box">Content Type</label>
                        </th>
                        <td>
                            <select id="corpora_search_ct_box" style="width: 100%; max-width: none!important;">
                                <option value="">Select a content type for this search</option>
                            </select>
                        </td>
                    </tr>
                    <tr id="corpora_search_timeslider_row" style="display: none;">
                        <th scope="row">
                            <label for="corpora_search_timeslider_field_box">Timeslider Field</label>
                        </th>
                        <td>
                            <select id="corpora_search_timeslider_field_box" style="width: 100%; max-width: none!important;">
                                <option value="">None</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="corpora_search_page_size_box">Default Page Size</label>
                        </th>
                        <td>
                            <select id="corpora_search_page_size_box" style="width: 100%; max-width: none!important;">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="corpora_search_default_sort_box">Default Sorting</label>
                        </th>
                        <td>
                            <select id="corpora_search_default_sort_box" style="width: 100%; max-width: none!important;">
                            </select>
                            <div style="margin-top: 10px;">
                                <input type="radio" id="corpora_search_default_sort_direction_asc" name="corpora_search_default_sort_direction" class="corpora-search-default-sort-radio" value="asc">
                                <label for="corpora_search_default_sort_direction_asc">Ascending</label>
                                <input type="radio" id="corpora_search_default_sort_direction_desc" name="corpora_search_default_sort_direction" class="corpora-search-default-sort-radio" value="desc" style="margin-left: 10px;">
                                <label for="corpora_search_default_sort_direction_asc">Descending</label>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div id="corpora_search_field_options" style="display: none; margin-top: 20px;">
                <label style="font-size: 14px; font-weight: bold; color: #1d2327;">Field Options</label>
                <table class="wp-list-table widefat fixed striped table-view-list" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th scope="col" class="sortable" style="vertical-align: middle; padding-left: 10px; font-weight: bold;">Field</th>
                            <th scope="col" class="sortable" style="vertical-align: middle; padding-left: 10px; font-weight: bold;">Show in Searches</th>
                        </tr>
                    </thead>
                    <tbody id="corpora_search_field_options_table">
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 38px;">
                <a id="corpora_search_field_options_save_button" href="javascript:save_search();" class="button button-primary" style="margin-right: 8px;">Save</a>
                <a href="javascript:tb_remove();" class="button">Cancel</a>
            </div>
        </div>

        <!-- map configuration modal -->
        <div id="corpora_map_modal" style="display: none;">
            <h2>New Map</h2>
            <hr />
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="corpora_map_name_box">Map Name</label>
                        </th>
                        <td>
                            <input type="text" id="corpora_map_name_box" placeholder="My Map" style="width: 100%; margin-bottom: 10px;" /><br />
                            <input type="checkbox" id="corpora_map_display_name_box"> <label for="corpora_map_display_name_box">Display map name?</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="corpora_map_ct_box">Content Type</label>
                        </th>
                        <td>
                            <select id="corpora_map_ct_box" style="width: 100%; max-width: none!important;">
                                <option value="">Select a content type for this map</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="corpora_map_geopoint_field_box">Geopoint Field</label>
                        </th>
                        <td>
                            <select id="corpora_map_geopoint_field_box" style="width: 100%; max-width: none!important;">
                                <option value="">Select the geopoint field to use for plotting locations</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="corpora_map_location_id_field_box">Location ID Field</label>
                        </th>
                        <td>
                            <select id="corpora_map_location_id_field_box" style="width: 100%; max-width: none!important;">
                                <option value="">Select the ID field to use for uniquely identifying locations on the map</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="corpora_map_location_ct_box">Location Content Type</label>
                        </th>
                        <td>
                            <select id="corpora_map_location_ct_box" style="width: 100%; max-width: none!important;">
                                <option value="">Select a content type that represents individual locations on the map</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="corpora_map_basemap_uri_box">Basemap URI</label>
                        </th>
                        <td>
                            <input type="text" id="corpora_map_basemap_uri_box" placeholder="Basemap URI" style="width: 100%; margin-bottom: 10px;" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="corpora_map_location_icon_url_box">Location Icon URL</label>
                        </th>
                        <td>
                            <input type="text" id="corpora_map_location_icon_url_box" placeholder="Location Icon URL" style="width: 100%; margin-bottom: 10px;" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="corpora_map_location_icon_color_box">Location Icon Color</label>
                        </th>
                        <td>
                            <input type="color" id="corpora_map_location_icon_color_box">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="corpora_map_location_font_color_box">Location Font Color</label>
                        </th>
                        <td>
                            <input type="color" id="corpora_map_location_font_color_box">
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-top: 38px;">
                <a id="corpora_map_options_save_button" href="javascript:save_map();" class="button button-primary" style="margin-right: 8px;">Save</a>
                <a href="javascript:tb_remove();" class="button">Cancel</a>
            </div>
        </div>

        <?php add_thickbox(); ?>
        <script type="application/javascript">
            let host_field = null
            let corpus_field = null
            let content_type_templates_field = null
            let content_type_templates_div = null
            let searches_field = null
            let searches_div = null
            let searches_table = null
            let maps_field = null
            let maps_div = null
            let colors_field = null

            let host = "<?php echo get_option( 'corpora_host_field' ); ?>"
            let host_timer = null
            let corpora = null
            let corpus = null
            let corpus_id = "<?php echo get_option( 'corpora_corpus_field' ); ?>"
            let detail_page = "<?php echo get_option( 'corpora_detail_page_field' ); ?>"
            let cts_with_geo = new Set()
            let geo_fields = {}

            let corpus_box_default = `<option value="">Select a corpus</option>`
            let ct_box_default = `<option value="">Select a content type</option>`
            let template_box_default = `<option value="">Default</option>`
            let template_field_toggler = (params) => `
                <span class="corpora-badge ${params.action === 'hide' ? 'admin' : 'gray'}" data-ct="${params.ct}" data-field="${params.field_name}">
                    ${params.field_label} <span class="dashicons dashicons-${params.action === 'hide' ? 'visibility' : 'hidden'} corpora-toggle-field" data-ct="${params.ct}" data-field="${params.field_name}" data-action="${params.action}"></span>
                </span>
            `
            
            let content_type_templates_json = "<?php echo $saved_content_type_templates ?>"
            let content_type_templates = null
            let searches_json = "<?php echo $saved_searches ?>"
            let searches = null
            let colors_json = "<?php echo $saved_colors ?>"
            let colors = null
            let maps_json = "<?php echo $saved_maps ?>"
            let maps = null

            jQuery(document).ready(function($)
            {
                host_field = jQuery('#corpora_host_field')
                corpus_field = jQuery('#corpora_corpus_field')
                content_type_templates_field = jQuery('#corpora_content_type_templates_field')
                content_type_templates_div = jQuery('#corpora_content_type_templates_div')
                searches_field = jQuery('#corpora_searches_field')
                searches_div = jQuery('#corpora_searches_div')
                maps_field = jQuery('#corpora_maps_field')
                maps_div = jQuery('#corpora_maps_div')
                colors_field = jQuery('#corpora_colors_field')

                try {
                    content_type_templates = JSON.parse(content_type_templates_json)
                    content_type_templates_field.val(JSON.stringify(content_type_templates))
                    
                    searches = JSON.parse(searches_json)
                    searches_field.val(JSON.stringify(searches))

                    colors = JSON.parse(colors_json)
                    colors_field.val(JSON.stringify(colors))

                    maps = JSON.parse(maps_json)
                    maps_field.val(JSON.stringify(maps))
                } catch(e) {
                    alert("An error occurred reading the settings for Corpora plugin.")
                    content_type_templates = {}
                    searches = []
                    colors = {}
                }

                if (host_field.val()) {
                    setup_corpus_field()
                    setup_colors()
                }

                host_field.keyup(function(e) {
                    clearTimeout(host_timer)
                    host_timer = setTimeout(function() {
                        let proceed = true

                        if (searches.length) {
                            proceed = confirm(`Are you sure you want to modify the Corpora Host field? Doing so will delete the searches you've already defined.`)
                        }

                        if (proceed) {
                            host = host_field.val()
                            corpus_field.empty()
                            reset_templates_and_searches()
                            setup_corpus_field()
                            setup_colors()
                        }
                    }, 2000)
                })
            })

            function setup_corpus_field() {
                corpora = new Corpora(host)
                corpora.list_corpora(function(data) {
                    if (data !== null) {
                        if (data.records) {
                            corpus_field.empty()
                            corpus_field.append(corpus_box_default)
                            corpus_field.prop('disabled', false)
                            data.records.map(corpus => {
                                corpus_field.append(`
                                    <option value="${corpus.id}" ${corpus_id === corpus.id ? 'selected' : ''}>${corpus.name}</option>
                                `)
                            })

                            if (corpus_id) {
                                corpus = new Corpus(corpora, corpus_id)
                                corpus.load(function(c) {
                                    setup_content_type_templates(c)
                                    setup_searches(c)
                                    setup_maps(c)
                                }, true)
                            }

                            corpus_field.change(function () {
                                let proceed = true

                                if (searches.length) {
                                    proceed = confirm(`Are you sure you want to choose a different corpus? Doing so will delete the searches you've already defined.`)
                                }

                                if (proceed) {
                                    reset_templates_and_searches()
                                    corpus = new Corpus(corpora, corpus_field.val())
                                    corpus.load(function(c) {
                                        setup_content_type_templates(c)
                                        setup_searches(c)
                                    }, true)
                                }
                            })
                        }
                    } else {
                        alert(`The Corpora host provided is not responding appropriately. Do you have the right URL?`)
                        corpus_field.prop('disabled', false)
                    }
                })
            }

            function setup_content_type_templates(corpus) {
                content_type_templates_div.empty()
                content_type_templates_div.append(`
                    <table class="wp-list-table widefat fixed striped table-view-list corpora-admin-table" style="margin-bottom: 10px;">
                        <thead>
                            <tr>
                                <th scope="col" style="vertical-align: middle; padding-left: 10px;">Content Type</th>
                                <th scope="col" style="vertical-align: middle;">Template</th>
                                <th scope="col" style="vertical-align: middle;">Field Display</th>
                                <th scope="col" style="vertical-align: middle;">Associated Content</th>
                                <th scope="col" style="vertical-align: middle;">Empty Fields</th>
                            </tr>
                        </thead>
                        <tbody id="corpora_content_type_templates_table">
                        </tbody>
                    </table>
                `)
                let content_type_templates_table = jQuery('#corpora_content_type_templates_table')

                Object.keys(corpus.content_types).forEach(ct_name => {
                    let ct = corpus.content_types[ct_name]
                    let template_opts = [template_box_default]

                    if (ct.templates) {
                        for (let template_name in ct.templates) {
                            if (template_name !== 'Label' && ct.templates[template_name].mime_type === 'text/html') {
                                let template_selected = ''
                                if (content_type_templates &&
                                        (ct_name in content_type_templates) &&
                                        content_type_templates[ct_name].template === template_name) {

                                    template_selected = ' selected'
                                }

                                template_opts.push(`
                                    <option value="${template_name}"${template_selected}>${template_name}</option>
                                `)
                            }
                        }
                    }

                    let field_togglers = []
                    ct.fields.forEach(f => {
                        let field_toggler_params = {
                            action: 'hide',
                            ct: ct_name,
                            field_name: f.name,
                            field_label: f.label
                        }

                        if (content_type_templates &&
                                (ct_name in content_type_templates) &&
                                content_type_templates[ct_name].hidden_fields.includes(f.name)) {
                            field_toggler_params['action'] = 'show'
                        }

                        field_togglers.push(template_field_toggler(field_toggler_params))
                    })

                    let disabled = ' disabled'
                    if (template_opts.length > 1) disabled = ''

                    let associated_hidden = false
                    if (content_type_templates &&
                        (ct_name in content_type_templates) &&
                        content_type_templates[ct_name].show_associated === false) {
                        associated_hidden = true
                    }

                    let show_empty = false
                    if (content_type_templates &&
                        (ct_name in content_type_templates) &&
                        content_type_templates[ct_name].show_empty === true) {
                        show_empty = true
                    }

                    content_type_templates_table.append(`
                        <tr>
                            <td>${ct_name}</td>
                            <td>
                                <select class="corpora-content-type-template-selector" data-content_type="${ct_name}"${disabled}>
                                    ${template_opts.join(' ')}
                                </select>
                            </td>
                            <td>
                                ${field_togglers.join(' ')}
                            </td>
                            <td>
                                <select class="corpora-content-type-associated-selector" data-content_type=${ct_name}>
                                    <option>Show</option>
                                    <option ${associated_hidden ? 'selected' : ''}>Hide</option>
                                </select>
                            </td>
                            <td>
                            <select class="corpora-content-type-empty-selector" data-content_type=${ct_name}>
                                <option>Hide</option>
                                <option ${show_empty ? 'selected' : ''}>Show</option>
                            </select>
                        </td>
                        </tr>
                    `)
                })

                let default_settings = {
                    template: '',
                    hidden_fields: [],
                    show_associated: true,
                    show_empty: false
                }

                jQuery('.corpora-content-type-template-selector').change(function() {
                    let selector = jQuery(this)
                    let ct = selector.data('content_type')
                    if (!(ct in content_type_templates)) content_type_templates[ct] = Object.assign({}, default_settings)

                    content_type_templates[ct].template = selector.val()
                    content_type_templates_field.val(JSON.stringify(content_type_templates))
                })

                jQuery('.corpora-content-type-associated-selector').change(function() {
                    let selector = jQuery(this)
                    let ct = selector.data('content_type')
                    if (!(ct in content_type_templates)) content_type_templates[ct] = Object.assign({}, default_settings)

                    content_type_templates[ct].show_associated = selector.val() === 'Show'
                    content_type_templates_field.val(JSON.stringify(content_type_templates))
                })

                jQuery('.corpora-content-type-empty-selector').change(function() {
                    let selector = jQuery(this)
                    let ct = selector.data('content_type')
                    if (!(ct in content_type_templates)) content_type_templates[ct] = Object.assign({}, default_settings)

                    content_type_templates[ct].show_empty = selector.val() === 'Show'
                    content_type_templates_field.val(JSON.stringify(content_type_templates))
                })

                jQuery(document).on('click', '.corpora-toggle-field', function() {
                    let toggler = jQuery(this)
                    let ct = toggler.data('ct')
                    let field = toggler.data('field')
                    let action = toggler.data('action')

                    if (action === 'hide') {
                        if (!(ct in content_type_templates)) content_type_templates[ct] = Object.assign({}, default_settings)
                        content_type_templates[ct].hidden_fields.push(field)

                        toggler.data('action', 'show')
                        toggler.removeClass('dashicons-visibility')
                        toggler.addClass('dashicons-hidden')
                        toggler.parent().removeClass('admin')
                        toggler.parent().addClass('gray')
                    } else if ((ct in content_type_templates) && content_type_templates[ct].hidden_fields.includes(field)) {
                        content_type_templates[ct].hidden_fields = content_type_templates[ct].hidden_fields.filter(val => val !== field)

                        toggler.data('action', 'hide')
                        toggler.addClass('dashicons-visibility')
                        toggler.removeClass('dashicons-hidden')
                        toggler.parent().addClass('admin')
                        toggler.parent().removeClass('gray')
                    }

                    content_type_templates_field.val(JSON.stringify(content_type_templates))
                })
            }

            function setup_searches(corpus) {
                searches_div.empty()
                searches_div.append(`
                    <table class="wp-list-table widefat fixed striped table-view-list" style="margin-bottom: 10px;">
                        <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col" style="vertical-align: middle;">Short Code</th>
                                <th scope="col" style="vertical-align: middle;">Name</th>
                                <th scope="col" style="vertical-align: middle;">Content Type</th>
                            </tr>
                        </thead>
                        <tbody id="corpora_searches_table">
                        </tbody>
                    </table>
                    <a href="javascript:edit_search();" class="button button-primary">Create a Search</a>
                `)
                searches_table = jQuery('#corpora_searches_table')

                if (searches.length) {
                    searches.map((search, search_index) => {
                        searches_table.append(`
                            <tr>
                                <td>
                                    <a href="javascript:edit_search(${search_index});" class="button button-primary">Edit</a>
                                    <a href="javascript:delete_search(${search_index});" class="button button-danger"><span class="dashicons dashicons-trash" style="margin-top: 4px;"></span></a>
                                </td>
                                <td>[corpora_search id="${search.slug}"]</td>
                                <td>${search.name}</td>
                                <td>${search.content_type}</td>
                            </tr>
                        `)
                    })
                } else {
                    searches_table.append(`
                        <tr>
                            <td colspan="4">
                                <b>No searches defined yet.</b>
                            </td>
                        </tr>
                    `)
                }
            }

            function setup_colors() {
                let color_boxes = {
                    primary: jQuery('#corpora_color_primary_box'),
                    secondary: jQuery('#corpora_color_secondary_box'),
                    foreground: jQuery('#corpora_color_foreground_box'),
                    background: jQuery('#corpora_color_background_box')
                }
                if (Object.keys(colors).length === 0) {
                    colors = corpora.get_css_colors()
                }
                Object.keys(colors).forEach(color => {
                    color_boxes[color].val(colors[color])
                })
                jQuery('.corpora-color-box').change(function() {
                    let color_box = jQuery(this)
                    colors[color_box.data('color')] = color_box.val()
                    colors_field.val(JSON.stringify(colors))
                })
            }

            function setup_maps(corpus) {
                cts_with_geo = new Set()
                // first iteration to determine which ct's have geo_point fields
                Object.keys(corpus.content_types).forEach(ct_name => {
                    let ct = corpus.content_types[ct_name]
                    ct.fields.forEach(field => {
                        if (field.type === 'geo_point') {
                            cts_with_geo.add(ct_name)
                            if (!geo_fields[ct_name]) geo_fields[ct_name] = []
                            geo_fields[ct_name].push({
                                name: field.name,
                                label: field.label
                            })
                        }
                    })
                })
                // second iteration to seek any ct's that reference ct's with geo_point fields
                Object.keys(corpus.content_types).forEach(ct_name => {
                    let ct = corpus.content_types[ct_name]
                    ct.fields.forEach(field => {
                        if (field.type === 'cross_reference' && cts_with_geo.has(field.cross_reference_type)) cts_with_geo.add(ct_name)
                    })
                })

                maps_div.empty()
                maps_div.append(`
                    <table class="wp-list-table widefat fixed striped table-view-list" style="margin-bottom: 10px;">
                        <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col" style="vertical-align: middle;">Short Code</th>
                                <th scope="col" style="vertical-align: middle;">Name</th>
                                <th scope="col" style="vertical-align: middle;">Content Type</th>
                            </tr>
                        </thead>
                        <tbody id="corpora_maps_table">
                        </tbody>
                    </table>
                    <a href="javascript:edit_map();" class="button button-primary">Create a Map</a>
                `)
                maps_table = jQuery('#corpora_maps_table')

                if (maps.length) {
                    maps.forEach((map, map_index) => {
                        maps_table.append(`
                            <tr>
                                <td>
                                    <a href="javascript:edit_map(${map_index});" class="button button-primary">Edit</a>
                                    <a href="javascript:delete_map(${map_index});" class="button button-danger"><span class="dashicons dashicons-trash" style="margin-top: 4px;"></span></a>
                                </td>
                                <td>[corpora_map id="${map.slug}"]</td>
                                <td>${map.name}</td>
                                <td>${map.content_type}</td>
                            </tr>
                        `)
                    })
                } else {
                    maps_table.append(`
                        <tr>
                            <td colspan="4">
                                <b>No maps defined yet.</b>
                            </td>
                        </tr>
                    `)
                }
            }

            function edit_search(search_index=null) {
                let existing_search = null
                let name_box = jQuery('#corpora_search_name_box')
                let display_name_box = jQuery('#corpora_search_display_name_box')
                let ct_box = jQuery('#corpora_search_ct_box')
                let timeslider_row = jQuery('#corpora_search_timeslider_row')
                let timeslider_field_box = jQuery('#corpora_search_timeslider_field_box')
                let page_size_box = jQuery('#corpora_search_page_size_box')
                let default_sort_box = jQuery('#corpora_search_default_sort_box')
                let save_button = jQuery('#corpora_search_field_options_save_button')
                let ct_opts = ''

                if (search_index === null) {
                    name_box.val('')
                    display_name_box.prop('checked', true)
                    timeslider_field_box.val('')
                    timeslider_row.css('display', 'none')
                    page_size_box.val(25)
                    save_button.attr('href', 'javascript:save_search();')
                    jQuery('#corpora_search_default_sort_direction_asc').prop('checked', true)
                } else {
                    existing_search = searches[search_index]
                    console.log(existing_search)
                    save_button.attr('href', `javascript:save_search(${search_index});`)
                    name_box.val(existing_search.name)
                    display_name_box.prop('checked', existing_search.display_name)
                    page_size_box.val(existing_search.page_size)
                    if (existing_search.default_sort)
                        jQuery(`#corpora_search_default_sort_direction_${existing_search.default_sort.direction}`).prop('checked', true)
                    else
                        jQuery('#corpora_search_default_sort_direction_asc').prop('checked', true)
                }

                jQuery('#corpora_search_field_options').hide()

                ct_opts += ct_box_default
                for (let ct_name in corpus.content_types) {
                    let ct_selected = ''
                    if (existing_search && existing_search.content_type === ct_name) ct_selected = ' selected'

                    ct_opts += `
                        <option value="${ct_name}"${ct_selected}>${ct_name}</option>
                    `

                    if (corpus.content_types[ct_name].views) {
                        ct_opts += `<optgroup label="${ct_name} Views">`
                        corpus.content_types[ct_name].views.map(view => {
                            let view_value = `${ct_name}--${view.es_document_id}`
                            ct_selected = ''
                            if (existing_search && existing_search.content_type === view_value) ct_selected = ' selected'
                            ct_opts += `<option value="${view_value}"${ct_selected}>${view.name}</option>`
                        })
                        ct_opts += `</optgroup>`
                    }
                }
                ct_box.html(ct_opts)

                ct_box.off('change').on('change', function() {
                    let field_options = []
                    let ct_selection = ct_box.val()
                    if (ct_selection.includes('--')) {
                        ct_selection = ct_selection.split('--')[0]
                    }
                    default_sort_box.html(`<option value=''>None</option>`)
                    timeslider_field_box.html(`<option value=''>None</option>`)
                    timeslider_row.css('display', 'none')

                    corpus.content_types[ct_selection].fields.map(f => {
                        // setup field options
                        f_opt = {
                            name: f.name,
                            label: f.label,
                            type: f.type,
                        }

                        if (existing_search && (f.name in existing_search.field_options)) {
                            Object.assign(f_opt, existing_search.field_options[f.name])
                        } else {
                            f_opt.show_in_searches = true
                        }

                        if (['date', 'timespan'].includes(f.type)) {
                            let selected = ''

                            if (existing_search &&
                                existing_search.timeslider_field &&
                                existing_search.timeslider_field === f.name) {
                                    selected = ' selected'
                            }

                            timeslider_row.css('display', 'table-row')
                            timeslider_field_box.append(`
                                <option value="${f.name}"${selected}>${f.label}</option>
                            `)
                        }

                        field_options.push(f_opt)

                        // setup default sort field
                        if (sortableFieldTypes.includes(f.type)) {
                            let selected = ''
                            if (existing_search && existing_search.default_sort && existing_search.default_sort.field === f.name) selected = ' selected'

                            default_sort_box.append(`
                                <option value="${f.name}"${selected}>${f.label}</option>
                            `)
                        }
                    })

                    render_field_search_options(field_options)
                })

                tb_show(
                    "",
                    "#TB_inline?width=800&height=550&inlineId=corpora_search_modal",
                    ""
                )

                if (existing_search) ct_box.trigger('change')
            }

            function save_search(search_index=null) {
                let name = jQuery('#corpora_search_name_box').val()
                let ct = jQuery('#corpora_search_ct_box').val()
                let display_name = jQuery('#corpora_search_display_name_box').is(':checked')
                let timeslider_field = jQuery('#corpora_search_timeslider_field_box').val()
                let page_size = parseInt(jQuery('#corpora_search_page_size_box').val())
                let default_sort_field = jQuery('#corpora_search_default_sort_box').val()
                let default_sort_direction = jQuery('.corpora-search-default-sort-radio:checked').val()
                let field_options = {}

                jQuery('.corpora-search-field-option').each(function() {
                    let indicator = jQuery(this)
                    let field = indicator.data('field')
                    let option = indicator.data('option')
                    let checked = indicator.is(':checked')

                    if (!(field in field_options)) field_options[field] = {}
                    field_options[field][option] = checked
                })

                let search = {
                    name: name,
                    display_name: display_name,
                    page_size: page_size,
                    default_sort: {
                        field: default_sort_field,
                        direction: default_sort_direction
                    },
                    slug: slugify(name),
                    content_type: ct,
                    field_options: field_options
                }

                if (timeslider_field.length) search['timeslider_field'] = timeslider_field

                if (search_index !== null)
                    searches[search_index] = search
                else
                    searches.push(search)

                searches_field.val(JSON.stringify(searches))
                setup_searches(corpus)
                tb_remove()
            }

            function delete_search(search_index) {
                searches.splice(search_index, 1)
                searches_field.val(JSON.stringify(searches))
                setup_searches(corpus)
            }

            function render_field_search_options(opts) {
                let field_table = jQuery('#corpora_search_field_options_table')
                field_table.empty()
                jQuery('#corpora_search_field_options').show()

                opts.map(opt => {
                    field_table.append(`
                        <tr>
                            <th scope="row">${opt.label}</th>
                            <td><input type="checkbox" class="corpora-search-field-option" data-field="${opt.name}" data-option="show_in_searches" ${opt.show_in_searches ? 'checked' : ''}></td>
                        </tr>
                    `)
                })
            }

            function edit_map(map_index=null) {
                let existing_map = null
                let name_box = jQuery('#corpora_map_name_box')
                let display_name_box = jQuery('#corpora_map_display_name_box')
                let ct_box = jQuery('#corpora_map_ct_box')
                let geopoint_field_box = jQuery('#corpora_map_geopoint_field_box')
                let location_ct_box = jQuery('#corpora_map_location_ct_box')
                let location_id_field_box = jQuery('#corpora_map_location_id_field_box')
                let basemap_uri_box = jQuery('#corpora_map_basemap_uri_box')
                let location_icon_url_box = jQuery('#corpora_map_location_icon_url_box')
                let location_icon_color_box = jQuery('#corpora_map_location_icon_color_box')
                let location_font_color_box = jQuery('#corpora_map_location_font_color_box')
                let save_button = jQuery('#corpora_map_options_save_button')
                let ct_opts = ''
                let location_ct_opts = ''

                if (map_index === null) {
                    name_box.val('')
                    display_name_box.prop('checked', true)
                    ct_box.empty()
                    geopoint_field_box.empty()
                    location_ct_box.empty()
                    location_id_field_box.empty()
                    basemap_uri_box.val('')
                    location_icon_url_box.val('')
                    location_icon_color_box.val('')
                    location_icon_font_box.val('')
                    save_button.attr('href', 'javascript:save_map();')
                } else {
                    existing_map = maps[map_index]
                    name_box.val(existing_map.name)
                    display_name_box.prop('checked', existing_map.display_name)
                    basemap_uri_box.val(existing_map.basemap_uri)
                    location_icon_url_box.val(existing_map.location_icon_url)
                    location_icon_color_box.val(existing_map.location_icon_color)
                    location_icon_color_box.val(existing_map.location_font_color ? existing_map.location_font_color : '')
                    save_button.attr('href', `javascript:save_map(${map_index});`)
                }

                ct_opts += ct_box_default
                cts_with_geo.forEach(ct_name => {
                    let ct_selected = ''
                    if (existing_map && existing_map.content_type === ct_name) ct_selected = ' selected'

                    ct_opts += `
                        <option value="${ct_name}"${ct_selected}>${ct_name}</option>
                    `
                })
                ct_box.html(ct_opts)

                Object.keys(geo_fields).forEach(id_ct => {
                    let ct_selected = ''
                    if (existing_map && existing_map.location_content_type === id_ct) ct_selected = ' selected'

                    location_ct_opts += `
                        <option value="${id_ct}"${ct_selected}>${id_ct}</option>
                    `
                })
                location_ct_box.html(location_ct_opts)

                ct_box.off('change').on('change', function() {
                    let geo_field_choices = []
                    let geo_field_opts = ''
                    let id_field_opts = ''
                    let ct_selection = ct_box.val()

                    if (ct_selection in geo_fields) {
                        geo_field_choices = geo_field_choices.concat(geo_fields[ct_selection])
                        location_id_field_box.html('<option value="id">ID</option>')
                    } else {
                        corpus.content_types[ct_selection].fields.map(f => {
                            if (f.type === 'cross_reference' && (f.cross_reference_type in geo_fields)) {
                                geo_fields[f.cross_reference_type].forEach(known_geo_field => {
                                    geo_field_choices.push({
                                        name: `${f.name}.${known_geo_field.name}`,
                                        label: `${f.label} -> ${known_geo_field.label}`
                                    })
                                })
                                let id_field_selected = ''
                                if (existing_map && existing_map.location_id_field === `${f.name}.id`) id_field_selected = ' selected'
                                id_field_opts += `
                                    <option value="${f.name}.id"${id_field_selected}>${f.label} -> ID</option>
                                `
                            }
                        })
                        location_id_field_box.html(id_field_opts)
                    }

                    geo_field_choices.forEach(choice => {
                        let geo_field_selected = ''
                        if (existing_map && existing_map.geopoint_field === choice.name) geo_field_selected = ' selected'
                        geo_field_opts += `
                            <option value="${choice.name}"${geo_field_selected}>${choice.label}</option>
                        `
                    })

                    geopoint_field_box.html(geo_field_opts)
                })

                tb_show(
                    "",
                    "#TB_inline?width=800&height=550&inlineId=corpora_map_modal",
                    ""
                )

                if (existing_map) ct_box.trigger('change')
            }

            function save_map(map_index=null) {
                let name = jQuery('#corpora_map_name_box').val()
                let map = {
                    name: name,
                    display_name: jQuery('#corpora_map_display_name_box').is(':checked'),
                    slug: slugify(name),
                    content_type: jQuery('#corpora_map_ct_box').val(),
                    geopoint_field: jQuery('#corpora_map_geopoint_field_box').val(),
                    location_id_field: jQuery('#corpora_map_location_id_field_box').val(),
                    location_ct: jQuery('#corpora_map_location_ct_box').val(),
                    basemap_uri: jQuery('#corpora_map_basemap_uri_box').val(),
                    location_icon_url: jQuery('#corpora_map_location_icon_url_box').val(),
                    location_icon_color: jQuery('#corpora_map_location_icon_color_box').val(),
                    location_font_color: jQuery('#corpora_map_location_font_color_box').val()
                }

                if (map_index !== null)
                    maps[map_index] = map
                else
                    maps.push(map)

                maps_field.val(JSON.stringify(maps))
                setup_maps(corpus)
                tb_remove()
            }

            function delete_map(map_index) {
                maps.splice(map_index, 1)
                maps_field.val(JSON.stringify(maps))
                setup_maps(corpus)
            }

            function reset_templates_and_searches() {
                content_type_templates = {}
                content_type_templates_field.val('')
                content_type_templates_div.empty()

                colors = {}
                colors_field.val('{}')

                searches = []
                searches_field.val('[]')
                searches_div.empty()

                maps = []
                maps_field.val('[]')
                maps_div.empty()
            }

            function slugify(str) {
                str = str.replace(/^\s+|\s+$/g, ''); // trim leading/trailing white space
                str = str.toLowerCase(); // convert string to lowercase
                str = str.replace(/[^a-z0-9 -]/g, '') // remove any non-alphanumeric characters
                       .replace(/\s+/g, '-') // replace spaces with hyphens
                       .replace(/-+/g, '-'); // remove consecutive hyphens
                return str
            }

        </script>
        <?php
    }
