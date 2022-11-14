<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite1fffe366e47f946936d64b71218b16d
{
    public static $files = array (
        '5255c38a0faeba867671b61dfda6d864' => __DIR__ . '/..' . '/paragonie/random_compat/lib/random.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Finder\\' => 25,
        ),
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
            'PhpZip\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Finder\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/finder',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'PhpZip\\' => 
        array (
            0 => __DIR__ . '/..' . '/nelexa/zip/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite1fffe366e47f946936d64b71218b16d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite1fffe366e47f946936d64b71218b16d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite1fffe366e47f946936d64b71218b16d::$classMap;

        }, null, ClassLoader::class);
    }
}
