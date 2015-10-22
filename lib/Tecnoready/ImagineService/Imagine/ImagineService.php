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

/**
 * Servicio manejador de imagenes
 *
 * @author Carlos Mendoza <inhack20@gmail.com>
 */
class ImagineService 
{
    /**
     *
     * @var Data\DataManagerInterface
     */
    private $dataManager;
    /**
     *
     * @var Filter\FilterManagerInterface
     */
    private $filterManager;
    /**
     *
     * @var Cache\CacheManagerInterface
     */
    private $cacheManager;
    
    /**
     *
     * @var Cache\SignerInterface
     */
    private $signer;
    
    public function getDataManager() {
        return $this->dataManager;
    }

    public function setDataManager(Data\DataManagerInterface $dataManager) {
        $this->dataManager = $dataManager;
        return $this;
    }
    
    public function getFilterManager() {
        return $this->filterManager;
    }

    public function setFilterManager(Filter\FilterManagerInterface $filterManager) {
        $this->filterManager = $filterManager;
        return $this;
    }
    
    public function getCacheManager() {
        return $this->cacheManager;
    }

    public function setCacheManager(Cache\CacheManagerInterface $cacheManager) {
        $this->cacheManager = $cacheManager;
        return $this;
    }
    
    public function getSigner() {
        return $this->signer;
    }

    public function setSigner(Cache\SignerInterface $signer) {
        $this->signer = $signer;
        return $this;
    }
}
