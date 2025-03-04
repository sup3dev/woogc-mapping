<?php
/**
 * Класс для работы с административным интерфейсом
 */
class WOOGC_Admin {
    /**
     * Инициализация класса
     */
    public static function init() {
        // Добавляем страницу в меню администратора
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        
        // Регистрируем стили и скрипты
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
        
        // Обработка AJAX запросов
        add_action('wp_ajax_woogc_save_mapping', array(__CLASS__, 'ajax_save_mapping'));
        add_action('wp_ajax_woogc_add_mapping_row', array(__CLASS__, 'ajax_add_mapping_row'));
        add_action('wp_ajax_woogc_delete_mapping', array(__CLASS__, 'ajax_delete_mapping'));
        add_action('wp_ajax_woogc_save_account_name', array(__CLASS__, 'ajax_save_account_name'));
    }
    
    /**
     * Добавляет пункт меню в админке
     */
    public static function add_admin_menu() {
        add_menu_page(
            __('WooCommerce GetCourse', 'woo-getcourse'),  // Заголовок страницы
            __('WooCommerce GetCourse', 'woo-getcourse'),  // Название пункта меню
            'manage_options',                             // Права доступа
            'woogc-settings',                             // Slug страницы
            array(__CLASS__, 'render_admin_page'),        // Callback-функция для вывода содержимого
            'dashicons-cart',                             // Иконка
            56                                            // Позиция в меню
        );
        
        // Добавляем подменю
        add_submenu_page(
            'woogc-settings',                                    // Родительский slug
            __('Настройки интеграции', 'woo-getcourse'),        // Заголовок страницы
            __('Настройки', 'woo-getcourse'),                   // Название пункта меню
            'manage_options',                                   // Права доступа
            'woogc-settings',                                   // Slug страницы
            array(__CLASS__, 'render_admin_page')               // Callback-функция
        );
        
        add_submenu_page(
            'woogc-settings',                                    // Родительский slug
            __('Логи интеграции', 'woo-getcourse'),             // Заголовок страницы
            __('Логи', 'woo-getcourse'),                        // Название пункта меню
            'manage_options',                                   // Права доступа
            'woogc-logs',                                       // Slug страницы
            array(__CLASS__, 'render_logs_page')                // Callback-функция
        );
    }
    
    /**
     * Подключает стили и скрипты для админки
     */
    public static function enqueue_admin_assets($hook) {
        // Подключаем стили и скрипты только на страницах плагина
        if (strpos($hook, 'woogc-') === false) {
            return;
        }
        
        // Регистрируем и подключаем CSS
        wp_register_style(
            'woogc-admin-css',
            WOOGC_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WOOGC_VERSION
        );
        wp_enqueue_style('woogc-admin-css');
        
        // Регистрируем и подключаем JS
        wp_register_script(
            'woogc-admin-js',
            WOOGC_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WOOGC_VERSION,
            true
        );
        
        // Локализация переменных для JS
        wp_localize_script('woogc-admin-js', 'woogc_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('woogc-admin-nonce'),
            'confirm_delete' => __('Вы уверены, что хотите удалить это сопоставление?', 'woo-getcourse'),
            'success' => __('Успешно сохранено!', 'woo-getcourse'),
            'error' => __('Произошла ошибка. Попробуйте еще раз.', 'woo-getcourse')
        ));
        
