<?php

return array(
    'router' => array(
        'routes' => array(
            'captcha' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/captcha',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'captcha',
                        'action'        => 'regenerate',
                    ),
                ),
            ),
        )
    ),

    'service_manager' => array(
        'invokables' => array(
            'MailService' => 'Application\Service\Mail\Mail',
            'FirePhpLogger' => 'Application\Logger\Doctrine\FirePhp',
        ),
        'factories' => array(
            'translation/translation' => 'Application\Translation\Factory\TranslationFactory',
            'logger' => 'Application\Logger\Factory\LoggerFactory',
            'cache' => 'Application\Cache\Factory\CacheFilesystemFactory',
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Captcha'  => 'Application\Controller\CaptchaController',
        )
    ),

    'controller_plugins' => array (
        'invokables' => array(
            'logger' => 'Application\Controller\Plugin\Logger',
            'Lang'   => 'Application\Controller\Plugin\Lang',
            'translate' => 'Application\Controller\Plugin\Translate',
        ),
    ),

    'view_manager' => array(
        'display_not_found_reason' => 'development' === APPLICATION_ENV,
        'display_exceptions'       => 'development' === APPLICATION_ENV,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            //'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            //'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'layout/error'            => __DIR__ . '/../view/layout/error.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'template/email/error/html' => __DIR__ . '/../view/templates/email/error/html_message.phtml',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'truncate'   => 'Application\ViewHelper\Truncate',
            'filterLink' => 'Application\ViewHelper\FilterLink',
            'lang'       => 'Application\ViewHelper\Lang',
            'errorMessage' => 'Application\ViewHelper\ErrorMessage',
            'stripTags'    => 'Application\ViewHelper\StripTags',
        ),
    ),

    'view_helper_config' => array(
        'flashmessenger' => array(
            'message_open_format'      => '<div%s><ul><li>',
            'message_close_string'     => '</li></ul></div>',
            'message_separator_string' => '</li><li>',
        ),
    ),

    // Translation
    'translator' => array(
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
);
