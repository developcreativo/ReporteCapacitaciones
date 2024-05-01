<?php

namespace Developcreativo\ReporteCapacitaciones\Filters;

use App\Traits\AccessScopeTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Filters\Filter;

class LocationFilterCapacitaciones extends Filter
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
		return __( 'Filter by location' );
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
		return $query->whereHas('person', function ($query) use ($value) {
            $query->where('id_ubicacion', $value);
        });
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
			->join( 'ubicaciones', 'persons.id_ubicacion', '=', 'ubicaciones.id' );

        if (!empty($accessScopeIds['clientes'])) {
            $query = $query->whereIn('ubicaciones.id_cliente', $accessScopeIds['clientes']);
        }

        if (!empty($accessScopeIds['ubicaciones'])) {
            $query =  $query->orWhereIn('ubicaciones.id', $accessScopeIds['ubicaciones']);
        }
        $ubicaciones = $query->groupBy('ubicaciones.nombre_ubicacion')->get();

        $collectArray = array();
		foreach ($ubicaciones as $ubicacion) {
            $collectArray[$ubicacion->nombre_ubicacion] = $ubicacion->id;
		}
		return $collectArray;
	}
}