        wp_enqueue_script('woogc-admin-js');
    }
    
    /**
     * Отображает страницу настроек
     */
    public static function render_admin_page() {
        // Получаем данные из настроек
        $account_names = get_option('woogc_account_names', array(
            '1' => __('Аккаунт GetCourse 1', 'woo-getcourse'),
            '2' => __('Аккаунт GetCourse 2', 'woo-getcourse')
        ));
        
        $account1_mapping = get_option('woogc_account1_mapping', array());
        $account2_mapping = get_option('woogc_account2_mapping', array());
        
        // Получаем все товары WooCommerce
        $products = wc_get_products(array('limit' => -1));
        
        // Подключаем шаблон
        include WOOGC_PLUGIN_DIR . 'templates/admin-settings.php';
    }
    
    /**
     * Отображает страницу логов
     */
    public static function render_logs_page() {
        // Получаем список всех файлов логов
        $log_files = glob(WOOGC_LOG_DIR . '*.log');
        
        // Сортируем файлы по дате изменения (новые в начале)
        usort($log_files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // Содержимое файла лога
        $log_content = '';
        
        // Если выбран файл для просмотра
        if (isset($_GET['file']) && !empty($_GET['file'])) {
            $file_name = sanitize_file_name($_GET['file']);
            $file_path = WOOGC_LOG_DIR . $file_name;
            
            if (file_exists($file_path) && is_readable($file_path)) {
                $log_content = file_get_contents($file_path);
            }
        }
        
        // Подключаем шаблон
        include WOOGC_PLUGIN_DIR . 'templates/admin-logs.php';
    }
    
    /**
     * Обрабатывает AJAX запрос на сохранение сопоставления
     */
    public static function ajax_save_mapping() {
        // Проверяем nonce для безопасности
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woogc-admin-nonce')) {
            wp_send_json_error('Неверный nonce');
        }
        
        // Проверяем права доступа
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        // Получаем данные из запроса
        $account_id = isset($_POST['account_id']) ? sanitize_text_field($_POST['account_id']) : '';
        $getcourse_id = isset($_POST['getcourse_id']) ? sanitize_text_field($_POST['getcourse_id']) : '';
        $old_getcourse_id = isset($_POST['old_getcourse_id']) ? sanitize_text_field($_POST['old_getcourse_id']) : '';
        $woocommerce_id = isset($_POST['woocommerce_id']) ? absint($_POST['woocommerce_id']) : 0;
        $user_role = isset($_POST['user_role']) ? sanitize_text_field($_POST['user_role']) : '';
        
        // Проверяем обязательные данные
        if (empty($account_id) || empty($getcourse_id) || empty($woocommerce_id)) {
            wp_send_json_error('Не все поля заполнены');
        }
        
        // Получаем текущее сопоставление
        $option_name = 'woogc_account' . $account_id . '_mapping';
        $mapping = get_option($option_name, array());
        
        // Проверяем, изменился ли ID тарифа
        if (!empty($old_getcourse_id) && $old_getcourse_id !== $getcourse_id) {
            // Удаляем старое сопоставление
            if (isset($mapping[$old_getcourse_id])) {
                unset($mapping[$old_getcourse_id]);
            }
        }
        
        // Проверяем, существует ли такое сопоставление
        if (isset($mapping[$getcourse_id]) && is_array($mapping[$getcourse_id])) {
            // Обновляем существующее сопоставление
            $mapping[$getcourse_id]['product_id'] = $woocommerce_id;
            $mapping[$getcourse_id]['role'] = $user_role;
        } else {
            // Создаем новое сопоставление с поддержкой роли
            $mapping[$getcourse_id] = array(
                'product_id' => $woocommerce_id,
                'role' => $user_role
            );
        }
        
        // Сохраняем сопоставление
        update_option($option_name, $mapping);
        
        // Отправляем успешный ответ
        wp_send_json_success(array(
            'message' => 'Сопоставление успешно сохранено',
            'mapping' => $mapping
        ));
    }
    
    /**
     * Обрабатывает AJAX запрос на добавление новой строки сопоставления
     */
    public static function ajax_add_mapping_row() {
        // Проверяем nonce для безопасности
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woogc-admin-nonce')) {
            wp_send_json_error('Неверный nonce');
        }
        
        // Проверяем права доступа
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        // Получаем данные из запроса
        $account_id = isset($_POST['account_id']) ? sanitize_text_field($_POST['account_id']) : '';
        
        // Проверяем обязательные данные
        if (empty($account_id)) {
            wp_send_json_error('Не указан ID аккаунта');
        }
        
        // Получаем все товары WooCommerce
        $products = wc_get_products(array('limit' => -1));
        
        // Формируем HTML для новой строки
        ob_start();
        include WOOGC_PLUGIN_DIR . 'templates/mapping-row.php';
        $html = ob_get_clean();
        
        // Отправляем успешный ответ
        wp_send_json_success(array(
            'html' => $html
        ));
    }
    
    /**
     * Обрабатывает AJAX запрос на удаление сопоставления
     */
    public static function ajax_delete_mapping() {
        // Проверяем nonce для безопасности
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woogc-admin-nonce')) {
            wp_send_json_error('Неверный nonce');
        }
        
        // Проверяем права доступа
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        // Получаем данные из запроса
        $account_id = isset($_POST['account_id']) ? sanitize_text_field($_POST['account_id']) : '';
        $getcourse_id = isset($_POST['getcourse_id']) ? sanitize_text_field($_POST['getcourse_id']) : '';
        
        // Проверяем обязательные данные
        if (empty($account_id) || empty($getcourse_id)) {
            wp_send_json_error('Не все поля заполнены');
        }
        
        // Получаем текущее сопоставление
        $option_name = 'woogc_account' . $account_id . '_mapping';
        $mapping = get_option($option_name, array());
        
        // Удаляем сопоставление
        if (isset($mapping[$getcourse_id])) {
            unset($mapping[$getcourse_id]);
            update_option($option_name, $mapping);
        }
        
        // Отправляем успешный ответ
        wp_send_json_success(array(
            'message' => 'Сопоставление успешно удалено'
        ));
    }
    
    /**
     * Обрабатывает AJAX запрос на сохранение имени аккаунта
     */
    public static function ajax_save_account_name() {
        // Проверяем nonce для безопасности
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woogc-admin-nonce')) {
            wp_send_json_error('Неверный nonce');
        }
        
        // Проверяем права доступа
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Недостаточно прав');
        }
        
        // Получаем данные из запроса
        $account_id = isset($_POST['account_id']) ? sanitize_text_field($_POST['account_id']) : '';
        $account_name = isset($_POST['account_name']) ? sanitize_text_field($_POST['account_name']) : '';
        
        // Проверяем обязательные данные
        if (empty($account_id) || empty($account_name)) {
            wp_send_json_error('Не все поля заполнены');
        }
        
        // Получаем текущие имена аккаунтов
        $account_names = get_option('woogc_account_names', array());
        
        // Обновляем имя аккаунта
        $account_names[$account_id] = $account_name;
        
        // Сохраняем настройки
        update_option('woogc_account_names', $account_names);
        
        // Отправляем успешный ответ
        wp_send_json_success(array(
            'message' => 'Имя аккаунта успешно сохранено'
        ));
    }
}