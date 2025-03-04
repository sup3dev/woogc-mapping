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

// Определяем, редактирование это или создание новой строки
$is_edit_mode = !empty($getcourse_id);

// Получаем текущие значения для существующего сопоставления
if ($is_edit_mode) {
    $mapping_data = $account_id === '1' ? $account1_mapping[$getcourse_id] : $account2_mapping[$getcourse_id];
    
    // Обработка старого формата данных (для обратной совместимости)
    if (!is_array($mapping_data)) {
        $woo_product_id = $mapping_data;
        $selected_role = '';
    } else {
        $woo_product_id = isset($mapping_data['product_id']) ? $mapping_data['product_id'] : '';
        $selected_role = isset($mapping_data['role']) ? $mapping_data['role'] : '';
    }
} else {
    $woo_product_id = '';
    $selected_role = '';
}
?>

<tr <?php echo $is_edit_mode ? 'data-getcourse-id="' . esc_attr($getcourse_id) . '"' : ''; ?>>
    <td>
        <input type="text" class="regular-text woogc-getcourse-id" value="<?php echo $is_edit_mode ? esc_attr($getcourse_id) : ''; ?>" placeholder="<?php _e('ID тарифа GetCourse', 'woo-getcourse'); ?>" required>
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
        <select class="woogc-user-role">
            <option value=""><?php _e('Не менять роль', 'woo-getcourse'); ?></option>
            <?php 
            // Получаем все роли в системе
            $roles = wp_roles()->get_names();
            foreach ($roles as $role_id => $role_name) : ?>
                <option value="<?php echo esc_attr($role_id); ?>" <?php selected($selected_role, $role_id); ?>>
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