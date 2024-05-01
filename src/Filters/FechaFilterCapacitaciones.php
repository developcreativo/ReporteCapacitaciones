<?php


namespace Developcreativo\ReporteCapacitaciones\Filters;


use Ampeco\Filters\DateRangeFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FechaFilterCapacitaciones extends DateRangeFilter {


	/**
	 * Apply the filter to the given query.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param mixed $value
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function apply( Request $request, $query, $value ) {
		$from = Carbon::parse( $value[0] )->startOfDay();
		$to   = Carbon::parse( $value[1] )->endOfDay();

		return $query->whereBetween( 'persons_courses.initial_date', [ $from, $to ] );
	}

	public function name() {
		return __('Filter by date');
	}

}
