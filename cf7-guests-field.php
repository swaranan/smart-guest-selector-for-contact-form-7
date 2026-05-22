<?php
/**
 * Plugin Name: CF7 Guests Field
 * Description: Adds a dynamic Guests field for Contact Form 7 with Adults, Children, and optional child age inputs.
 * Version: 1.1.0
 * Author: swaranan
 * License: GPL-2.0-or-later
 * Requires Plugins: contact-form-7
 */

if (!defined('ABSPATH')) {
    exit;
}

final class CF7_Guests_Field_Plugin {
    const VERSION = '1.1.0';

    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function init() {
        if (!defined('WPCF7_VERSION')) {
            add_action('admin_notices', array($this, 'missing_cf7_notice'));
            return;
        }

        add_action('wpcf7_init', array($this, 'add_form_tag'));
        add_filter('wpcf7_validate_cf7_guests', array($this, 'validate_guests'), 10, 2);
        add_filter('wpcf7_validate_cf7_guests*', array($this, 'validate_guests'), 10, 2);
        add_filter('wpcf7_mail_tag_replaced_cf7-guests-summary', array($this, 'mail_tag_summary'), 10, 4);
        add_filter('wpcf7_mail_components', array($this, 'replace_summary_mail_tag'), 10, 3);
        add_action('wpcf7_admin_init', array($this, 'add_tag_generator'), 30);
    }

    public function missing_cf7_notice() {
        if (!current_user_can('activate_plugins')) {
            return;
        }
        echo '<div class="notice notice-error"><p><strong>CF7 Guests Field</strong> requires Contact Form 7 to be installed and active.</p></div>';
    }


    public function enqueue_admin_assets($hook) {
        if (!defined('WPCF7_VERSION')) {
            return;
        }

        if (strpos($hook, 'wpcf7') === false && strpos($hook, 'contact-form-7') === false) {
            return;
        }

        wp_enqueue_script(
            'cf7-guests-field-admin',
            plugin_dir_url(__FILE__) . 'assets/js/cf7-guests-field-admin.js',
            array('jquery'),
            self::VERSION,
            true
        );
    }

    public function add_tag_generator() {
        if (!class_exists('WPCF7_TagGenerator')) {
            return;
        }

        $tag_generator = WPCF7_TagGenerator::get_instance();
        $tag_generator->add(
            'cf7_guests',
            __('guests', 'cf7-guests-field'),
            array($this, 'tag_generator_panel'),
            array('version' => 2)
        );
    }

    public function tag_generator_panel($contact_form, $args = '') {
        ?>
        <header class="description-box">
            <h3><?php esc_html_e('Guests form-tag generator', 'cf7-guests-field'); ?></h3>
            <p><?php esc_html_e('Generate a Guests field. The visitor enters total guests, then selects adults and children. Child age fields can be enabled optionally.', 'cf7-guests-field'); ?></p>
        </header>

        <div class="control-box">
            <input type="hidden" value="cf7_guests" data-tag-part="basetype" />

            <fieldset>
                <legend><?php esc_html_e('Field type', 'cf7-guests-field'); ?></legend>
                <label><input type="checkbox" value="*" data-tag-part="type-suffix" /> <?php esc_html_e('Required field', 'cf7-guests-field'); ?></label>
            </fieldset>

            <fieldset>
                <legend><label for="tag-generator-panel-cf7-guests-name"><?php esc_html_e('Name', 'cf7-guests-field'); ?></label></legend>
                <input type="text" id="tag-generator-panel-cf7-guests-name" class="oneline" data-tag-part="name" />
            </fieldset>

            <fieldset>
                <legend><label for="tag-generator-panel-cf7-guests-min"><?php esc_html_e('Minimum guests', 'cf7-guests-field'); ?></label></legend>
                <input type="number" id="tag-generator-panel-cf7-guests-min" min="0" step="1" value="1" data-tag-part="option" data-tag-option="min:" />
            </fieldset>

            <fieldset>
                <legend><label for="tag-generator-panel-cf7-guests-max"><?php esc_html_e('Maximum guests', 'cf7-guests-field'); ?></label></legend>
                <input type="number" id="tag-generator-panel-cf7-guests-max" min="1" step="1" value="20" data-tag-part="option" data-tag-option="max:" />
            </fieldset>

            <fieldset>
                <legend><label for="tag-generator-panel-cf7-guests-default"><?php esc_html_e('Default total', 'cf7-guests-field'); ?></label></legend>
                <input type="number" id="tag-generator-panel-cf7-guests-default" min="0" step="1" value="0" data-tag-part="option" data-tag-option="default:" />
            </fieldset>

            <fieldset>
                <legend><?php esc_html_e('Children ages', 'cf7-guests-field'); ?></legend>
                <label><input type="checkbox" data-tag-part="option" data-tag-option="child_ages" /> <?php esc_html_e('Add an age field for each child', 'cf7-guests-field'); ?></label>
            </fieldset>

            <fieldset>
                <legend><label for="tag-generator-panel-cf7-guests-id"><?php esc_html_e('Id attribute', 'cf7-guests-field'); ?></label></legend>
                <input type="text" id="tag-generator-panel-cf7-guests-id" class="idvalue oneline" data-tag-part="option" data-tag-option="id:" />
            </fieldset>

            <fieldset>
                <legend><label for="tag-generator-panel-cf7-guests-class"><?php esc_html_e('Class attribute', 'cf7-guests-field'); ?></label></legend>
                <input type="text" id="tag-generator-panel-cf7-guests-class" class="classvalue oneline" data-tag-part="option" data-tag-option="class:" />
            </fieldset>
        </div>

        <footer class="insert-box">
            <div class="flex-container">
                <input type="text" class="code" readonly="readonly" onfocus="this.select()" data-tag-part="tag" aria-label="<?php esc_attr_e('The form-tag to be inserted into the form template', 'contact-form-7'); ?>" />
                <button type="button" class="button-primary" data-taggen="insert-tag"><?php esc_html_e('Insert Tag', 'contact-form-7'); ?></button>
            </div>
            <p class="mail-tag-tip">
                <?php esc_html_e('To use the total guests value in mail, insert the corresponding mail-tag', 'cf7-guests-field'); ?> <strong data-tag-part="mail-tag"></strong>.
            </p>
            <p class="mail-tag-tip"><?php esc_html_e('You can also use [fieldname_adults], [fieldname_children], and [cf7-guests-summary]. Replace fieldname with the field name.', 'cf7-guests-field'); ?></p>
        </footer>
        <?php
    }

