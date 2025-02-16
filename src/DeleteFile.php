<?php

namespace Rootscratch\Cloudstorage;

use Aws\Exception\AwsException;

class DeleteFile extends Configuration
{

    public function __construct()
    {
        parent::__construct();
    }


    public function deleteFile($fileName, $path = 'uploads/')
    {
        $fileKey = $path . $fileName; // Full path to the file in R2

        try {
            // Delete file from Cloudflare R2
            Configuration::$s3->deleteObject([
                'Bucket' => Configuration::$bucketName,
                'Key'    => $fileKey,
            ]);

            return [
                "status"    => "success",
                "message"   => "File deleted successfully.",
                "file_name" => $fileName
            ];
        } catch (AwsException $e) {
            return [
                "status"  => "error",
                "message" => $e->getMessage()
            ];
        }
    }
}
