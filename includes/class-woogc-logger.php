<?php
/**
 * Класс для управления логированием в плагине
 */
class WOOGC_Logger {
    /**
     * Инициализация класса
     */
    public static function init() {
        // Создаем директорию для логов, если не существует
        if (!file_exists(WOOGC_LOG_DIR)) {
            wp_mkdir_p(WOOGC_LOG_DIR);
        }
    }
    
    /**
     * Записывает сообщение в лог
     *
     * @param mixed $data Данные для записи в лог
     * @param string $account_id ID аккаунта (1 или 2)
     * @param string $type Тип сообщения (info, error, debug)
     * @return void
     */
    public static function log($data, $account_id = '1', $type = 'info') {
        $log_file = WOOGC_LOG_DIR . 'account' . $account_id . '_' . date('Y-m-d') . '.log';
        $current_date = date('Y-m-d H:i:s');
        
        // Форматируем данные для записи
        if (is_array($data) || is_object($data)) {
            $log_data = print_r($data, true);
        } else {
            $log_data = (string) $data;
        }
        
        // Добавляем информацию о типе сообщения
        $log_entry = "[{$current_date}] [{$type}] {$log_data}\n";
        
        // Записываем в лог
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
    
    /**
     * Записывает информацию об ошибке в лог
     *
     * @param Exception|WP_Error $error Объект ошибки
     * @param string $account_id ID аккаунта (1 или 2)
     * @return void
     */
    public static function log_error($error, $account_id = '1') {
        $error_data = array();
        
        if ($error instanceof Exception) {
            $error_data['error_type'] = get_class($error);
            $error_data['error_message'] = $error->getMessage();
            $error_data['error_trace'] = $error->getTraceAsString();
        } elseif ($error instanceof WP_Error) {
            $error_data['error_type'] = 'WP_Error';
            $error_data['error_message'] = $error->get_error_message();
            $error_data['error_code'] = $error->get_error_code();
            $error_data['error_data'] = $error->get_error_data();
        } else {
            $error_data['error_message'] = (string) $error;
        }
        
        self::log($error_data, $account_id, 'error');
    }
    
    /**
     * Записывает информацию о запросе в лог
     *
     * @param WP_REST_Request $request Объект запроса
     * @param string $account_id ID аккаунта (1 или 2)
     * @return void
     */
    public static function log_request(WP_REST_Request $request, $account_id = '1') {
        $request_data = array(
            'method' => $request->get_method(),
            'headers' => $request->get_headers(),
            'params' => $request->get_params(),
            'url' => $request->get_route(),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown'
        );
        
        self::log($request_data, $account_id, 'request');
    }
    
    /**
     * Записывает информацию о создании заказа в лог
     *
     * @param WC_Order $order Объект заказа
     * @param string $account_id ID аккаунта (1 или 2)
     * @return void
     */
    public static function log_order_created($order, $account_id = '1') {
        $order_data = array(
            'order_id' => $order->get_id(),
            'customer_id' => $order->get_customer_id(),
            'total' => $order->get_total(),
            'status' => $order->get_status(),
            'items' => array()
        );
        
        foreach ($order->get_items() as $item) {
            $order_data['items'][] = array(
                'product_id' => $item->get_product_id(),
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity()
            );
        }
        
        self::log($order_data, $account_id, 'order_created');
    }
}
