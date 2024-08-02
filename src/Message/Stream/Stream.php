<?php
namespace Concept\Http\Message\Stream;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * @var resource $resource
     */
    protected $resource = null;

    /**
     * @var array|null
     */
    protected ?array $metadata = null;

    /**
     * @var int|null
     */
    protected ?int $size = null;

    /**
     * @todo:vg Interface
     */
    public function setResource($resource): StreamInterface
    {
        $this->resource = $resource;
        $this->metadata = stream_get_meta_data($this->getResource());
        $this->size = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            $this->rewind();
            return $this->getContents();
        } catch (\Exception $e) {
            /**
             * @todo:vg: remove throw
             * temporary throw for debugging
             */
            throw $e;
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (is_resource($this->getResource())) {
            fclose($this->getResource());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $resource = $this->getResource();
        $this->resource = null;
        $this->size = null;
        $this->metadata = null;

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!is_resource($this->getResource())) {
            return null;
        }

        $stats = fstat($this->getResource());
        if ($stats === false) {
            return null;
        }

        if ($this->isSeekable()) {
            $this->size = $stats['size'];
            return $this->size;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        if (!is_resource($this->getResource())) {
            throw new \RuntimeException('Stream is detached');
        }

        $result = ftell($this->getResource());
        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return !$this->getResource() || feof($this->getResource());
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return $this->metadata['seekable'];
    }

    /**
     * {@inheritdoc}
     */
    public function seek(int $offset, int $whence = SEEK_SET)
    {
        if (!is_resource($this->getResource())) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }

        if (fseek($this->getResource(), $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position '.$offset.' with whence '.$whence);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        /**
         * @todo:vg: review
         */
        return preg_match('/w|a|x|c/', $this->getMetaData('mode'));
        //return in_array($this->getMetaData('mode'), ['w', 'w+', 'a', 'a+', 'r+', 'x+', 'c+']);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {   
        /**
         * @todo:vg: review
         */
        return preg_match('/r|\+/', $this->getMetaData('mode'));
        //return in_array($this->getMetaData('mode'), ['r', 'r+', 'w+', 'a+', 'x+', 'c+']);
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        if (!is_resource($this->getResource())) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->isWritable()) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        $result = fwrite($this->getResource(), $string);
        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }

        $this->size = null;
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        if (!is_resource($this->getResource())) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->isReadable()) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }

        $result = fread($this->getResource(), $length);
        if ($result === false) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        if (!is_resource($this->getResource())) {
            throw new \RuntimeException('Stream is detached');
        }

        $contents = stream_get_contents($this->getResource());
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return $this->metadata;
        }

        return $this->metadata[$key] ?? null;
    }

    /**
     * @return resource
     */
    protected function getResource()
    {
        return $this->resource;
    }

}