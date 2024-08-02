<?php
namespace Concept\Http\Message\Request;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{

    protected ?StreamInterface $stream = null;
    protected bool $moved = false;
    protected ?int $size = null;
    protected ?int $error = null;
    protected ?string $clientFilename = null;
    protected ?string $clientMediaType = null;

    public function getStream()
    {
        if ($this->getStream() === null) {
            throw new \RuntimeException('No stream available');
        }

        // if ($this->getStream()->isSeekable() === false) {
        //     throw new \RuntimeException('The stream is not seekable');
        // }

        if ($this->getStream()->isReadable() === false) {
            throw new \RuntimeException('The stream is not readable');
        }

        return $this->stream;    
    }

    public function moveTo(string $targetPath)
    {
        if ($this->moved) {
            throw new \RuntimeException('The file has already been moved');
        }

        if (!is_writable(dirname($targetPath))) {
            throw new \RuntimeException('The target path is not writable');
        }



        $this->size = $this->getStream()->getSize();

        $this->getStream()->rewind();
        $stream = $this->getStream()->detach();
        $targetStream = fopen($targetPath, 'w');
        stream_copy_to_stream($stream, $targetStream);
        fclose($stream);
        fclose($targetStream);


        $this->moved = true;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getError()
    {
        
    }

    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    public function __toString()
    {
        
    }

    public function setStream(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function setSize(int $size)
    {
        $this->size = $size;
    }

    public function setError(int $error)
    {
        $this->error = $error;
    }

    public function setClientFilename(string $clientFilename)
    {
        $this->clientFilename = $clientFilename;
    }

    public function setClientMediaType(string $clientMediaType)
    {
        $this->clientMediaType = $clientMediaType;
    }


}
