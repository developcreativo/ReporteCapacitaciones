<?php


namespace Developcreativo\ReporteCapacitaciones\Actions;


use App\Cliente;
use App\CourseType;
use App\PersonCourse;
use App\Sucursal;
use App\Traits\AccessScopeTraits;
use Brightspot\Nova\Tools\DetachedActions\DetachedAction;
use Developcreativo\Ajaxselected\Ajaxselected;
use Illuminate\Support\Facades\Storage;
use KossShtukert\LaravelNovaSelect2\Select2;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Date;
use League\Csv\Writer;

class ExportCapacitacionesImpartidasExcel extends DetachedAction
{
	use AccessScopeTraits;

	public function handle(ActionFields $fields)
	{
        $sucursal  = $fields->id_sucursal;
        $cliente  = $fields->id_cliente;
        $ubicacion = $fields->id_ubicacion;
        $from    = $fields->from;
        $to      = $fields->to;
        $tipo     = $fields->tipo;


        $trans = __('Capacitaciones Impartidas');
        $now              = now()->format('U');
        $fileName         = "tmp/$trans-$now.csv";
        $storageInstance  = Storage::disk('reportes');
        $putFileOnStorage = $storageInstance->put($fileName, '');
        $fileContent      = $storageInstance->get($fileName);


        $query = PersonCourse::query()->with(['person', 'person.ubicacion', 'course']);

        if ($sucursal !== null) {
            $query = $query->whereHas('person.ubicacion', function ($query) use ($sucursal) {
                $query->where('sucursal', $sucursal);
            });
        }

        if ($cliente !== null) {
            $query = $query->whereHas('person', function ($query) use ($cliente) {
                $query->where('id_cliente', $cliente);
            });
        }

        if ($tipo !== null) {
            $query = $query->whereHas('course', function ($query) use ($tipo) {
                $query->where('course_type', $tipo);
            });
        }

        if ($ubicacion !== null) {
            $query = $query->whereHas('person', function ($query) use ($ubicacion) {
                $query->where('id_ubicacion', $ubicacion);
            });
        }

        if ($from !== null) {
            $query = $query->whereDate( 'initial_date', '>=', $from);
        }

        if ($to !== null) {
            $query = $query->whereDate( 'initial_date', '<=', $to);
        }


        $headers = [
            0 => [
                'Id',
                __('Branch'),
                __('Customer'),
                __('Location'),
                __('Person Id'),
                __('Person'),
                __('Course Type'),
                __('Course'),
                __('Score'),
                __('Received Hours'),
                __('Initial Date'),
                __('Final Date'),
                __('Due Date'),
            ]
        ];

        $records = $query->get();

        $records = collect($records)->map(function ($x) {
            $ubicacion =  $x->person->ubicacion;
            $person =  $x->person;
            $sucursales = $ubicacion->sucursales;
            $courseType = $x->course->courseType;
            return (array) [
                $x->id,
                $sucursales->nombre_sucursal,
                $ubicacion->nombre_ubicacion,
                $person->id_usuario,
                $person->nombre,
                $courseType->descrip_corta,
                $x->course->name,
                $x->score,
                $x->total_hours,
                $x->initial_date,
                $x->final_date,
                $x->due_date,
            ];
        })->toArray();

        if (count($records) > 99000) {
            return DetachedAction::danger(__('The query is larger than 99000 records. Please narrow your filters'));
        }

        $writer = Writer::createFromString($fileContent, 'w');
        $writer->insertAll($headers);
        $writer->insertAll($records);
        $csvContent       = $writer->getContent();
        $putFileOnStorage = $storageInstance->put($fileName, $csvContent, 'public');
        $uploadedFileUrl  = $storageInstance->url($fileName, \Carbon\Carbon::now()->addMinutes(1));

        return DetachedAction::redirect($uploadedFileUrl);


	}


	public function fields()
	{
        $accessScopeIds = self::getAccessScopeIds();

        $query = Cliente::query();

        if (!empty($accessScopeIds['clientes'])) {
            $query = $query->whereIn('id', $accessScopeIds['clientes']);
        }

        if (!empty($accessScopeIds['ubicaciones'])) {
            $ubicaciones  = \App\Ubicacion::query()->whereIn('id', $accessScopeIds['ubicaciones'])->groupBy('id_cliente')->pluck('id_cliente');
            $query = $query->whereIn('id', $ubicaciones);
        }
		return [
            Select2::make(__('Customer'), 'id_sucursal')
                ->options(Sucursal::query()->orderBy('nombre_sucursal', 'asc')->pluck('nombre_sucursal', 'id'))
                ->configuration([
                    'placeholder'             => __('Choose an option'),
                    'allowClear'              => true,
                    'minimumResultsForSearch' => 1,
                    'multiple'                => false,
                ])->sortable(),

            Select2::make(__('Customer'), 'id_cliente')
                ->options($query->orderBy('nombre_cliente', 'asc')->pluck('nombre_cliente', 'id'))
                ->configuration([
                    'placeholder'             => __('Choose an option'),
                    'allowClear'              => true,
                    'minimumResultsForSearch' => 1,
                    'multiple'                => false,
                ])->sortable(),
            Ajaxselected::make( __( 'Location' ), 'id_ubicacion' )
                ->get( '/api/clientes/{id_cliente}/ubicaciones' )
                ->parent( 'id_cliente' )->sortable(),
            Select2::make(__('Tipo de Capacitación'), 'tipo')
                ->options(CourseType::query()->orderBy('descrip_corta', 'asc')->pluck('descrip_corta', 'id'))
                ->configuration([
                    'placeholder'             => __('Choose an option'),
                    'allowClear'              => true,
                    'minimumResultsForSearch' => 1,
                    'multiple'                => false,
                ])->sortable(),
            Date::make(__('From'), 'from'),
            Date::make(__('To'), 'to'),
        ];
	}
}
