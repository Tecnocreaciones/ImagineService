<?php

namespace Tecnoready\ImagineService\Binary;

/**
 * Representa un archivo
 */
interface BinaryInterface
{
    /**
     * @return string
     */
    public function getContent();

    /**
     * @return string
     */
    public function getMimeType();

    /**
     * @return string
     */
    public function getFormat();
}
