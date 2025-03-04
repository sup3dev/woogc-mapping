<?php
/**
 * Шаблон страницы логов плагина
 *
 * @package WooCommerce GetCourse Integration
 */

// Выход, если файл был вызван напрямую
if (!defined('ABSPATH')) {
    exit;
}

// Подключаем необходимые файлы WordPress
if (!function_exists('settings_errors')) {
    require_once(ABSPATH . 'wp-admin/includes/template.php');
}

// Обработка запроса на удаление выбранных логов
if (isset($_POST['woogc_delete_logs']) && isset($_POST['log_files']) && check_admin_referer('woogc_manage_logs', 'woogc_logs_nonce')) {
    $selected_files = $_POST['log_files'];
    $deleted_count = 0;
    
    foreach ($selected_files as $file_name) {
        $file_name = sanitize_file_name($file_name);
        $file_path = WOOGC_LOG_DIR . $file_name;
        
        if (file_exists($file_path) && is_file($file_path)) {
            if (unlink($file_path)) {
                $deleted_count++;
            }
        }
    }
    
    if ($deleted_count > 0) {
        add_settings_error(
            'woogc_logs',
            'woogc_logs_deleted',
            sprintf(_n('Удален %d лог-файл.', 'Удалено %d лог-файлов.', $deleted_count, 'woo-getcourse'), $deleted_count),
            'updated'
        );
    }
}

// Обработка запроса на скачивание файла лога
if (isset($_GET['download']) && $_GET['download'] == 1 && isset($_GET['file'])) {
    $file_name = sanitize_file_name($_GET['file']);
    $file_path = WOOGC_LOG_DIR . $file_name;
    
    if (file_exists($file_path) && is_file($file_path)) {
        // Установка правильных заголовков для скачивания файла
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=' . $file_name);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        
        // Очистка буфера вывода
        ob_clean();
        flush();
        
        // Считывание файла и отправка содержимого клиенту
        readfile($file_path);
        exit;
    }
}

// Вывод сообщений об ошибках/успешных операциях
settings_errors('woogc_logs');
?>

<div class="wrap woogc-admin">
    <h1 class="wp-heading-inline"><?php _e('Логи WooCommerce GetCourse', 'woo-getcourse'); ?></h1>
    
    <div class="woogc-admin-container">
        <div class="woogc-logs-container">
            <div class="woogc-logs-sidebar">
                <h3><?php _e('Файлы логов', 'woo-getcourse'); ?></h3>
                
                <?php if (empty($log_files)) : ?>
                    <p class="woogc-no-logs"><?php _e('Файлы логов не найдены.', 'woo-getcourse'); ?></p>
                <?php else : ?>
                    <form method="post" action="">
                        <?php wp_nonce_field('woogc_manage_logs', 'woogc_logs_nonce'); ?>
                        
                        <div class="woogc-log-actions">
                            <label>
                                <input type="checkbox" id="select-all-logs"> 
                                <?php _e('Выбрать все', 'woo-getcourse'); ?>
                            </label>
                            <button type="submit" name="woogc_delete_logs" class="button button-secondary" onclick="return confirm('<?php esc_attr_e('Вы уверены, что хотите удалить выбранные логи?', 'woo-getcourse'); ?>');">
                                <?php _e('Удалить выбранные', 'woo-getcourse'); ?>
                            </button>
                        </div>
                        
                        <ul class="woogc-log-files">
                            <?php foreach ($log_files as $log_file) : 
                                $file_name = basename($log_file);
                                $file_date = date('Y-m-d', filemtime($log_file));
                                $active_class = (isset($_GET['file']) && $_GET['file'] === $file_name) ? 'active' : '';
                                // Извлекаем ID аккаунта из имени файла (формат account1_YYYY-MM-DD.log)
                                preg_match('/account(\d+)_/', $file_name, $matches);
                                $account_id = isset($matches[1]) ? $matches[1] : '?';
                            ?>
                                <li class="<?php echo esc_attr($active_class); ?>">
                                    <label class="woogc-log-item">
                                        <input type="checkbox" name="log_files[]" value="<?php echo esc_attr($file_name); ?>" class="log-file-checkbox">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=woogc-logs&file=' . $file_name)); ?>">
                                            <span class="woogc-log-account"><?php printf(__('Аккаунт %s', 'woo-getcourse'), $account_id); ?></span>
                                            <span class="woogc-log-date"><?php echo esc_html($file_date); ?></span>
                                            <span class="woogc-log-size"><?php echo esc_html(size_format(filesize($log_file))); ?></span>
                                        </a>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="woogc-logs-content">
                <?php if (empty($log_content)) : ?>
                    <div class="woogc-log-placeholder">
                        <p><?php _e('Выберите файл лога для просмотра его содержимого.', 'woo-getcourse'); ?></p>
                    </div>
                <?php else : ?>
                    <div class="woogc-log-header">
                        <h3>
                            <?php echo esc_html(isset($_GET['file']) ? $_GET['file'] : ''); ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=woogc-logs&file=' . $_GET['file'] . '&download=1')); ?>" class="button button-secondary woogc-download-log">
                                <?php _e('Скачать лог', 'woo-getcourse'); ?>
                            </a>
                        </h3>
                    </div>
                    
                    <div class="woogc-log-viewer">
                        <pre><?php echo esc_html($log_content); ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>