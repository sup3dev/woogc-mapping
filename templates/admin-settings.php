<?php
/**
 * Шаблон страницы настроек плагина
 *
 * @package WooCommerce GetCourse Integration
 */

// Выход, если файл был вызван напрямую
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap woogc-admin">
    <h1 class="wp-heading-inline"><?php _e('Настройки WooCommerce GetCourse', 'woo-getcourse'); ?></h1>
    
    <div class="woogc-notice-area"></div>
    
    <div class="woogc-admin-container">
        <div class="woogc-admin-header">
            <h2><?php _e('Интеграция с GetCourse', 'woo-getcourse'); ?></h2>
            <p><?php _e('Настройте сопоставление тарифов GetCourse с товарами WooCommerce и ролями пользователей.', 'woo-getcourse'); ?></p>
        </div>
        
        <div class="woogc-admin-tabs">
            <ul class="woogc-tabs-nav">
                <li class="active"><a href="#account1"><?php echo esc_html($account_names['1']); ?></a></li>
                <li><a href="#account2"><?php echo esc_html($account_names['2']); ?></a></li>
                <li><a href="#settings"><?php _e('Основные настройки', 'woo-getcourse'); ?></a></li>
            </ul>
            
            <div class="woogc-tabs-content">
                <!-- Вкладка первого аккаунта -->
                <div id="account1" class="woogc-tab-content active">
                    <div class="woogc-account-header">
                        <h3><?php echo esc_html($account_names['1']); ?></h3>
                        <div class="woogc-account-actions">
                            <input type="text" id="account1-name" value="<?php echo esc_attr($account_names['1']); ?>" class="regular-text">
                            <button type="button" class="button woogc-save-account-name" data-account="1"><?php _e('Сохранить название', 'woo-getcourse'); ?></button>
                        </div>
                    </div>
                    
                    <div class="woogc-endpoint-info">
                        <h4><?php _e('Информация об Endpoint', 'woo-getcourse'); ?></h4>
                        <p><?php _e('Используйте следующие URL для настройки отправки данных из GetCourse:', 'woo-getcourse'); ?></p>
                        
                        <div class="woogc-endpoint-item">
                            <span class="woogc-endpoint-label"><?php _e('Новый URL:', 'woo-getcourse'); ?></span>
                            <code class="woogc-endpoint-url"><?php echo esc_url(rest_url('woogc/v1/account1')); ?></code>
                            <button type="button" class="button woogc-copy-endpoint" data-endpoint="<?php echo esc_url(rest_url('woogc/v1/account1')); ?>"><?php _e('Копировать', 'woo-getcourse'); ?></button>
                        </div>
                        
                        <div class="woogc-endpoint-item">
                            <span class="woogc-endpoint-label"><?php _e('Старый URL (для совместимости):', 'woo-getcourse'); ?></span>
                            <code class="woogc-endpoint-url"><?php echo esc_url(rest_url('getcourse/v1/buy')); ?></code>
                            <button type="button" class="button woogc-copy-endpoint" data-endpoint="<?php echo esc_url(rest_url('getcourse/v1/buy')); ?>"><?php _e('Копировать', 'woo-getcourse'); ?></button>
                        </div>
                    </div>
                    
                    <div class="woogc-mapping-container">
                        <h4><?php _e('Сопоставление тарифов', 'woo-getcourse'); ?></h4>
                        <p><?php _e('Укажите соответствие между ID тарифов GetCourse, товарами WooCommerce и ролями пользователей:', 'woo-getcourse'); ?></p>
                        
                        <table class="woogc-mapping-table widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('ID тарифа GetCourse', 'woo-getcourse'); ?></th>
                                    <th><?php _e('Товар WooCommerce', 'woo-getcourse'); ?></th>
                                    <th><?php _e('Роль после покупки', 'woo-getcourse'); ?></th>
                                    <th><?php _e('Действия', 'woo-getcourse'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($account1_mapping)) : ?>
                                    <tr class="woogc-empty-row">
                                        <td colspan="4"><?php _e('Сопоставления не настроены. Добавьте первое сопоставление.', 'woo-getcourse'); ?></td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($account1_mapping as $getcourse_id => $mapping_data) : ?>
                                        <?php
                                        // Включаем шаблон строки сопоставления
                                        $account_id = '1';
                                        include WOOGC_PLUGIN_DIR . 'templates/mapping-row.php';
                                        ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4">
                                        <button type="button" class="button woogc-add-mapping-row" data-account="1"><?php _e('Добавить сопоставление', 'woo-getcourse'); ?></button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <!-- Вкладка второго аккаунта -->
                <div id="account2" class="woogc-tab-content">
                    <div class="woogc-account-header">
                        <h3><?php echo esc_html($account_names['2']); ?></h3>
                        <div class="woogc-account-actions">
                            <input type="text" id="account2-name" value="<?php echo esc_attr($account_names['2']); ?>" class="regular-text">
                            <button type="button" class="button woogc-save-account-name" data-account="2"><?php _e('Сохранить название', 'woo-getcourse'); ?></button>
                        </div>
                    </div>
                    
                    <div class="woogc-endpoint-info">
                        <h4><?php _e('Информация об Endpoint', 'woo-getcourse'); ?></h4>
                        <p><?php _e('Используйте следующие URL для настройки отправки данных из GetCourse:', 'woo-getcourse'); ?></p>
                        
                        <div class="woogc-endpoint-item">
                            <span class="woogc-endpoint-label"><?php _e('Новый URL:', 'woo-getcourse'); ?></span>
                            <code class="woogc-endpoint-url"><?php echo esc_url(rest_url('woogc/v1/account2')); ?></code>
                            <button type="button" class="button woogc-copy-endpoint" data-endpoint="<?php echo esc_url(rest_url('woogc/v1/account2')); ?>"><?php _e('Копировать', 'woo-getcourse'); ?></button>
                        </div>
                        
                        <div class="woogc-endpoint-item">
                            <span class="woogc-endpoint-label"><?php _e('Старый URL (для совместимости):', 'woo-getcourse'); ?></span>
                            <code class="woogc-endpoint-url"><?php echo esc_url(rest_url('getcourse/v1/buy2')); ?></code>
                            <button type="button" class="button woogc-copy-endpoint" data-endpoint="<?php echo esc_url(rest_url('getcourse/v1/buy2')); ?>"><?php _e('Копировать', 'woo-getcourse'); ?></button>
                        </div>
                    </div>
                    
                    <div class="woogc-mapping-container">
                        <h4><?php _e('Сопоставление тарифов', 'woo-getcourse'); ?></h4>
                        <p><?php _e('Укажите соответствие между ID тарифов GetCourse, товарами WooCommerce и ролями пользователей:', 'woo-getcourse'); ?></p>
                        
                        <table class="woogc-mapping-table widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('ID тарифа GetCourse', 'woo-getcourse'); ?></th>
                                    <th><?php _e('Товар WooCommerce', 'woo-getcourse'); ?></th>
                                    <th><?php _e('Роль после покупки', 'woo-getcourse'); ?></th>
                                    <th><?php _e('Действия', 'woo-getcourse'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($account2_mapping)) : ?>
                                    <tr class="woogc-empty-row">
                                        <td colspan="4"><?php _e('Сопоставления не настроены. Добавьте первое сопоставление.', 'woo-getcourse'); ?></td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($account2_mapping as $getcourse_id => $mapping_data) : ?>
                                        <?php
                                        // Включаем шаблон строки сопоставления
                                        $account_id = '2';
                                        include WOOGC_PLUGIN_DIR . 'templates/mapping-row.php';
                                        ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4">
                                        <button type="button" class="button woogc-add-mapping-row" data-account="2"><?php _e('Добавить сопоставление', 'woo-getcourse'); ?></button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <!-- Вкладка основных настроек -->
                <div id="settings" class="woogc-tab-content">
                    <h3><?php _e('Основные настройки', 'woo-getcourse'); ?></h3>
                    
                    <form id="woogc-settings-form" method="post" action="">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="woogc-log-enabled"><?php _e('Включить логирование', 'woo-getcourse'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="woogc-log-enabled" name="woogc_log_enabled" value="1" <?php checked(get_option('woogc_log_enabled', '1'), '1'); ?>>
                                    <p class="description"><?php _e('Записывать все действия в лог-файлы для отладки.', 'woo-getcourse'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="woogc-email-notification"><?php _e('Email-уведомления пользователям', 'woo-getcourse'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="woogc-email-notification" name="woogc_email_notification" value="1" <?php checked(get_option('woogc_email_notification', '0'), '1'); ?>>
                                    <p class="description"><?php _e('Отправлять пользователям уведомления о создании учетной записи (стандартное уведомление WordPress).', 'woo-getcourse'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="woogc-default-role"><?php _e('Роль для новых пользователей', 'woo-getcourse'); ?></label>
                                </th>
                                <td>
                                    <select id="woogc-default-role" name="woogc_default_role">
                                        <option value=""><?php _e('По умолчанию WordPress', 'woo-getcourse'); ?></option>
                                        <?php 
                                        // Получаем все роли в системе
                                        $roles = wp_roles()->get_names();
                                        $default_role = get_option('woogc_default_role', '');
                                        foreach ($roles as $role_id => $role_name) : ?>
                                            <option value="<?php echo esc_attr($role_id); ?>" <?php selected($default_role, $role_id); ?>>
                                                <?php echo esc_html($role_name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description"><?php _e('Выберите роль, которая будет назначаться при создании новых пользователей.', 'woo-getcourse'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="woogc-add-delay"><?php _e('Добавить задержку обработки', 'woo-getcourse'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="woogc-add-delay" name="woogc_add_delay" value="1" <?php checked(get_option('woogc_add_delay', '0'), '1'); ?>>
                                    <p class="description"><?php _e('Добавить принудительную задержку при обработке заказа.', 'woo-getcourse'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="woogc-delay-seconds"><?php _e('Время задержки (сек)', 'woo-getcourse'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="woogc-delay-seconds" name="woogc_delay_seconds" value="<?php echo esc_attr(get_option('woogc_delay_seconds', '5')); ?>" min="1" max="30">
                                    <p class="description"><?php _e('Количество секунд для принудительной задержки при обработке заказа.', 'woo-getcourse'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <button type="submit" class="button button-primary" id="woogc-save-settings"><?php _e('Сохранить настройки', 'woo-getcourse'); ?></button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>