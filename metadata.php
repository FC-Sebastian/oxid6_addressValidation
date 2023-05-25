<?php

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => 'fcaddressvalidation',
    'title'       => [
        'de' => 'Adressvalidierung',
        'en' => 'Address validation'
    ],
    'description' => [
        'de' => 'Dieses Modul ermöglicht das importieren von Adressen in die Datenbank mithilfe einer CSV-Datei, um Nutzeradressen während des Check-Outs zu validieren.',
        'en' => 'This module allows you to import addresses to the database using a CSV file, to validate user addresses during checkout.'
    ],
    'version'     => '1.0.0',
    'author'      => 'FC-Sebastian',
    'extend'      => [
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => \Fatchip\AddressValidation\extend\Application\Controller\Admin\ModuleConfiguration::class,
    ],
    'blocks'      => [
        [
            'template' => 'module_config.tpl',
            'block'    => 'admin_module_config_form',
            'file'     => 'admin_module_config_form.tpl'
        ],
        [
            'template' => 'form/fieldset/user_billing.tpl',
            'block'    => 'form_user_billing_country',
            'file'     => 'form_user_billing_country.tpl'
        ]
    ],
    'events'      => [
        'onActivate' => 'Fatchip\AddressValidation\Core\Events::onActivate'
    ],
    'controllers' => [
        'fcAddressAjax' => \Fatchip\AddressValidation\Application\Controller\AddressAjaxController::class
    ]
];