    public function enqueue_assets() {
        if (!defined('WPCF7_VERSION')) {
            return;
        }

        wp_enqueue_style(
            'cf7-guests-field',
            plugin_dir_url(__FILE__) . 'assets/css/cf7-guests-field.css',
            array(),
            self::VERSION
        );

        wp_enqueue_script(
            'cf7-guests-field',
            plugin_dir_url(__FILE__) . 'assets/js/cf7-guests-field.js',
            array(),
            self::VERSION,
            true
        );
    }

    public function add_form_tag() {
        if (!function_exists('wpcf7_add_form_tag')) {
            return;
        }

        wpcf7_add_form_tag(
            array('cf7_guests', 'cf7_guests*'),
            array($this, 'render_form_tag'),
            array('name-attr' => true)
        );
    }

    public function render_form_tag($tag) {
        if (empty($tag->name)) {
            return '';
        }

        $name = sanitize_key($tag->name);
        $required = $tag->type === 'cf7_guests*';
        $id = $tag->get_id_option() ?: 'cf7-guests-' . wp_rand(1000, 9999);
        $class = $tag->get_class_option('cf7-guests-field');

        $min = $this->get_int_option($tag, 'min', 0);
        $max = $this->get_int_option($tag, 'max', 20);
        $default_total = $this->get_int_option($tag, 'default', 0);
        $default_adults = $this->get_int_option($tag, 'adults', 0);
        $default_children = $this->get_int_option($tag, 'children', 0);
        $child_ages_enabled = $tag->has_option('child_ages') || $tag->has_option('child-ages') || $tag->has_option('ages');

        $default_total = min(max($default_total, $min), $max);
        $default_adults = min(max($default_adults, 0), $max);
        $default_children = min(max($default_children, 0), $max);

        if ($default_total > 0 && ($default_adults + $default_children) !== $default_total) {
            $default_adults = $default_total;
            $default_children = 0;
        }

        $total_name = $name;
        $adults_name = $name . '_adults';
        $children_name = $name . '_children';
        $ages_name = $name . '_children_ages[]';

        ob_start();
        ?>
        <span class="wpcf7-form-control-wrap" data-name="<?php echo esc_attr($name); ?>">
            <div id="<?php echo esc_attr($id); ?>" class="cf7-guests-field <?php echo esc_attr($class); ?>" data-name="<?php echo esc_attr($name); ?>" data-min="<?php echo esc_attr($min); ?>" data-max="<?php echo esc_attr($max); ?>" data-child-ages="<?php echo $child_ages_enabled ? '1' : '0'; ?>">
                <label class="cf7-guests-label" for="<?php echo esc_attr($id); ?>-total">Guests<?php echo $required ? ' *' : ''; ?></label>
                <input
                    type="number"
                    id="<?php echo esc_attr($id); ?>-total"
                    name="<?php echo esc_attr($total_name); ?>"
                    class="wpcf7-form-control wpcf7-number cf7-guests-total"
                    min="<?php echo esc_attr($min); ?>"
                    max="<?php echo esc_attr($max); ?>"
                    value="<?php echo esc_attr($default_total); ?>"
                    <?php echo $required ? 'aria-required="true" required' : ''; ?>
                />

                <div class="cf7-guests-panel" hidden>
                    <div class="cf7-guests-row">
                        <label for="<?php echo esc_attr($id); ?>-adults">Adults</label>
                        <input type="number" id="<?php echo esc_attr($id); ?>-adults" name="<?php echo esc_attr($adults_name); ?>" class="cf7-guests-adults" min="0" max="<?php echo esc_attr($max); ?>" value="<?php echo esc_attr($default_adults); ?>" />
                    </div>
                    <div class="cf7-guests-row">
                        <label for="<?php echo esc_attr($id); ?>-children">Children</label>
                        <input type="number" id="<?php echo esc_attr($id); ?>-children" name="<?php echo esc_attr($children_name); ?>" class="cf7-guests-children" min="0" max="<?php echo esc_attr($max); ?>" value="<?php echo esc_attr($default_children); ?>" />
                    </div>
                    <?php if ($child_ages_enabled) : ?>
                        <div class="cf7-guests-child-ages" data-age-name="<?php echo esc_attr($ages_name); ?>"></div>
                    <?php endif; ?>
                    <p class="cf7-guests-help">Adults + Children must equal total Guests.</p>
                </div>
            </div>
        </span>
        <?php
        return ob_get_clean();
    }

