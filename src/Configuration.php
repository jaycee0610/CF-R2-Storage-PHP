<?php

namespace Rootscratch\Cloudstorage;

use Aws\S3\S3Client;

class Configuration
{
    public static $s3;
    public static $bucketName;
    public static $endpoint;
    public static $accessKey;
    public static $secretKey;

    public function __construct()
    {
        // Initialize S3 Client for R2
        self::$s3 = new S3Client([
            'region' => 'auto',
            'version' => 'latest',
            'endpoint' => self::$endpoint,  // Use self:: for static properties
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => self::$accessKey,
                'secret' => self::$secretKey,
            ],
        ]);
    }

    // Static Methods to Set Configurations
    public static function setEndpoint($endpoint)
    {
        self::$endpoint = $endpoint;
    }

    public static function setBucketName($bucketName)
    {
        self::$bucketName = $bucketName;
    }

    public static function setAccessKey($accessKey)
    {
        self::$accessKey = $accessKey;
    }

    public static function setSecretKey($secretKey)
    {
        self::$secretKey = $secretKey;
    }
}
