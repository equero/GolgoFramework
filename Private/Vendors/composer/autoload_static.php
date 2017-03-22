<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit413ae72513b6ead33c41bd53b1b4031b
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Predis\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Predis\\' => 
        array (
            0 => __DIR__ . '/..' . '/predis/predis/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit413ae72513b6ead33c41bd53b1b4031b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit413ae72513b6ead33c41bd53b1b4031b::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
