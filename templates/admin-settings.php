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
            <p><?php _e('Настройте сопоставление тарифов GetCourse с товарами WooCommerce.', 'woo-getcourse'); ?></p>
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
                        <p><?php _e('Используйте следующий URL для настройки отправки данных из GetCourse:', 'woo-getcourse'); ?></p>
                        <code class="woogc-endpoint-url"><?php echo esc_url(rest_url('woogc/v1/account1')); ?></code>
                        <button type="button" class="button woogc-copy-endpoint" data-endpoint="<?php echo esc_url(rest_url('woogc/v1/account1')); ?>"><?php _e('Копировать', 'woo-getcourse'); ?></button>
                    </div>
                    
                    <div class="woogc-mapping-container">
                        <h4><?php _e('Сопоставление тарифов', 'woo-getcourse'); ?></h4>
                        <p><?php _e('Укажите соответствие между ID тарифов GetCourse и товарами WooCommerce:', 'woo-getcourse'); ?></p>
                        
                        <table class="woogc-mapping-table widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('ID тарифа GetCourse', 'woo-getcourse'); ?></th>
                                    <th><?php _e('Товар WooCommerce', 'woo-getcourse'); ?></th>
                                    <th><?php _e('Действия', 'woo-getcourse'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($account1_mapping)) : ?>
                                    <tr class="woogc-empty-row">
                                        <td colspan="3"><?php _e('Сопоставления не настроены. Добавьте первое сопоставление.', 'woo-getcourse'); ?></td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($account1_mapping as $getcourse_id => $woo_product_id) : ?>
                                        <tr data-getcourse-id="<?php echo esc_attr($getcourse_id); ?>">
                                            <td>
                                                <input type="text" class="regular-text woogc-getcourse-id" value="<?php echo esc_attr($getcourse_id); ?>" readonly>
                                            </td>
                                            <td>
                                                <select class="woogc-woo-product-id">
                                                    <?php foreach ($products as $product) : ?>
                                                        <option value="<?php echo esc_attr($product->get_id()); ?>" <?php selected($woo_product_id, $product->get_id()); ?>>
                                                            <?php echo esc_html($product->get_name() . ' (ID: ' . $product->get_id() . ')'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <button type="button" class="button woogc-save-mapping" data-account="1"><?php _e('Сохранить', 'woo-getcourse'); ?></button>
                                                <button type="button" class="button woogc-delete-mapping" data-account="1"><?php _e('Удалить', 'woo-getcourse'); ?></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3">
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
                        <p><?php _e('Используйте следующий URL для настройки отправки данных из GetCourse:', 'woo-getcourse'); ?></p>
                        <code class="woogc-endpoint-url"><?php echo esc_url(rest_url('woogc/v1/account2')); ?></code>
                        <button type="button" class="button woogc-copy-endpoint" data-endpoint="<?php echo esc_url(rest_url('woogc/v1/account2')); ?>"><?php _e('Копировать', 'woo-getcourse'); ?></button>
                    </div>
                    
                    <div class="woogc-mapping-container">
                        <h4><?php _e('Сопоставление тарифов', 'woo-getcourse'); ?></h4>
                        <p><?php _e('Укажите соответствие между ID тарифов GetCourse и товарами WooCommerce:', 'woo-getcourse'); ?></p>
                        
                        <table class="woogc-mapping-table widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('ID тарифа GetCourse', 'woo-getcourse'); ?></th>
                                    <th><?php _e('Товар WooCommerce', 'woo-getcourse'); ?></th>
                                    <th><?php _e('Действия', 'woo-getcourse'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($account2_mapping)) : ?>
                                    <tr class="woogc-empty-row">
                                        <td colspan="3"><?php _e('Сопоставления не настроены. Добавьте первое сопоставление.', 'woo-getcourse'); ?></td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($account2_mapping as $getcourse_id => $woo_product_id) : ?>
                                        <tr data-getcourse-id="<?php echo esc_attr($getcourse_id); ?>">
                                            <td>
                                                <input type="text" class="regular-text woogc-getcourse-id" value="<?php echo esc_attr($getcourse_id); ?>" readonly>
                                            </td>
                                            <td>
                                                <select class="woogc-woo-product-id">
                                                    <?php foreach ($products as $product) : ?>
                                                        <option value="<?php echo esc_attr($product->get_id()); ?>" <?php selected($woo_product_id, $product->get_id()); ?>>
                                                            <?php echo esc_html($product->get_name() . ' (ID: ' . $product->get_id() . ')'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <button type="button" class="button woogc-save-mapping" data-account="2"><?php _e('Сохранить', 'woo-getcourse'); ?></button>
                                                <button type="button" class="button woogc-delete-mapping" data-account="2"><?php _e('Удалить', 'woo-getcourse'); ?></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3">
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
                                    <p class="description"><?php _e('Отправлять пользователям уведомления о создании учетной записи.', 'woo-getcourse'); ?></p>
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
