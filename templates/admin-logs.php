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
                                <a href="<?php echo esc_url(admin_url('admin.php?page=woogc-logs&file=' . $file_name)); ?>">
                                    <span class="woogc-log-account"><?php printf(__('Аккаунт %s', 'woo-getcourse'), $account_id); ?></span>
                                    <span class="woogc-log-date"><?php echo esc_html($file_date); ?></span>
                                    <span class="woogc-log-size"><?php echo esc_html(size_format(filesize($log_file))); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
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
