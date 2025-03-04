<?php
/**
 * Plugin Name: WooCommerce GetCourse Integration
 * Description: Улучшенная интеграция WooCommerce с платформой GetCourse
 * Version: 2.0
 * Author: Alexander Mikheev
 * Author URI: https://t.me/ialexdev
 * Text Domain: woo-getcourse
 */

// Защита от прямого доступа к файлу
if (!defined('ABSPATH')) {
    exit;
}

// Определяем константы плагина
define('WOOGC_VERSION', '1.0.0');
define('WOOGC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOOGC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOOGC_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WOOGC_LOG_DIR', WP_CONTENT_DIR . '/logs/woo-getcourse/');

// Создаем директорию для логов, если не существует
if (!file_exists(WOOGC_LOG_DIR)) {
    wp_mkdir_p(WOOGC_LOG_DIR);
}

// Подключаем основные файлы плагина
require_once WOOGC_PLUGIN_DIR . 'includes/class-woogc-logger.php';
require_once WOOGC_PLUGIN_DIR . 'includes/class-woogc-api.php';
require_once WOOGC_PLUGIN_DIR . 'includes/class-woogc-admin.php';
require_once WOOGC_PLUGIN_DIR . 'includes/class-woogc-core.php';

// Инициализация плагина
function woogc_init() {
    // Проверка наличия WooCommerce
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'woogc_woocommerce_missing_notice');
        return;
    }
    
    // Загрузка переводов
    // load_plugin_textdomain('woo-getcourse', false, dirname(WOOGC_PLUGIN_BASENAME) . '/languages/');
    
    // Инициализация классов
    WOOGC_Logger::init();
    WOOGC_API::init();
    WOOGC_Admin::init();
    WOOGC_Core::init();
}
add_action('plugins_loaded', 'woogc_init');

// Уведомление о отсутствии WooCommerce
function woogc_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('WooCommerce GetCourse Integration требует установленного и активированного плагина WooCommerce.', 'woo-getcourse'); ?></p>
    </div>
    <?php
}

// Регистрация активации, деактивации и удаления плагина
register_activation_hook(__FILE__, 'woogc_activate');
register_deactivation_hook(__FILE__, 'woogc_deactivate');
register_uninstall_hook(__FILE__, 'woogc_uninstall');

// Функция активации плагина
function woogc_activate() {
    // Создаем директорию для логов
    if (!file_exists(WOOGC_LOG_DIR)) {
        wp_mkdir_p(WOOGC_LOG_DIR);
    }
    
    // Миграция существующих сопоставлений
    $old_mapping = get_option('getcourse_woocommerce_mapping', array());
    if (!empty($old_mapping) && empty(get_option('woogc_account1_mapping'))) {
        update_option('woogc_account1_mapping', $old_mapping);
    }
    
    if (!get_option('woogc_account2_mapping')) {
        update_option('woogc_account2_mapping', array());
    }
    
    // Устанавливаем имена аккаунтов по умолчанию
    if (!get_option('woogc_account_names')) {
        update_option('woogc_account_names', array(
            '1' => 'Аккаунт GetCourse 1',
            '2' => 'Аккаунт GetCourse 2'
        ));
    }
    
    // Очищаем кэш перезаписи маршрутов для применения новых API конечных точек
    flush_rewrite_rules();
}

// Функция деактивации плагина
function woogc_deactivate() {
    // Очищаем кэш перезаписи маршрутов
    flush_rewrite_rules();
}

// Функция удаления плагина
function woogc_uninstall() {
    // Удаляем опции плагина
    delete_option('woogc_account1_mapping');
    delete_option('woogc_account2_mapping');
    delete_option('woogc_account_names');
    delete_option('woogc_settings');
}
