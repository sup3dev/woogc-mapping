<?php
/**
 * Шаблон строки сопоставления для таблицы
 *
 * @package WooCommerce GetCourse Integration
 */

// Выход, если файл был вызван напрямую
if (!defined('ABSPATH')) {
    exit;
}
?>

<tr>
    <td>
        <input type="text" class="regular-text woogc-getcourse-id" placeholder="<?php _e('ID тарифа GetCourse', 'woo-getcourse'); ?>" required>
    </td>
    <td>
        <select class="woogc-woo-product-id">
            <?php foreach ($products as $product) : ?>
                <option value="<?php echo esc_attr($product->get_id()); ?>">
                    <?php echo esc_html($product->get_name() . ' (ID: ' . $product->get_id() . ')'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
    <td>
        <select class="woogc-user-role">
            <option value=""><?php _e('Не менять роль', 'woo-getcourse'); ?></option>
            <?php 
            // Получаем все роли в системе
            $roles = wp_roles()->get_names();
            foreach ($roles as $role_id => $role_name) : 
                $selected = '';
                if (isset($mapping[$getcourse_id]) && is_array($mapping[$getcourse_id]) && $mapping[$getcourse_id]['role'] === $role_id) {
                    $selected = 'selected';
                }
            ?>
                <option value="<?php echo esc_attr($role_id); ?>" <?php echo $selected; ?>>
                    <?php echo esc_html($role_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
    <td>
        <button type="button" class="button woogc-save-mapping" data-account="<?php echo esc_attr($account_id); ?>"><?php _e('Сохранить', 'woo-getcourse'); ?></button>
        <button type="button" class="button woogc-delete-mapping" data-account="<?php echo esc_attr($account_id); ?>"><?php _e('Удалить', 'woo-getcourse'); ?></button>
    </td>
</tr>
