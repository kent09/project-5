<?php

namespace FusedSoftware\Contracts;

interface InfusionSoftContract
{
    /**
     * Create new instance for admin infusionsoft account
     * 
     * @param integer $accountId
     * @return InfusionSoftPackage\InfusionSoft
     */
    public function admin($accountId);

    /**
     * Create new instance for client infusionsoft account
     * 
     * @param integer $accountId
     * @return InfusionSoftPackage\InfusionSoft
     */
    public function client($accountId);
    
}
