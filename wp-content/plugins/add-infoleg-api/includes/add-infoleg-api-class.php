<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('Add_Infoleg_API_Plugin')) {

    final class Add_Infoleg_API_Plugin
    {

        private $plugin_file;

        /**
         * Class constructor.
         * This is where all WordPress hooks (actions and filters) are added.
         */
        public function __construct($plugin_file)
        {
            $this->plugin_file = $plugin_file;

            // Hooks for rewrite rules and query vars
            add_filter('query_vars', [$this, 'register_query_vars']);
            add_action('init', [$this, 'add_rewrite_rules']);

            // Hook to register all blocks
            add_action('init', [$this, 'register_blocks']);

            // Activation and deactivation hooks for the plugin
            register_activation_hook($this->plugin_file, [$this, 'activate']);
            register_deactivation_hook($this->plugin_file, [$this, 'deactivate']);
        }

        public function register_query_vars($vars)
        {
            $vars[] = 'tipo_norma';
            $vars[] = 'id_norma';
            $vars[] = 'resumen';
            $vars[] = 'numero';
            $vars[] = 'texto';
            $vars[] = 'dependencia';
            $vars[] = 'publicacion_desde';
            $vars[] = 'publicacion_hasta';
            $vars[] = 'sancion';
            $vars[] = 'limit';
            $vars[] = 'offset';
            return $vars;
        }

        public function add_rewrite_rules()
        {

            $tipos_norma_validos = ['leyes', 'legislaciones', 'decretos', 'decisiones_administrativas', 'resoluciones', 'disposiciones', 'acordadas', 'actas', 'actuaciones', 'acuerdos', 'circulares', 'comunicaciones', 'comunicados', 'convenios', 'decisiones', 'decretos', 'directivas', 'instrucciones', 'interpretaciones', 'laudos', 'memorandums', 'misiones', 'notas', 'notas_externas', 'protocolos', 'providencias', 'recomendaciones'];
            $tipos_norma_regex = '(' . implode('|', $tipos_norma_validos) . ')';

            // Route for the "resultados" page
            add_rewrite_rule(
                '^nacionales/normativas/' . $tipos_norma_regex . '/?$',
                'index.php?pagename=resultados&tipo_norma=$matches[1]',
                'top'
            );

            // Route for the "detalle" page
            add_rewrite_rule(
                '^nacionales/normativas/' . $tipos_norma_regex . '/([0-9]{1,6})/?$',
                'index.php?pagename=detalle&tipo_norma=$matches[1]&id_norma=$matches[2]',
                'top'
            );
        }

        /**
         * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
         * Behind the scenes, it also registers all assets so they can be enqueued
         * through the block editor in the corresponding context.
         *
         * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
         * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
         */
        public function register_blocks()
        {

            $plugin_path = plugin_dir_path($this->plugin_file);
            $build_path = $plugin_path . 'build';
            $manifest_path = $build_path . '/blocks-manifest.php';

            /**
             * Logic for WP 6.8+ (most efficient)
             * 
             * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
             * based on the registered block metadata.
             * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
             *
             * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
             */
            if (function_exists('wp_register_block_types_from_metadata_collection')) {
                wp_register_block_types_from_metadata_collection($build_path, $manifest_path);
                return;
            } else {
                /**
                 * Fallback for WP 6.7
                 * 
                 * Registers the block(s) metadata from the `blocks-manifest.php` file.
                 * Added to WordPress 6.7 to improve the performance of block type registration.
                 *
                 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
                 */
                if (function_exists('wp_register_block_metadata_collection')) {
                    wp_register_block_metadata_collection($build_path, $manifest_path);
                }

                /**
                 * Fallback for older versions (reads the manifest and registers each one individually)
                 * 
                 * Registers the block type(s) in the `blocks-manifest.php` file.
                 *
                 * @see https://developer.wordpress.org/reference/functions/register_block_type/
                 */
                $manifest_data = require $manifest_path;
                foreach (array_keys($manifest_data) as $block_name) {
                    register_block_type($build_path . "/{$block_name}");
                }
            }
        }

        /**
         * Runs when the plugin is activated.
         * It is crucial for adding the rules and flushing them.
         */
        public function activate()
        {
            $this->add_rewrite_rules();
            flush_rewrite_rules();
        }

        public function deactivate()
        {
            flush_rewrite_rules();
        }
    }
}
