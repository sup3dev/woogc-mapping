<?php
/**
 * Класс для работы с API
 */
class WOOGC_API {
    /**
     * Инициализация класса
     */
    public static function init() {
        // Регистрируем REST API эндпоинты
        add_action('rest_api_init', array(__CLASS__, 'register_endpoints'));
    }
    
    /**
     * Регистрирует эндпоинты REST API
     */
    public static function register_endpoints() {
        // Новые эндпоинты
        register_rest_route('woogc/v1', '/account1', [
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'handle_account1_callback'),
            'permission_callback' => '__return_true'
        ]);
        
        register_rest_route('woogc/v1', '/account2', [
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'handle_account2_callback'),
            'permission_callback' => '__return_true'
        ]);
        
        // Поддержка обратной совместимости - старые эндпоинты
        register_rest_route('getcourse/v1', '/buy', [
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'handle_account1_callback'), // Перенаправляем на тот же обработчик
            'permission_callback' => '__return_true'
        ]);
        
        register_rest_route('getcourse/v1', '/buy2', [
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'handle_account2_callback'), // Перенаправляем на тот же обработчик
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * Обрабатывает callback для первого аккаунта
     *
     * @param WP_REST_Request $request Объект запроса
     * @return WP_REST_Response|WP_Error Ответ или ошибка
     */
    public static function handle_account1_callback(WP_REST_Request $request) {
        return self::process_callback($request, '1');
    }
    
    /**
     * Обрабатывает callback для второго аккаунта
     *
     * @param WP_REST_Request $request Объект запроса
     * @return WP_REST_Response|WP_Error Ответ или ошибка
     */
    public static function handle_account2_callback(WP_REST_Request $request) {
        return self::process_callback($request, '2');
    }
    
    /**
     * Обрабатывает callback от GetCourse
     *
     * @param WP_REST_Request $request Объект запроса
     * @param string $account_id ID аккаунта (1 или 2)
     * @return WP_REST_Response|WP_Error Ответ или ошибка
     */
    private static function process_callback(WP_REST_Request $request, $account_id) {
        try {
            // Логируем входящий запрос
            WOOGC_Logger::log_request($request, $account_id);
            
            // Получаем параметры запроса
            $name = $request->get_param('name');
            $email = $request->get_param('email');
            $tariff_id = $request->get_param('offers');
            $status = $request->get_param('status');
            $getcourse_order_id = $request->get_param('getcourse_order_id');
            
            // Проверяем обязательные параметры
            if (!$name || !$email || !$tariff_id) {
                WOOGC_Logger::log('Отсутствуют обязательные параметры: name, email или offers', $account_id, 'error');
                return new WP_Error('missing_parameters', 'Отсутствуют обязательные параметры', array('status' => 400));
            }
            
            // Проверка на дублирование заказа GetCourse
            if (!empty($getcourse_order_id)) {
                global $wpdb;
                
                // Проверяем наличие заказов с таким же getcourse_order_id в метаданных
                $existing_order_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = '_woogc_getcourse_order_id' 
                    AND meta_value = %s 
                    LIMIT 1",
                    $getcourse_order_id
                ));
                
                if ($existing_order_id) {
                    WOOGC_Logger::log("Заказ с GetCourse ID {$getcourse_order_id} уже существует (WooCommerce Order #{$existing_order_id}). Прерываем обработку.", $account_id, 'info');
                    
                    // Возвращаем информацию о существующем заказе
                    return new WP_REST_Response(array(
                        'status' => 'success',
                        'message' => 'Заказ уже существует',
                        'order_id' => $existing_order_id
                    ), 200);
                }
            }
            
            // Получаем сопоставление тарифов для текущего аккаунта
            $mapping_option = 'woogc_account' . $account_id . '_mapping';
            $mapping = get_option($mapping_option, array());
            
            // Проверяем, возможно, это старый формат данных из оригинального плагина
            if ($account_id === '1' && empty($mapping) && $request->get_route() === '/wp-json/getcourse/v1/buy') {
                $legacy_mapping = get_option('getcourse_woocommerce_mapping', array());
                if (!empty($legacy_mapping)) {
                    // Конвертируем в новый формат с поддержкой ролей
                    $new_mapping = array();
                    foreach ($legacy_mapping as $gc_id => $woo_id) {
                        $new_mapping[$gc_id] = array(
                            'product_id' => $woo_id,
                            'role' => ''
                        );
                    }
                    
                    // Сохраняем в новый формат
                    update_option($mapping_option, $new_mapping);
                    $mapping = $new_mapping;
                    
                    WOOGC_Logger::log('Выполнена миграция сопоставлений из устаревшего формата', $account_id);
                }
            }
            
            // Проверяем наличие тарифа в сопоставлении
            if (!isset($mapping[$tariff_id])) {
                WOOGC_Logger::log("Тариф с ID {$tariff_id} не найден в сопоставлении", $account_id, 'error');
                return new WP_Error('invalid_tariff_id', 'Тариф не найден в сопоставлении', array('status' => 400));
            }
            
            // Получаем ID товара WooCommerce и роль из сопоставления
            $product_id = null;
            $role = '';
            
            if (is_array($mapping[$tariff_id])) {
                // Новый формат сопоставления с поддержкой ролей
                $product_id = $mapping[$tariff_id]['product_id'];
                $role = isset($mapping[$tariff_id]['role']) ? $mapping[$tariff_id]['role'] : '';
            } else {
                // Старый формат сопоставления (для обратной совместимости)
                $product_id = $mapping[$tariff_id];
            }
            
            WOOGC_Logger::log("Найден товар WooCommerce с ID {$product_id} для тарифа {$tariff_id}", $account_id);
            
            // Обрабатываем пользователя и заказ, передавая getcourse_order_id
            return WOOGC_Core::process_order($name, $email, $product_id, $status, $account_id, $role, $getcourse_order_id);
            
        } catch (Exception $e) {
            // Логируем ошибку
            WOOGC_Logger::log_error($e, $account_id);
            return new WP_Error('internal_error', 'Внутренняя ошибка сервера: ' . $e->getMessage(), array('status' => 500));
        }
    }
}