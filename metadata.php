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
        'de' => 'deutsch placeholder',
        'en' => 'english placeholder'
    ],
    'version'     => '1.0.0',
    'author'      => 'FC-Sebastian',
    'extend'      => [
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => \Fatchip\AddressValidation\extend\ModuleConfiguration::class
    ],
    'blocks'      => [
        [
            'template' => 'module_config.tpl',
            'block'    => 'admin_module_config_form',
            'file'     => 'admin_module_config_form.tpl'
        ]
    ]
];