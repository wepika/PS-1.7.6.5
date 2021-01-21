<?php

class AdminWpkColocoController extends ModuleAdminController
{
    /**
     * AdminWpkColocoController constructor.
     */
    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();

        $this->name = $this->trans('Coloco');
    }
}