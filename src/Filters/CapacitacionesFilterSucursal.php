<?php

namespace Developcreativo\ReporteCapacitaciones\Filters;

use App\Sucursales;
use App\Traits\AccessScopeTraits;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class CapacitacionesFilterSucursal extends Filter
{
    use AccessScopeTraits;
	/**
	 * The filter's component.
	 *
	 * @var string
	 */
	public $component = 'select-filter';

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
		return $query->whereHas('person.ubicacion', function ($query) use ($value) {
            $query->where('sucursal', $value);
        });;
	}

	public function name()
	{
        return __( 'Filtrar por sucursal' );
	}

	/**
	 * Get the filter's available options.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function options( Request $request )
	{
        $tipos = Sucursales::query()->get();
        $tiposArray = array();
        foreach ($tipos as $tipo) {
            $tiposArray[$tipo->nombre_sucursal] = $tipo->id;
        }
        return $tiposArray;
	}
}
