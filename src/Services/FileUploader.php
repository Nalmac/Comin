<?php
// src/Service/FileUploader.php
namespace App\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;
    private $targetPost;

    public function __construct($targetDirectory, $targetPost)
    {
        $this->targetDirectory = $targetDirectory;
        $this->targetPost = $targetPost;

    }

    public function upload(UploadedFile $file, String $path = null)
    {
        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);          
        

        $safeFilename = htmlspecialchars($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

        if ($path) {
            $file->move($this->getTargetPost(), $fileName);
        }else{

            try {
                $file->move($this->getTargetDirectory(), $fileName);
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
        }

        return $fileName;
        }
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    public function getTargetPost()
    {
        return $this->targetPost;
    }
}

