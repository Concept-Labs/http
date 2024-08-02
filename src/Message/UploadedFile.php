<?php
namespace Concept\Http\Message;

use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    protected $file;
    protected $size;
    protected $error;
    protected $clientFilename;
    protected $clientMediaType;
    protected $moved = false;

    public function __construct($file, $size, $error, $clientFilename, $clientMediaType)
    {
        $this->file = $file;
        $this->size = $size;
        $this->error = $error;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * {@inheritdoc}
     */
    public function getStream()
    {
        if ($this->moved) {
            throw new \RuntimeException('Uploaded file has already been moved');
        }

        return new Stream($this->file);
    }

    /**
     * {@inheritdoc}
     */
    public function moveTo($targetPath)
    {
        if ($this->moved) {
            throw new \RuntimeException('Uploaded file has already been moved');
        }

        if (!is_writable(dirname($targetPath))) {
            throw new \RuntimeException('Target path is not writable');
        }

        if (!move_uploaded_file($this->file, $targetPath)) {
            throw new \RuntimeException('Error moving uploaded file');
        }

        $this->moved = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }
}