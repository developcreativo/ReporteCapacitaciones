<?php

namespace Developcreativo\ReporteCapacitaciones;

use Developcreativo\ReporteCapacitaciones\Nova\ReporteCapacitacionesResource;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class ReporteCapacitaciones extends Tool
{
    public $reporteCapacitaciones = ReporteCapacitacionesResource::class;


    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::resources([
            $this->reporteCapacitaciones,
        ]);
    }

    /**
     * @param string $roleResource
     *
     * @return mixed
     */
    public function reporteCapacitacionesResource($resourceClass)
    {
        $this->reporteCapacitaciones = $resourceClass;
        return $this;
    }
}
