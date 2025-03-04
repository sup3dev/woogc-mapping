<?php
/**
 * Plugin Name: WooCommerce GetCourse Integration
 * Plugin URI: https://github.com/sup3dev/woogc-mapping.git
 * Description: Улучшенная интеграция WooCommerce с платформой GetCourse
 * Version: 2.2
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
    
    // Миграция существующих сопоставлений из старого плагина
    $old_mapping = get_option('getcourse_woocommerce_mapping', array());
    
    if (!empty($old_mapping) && empty(get_option('woogc_account1_mapping'))) {
        // Конвертируем старый формат в новый с поддержкой ролей
        $new_mapping = array();
        foreach ($old_mapping as $gc_id => $woo_id) {
            $new_mapping[$gc_id] = array(
                'product_id' => $woo_id,
                'role' => ''
            );
        }
        
        // Сохраняем в новый формат
        update_option('woogc_account1_mapping', $new_mapping);
    }
    
    // Создаем опцию для второго аккаунта, если не существует
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
    
    // Копируем настройки задержки из старого плагина для обратной совместимости
    if (null !== get_option('woogc_add_delay', null)) {
        // Уже существует, не перезаписываем
    } else {
        update_option('woogc_add_delay', '1'); // Включаем по умолчанию для совместимости со старым плагином
        update_option('woogc_delay_seconds', '30'); // 30 секунд, как в оригинальном плагине
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
    // Удаляем опции плагина при удалении (но не старого плагина для безопасности)
    delete_option('woogc_account1_mapping');
    delete_option('woogc_account2_mapping');
    delete_option('woogc_account_names');
    delete_option('woogc_log_enabled');
    delete_option('woogc_email_notification');
    delete_option('woogc_default_role');
    delete_option('woogc_add_delay');
    delete_option('woogc_delay_seconds');
    
    // Не удаляем старое сопоставление для безопасности
    // delete_option('getcourse_woocommerce_mapping');
}