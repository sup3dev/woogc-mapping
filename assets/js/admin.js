/**
 * JavaScript для административного интерфейса
 *
 * @package WooCommerce GetCourse Integration
 */

(function($) {
    'use strict';
    
    /**
     * Объект для управления административным интерфейсом
     */
    const WooGCAdmin = {
        /**
         * Инициализация
         */
        init: function() {
            this.cacheDom();
            this.bindEvents();
            this.initTabs();
        },
        
        /**
         * Кэширование DOM элементов
         */
        cacheDom: function() {
            this.$tabs = $('.woogc-tabs-nav li');
            this.$tabContent = $('.woogc-tab-content');
            this.$addMappingRow = $('.woogc-add-mapping-row');
            this.$mappingTables = $('.woogc-mapping-table tbody');
            this.$saveMapping = $('.woogc-save-mapping');
            this.$deleteMapping = $('.woogc-delete-mapping');
            this.$saveAccountName = $('.woogc-save-account-name');
            this.$copyEndpoint = $('.woogc-copy-endpoint');
            this.$settingsForm = $('#woogc-settings-form');
            this.$noticeArea = $('.woogc-notice-area');
        },
        
        /**
         * Привязка событий
         */
        bindEvents: function() {
            // Переключение вкладок
            this.$tabs.on('click', 'a', this.handleTabClick.bind(this));
            
            // Добавление новой строки сопоставления
            this.$addMappingRow.on('click', this.handleAddMappingRow.bind(this));
            
            // Сохранение сопоставления
            $(document).on('click', '.woogc-save-mapping', this.handleSaveMapping.bind(this));
            
            // Удаление сопоставления
            $(document).on('click', '.woogc-delete-mapping', this.handleDeleteMapping.bind(this));
            
            // Сохранение имени аккаунта
            this.$saveAccountName.on('click', this.handleSaveAccountName.bind(this));
            
            // Копирование URL эндпоинта
            this.$copyEndpoint.on('click', this.handleCopyEndpoint.bind(this));
            
            // Сохранение общих настроек
            this.$settingsForm.on('submit', this.handleSaveSettings.bind(this));
        },
        
        /**
         * Инициализация вкладок
         */
        initTabs: function() {
            // Проверяем, есть ли хэш в URL
            const hash = window.location.hash;
            if (hash) {
                const $tab = this.$tabs.find('a[href="' + hash + '"]');
                if ($tab.length) {
                    $tab.trigger('click');
                }
            }
        },
        
        /**
         * Обработчик клика по вкладке
         */
        handleTabClick: function(e) {
            e.preventDefault();
            
            const $clickedTab = $(e.currentTarget);
            const target = $clickedTab.attr('href');
            
            // Убираем активный класс со всех вкладок
            this.$tabs.removeClass('active');
            this.$tabContent.removeClass('active');
            
            // Добавляем активный класс к выбранной вкладке
            $clickedTab.parent().addClass('active');
            $(target).addClass('active');
            
            // Обновляем URL с хешем
            window.location.hash = target;
        },
        
        /**
         * Обработчик добавления новой строки сопоставления
         */
        handleAddMappingRow: function(e) {
            const $button = $(e.currentTarget);
            const accountId = $button.data('account');
            const $table = $button.closest('table').find('tbody');
            
            // Удаляем строку с сообщением о пустой таблице, если она есть
            $table.find('.woogc-empty-row').remove();
            
            // Отправляем AJAX запрос для получения HTML новой строки
            $.ajax({
                url: woogc_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'woogc_add_mapping_row',
                    nonce: woogc_admin.nonce,
                    account_id: accountId
                },
                success: function(response) {
                    if (response.success) {
                        // Добавляем новую строку в таблицу
                        $table.append(response.data.html);
                    } else {
                        WooGCAdmin.showNotice(response.data, 'error');
                    }
                },
                error: function() {
                    WooGCAdmin.showNotice(woogc_admin.error, 'error');
                }
            });
        },
        
        /**
         * Обработчик сохранения сопоставления
         */
        handleSaveMapping: function(e) {
            const $button = $(e.currentTarget);
            const accountId = $button.data('account');
            const $row = $button.closest('tr');
            const getcourseId = $row.find('.woogc-getcourse-id').val();
            const woocommerceId = $row.find('.woogc-woo-product-id').val();
            
            // Проверяем, что ID тарифа GetCourse не пустой
            if (!getcourseId) {
                WooGCAdmin.showNotice('ID тарифа GetCourse не может быть пустым', 'error');
                return;
            }
            
            // Отправляем AJAX запрос для сохранения сопоставления
            $.ajax({
                url: woogc_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'woogc_save_mapping',
                    nonce: woogc_admin.nonce,
                    account_id: accountId,
                    getcourse_id: getcourseId,
                    woocommerce_id: woocommerceId
                },
                success: function(response) {
                    if (response.success) {
                        WooGCAdmin.showNotice('Сопоставление успешно сохранено', 'success');
                        
                        // Если это новая строка, делаем поле ID тарифа readonly
                        if (!$row.data('getcourse-id')) {
                            $row.attr('data-getcourse-id', getcourseId);
                            $row.find('.woogc-getcourse-id').attr('readonly', 'readonly');
                        }
                    } else {
                        WooGCAdmin.showNotice(response.data, 'error');
                    }
                },
                error: function() {
                    WooGCAdmin.showNotice(woogc_admin.error, 'error');
                }
            });
        },
        
        /**
         * Обработчик удаления сопоставления
         */
        handleDeleteMapping: function(e) {
            const $button = $(e.currentTarget);
            const accountId = $button.data('account');
            const $row = $button.closest('tr');
            const getcourseId = $row.data('getcourse-id') || $row.find('.woogc-getcourse-id').val();
            
            // Если строка не сохранена, просто удаляем её из DOM
            if (!$row.data('getcourse-id')) {
                $row.remove();
                return;
            }
            
            // Запрашиваем подтверждение
            if (!confirm(woogc_admin.confirm_delete)) {
                return;
            }
            
            // Отправляем AJAX запрос для удаления сопоставления
            $.ajax({
                url: woogc_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'woogc_delete_mapping',
                    nonce: woogc_admin.nonce,
                    account_id: accountId,
                    getcourse_id: getcourseId
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                            
                            // Если это была последняя строка, добавляем сообщение о пустой таблице
                            const $tbody = $('#account' + accountId).find('.woogc-mapping-table tbody');
                            if ($tbody.find('tr').length === 0) {
                                $tbody.html('<tr class="woogc-empty-row"><td colspan="3">Сопоставления не настроены. Добавьте первое сопоставление.</td></tr>');
                            }
                        });
                        
                        WooGCAdmin.showNotice('Сопоставление успешно удалено', 'success');
                    } else {
                        WooGCAdmin.showNotice(response.data, 'error');
                    }
                },
                error: function() {
                    WooGCAdmin.showNotice(woogc_admin.error, 'error');
                }
            });
        },
        
        /**
         * Обработчик сохранения имени аккаунта
         */
        handleSaveAccountName: function(e) {
            const $button = $(e.currentTarget);
            const accountId = $button.data('account');
            const accountName = $('#account' + accountId + '-name').val();
            
            // Отправляем AJAX запрос для сохранения имени аккаунта
            $.ajax({
                url: woogc_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'woogc_save_account_name',
                    nonce: woogc_admin.nonce,
                    account_id: accountId,
                    account_name: accountName
                },
                success: function(response) {
                    if (response.success) {
                        // Обновляем имя в заголовке вкладки и раздела
                        $('.woogc-tabs-nav li a[href="#account' + accountId + '"]').text(accountName);
                        $('#account' + accountId + ' .woogc-account-header h3').text(accountName);
                        
                        WooGCAdmin.showNotice('Имя аккаунта успешно сохранено', 'success');
                    } else {
                        WooGCAdmin.showNotice(response.data, 'error');
                    }
                },
                error: function() {
                    WooGCAdmin.showNotice(woogc_admin.error, 'error');
                }
            });
        },
        
        /**
         * Обработчик копирования URL эндпоинта
         */
        handleCopyEndpoint: function(e) {
            const $button = $(e.currentTarget);
            const endpoint = $button.data('endpoint');
            
            // Создаем временный элемент textarea
            const $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(endpoint).select();
            
            // Копируем текст из textarea
            document.execCommand('copy');
            
            // Удаляем временный элемент
            $temp.remove();
            
            // Показываем уведомление
            WooGCAdmin.showNotice('URL эндпоинта скопирован в буфер обмена', 'success');
            
            // Меняем текст кнопки на время
            const originalText = $button.text();
            $button.text('Скопировано!');
            
            // Возвращаем оригинальный текст через 2 секунды
            setTimeout(function() {
                $button.text(originalText);
            }, 2000);
        },
        
        /**
         * Обработчик сохранения общих настроек
         */
        handleSaveSettings: function(e) {
            e.preventDefault();
            
            const $form = $(e.currentTarget);
            const formData = $form.serialize();
            
            // Отправляем AJAX запрос для сохранения настроек
            $.ajax({
                url: woogc_admin.ajax_url,
                type: 'POST',
                data: formData + '&action=woogc_save_settings&nonce=' + woogc_admin.nonce,
                success: function(response) {
                    if (response.success) {
                        WooGCAdmin.showNotice('Настройки успешно сохранены', 'success');
                    } else {
                        WooGCAdmin.showNotice(response.data, 'error');
                    }
                },
                error: function() {
                    WooGCAdmin.showNotice(woogc_admin.error, 'error');
                }
            });
        },
        
        /**
         * Показывает уведомление
         */
        showNotice: function(message, type) {
            const $notice = $('<div class="woogc-notice woogc-notice-' + type + '">' + message + '</div>');
            
            this.$noticeArea.html($notice);
            
            // Прокручиваем страницу к уведомлению
            $('html, body').animate({
                scrollTop: this.$noticeArea.offset().top - 50
            }, 200);
            
            // Скрываем уведомление через 5 секунд
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };
    
    // Инициализация после загрузки DOM
    $(function() {
        WooGCAdmin.init();
    });
    
})(jQuery);
