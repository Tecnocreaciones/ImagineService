<?php

namespace Tecnoready\ImagineService\Imagine\Filter\PostProcessor;

use Tecnoready\ImagineService\Binary\BinaryInterface;

/**
 * Interface for PostProcessors - handlers which can operate on binaries prepared in FilterManager.
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
interface PostProcessorInterface
{
    /**
     * @param BinaryInterface $binary
     *
     * @return BinaryInterface
     */
    public function process(BinaryInterface $binary);
}
