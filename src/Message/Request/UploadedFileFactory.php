<?php
namespace Concept\Http\Message\Request;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * @var UploadedFileInterface|null
     */
    protected ?UploadedFileInterface $uploadedFileInstance = null;

    /**
     * @var StreamFactoryInterface|null
     */
    protected ?StreamFactoryInterface $streamFactory = null;

    /**
     * @param UploadedFileInterface $uploadedFile
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(
        UploadedFileInterface $uploadedFileInstance,
        StreamFactoryInterface $streamFactory
    ) {
        $this->uploadedFileInstance = $uploadedFileInstance;
        $this->streamFactory = $streamFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = \UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface {
        $uploadedFile = $this->getUploadedFileInstance();
        $uploadedFile->setStream($stream);
        $uploadedFile->setSize($size);
        $uploadedFile->setError($error);
        $uploadedFile->setClientFilename($clientFilename);
        $uploadedFile->setClientMediaType($clientMediaType);

        return $uploadedFile;
    }

    /**
     * Get the injected uploaded file instance
     * 
     * @return UploadedFileInterface
     */
    protected function getUploadedFileInstance(): UploadedFileInterface
    {
        return $this->uploadedFileInstance;
    }
}