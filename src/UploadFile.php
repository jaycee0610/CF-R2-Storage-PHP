<?php

namespace Rootscratch\Cloudstorage;

use Aws\Exception\AwsException;

class UploadFile extends Configuration
{

    public function __construct()
    {
        parent::__construct();
    }


    public function uploadFile($file, $category = null, $path = 'uploads/')
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ["status" => "error", "message" => "Invalid file upload."];
        }

        // Define allowed file extensions and MIME types per category
        $allowedCategories = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'video' => ['mp4', 'mov', 'avi', 'mkv'],
            'docs'  => ['pdf', 'docx', 'xlsx', 'txt'],
        ];

        // MIME types for each file type
        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'mp4'  => 'video/mp4',
            'mov'  => 'video/quicktime',
            'avi'  => 'video/x-msvideo',
            'mkv'  => 'video/x-matroska',
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt'  => 'text/plain'
        ];

        // Get file extension
        $fileExtension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

        // Validate category or specific file type
        if ($category !== null) {
            if (isset($allowedCategories[$category])) {
                // If category is provided, validate against allowed extensions in that category
                if (!in_array($fileExtension, $allowedCategories[$category])) {
                    return [
                        "status"  => "error",
                        "message" => "Invalid format for category '$category'. Allowed: " . implode(", ", $allowedCategories[$category])
                    ];
                }
            } elseif (!isset($mimeTypes[$category])) {
                // If not a category, check if it's a specific file type (e.g., 'pdf', 'txt')
                return ["status" => "error", "message" => "Invalid category or file type specified."];
            } elseif ($fileExtension !== $category) {
                // If a specific type is given, ensure it matches the file extension
                return ["status" => "error", "message" => "Only .$category files are allowed."];
            }
        } else {
            // If no category is provided, allow all supported file types
            $allExtensions = array_merge(...array_values($allowedCategories));
            if (!in_array($fileExtension, $allExtensions)) {
                return ["status" => "error", "message" => "Unsupported file type."];
            }
        }

        // Set correct MIME type
        $contentType = $mimeTypes[$fileExtension] ?? 'application/octet-stream';

        // Generate unique filename
        $fileName = md5(time()) . "_" . basename($file["name"]);
        $fileTmp = $file["tmp_name"];
        $fileKey = $path . $fileName; // Store file in the specified folder

        try {
            // Upload File to Cloudflare R2 with correct Content-Type
            Configuration::$s3->putObject([
                'Bucket'      => Configuration::$bucketName,
                'Key'         => $fileKey,
                'SourceFile'  => $fileTmp,
                'ContentType' => $contentType, // Set correct MIME type
                'ACL'         => 'public-read',
            ]);

            // Return the file name and MIME type (no JSON encoding inside the class)
            return [
                "status"    => "success",
                "message"   => "File uploaded successfully.",
                "file_name" => $fileName,
                "mime_type" => $contentType
            ];
        } catch (AwsException $e) {
            return ["status" => "error", "message" => $e->getMessage()];
        }
    }

}
