<?php

/*
 * This file is part of the BtoB Rewards package.
 * 
 * (c) www.btobrewards.com
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tecnoready\ImagineService\Imagine;

use Imagine\Image\ImagineInterface;
use Tecnoready\ImagineService\Imagine\Cache\CacheManagerInterface;
use Tecnoready\ImagineService\Imagine\Cache\SignerInterface;
use Tecnoready\ImagineService\Imagine\Data\DataManagerInterface;
use Tecnoready\ImagineService\Imagine\Filter\FilterManagerInterface;

/**
 * Servicio manejador de imagenes
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class ImagineService 
{
    /**
     *
     * @var ImagineInterface
     */
    private $imagine;
    /**
     *
     * @var CacheManagerInterface
     */
    private $cacheManager;
    /**
     *
     * @var DataManagerInterface
     */
    private $dataManager;
    /**
     *
     * @var FilterManagerInterface
     */
    private $filterManager;
    
    /**
     *
     * @var SignerInterface
     */
    private $signer;
    
    public function __construct(CacheManagerInterface $cacheManager,
            DataManagerInterface $dataManager, 
            FilterManagerInterface $filterManager, 
            SignerInterface $signer,
             ImagineInterface $imagine) {
        $this->cacheManager = $cacheManager;
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->signer = $signer;
        $this->imagine = $imagine;
    }

    /**
     * 
     * @return ImagineInterface
     */
    public function getImagine() {
        return $this->imagine;
    }

    public function getCacheManager() {
        return $this->cacheManager;
    }

    public function getDataManager() {
        return $this->dataManager;
    }

    public function getFilterManager() {
        return $this->filterManager;
    }

    public function getSigner() {
        return $this->signer;
    }
}
