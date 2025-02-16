![CF R2 Storage](https://github.com/jaycee0610/CF-R2-Storage-PHP/blob/main/image.png?raw=true)


# Cloudflare R2 Storage PHP

This project allows users to upload/delete files to Cloudflare R2 storage using PHP and AWS SDK. It supports image, video, and document uploads with flexible validation.
## Features

- Upload files to Cloudflare R2
- Supports multiple categories (image, video, docs, or a single file type)
- Delete files from Cloudflare R2
- Uses AWS S3 SDK for managing R2 storage
- Prevents invalid file uploads
## Deployment

To deploy this via cloning this URL

```bash
git clone https://github.com/jaycee0610/CF-R2-Storage-PHP.git
cd cloudflare-r2-upload
composer require aws/aws-sdk-php
```
Or Via Composer Package
```bash
composer require Rootscratch/Cloudstorage
```


## Usage/Examples
Add your Cloudflare R2 credentials:
- Using Composer Package
```php
require_once __DIR__ . '../vendor/autoload.php';
use Rootscratch\Cloudstorage\Configuration;

// Set configuration values
Configuration::setEndpoint("https://your_end_point.r2.cloudflarestorage.com");
Configuration::setBucketName("bucket_name");
Configuration::setAccessKey("access_key");
Configuration::setSecretKey("secrey_key");

// Create an instance
$cs = new Configuration();
```

### Upload a File
```php
use Rootscratch\Cloudstorage\UploadFile;
$cloud_upload = new UploadFile();
$upload_file = $cloud_upload->uploadFile($_FILES['test'], null, null);

echo json_encode($upload_file, JSON_PRETTY_PRINT);
```

### Delete a File
To delete a file, use:
```php
use Rootscratch\Cloudstorage\DeleteFile;
$cloud_delete = new DeleteFile();
$delete_file = $cloud_delete->deleteFile('filename.png', null);

echo json_encode($delete_file, JSON_PRETTY_PRINT);
```
### Valid File Categories

| Category                  | Allowed Formats                       |
|---------------------------|---------------------------------------|
| `image`                   | jpg, jpeg, png, gif, webp, svg        |
| `video`                   | mp4, mov, avi, mkv                    |
| `docs`                    | pdf, docx, xlsx, txt                  |
| Specific File `pdf`       | pdf                                   |
| `null`                    | All                                   |

### Cloudflare R2 Returning 403?
- Go to **Cloudflare Dashboard** → **R2 Storage** → **Permissions**
- Enable **Public Read Access** for the bucket.