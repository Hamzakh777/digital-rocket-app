@php
    $currentLogedInUserRole =strtolower(auth()->user()->role->name);
@endphp
@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing').' '.$dataType->display_name_plural)

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="{{ $dataType->icon }}"></i> Termin
        </h1>
        @can('add', app($dataType->model_name))
            <a href="{{ route('voyager.'.$dataType->slug.'.create') }}" class="btn btn-success btn-add-new">
                <i class="voyager-plus"></i> <span>{{ __('voyager::generic.add_new') }}</span>
            </a>
        @endcan
        @can('delete', app($dataType->model_name))
            @include('voyager::partials.bulk-delete')
        @endcan
        @can('edit', app($dataType->model_name))
            @if(isset($dataType->order_column) && isset($dataType->order_display_column))
                <a href="{{ route('voyager.'.$dataType->slug.'.order') }}" class="btn btn-primary btn-add-new">
                    <i class="voyager-list"></i> <span>{{ __('voyager::bread.order') }}</span>
                </a>
            @endif
        @endcan
        @can('delete', app($dataType->model_name))
            @if($usesSoftDeletes)
                <input type="checkbox" @if ($showSoftDeleted) checked @endif id="show_soft_deletes" data-toggle="toggle" data-on="{{ __('voyager::bread.soft_deletes_off') }}" data-off="{{ __('voyager::bread.soft_deletes_on') }}">
            @endif
        @endcan
        @foreach(Voyager::actions() as $action)
            @if (method_exists($action, 'massAction'))
                @include('voyager::bread.partials.actions', ['action' => $action, 'data' => null])
            @endif
        @endforeach
        @include('voyager::multilingual.language-selector')
    </div>
@stop

@section('content')


@if ($currentLogedInUserRole == 'sales_agent' )
<div class="page-content browse container-fluid" id="app">    
    {{-- comments modal --}}
    <appointments-comments-modal></appointments-comments-modal>
    @include('voyager::alerts')
    <div class="row" >
        <div class="col-md-12">
            <div class="panel panel-primary panelbordered">
                <div class="panel-heading">
                    {{-- appointments filter --}}
                    <h3 class="panel-title panel-icon"><i class="voyager-search"></i>Suche nach</h3>
                    <div class="panel-actions">
                        <a class="panel-action voyager-angle-up" data-toggle="panel-collapse" aria-hidden="true"></a>
                    </div>
                </div>
                <div class="panel-body mt-2">
                    <appointment-filter @filter="getResults" :is-agent-view="true">
                        @foreach ($dataType->addRows as $row)
                            @if ($row->field == 'wanted_expert')
                                <template v-slot:experts>
                                    @foreach ($row->details->options as $key => $option)
                                        <option value="{{$key}}">{{ $option }}</option> 
                                    @endforeach
                                </template>
                            @elseif($row->field == 'canton_city')
                                <template v-slot:cities>
                                    @foreach ($row->details->options as $key => $option)
                                        <option value="{{$key}}">{{ $option }}</option> 
                                    @endforeach
                                </template>
                            @elseif($row->field == 'appointment_belongsto_user_relationship_1')
                                <template v-slot:users>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->user_name }}</option>
                                    @endforeach
                                </template>
                            @endif
                        @endforeach
                    </appointment-filter>
                </div>
            </div>
        </div>
    </div>
    
    {{-- {{ dd([$dataTypeContent, $appointmentsGroupOpen] ) }} --}}
    {{-- if feedback open group is not null,
    the agent shouldn't be able to edit the 
    open appointments  --}}
    {{-- Feedback open group --}}
    @include('vendor.voyager.appointments.browse_agent_panel', 
    [
        'tableName' => 'Feedback offen', 
        'eltId' => 'feedbackOpen',
        'disableActions' => false,
        'dataTypeContent' => $appointmentsGroupFeedbackPending,
    ])
    {{-- Open appointments group --}}
    @include('vendor.voyager.appointments.browse_agent_panel', 
    [
        'tableName' => 'Offene Termine', 
        'eltId' => 'openAppointments',
        'disableActions' => (count($appointmentsGroupFeedbackPending) == 0) ? false : true,
        'dataTypeContent' => $appointmentsGroupOpen,
    ])
    {{-- Closed appointments group --}}
    @include('vendor.voyager.appointments.browse_agent_panel', 
    [
        'tableName' => 'Abgeschlosse Termine', 
        'eltId' => 'closedAppointments',
        'disableActions' => true,
        'dataTypeContent' => $appointmentsGroupClosed,
    ])
