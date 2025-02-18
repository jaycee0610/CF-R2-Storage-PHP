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
            'image'    => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'video'    => ['mp4', 'mov', 'avi', 'mkv'],
            'docs'     => ['pdf', 'docx', 'xlsx', 'txt'],
            'archives' => ['zip', 'rar'],
            'database' => ['sql']
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
            'txt'  => 'text/plain',
            'zip'  => 'application/zip',
            'rar'  => 'application/vnd.rar',
            'sql'  => 'application/sql'
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



    /**
     * Base64Image Usage / Examples
     * $convert = base64Image($base64_code, 'picmo.png');
     * $upload = $class->uploadFile($convert, null, null);
     */

    public function base64Image($base64_string, $file_name = 'converted.png')
    {
        // Extract the base64 data from the string (remove the "data:image/png;base64," part)
        if (strpos($base64_string, 'base64,') !== false) {
            list(, $base64_string) = explode('base64,', $base64_string);
        }

        // Decode the Base64 string
        $decoded_data = base64_decode($base64_string);

        // Define a temporary file path
        $temp_file = tempnam(sys_get_temp_dir(), 'upload_');

        // Write the decoded image data to the temporary file
        file_put_contents($temp_file, $decoded_data);

        // Get MIME type and extension
        $mime_type = mime_content_type($temp_file);
        $extension = explode('/', $mime_type)[1] ?? 'png';

        // Ensure the correct file extension
        $file_name = pathinfo($file_name, PATHINFO_FILENAME) . '.' . $extension;

        // Simulate the `$_FILES` array structure
        return [
            'name' => $file_name,
            'type' => $mime_type,
            'tmp_name' => $temp_file,
            'error' => 0,
            'size' => filesize($temp_file),
        ];
    }
    
}
