<?php
namespace Concept\Http\Message\Stream;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{

    /**
     * @var StreamInterface|null
     */
    protected ?StreamInterface $streamInstance = null;


    public function __construct(StreamInterface $streamInstance)
    {
        $this->streamInstance = $streamInstance;
    }

    /**
     * {@inheritDoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $content);

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritDoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return $this->createStreamFromResource(fopen($filename, $mode));
    }

    /**
     * {@inheritDoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        /**
         * @todo:vg setResource()? create interface? another way?
         */
        $stream = $this->getStreamInstance();
        $stream->setResource($resource);
        return $stream;
    }

    /**
     * Get the injected stream instance
     * 
     * @return StreamInterface
     */
    protected function getStreamInstance(): StreamInterface
    {
        return $this->streamInstance ?? /** non container version */ new Stream();
    }
}