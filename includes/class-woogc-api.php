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
        
        // Поддержка обратной совместимости - старый эндпоинт
        register_rest_route('getcourse/v1', '/buy', [
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'handle_account1_callback'), // Тот же обработчик, что и для account1
            'permission_callback' => '__return_true'
        ]);
        
        // Дополнительный эндпоинт для второго аккаунта с сохранением старого паттерна именования
        register_rest_route('getcourse/v1', '/buy2', [
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'handle_account2_callback'),
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
            
            // Проверяем обязательные параметры
            if (!$name || !$email || !$tariff_id) {
                WOOGC_Logger::log('Отсутствуют обязательные параметры: name, email или offers', $account_id, 'error');
                return new WP_Error('missing_parameters', 'Отсутствуют обязательные параметры', array('status' => 400));
            }
            
            // Получаем информацию о сопоставлении
            $mapping_option = 'woogc_account' . $account_id . '_mapping';
            $mapping = get_option($mapping_option, array());

            // Проверяем, есть ли для этого тарифа установка роли
            if (isset($mapping[$tariff_id]) && is_array($mapping[$tariff_id]) && !empty($mapping[$tariff_id]['role'])) {
                $new_role = $mapping[$tariff_id]['role'];
                
                // Устанавливаем новую роль пользователю
                $user_obj = new WP_User($user->ID);
                $user_obj->set_role($new_role);
                
                WOOGC_Logger::log("Установлена роль {$new_role} для пользователя {$user->ID}", $account_id);
            }
            
            // Получаем ID товара WooCommerce
            $woocommerce_product_id = $mapping[$tariff_id];
            WOOGC_Logger::log("Найден товар WooCommerce с ID {$woocommerce_product_id} для тарифа {$tariff_id}", $account_id);
            
            // Обрабатываем пользователя и заказ
            return WOOGC_Core::process_order($name, $email, $woocommerce_product_id, $status, $account_id);
            
        } catch (Exception $e) {
            // Логируем ошибку
            WOOGC_Logger::log_error($e, $account_id);
            return new WP_Error('internal_error', 'Внутренняя ошибка сервера', array('status' => 500));
        }
    }
}
