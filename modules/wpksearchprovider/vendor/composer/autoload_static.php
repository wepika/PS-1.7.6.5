<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5ea5cf65bd110be032dace5b5b5867f6
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Wpk\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Wpk\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Wpk\\Classes\\WpkProductSearchProvider' => __DIR__ . '/../..' . '/src/classes/WpkProductSearchProvider.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5ea5cf65bd110be032dace5b5b5867f6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5ea5cf65bd110be032dace5b5b5867f6::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5ea5cf65bd110be032dace5b5b5867f6::$classMap;

        }, null, ClassLoader::class);
    }
}
