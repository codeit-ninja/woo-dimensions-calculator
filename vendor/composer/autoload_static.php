<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6cf2b60e3d3433e6be0e225e3bf11c84
{
    public static $files = array (
        '73077c568af3564ae20d63feeb9f5d1f' => __DIR__ . '/../..' . '/inc/options-class.php',
        'd33611bbb09d6fa598d975a2cc9f648e' => __DIR__ . '/../..' . '/inc/updater-class.php',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit6cf2b60e3d3433e6be0e225e3bf11c84::$classMap;

        }, null, ClassLoader::class);
    }
}
