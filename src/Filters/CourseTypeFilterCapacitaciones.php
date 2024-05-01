<?php

namespace Developcreativo\ReporteCapacitaciones\Filters;

use App\Traits\AccessScopeTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Filters\Filter;

class CourseTypeFilterCapacitaciones extends Filter
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
		return __( 'Filter by Type of training' );
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
		return $query->whereHas('course', function ($query) use ($value) {
            $query->where('course_type', $value);
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
        $query = DB::table( 'persons_courses' )
            ->join( 'courses', 'courses.id', '=', 'persons_courses.id_course' )
			->join( 'claves', 'courses.course_type', '=', 'claves.valor' )->where('clave', '=', 'tipo_curso');


        $ubicaciones = $query->groupBy('claves.descrip_corta')->get();

        $collectArray = array();
		foreach ($ubicaciones as $ubicacion) {
            $collectArray[$ubicacion->descrip_corta] = $ubicacion->valor;
		}
		return $collectArray;
	}
}
