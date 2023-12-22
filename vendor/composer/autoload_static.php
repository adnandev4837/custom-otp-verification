<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit68c1389b167030c58e399cbf1a544caf
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Twilio\\' => 7,
        ),
        'L' => 
        array (
            'Leed\\CustomOtpVerification\\' => 27,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Twilio\\' => 
        array (
            0 => __DIR__ . '/..' . '/twilio/sdk/src/Twilio',
        ),
        'Leed\\CustomOtpVerification\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit68c1389b167030c58e399cbf1a544caf::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit68c1389b167030c58e399cbf1a544caf::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit68c1389b167030c58e399cbf1a544caf::$classMap;

        }, null, ClassLoader::class);
    }
}
