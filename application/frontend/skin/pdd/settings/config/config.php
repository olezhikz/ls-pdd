<?php

$config = array();

$config['view']['grid']['breakpoint'] = 'md';  //граница сворачивания панели блоков  вниз
$config['view']['grid']['collapse'] = 'sm'; // Граница сворачивания меню

// Подключение ресурсов шаблона
$config['assets']['template'] = [
    'js' => array(
        //"https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js",
        'recaptcha' => array(
            'file' => "https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit",
            'loader' => LS\Module\Asset\Loader\RemoteLoader::class
            ),
        '___path.skin.assets.server___/js/init.js',
    ),

    // Подключение стилей шаблона
    'css' => array(
        "___path.skin.assets.server___/css/layout.css",
        "___path.skin.assets.server___/css/print.css"
        ),
    'img' => [
        "logo" => "___path.skin.assets.server___/images/logo.png"
    ]
];  


$config['view']['bs_themes'] = [
    'default' => [
        'color' => 'light',
        'bg' => 'light'
    ],
    'light' => [
        'color' => 'light',
        'bg' => 'light'
    ],

];

// Подключение темы
$theme = Config::Get('view.theme')?Config::Get('view.theme'):'default'; 

$config['view']['bs_theme'] = $config['view']['bs_themes'][$theme];

$config['components'] = [
    // Базовые компоненты
    'ls-vendor', 
    'ls-core', 
    'ls-component', 
    'notification', 
    'performance', 
    'confirm', 
    'lightbox', 
    'bootstrap',
    'tinymce',
    
    //Компоненты шаблона
    'dropdown', 
    'form', 
    'pagination', 
    'nav', 
    'ajax',
    'icon', 
    'autocomplete',
    'popover',
    'text', 
    'button'
];

/**
 * SEO
 */

// Тег используемый для заголовков топиков
$config['view']['seo']['topic_heading'] = 'h1';
$config['view']['seo']['topic_heading_list'] = 'h2';

$config['block']['userProfile'] = array(
    'action' => array(
        'profile' => [
            '{profile}'
        ]
    ),
    'blocks' => array(
        'left' => array(
            'component@user.block-photo'    => array('priority' => 100),
            'menuProfile'      => array('priority' => 99),
            'component@user.block-actions'  => array('priority' => 98),
        )
    )
);

$config['block']['moderation'] = array(
    'action' => array(
        'moderation' => [
            '{moderation}'
        ]
    ),
    'blocks' => array(
        'left' => array(
            'menu' => ['priority' => 99, 'params' => ['name' => 'moderation', "template" => "profile"]]
        )
    )
);

$config['block']['settingsProfile'] = array(
    'action' => array(
        'profile' => [
            '{settings}','{notices}','{security}'
        ]
    ),
    'blocks' => array(
        'left' => array(
            'menuSettings'     => array('priority' => 100)
            
        )
    )
);

return $config;
