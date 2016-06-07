<?php

namespace BitWasp\Bitcoin\Node\Chain;


use BitWasp\Bitcoin\Chain\BlockLocator;
use BitWasp\Bitcoin\Node\ChainSegment;
use BitWasp\Buffertools\BufferInterface;

interface ChainViewInterface
{
    /**
     * @param BufferInterface $hash
     * @return bool
     */
    public function containsHash(BufferInterface $hash);

    /**
     * @return ChainSegment
     */
    public function getSegment();

    /**
     * @return BlockIndexInterface
     */
    public function getIndex();

    /**
     * @param BufferInterface $hash
     * @return int
     */
    public function getHeightFromHash(BufferInterface $hash);

    /**
     * @param int $height
     * @return BufferInterface
     */
    public function getHashFromHeight($height);

    /**
     * Produce a block locator for a given block height.
     * @param int $height
     * @param BufferInterface|null $final
     * @return BlockLocator
     */
    public function getLocator($height, BufferInterface $final = null);

    /**
     * @param BufferInterface|null $hashStop
     * @return BlockLocator
     */
    public function getHeadersLocator(BufferInterface $hashStop = null);

    /**
     * @param BufferInterface|null $hashStop
     * @return BlockLocator
     */
    public function getBlockLocator(BufferInterface $hashStop = null);
}