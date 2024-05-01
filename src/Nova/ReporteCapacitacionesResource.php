<?php

namespace Developcreativo\ReporteCapacitaciones\Nova;

use App\PersonCourse;
use Developcreativo\ReporteCapacitaciones\Actions\ExportCapacitacionesImpartidasExcel;
use Developcreativo\ReporteCapacitaciones\Filters\CapacitacionesFilterCliente;
use Developcreativo\ReporteCapacitaciones\Filters\CapacitacionesFilterSucursal;
use Developcreativo\ReporteCapacitaciones\Filters\CourseTypeFilterCapacitaciones;
use Developcreativo\ReporteCapacitaciones\Filters\FechaFilterCapacitaciones;
use Developcreativo\ReporteCapacitaciones\Filters\LocationFilterCapacitaciones;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource as NovaResource;

class ReporteCapacitacionesResource extends NovaResource
{
    public static $model = PersonCourse::class;

    public static $title = 'id';

    public static $search = [
        ''
    ];

    public static function group() {
        return __( 'Viewers' );
    }

    public static function label()
    {
        return __("Capacitaciones Impartidas");
    }

    public static function singularLabel()
    {
        return __("Capacitaciones Impartidas");
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make(__('Branch'), function () {
                $ubicacion =  $this->person->ubicacion;
                return $ubicacion->sucursales->nombre_sucursal;
            })->hideWhenCreating()->hideWhenUpdating(),

            Text::make(__('Customer'), function () {
                $cliente=  $this->person->cliente;
                return $cliente->nombre_cliente;
            })->hideWhenCreating()->hideWhenUpdating(),

            Text::make(__('Location'), function () {
                $ubicacion =  $this->person->ubicacion;
                return $ubicacion->nombre_ubicacion;
            })->hideWhenCreating()->hideWhenUpdating(),

            Text::make(__('Person Id'), function () {
                $person =  $this->person;
                return $person->id_usuario;
            })->hideWhenCreating()->hideWhenUpdating(),

            BelongsTo::make(__('Person'), 'person', \App\Nova\Persons::class)->rules('required')
                ->searchable()
                ->resolveUsing(function ($value) {
                    if ($this->resource->exists) {
                        return $value;
                    }
                    return $this->relation()->get()->first();
                })->readonly(function ($request) {
                    return $this->resource->exists;
                })->hideWhenCreating()->hideWhenUpdating(),


            Text::make(__('Course Type'), function () {
                $courseType =  $this->course->courseType;
                return $courseType->descrip_corta;
            })->hideWhenCreating()->hideWhenUpdating(),

            BelongsTo::make(__('Course'), 'course', \App\Nova\Course::class)->rules('required')
                ->searchable()
                ->resolveUsing(function ($value) {
                    if ($this->resource->exists) {
                        return $value;
                    }
                    return $this->relation()->get()->first();
                })->readonly(function ($request) {
                    return $this->resource->exists;
                })->hideWhenCreating()->hideWhenUpdating(),

            Number::make(__('Score'), 'score')
                ->rules('required', 'numeric', 'min:0'),
            Number::make(__('Received Hours'), 'total_hours')->rules('required'),
            Date::make(__('Initial Date'), 'initial_date')->rules('required'),
            Date::make(__('Final Date'), 'final_date')->rules('required'),
            Date::make(__('Due Date'), 'due_date'),
        ];
    }

    public function cards(Request $request): array
    {
        return [];
    }

    public function filters(Request $request): array
    {
        return [
            (new CapacitacionesFilterSucursal()),
            (new CapacitacionesFilterCliente()),
            (new LocationFilterCapacitaciones()),
            (new CourseTypeFilterCapacitaciones()),
            (new FechaFilterCapacitaciones())
        ];
    }

    public function lenses(Request $request): array
    {
        return [];
    }

    public function actions(Request $request): array
    {
        return [
            (new ExportCapacitacionesImpartidasExcel())
        ];
    }


    public static function authorizedToCreate(Request $request )
    {
        return false;
    }

    public function authorizedToUpdate(Request $request )
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }
}
