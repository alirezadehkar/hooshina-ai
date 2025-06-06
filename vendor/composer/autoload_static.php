<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite2da4ffd8f564c51caf01bef60b9ba5f
{
    public static $prefixLengthsPsr4 = array (
        'N' => 
        array (
            'Netcarver\\Textile\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Netcarver\\Textile\\' => 
        array (
            0 => __DIR__ . '/..' . '/netcarver/textile/src/Netcarver/Textile',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'Parsedown' => 
            array (
                0 => __DIR__ . '/..' . '/erusev/parsedown',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite2da4ffd8f564c51caf01bef60b9ba5f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite2da4ffd8f564c51caf01bef60b9ba5f::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInite2da4ffd8f564c51caf01bef60b9ba5f::$prefixesPsr0;
            $loader->classMap = ComposerStaticInite2da4ffd8f564c51caf01bef60b9ba5f::$classMap;

        }, null, ClassLoader::class);
    }
}