</div>

@elseif ($currentLogedInUserRole != 'call_agent')
@php
    if($currentLogedInUserRole == 'call_center_managner') {
        $isAgentView = true;
    } else {
        $isAgentView = false;
    }
@endphp
    <div class="page-content browse container-fluid" id="app">    
        {{-- comments modal --}}
        <appointments-comments-modal></appointments-comments-modal>
        @include('voyager::alerts')
        <div>
            
        </div>
        <div class="row" >
            <div class="col-md-12">
                <div class="panel panel-primary panelbordered">
                    <div class="panel-heading">
                        {{-- appointments filter --}}
                        <h3 class="panel-title panel-icon"><i class="voyager-search"></i>Suche nach</h3>
                        <div class="panel-actions">
                            <a class="panel-action voyager-angle-up" data-toggle="panel-collapse" aria-hidden="true"></a>
                        </div>
                    </div>
                    <div class="panel-body mt-2">
                        <appointment-filter @filter="getResults" :is-agent-view="@if($currentLogedInUserRole == 'call_center_manager') true @else false @endif">
                            @foreach ($dataType->addRows as $row)
                                @if ($row->field == 'wanted_expert')
                                    <template v-slot:experts>
                                        @foreach ($row->details->options as $key => $option)
                                            <option value="{{$key}}">{{ $option }}</option> 
                                        @endforeach
                                    </template>
                                @elseif($row->field == 'canton_city')
                                    <template v-slot:cities>
                                        @foreach ($row->details->options as $key => $option)
                                            <option value="{{$key}}">{{ $option }}</option> 
                                        @endforeach
                                    </template>
                                @elseif($row->field == 'appointment_belongsto_user_relationship_1')
                                    <template v-slot:users>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->user_name }}</option>
                                        @endforeach
                                    </template>
                                @endif
                            @endforeach
                            @if (isset($callCenters))
                                <template v-slot:call-centers>
                                    @foreach($callCenters as $callCenter)
                                        <option value="{{ $callCenter->id }}">{{ $callCenter->name }}</option>
                                    @endforeach
                                </template>
                            @endif
                        </appointment-filter>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-primary panel-bordered">
                    <div class="panel-heading">
                        {{-- results --}}
                        <h3 class="panel-title panel-icon"><i class="voyager-list"></i>Resultate</h3>
                        <div class="panel-actions">
                            <a class="panel-action voyager-angle-up" data-toggle="panel-collapse" aria-hidden="true"></a>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive" id="table">
                            <table id="dataTable" class="table table-hover table-striped table-bordered">
                                <thead>
                                    <tr>
                                        @foreach($dataType->browseRows as $row)
                                            @if ($loop->index == 1)
                                                @can('delete',app($dataType->model_name))
                                                    <th>
                                                        <input type="checkbox" class="select_all">
                                                    </th>
                                                @endcan
                                                <th class="actions text-right">{{ __('voyager::generic.actions') }}</th>
                                                <th>Feedback</th>
                                                @if ($currentLogedInUserRole == 'superadmin')
                                                    <th>Call center</th>
                                                @endif
                                                @endif
                                            <th>
                                                @if ($isServerSide)
                                                    <div>
                                                @endif
                                                
                                                {{ $row->display_name }}

                                                @if ($isServerSide)
                                                    @if ($row->isCurrentSortField($orderBy))
                                                        @if ($sortOrder == 'asc')
                                                            <i class="voyager-angle-up pull-right"></i>
                                                        @else
                                                            <i class="voyager-angle-down pull-right"></i>
                                                        @endif
                                                    @endif
                                                    </div>
                                                @endif
                                            </th> 
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataTypeContent as $data)
                                    <tr>
                                        @foreach($dataType->browseRows as $row)
                                            @if ($loop->index == 1)
                                                @can('delete',app($dataType->model_name))
                                                    <td>
                                                        <input type="checkbox" name="row_id" id="checkbox_{{ $data->getKey() }}" value="{{ $data->getKey() }}">
                                                    </td>
                                                @endcan
                                                <td class="no-sort no-click" id="bread-actions">
                                                    @foreach(Voyager::actions() as $action)
                                                        @if (!method_exists($action, 'massAction'))
                                                            @include('voyager::bread.partials.actions', ['action' => $action])
                                                        @endif
                                                    @endforeach
                                                </td>
                                                <td>
                                                    <appointments-modal-btn :appointment-id="{{ $data->getKey() }}" @open-comments-modal="openCommentsModal"></appointments-modal-btn>
                                                </td>
                                                @if ($currentLogedInUserRole == 'superadmin')
                                                    <td>
                                                        @php
                                                            $model = app('App\User');
                                                            if(isset($data->call_agent_id)) {
                                                                $callAgent = $model::where('id', $data->call_agent_id)->first();
                                                                if(isset($callAgent->role)) {
                                                                    if(isset($callAgent->callCenter->name)) {
                                                                        echo $callAgent->callCenter->name;
                                                                    }
                                                                }
                                                            }
                                                        @endphp 
                                                    </td>
                                                @endif
                                            @endif
                                            @php
                                            if ($data->{$row->field.'_browse'}) {
                                                $data->{$row->field} = $data->{$row->field.'_browse'};
                                            }
                                            @endphp
                                            <td class="no-sort no-click" @if ($row->field == "canton_city" or $row->field == 'meeting_time')
                                                {!! 'bgcolor="#62a8ea" style="color: #fff"'  !!}
                                            @endif>

                                                @if ($row->field == 'sales_agent_id' || $row->field == 'call_agent_id')
                                                    @php
                                                        $model = app('App\User');
                                                        $users2 = $model::where('id' , '=', $data->{$row->field})->get();
                                                    @endphp 
                                                    @foreach ($users2 as $user)
                                                        <span>{{ $user->user_name }}</span>
                                                    @endforeach
                                                    @continue
                                                @endif

                                                @if($row->type == 'image')
                                                    <img src="@if( !filter_var($data->{$row->field}, FILTER_VALIDATE_URL)){{ Voyager::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif" style="width:100px">
                                                @elseif($row->type == 'relationship')
                                                    @include('voyager::formfields.relationship', ['view' => 'browse','options' => $row->details])
                                                @elseif($row->type == 'select_multiple')
                                                    @if(property_exists($row->details, 'relationship'))

                                                        @foreach($data->{$row->field} as $item)
                                                            {{ $item->{$row->field} }}
                                                        @endforeach

                                                    @elseif(property_exists($row->details, 'options'))
                                                        @if (!empty(json_decode($data->{$row->field})))
                                                            @foreach(json_decode($data->{$row->field}) as $item)
                                                                @if (@$row->details->options->{$item})
                                                                    {{ $row->details->options->{$item} . (!$loop->last ? ', ' : '') }}
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            {{ __('voyager::generic.none') }}
                                                        @endif
                                                    @endif

                                                    @elseif($row->type == 'multiple_checkbox' && property_exists($row->details, 'options'))
                                                        @if (@count(json_decode($data->{$row->field})) > 0)
                                                            @foreach(json_decode($data->{$row->field}) as $item)
                                                                @if (@$row->details->options->{$item})
                                                                    {{ $row->details->options->{$item} . (!$loop->last ? ', ' : '') }}
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            {{ __('voyager::generic.none') }}
                                                        @endif

                                                @elseif(($row->type == 'select_dropdown' || $row->type == 'radio_btn') && property_exists($row->details, 'options'))
                                                    {!! $row->details->options->{$data->{$row->field}} ?? '' !!}
                                                @elseif($row->type == 'date' || $row->type == 'timestamp')
                                                    @if (isset($data->{$row->field}))
                                                        {{ property_exists($row->details, 'format') ? \Carbon\Carbon::parse($data->{$row->field})->formatLocalized($row->details->format) : $data->{$row->field} }}
                                                    @endif
                                                @elseif($row->type == 'checkbox')
                                                    @if(property_exists($row->details, 'on') && property_exists($row->details, 'off'))
                                                        @if($data->{$row->field})
                                                            <span class="label label-info">{{ $row->details->on }}</span>
                                                        @else
                                                            <span class="label label-primary">{{ $row->details->off }}</span>
                                                        @endif
                                                    @else
                                                    {{ $data->{$row->field} }}
                                                    @endif
                                                @elseif($row->type == 'color')
                                                    <span class="badge badge-lg" style="background-color: {{ $data->{$row->field} }}">{{ $data->{$row->field} }}</span>
                                                @elseif($row->type == 'text')
                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                    <div>{{ mb_strlen( $data->{$row->field} ) > 200 ? mb_substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>
                                                @elseif($row->type == 'text_area')
                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                    <div>{{ mb_strlen( $data->{$row->field} ) > 200 ? mb_substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>
                                                @elseif($row->type == 'file' && !empty($data->{$row->field}) )
                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                    @if(json_decode($data->{$row->field}) !== null)
                                                        @foreach(json_decode($data->{$row->field}) as $file)
                                                            <a href="{{ Storage::disk(config('voyager.storage.disk'))->url($file->download_link) ?: '' }}" target="_blank">
                                                                {{ $file->original_name ?: '' }}
                                                            </a>
                                                            <br/>
                                                        @endforeach
                                                    @else
                                                        <a href="{{ Storage::disk(config('voyager.storage.disk'))->url($data->{$row->field}) }}" target="_blank">
                                                            Download
                                                        </a>
                                                    @endif
                                                @elseif($row->type == 'rich_text_box')
                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                    <div>{{ mb_strlen( strip_tags($data->{$row->field}, '<b><i><u>') ) > 200 ? mb_substr(strip_tags($data->{$row->field}, '<b><i><u>'), 0, 200) . ' ...' : strip_tags($data->{$row->field}, '<b><i><u>') }}</div>
                                                @elseif($row->type == 'coordinates')
                                                    @include('voyager::partials.coordinates-static-image')
                                                @elseif($row->type == 'multiple_images')
                                                    @php $images = json_decode($data->{$row->field}); @endphp
                                                    @if($images)
                                                        @php $images = array_slice($images, 0, 3); @endphp
                                                        @foreach($images as $image)
                                                            <img src="@if( !filter_var($image, FILTER_VALIDATE_URL)){{ Voyager::image( $image ) }}@else{{ $image }}@endif" style="width:50px">
                                                        @endforeach
                                                    @endif
                                                @elseif($row->type == 'media_picker')
                                                    @php
                                                        if (is_array($data->{$row->field})) {
                                                            $files = $data->{$row->field};
                                                        } else {
                                                            $files = json_decode($data->{$row->field});
                                                        }
                                                    @endphp
                                                    @if ($files)
                                                        @if (property_exists($row->details, 'show_as_images') && $row->details->show_as_images)
                                                            @foreach (array_slice($files, 0, 3) as $file)
                                                            <img src="@if( !filter_var($file, FILTER_VALIDATE_URL)){{ Voyager::image( $file ) }}@else{{ $file }}@endif" style="width:50px">
                                                            @endforeach
                                                        @else
                                                            <ul>
                                                            @foreach (array_slice($files, 0, 3) as $file)
                                                                <li>{{ $file }}</li>
                                                            @endforeach
                                                            </ul>
                                                        @endif
                                                        @if (count($files) > 3)
                                                            {{ __('voyager::media.files_more', ['count' => (count($files) - 3)]) }}
                                                        @endif
                                                    @elseif (is_array($files) && count($files) == 0)
                                                        {{ trans_choice('voyager::media.files', 0) }}
                                                    @elseif ($data->{$row->field} != '')
                                                        @if (property_exists($row->details, 'show_as_images') && $row->details->show_as_images)
                                                            <img src="@if( !filter_var($data->{$row->field}, FILTER_VALIDATE_URL)){{ Voyager::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif" style="width:50px">
                                                        @else
                                                            {{ $data->{$row->field} }}
                                                        @endif
                                                    @else
                                                        {{ trans_choice('voyager::media.files', 0) }}
                                                    @endif
                                                
                                                @elseif($row->type == 'time')
                                                    @if (isset($data->{$row->field}))
                                                        {{ property_exists($row->details, 'format') ? \Carbon\Carbon::parse($data->{$row->field})->formatLocalized($row->details->format) : $data->{$row->field} }}
                                                    @endif
                                                @else
                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                        <span>{{ $data->{$row->field} }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($isServerSide)
                            <div class="pull-right">
                                <appointments-paginator 
                                :pagination-data="paginationData" 
                                @get-results="paginatorChangePage" 
                                :initial-pagination-data="{{ $dataTypeContent->toJson() }}"
                                table-id="table"
                            >
                        </appointments-paginator>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
            @if ( $currentLogedInUserRole == 'superadmin')
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-primary panelbordered">
                        <div class="panel-heading">
                            <!-- Multiple appointments assignment -->
                            <h3 class="panel-title panel-icon"><i class="voyager-external"></i>Zuteilung der Termine</h3>
                            <div class="panel-actions">
                                <a class="panel-action voyager-angle-up" data-toggle="panel-collapse" aria-hidden="true"></a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form action="{{ route('voyager.appointment.assign') }}" id="agentAssignementForm" style="margin-top: 1.6em;" method="POST">
                                {{ method_field("POST") }}
                                <!-- CSRF TOKEN -->
                                {{ csrf_field() }}
                                <div class="form-group">
                                    {{-- choose an agent --}}
                                    <label class="control-lab">Agent auswählen</label>
                                    @php
                                        $salesAgentRole = TCG\Voyager\Models\Role::where('name', 'sales_agent')->first();
                                    @endphp
                                    <select
                                        class="form-control"
                                        name="selected_agent_id"
                                        aria-hidden="true"
                                    >
                                        <option disabled value selected>Bitte auswählen</option>
                                        @foreach ($users as $user)
                                            @if ($user->role->id == $salesAgentRole->id)
                                                <option value="{{ $user->id }}">{{ $user->user_name }}</option>  
                                            @endif
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="selected_ids" value="" class="selected_ids">
                                    <div class="invalid-feedback" style="display: none; color: #dc3545;">Select an agent and an appointment(s)</div>
                                    <div class="valid-feedback" style="display: none; color: #28a745;">Changes done successfuly, refresh the page to see them</div>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary save pull-right">Zuteilen</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Single delete modal --}}
        <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><i class="voyager-trash"></i> {{ __('voyager::generic.delete_question') }} {{ strtolower($dataType->display_name_singular) }}?</h4>
                    </div>
                    <div class="modal-footer">
                        <form action="#" id="delete_form" method="POST">
                            {{ method_field('DELETE') }}
                            {{ csrf_field() }}
                            <input type="submit" class="btn btn-danger pull-right delete-confirm" value="{{ __('voyager::generic.delete_confirm') }}">
                        </form>
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </div>
@endif

@stop

@section('css')
@if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
    <link rel="stylesheet" href="{{ voyager_asset('lib/css/responsive.dataTables.min.css') }}">
@endif
<style>
    .invalid-feedback {
        display: none; 
        color: #dc3545 
    }

    .valid-feedback {
        display: none;
        color: #28a745;
    }
</style>
@stop

@section('javascript')
    <!-- VUEJS -->
    <script src="/js/pages/appointments.js"></script>
    <!-- DataTables -->
    @if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
        <script src="{{ voyager_asset('lib/js/dataTables.responsive.min.js') }}"></script>
    @endif
    <script>
        $(document).ready(function () {
            @if (!$dataType->server_side)
                var table = $('#dataTable').DataTable({!! json_encode(
                    array_merge([
                        "order" => $orderColumn,
                        "language" => __('voyager::datatable'),
                        "columnDefs" => [['targets' => -1, 'searchable' =>  false, 'orderable' => false]],
                    ],
                    config('voyager.dashboard.data_tables', []))
                , true) !!});
            @else
                $('#search-input select').select2({
                    minimumResultsForSearch: Infinity
                });
            @endif

            @if ($isModelTranslatable)
                $('.side-body').multilingual();
                //Reinitialise the multilingual features when they change tab
                $('#dataTable').on('draw.dt', function(){
                    $('.side-body').data('multilingual').init();
                })
            @endif
            $('.select_all').on('click', function(e) {
                $('input[name="row_id"]').prop('checked', $(this).prop('checked'));
            });
        });


        var deleteFormAction;
        $('td').on('click', '.delete', function (e) {
            $('#delete_form')[0].action = '{{ route('voyager.'.$dataType->slug.'.destroy', ['id' => '__id']) }}'.replace('__id', $(this).data('id'));
            $('#delete_modal').modal('show');
        });

        @if($usesSoftDeletes)
            @php
                $params = [
                    's' => $search->value,
                    'filter' => $search->filter,
                    'key' => $search->key,
                    'order_by' => $orderBy,
                    'sort_order' => $sortOrder,
                ];
            @endphp
            $(function() {
                $('#show_soft_deletes').change(function() {
                    if ($(this).prop('checked')) {
                        $('#dataTable').before('<a id="redir" href="{{ (route('voyager.'.$dataType->slug.'.index', array_merge($params, ['showSoftDeleted' => 1]), true)) }}"></a>');
                    }else{
                        $('#dataTable').before('<a id="redir" href="{{ (route('voyager.'.$dataType->slug.'.index', array_merge($params, ['showSoftDeleted' => 0]), true)) }}"></a>');
                    }

                    $('#redir')[0].click();
                })
            })
        @endif
        // watches the checkboxe changes

        // we want to make the following function available gloably 
        // so went we update the tables using vuejs
        // we can call the function again to watch the checkboxes
        window.watchTableCheckboxes = function() {
            $('input[name="row_id"]').on('change', function () {
                var ids = [];
                $('input[name="row_id"]').each(function() {
                    if ($(this).is(':checked')) {
                        ids.push($(this).val());
                    }
                });
                $('.selected_ids').val(ids);
                console.log(ids);
            });
        }

        watchTableCheckboxes();

        // from to assign multiple appointments to one agent
        $('#agentAssignementForm .btn').click(function(e) {
            if($('.selected_ids').val() == '' || $('.selected_agent_id').val() == '') {
                e.preventDefault();
                $('#agentAssignementForm .invalid-feedback').show();
            } else {
                $('#agentAssignementForm .invalid-feedback').hide();
                // $.ajax({
                //     url: 'appointment/assign',
                //     data: $('#agentAssignementForm').serialize(),
                //     method: 'POST',
                //     dataType: 'json'
                // }).done(function(data) {
                //     $('#agentAssignementForm .valid-feedback').show();
                //     setTimeout(function() {
                //         $('#agentAssignementForm .valid-feedback').hide();
                //     }, 2000);
                // }).fail(function() {
                //     console.error('shit happened');
                // });
            }
        })
    </script>
@stop