    private function get_int_option($tag, $option, $fallback) {
        $value = $tag->get_option($option, 'int', true);
        return is_numeric($value) ? absint($value) : $fallback;
    }

    public function validate_guests($result, $tag) {
        $name = $tag->name;
        if (!$name) {
            return $result;
        }

        $required = $tag->type === 'cf7_guests*';
        $min = $this->get_int_option($tag, 'min', 0);
        $max = $this->get_int_option($tag, 'max', 20);
        $child_ages_enabled = $tag->has_option('child_ages') || $tag->has_option('child-ages') || $tag->has_option('ages');

        $total = isset($_POST[$name]) ? absint($_POST[$name]) : 0;
        $adults = isset($_POST[$name . '_adults']) ? absint($_POST[$name . '_adults']) : 0;
        $children = isset($_POST[$name . '_children']) ? absint($_POST[$name . '_children']) : 0;
        $ages = isset($_POST[$name . '_children_ages']) && is_array($_POST[$name . '_children_ages']) ? array_map('absint', $_POST[$name . '_children_ages']) : array();

        if ($required && $total <= 0) {
            $result->invalidate($tag, 'Please enter the number of guests.');
            return $result;
        }

        if ($total < $min || $total > $max) {
            $result->invalidate($tag, sprintf('Guests must be between %d and %d.', $min, $max));
            return $result;
        }

        if (($adults + $children) !== $total) {
            $result->invalidate($tag, 'Adults and children must add up to the total guests.');
            return $result;
        }

        if ($child_ages_enabled && $children > 0) {
            if (count($ages) !== $children) {
                $result->invalidate($tag, 'Please enter an age for each child.');
                return $result;
            }

            foreach ($ages as $age) {
                if ($age > 17) {
                    $result->invalidate($tag, 'Child ages must be between 0 and 17.');
                    return $result;
                }
            }
        }

        return $result;
    }

    public function mail_tag_summary($replaced, $submitted, $html, $mail_tag) {
        return $this->build_summary_from_post();
    }

    public function replace_summary_mail_tag($components, $contact_form, $mail) {
        $summary = $this->build_summary_from_post();
        foreach ($components as $key => $value) {
            if (is_string($value)) {
                $components[$key] = str_replace('[cf7-guests-summary]', $summary, $value);
            }
        }
        return $components;
    }

    private function build_summary_from_post() {
        $posted = wp_unslash($_POST);
        $lines = array();

        foreach ($posted as $key => $value) {
            if (substr($key, -7) === '_adults') {
                $base = substr($key, 0, -7);
                $total = isset($posted[$base]) ? absint($posted[$base]) : 0;
                $adults = absint($value);
                $children = isset($posted[$base . '_children']) ? absint($posted[$base . '_children']) : 0;
                $ages = isset($posted[$base . '_children_ages']) && is_array($posted[$base . '_children_ages']) ? array_map('absint', $posted[$base . '_children_ages']) : array();

                $line = ucfirst(str_replace(array('-', '_'), ' ', $base)) . ': ' . $total . ' guest(s), ' . $adults . ' adult(s), ' . $children . ' child(ren)';
                if (!empty($ages)) {
                    $line .= ', child ages: ' . implode(', ', $ages);
                }
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }
}

new CF7_Guests_Field_Plugin();
