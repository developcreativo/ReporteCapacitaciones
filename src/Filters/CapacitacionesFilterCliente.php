<?php

/**
 *
 * Copyright (c) 2023.  FaceIt
 * @author Kelly Salazar <developmentcreativo@gmail.com>
 */

namespace Developcreativo\ReporteCapacitaciones\Filters;

use App\Traits\AccessScopeTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Filters\Filter;

class CapacitacionesFilterCliente  extends Filter
{
    use AccessScopeTraits;
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public function name()
    {
        return __( 'Filter by customer' );
    }

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply( Request $request, $query, $value )
    {
        return  $query->whereHas('person', function ($query) use ($value) {
            $query->where('id_cliente', $value);
        });;
    }

    /**
     * Get the filter's available options.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function options( Request $request )
    {
        $accessScopeIds = self::getAccessScopeIds();

        $query = DB::table( 'persons_courses' )
            ->join( 'persons', 'persons.id_usuario', '=', 'persons_courses.id_usuario' )
            ->join( 'clientes', 'persons.id_cliente', '=', 'clientes.id' );

        if (!empty($accessScopeIds['clientes'])) {
            $query = $query->whereIn('clientes.id', $accessScopeIds['clientes']);
        }

        if (!empty($accessScopeIds['ubicaciones'])) {
            $ubicaciones  = \App\Ubicacion::query()->whereIn('id', $accessScopeIds['ubicaciones'])->groupBy('id_cliente')->pluck('id_cliente');
            $query = $query->whereIn('clientes.id', $ubicaciones);
        }

        $clients = $query->get();

        $clientsArray = array();
        foreach ($clients as $client) {
            $clientsArray[$client->nombre_cliente] = $client->id;
        }
        return $clientsArray;
    }
}
