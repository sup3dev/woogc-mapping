<?php
/**
 * Основной класс для обработки данных
 */
class WOOGC_Core {
    /**
     * Соответствие статусов GetCourse и WooCommerce
     */
    private static $payment_status_mapping = array(
        'Ожидаем оплаты' => 'pending',
        'На удержании' => 'on-hold',
        'В обработке' => 'processing',
        'Завершен' => 'completed',
        'Отменено' => 'cancelled',
        'Возвращено' => 'refunded',
        'Частично оплачен' => 'completed',
        'Не удалось' => 'failed'
    );
    
    /**
     * Инициализация класса
     */
    public static function init() {
        // Нет необходимости в доп. действиях при инициализации
    }
    
    /**
     * Обрабатывает создание пользователя и заказа
     *
     * @param string $name Имя пользователя
     * @param string $email Email пользователя
     * @param int $product_id ID товара WooCommerce
     * @param string $status Статус заказа от GetCourse
     * @param string $account_id ID аккаунта (1 или 2)
     * @return WP_REST_Response|WP_Error Ответ или ошибка
     */
    public static function process_order($name, $email, $product_id, $status, $account_id) {
        try {
            // Проверяем существование пользователя по email
            $user = get_user_by('email', $email);
            
            // Если пользователь не существует, создаем его
            if (!$user) {
                WOOGC_Logger::log("Пользователь с email {$email} не найден, создаем нового пользователя", $account_id);
                
                // Подготавливаем имя пользователя
                $username = sanitize_user($name);
                $username = sanitize_title(sanitize_user(str_replace(' ', '_', $username)));
                
                // Проверяем уникальность имени пользователя
                if (username_exists($username)) {
                    $username .= '_' . wp_generate_password(4, false);
                    WOOGC_Logger::log("Имя пользователя {$username} уже существует, создаем уникальное имя", $account_id);
                }
                
                // Генерируем случайный пароль
                $random_password = wp_generate_password();
                
                // Создаем пользователя
                $user_data = array(
                    'user_login' => $username,
                    'user_pass' => $random_password,
                    'user_email' => $email,
                    'display_name' => $name,
                    'first_name' => self::extract_first_name($name),
                    'last_name' => self::extract_last_name($name)
                );
                
                WOOGC_Logger::log("Создаем пользователя: " . print_r($user_data, true), $account_id);
                
                $user_id = wp_insert_user($user_data);
                
                if (is_wp_error($user_id)) {
                    WOOGC_Logger::log_error($user_id, $account_id);
                    return new WP_Error('user_creation_failed', 'Не удалось создать пользователя: ' . $user_id->get_error_message(), array('status' => 500));
                }
                
                // Получаем объект созданного пользователя
                $user = get_user_by('ID', $user_id);
                
                // Дополнительно можно отправить пользователю email с данными для входа
                // wp_new_user_notification($user_id, null, 'user');
            }
            
            // Проверяем наличие товара
            $product = wc_get_product($product_id);
            if (!$product) {
                WOOGC_Logger::log("Товар с ID {$product_id} не найден", $account_id, 'error');
                return new WP_Error('product_not_found', 'Товар не найден', array('status' => 400));
            }
            
            // Создаем заказ
            WOOGC_Logger::log("Создаем заказ для пользователя {$user->ID}", $account_id);
            $order = wc_create_order();
            
            // Устанавливаем покупателя
            $order->set_customer_id($user->ID);
            
            // Добавляем товар в заказ
            $order->add_product($product, 1);
            
            // Обновляем адрес плательщика, если доступно
            $order->set_billing_email($email);
            if (!empty($user->first_name)) {
                $order->set_billing_first_name($user->first_name);
            }
            if (!empty($user->last_name)) {
                $order->set_billing_last_name($user->last_name);
            }
            
            // Расчет итогов заказа
            $order->calculate_totals();
            
            // Обновляем статус заказа
            if (isset(self::$payment_status_mapping[$status])) {
                $woocommerce_status = self::$payment_status_mapping[$status];
                $order->update_status($woocommerce_status, 'Статус обновлен через GetCourse callback');
                WOOGC_Logger::log("Установлен статус заказа: {$woocommerce_status}", $account_id);
            } else {
                // Устанавливаем статус по умолчанию - pending
                $order->update_status('pending', 'Получен неизвестный статус из GetCourse: ' . $status);
                WOOGC_Logger::log("Неизвестный статус GetCourse: {$status}, установлен статус по умолчанию", $account_id, 'warning');
            }
            
            // Сохраняем информацию о заказе в метаданных
            $order->update_meta_data('_woogc_account_id', $account_id);
            $order->update_meta_data('_woogc_tariff_id', $product_id);
            $order->save();
            
            // Логируем информацию о созданном заказе
            WOOGC_Logger::log_order_created($order, $account_id);
            
            // Возвращаем успешный ответ
            return new WP_REST_Response(array(
                'status' => 'success',
                'order_id' => $order->get_id(),
                'customer_id' => $user->ID,
                'message' => 'Заказ успешно создан'
            ), 200);
            
        } catch (Exception $e) {
            // Логируем ошибку
            WOOGC_Logger::log_error($e, $account_id);
            return new WP_Error('order_processing_failed', 'Ошибка при обработке заказа: ' . $e->getMessage(), array('status' => 500));
        }
    }
    
    /**
     * Извлекает имя из полного имени
     *
     * @param string $name Полное имя
     * @return string Имя
     */
    private static function extract_first_name($name) {
        $parts = explode(' ', $name, 2);
        return $parts[0];
    }
    
    /**
     * Извлекает фамилию из полного имени
     *
     * @param string $name Полное имя
     * @return string Фамилия
     */
    private static function extract_last_name($name) {
        $parts = explode(' ', $name, 2);
        return isset($parts[1]) ? $parts[1] : '';
    }
}
