<?php

/**
 * NOTICE OF LICENSE
 *
 * This file is licensed under the Software License Agreement.
 *
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author Arkonsoft
 * @copyright 2017-2023 Arkonsoft
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class EditableBannerConfig extends ObjectModel {
    public $id_banner;
    public $is_visible = true;
    public $banner_text = 'Sample Text';

    public static $definition = array(
        'table' => 'editablebannerconfig',
        'primary' => 'id_banner',
        'fields' => array(
            'is_visible' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'banner_text' => array('type' => self::TYPE_HTML, 'validate' => 'isString', 'required' => true, 'size' => 65535)
        )
    );
}