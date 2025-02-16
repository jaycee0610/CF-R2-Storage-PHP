<?php

require_once __DIR__ . '../vendor/autoload.php';


if(!isset($_FILES['test'])){
    echo 'No Files Detected';
}


use Rootscratch\Cloudstorage\Configuration;

// Set configuration values
Configuration::setEndpoint("https://your_end_point.r2.cloudflarestorage.com");
Configuration::setBucketName("bucket_name");
Configuration::setAccessKey("access_key");
Configuration::setSecretKey("secrey_key");

// Create an instance
$cs = new Configuration();



// Upload File
use Rootscratch\Cloudstorage\UploadFile;
$cloud_upload = new UploadFile();
$upload_file = $cloud_upload->uploadFile($_FILES['test'], null, null);

echo json_encode($upload_file, JSON_PRETTY_PRINT);


//Delete File
use Rootscratch\Cloudstorage\DeleteFile;
$cloud_delete = new DeleteFile();
$delete_file = $cloud_delete->deleteFile('filename.png', null);

echo json_encode($delete_file, JSON_PRETTY_PRINT);