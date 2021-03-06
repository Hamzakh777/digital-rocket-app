@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
@endphp

@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_title', __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->display_name_singular)

@section('page_header')
    
@stop

@section('content')
    <div class="page-content edit-add container-fluid" id="app"> 
        <h1 class="page-title">
            <i class="{{ $dataType->icon }}"></i>
            {{ __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.'Termin' }} {{ $edit ? 'Id '.$dataTypeContent->getKey() : '' }}
        </h1>
        <!-- GET CURRENT LOGED IN USER ROLE -->
        @php
            $currentUserRole = strtolower(auth()->user()->role->name);
        @endphp

        @if ($edit)
            {{-- TO DUPLICATE THE CURRENT APPOINTMENT --}}
            <div class="header-actions">
                @if ((strtolower(auth()->user()->role->name) == 'superadmin') || (strtolower(auth()->user()->role->name) == 'call_center_manager'))
                    <form 
                        action="{{ route('voyager.appointment.duplicate', ['id' => $dataTypeContent->getKey()]) }}"
                        method="POST"    
                        style="display: inline-block"
                    >
                        <!-- CSRF TOKEN -->
                        {{ csrf_field() }}
                        {{ method_field("POST") }}
                        <button type="submit" class="btn btn-primary">
                            <i class="voyager-list"></i> <span>Termin duplizieren</span>
                        </button>
                    </form>
                @endif

                <div style="display: inline-block">
                    <appointments-geolocation-btn 
                        @get-geolocation="getGeolocation" 
                        :appointment-id="{{$dataTypeContent->getKey()}}"
                        :is-loading="isSavingGeolocation"
                    >
                    </appointments-geolocation-btn>
                    <appointments-geolocation-modal :appointment-id="{{$dataTypeContent->getKey()}}"></appointments-geolocation-modal>
                </div>
                    <!-- notification modal -->
                    <base-notification-modal :message="responseMessage" :is-success="isGeoSavedSuccess" v-if="isNotificationModalOn"></base-notification-modal>
            </div>
        @endif
        @include('voyager::multilingual.language-selector')
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- form start -->
        <form role="form"
                class="form-edit-add"
                action="{{ $edit ? route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) : route('voyager.'.$dataType->slug.'.store') }}"
                method="POST" enctype="multipart/form-data">
            <!-- PUT Method if we are editing -->
            @if($edit)
                {{ method_field("PUT") }}
            @endif

            <!-- CSRF TOKEN -->
            {{ csrf_field() }}

            <!-- sperating the view -->
            @php
                $call_details_fields = [];
                $customer_details_fields = [];
                $visit_details_fields = [];
                $sales_details_fields = [];
                $questionaires_fields = [];
                $system_details_fields = [];
                $test= [];
            @endphp

            <!-- GET CURRENT LOGED IN USER ROLE -->
            @php
                $currentUserRole = auth()->user()->role->name;
            @endphp

            <!-- Adding / Editing -->
            @php
                $dataTypeRows = $dataType->{($edit ? 'editRows' : 'addRows' )};
            @endphp

            @foreach($dataTypeRows as $row)
                {{-- if appoitment is created we show the following two rows --}}
                @if(($row->field == 'comment_status' or $row->field == 'graduation_abschluss') &&  $add)
                    @continue
                @endif

                {{-- we dont to LOAD this fiels, so we jump them when iterating --}}
                @if( $row->display_name == "Comments")
                    @continue
                @endif

                {{-- having mutliple groups for form inputs --}}
                @php
                    $view_group = isset($row->details->viewGroup) ? $row->details->viewGroup : null;
                @endphp

                @if (isset($row->details->legend) && isset($row->details->legend->text))
                    <legend class="text-{{ $row->details->legend->align ?? 'center' }}" style="background-color: {{ $row->details->legend->bgcolor ?? '#f0f0f0' }};padding: 5px;">{{ $row->details->legend->text }}</legend>
                @endif
                
                @php
                    if($view_group == 'call details'){
                        array_push($call_details_fields, $row);

                    } elseif ($view_group == 'customer details') {
                        array_push($customer_details_fields, $row);

                    } elseif ($view_group == 'visit details') {
                        array_push($visit_details_fields, $row);

                    } elseif ($view_group == 'sales details') {
                        array_push($sales_details_fields, $row);

                    } elseif ($view_group == 'questionaires') {
                        array_push($questionaires_fields, $row);

                    } elseif ($view_group == 'system details') {
                        array_push($system_details_fields, $row);

                    } elseif($row->field == 'appointment_belongsto_user_relationship_1'){
                        array_push($system_details_fields, $row);
                    } else {
                        array_push($test, $row);
                    }
                @endphp
            @endforeach

            <!-- call details panel -->
            <div class="panel panel-primary panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title panel-icon"><i class="voyager-telephone"></i>Anruf Details</h3>
                    <div class="panel-actions">
                        <a class="panel-action voyager-angle-up" data-toggle="panel-collapse" aria-hidden="true"></a>
                    </div>
                </div>
                <div class="panel-body mt-1">
                    @foreach ($call_details_fields as $row)
                        @include('vendor.voyager.appointments.form-component')
                    @endforeach
                </div>
            </div>

            <!-- customer details panel -->
            <div class="panel panel-primary panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title panel-icon"><i class="voyager-person"></i>Kunden Details</h3>
                    <div class="panel-actions">
                        <a class="panel-action voyager-angle-up" data-toggle="panel-collapse" aria-hidden="true"></a>
                    </div>
                </div>
                <div class="panel-body mt-1">
                    @foreach ($customer_details_fields as $row)
                        @include('vendor.voyager.appointments.form-component')
                    @endforeach
                </div>
            </div>

            <!-- call details panel -->
            <div class="panel panel-primary panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title panel-icon"><i class="voyager-paper-plane"></i>Termin Details</h3>
                    <div class="panel-actions">
                        <a class="panel-action voyager-angle-up" data-toggle="panel-collapse" aria-hidden="true"></a>
                    </div>
                </div>
                <div class="panel-body mt-1">
                    @foreach ($visit_details_fields as $row)
                        @include('vendor.voyager.appointments.form-component')
                    @endforeach
                </div>
            </div>

            <!-- Sales details panel -->
            <div class="panel panel-primary panel-bordered" id="salesSection">
                <div class="panel-heading">
                    <h3 class="panel-title panel-icon"><i class="voyager-documentation"></i>ok</h3>
                    <div class="panel-actions">
                        <a class="panel-action voyager-angle-up" data-toggle="panel-collapse" aria-hidden="true"></a>
                    </div>
                </div>
                <div class="panel-body mt-1">
                    @foreach ($sales_details_fields as $row)
                        @include('vendor.voyager.appointments.form-component')
                    @endforeach
                </div>
            </div>

             @if ($edit)
                <div class="row">
                    <div class="col-md-12">
                        <appointments-comments :appointment-id="{{$dataTypeContent->getKey()}}">
                        </appointments-comments>
                    </div>
                </div>
            @endif

            <!-- questionaires panel -->
            <div class="panel panel-primary panel-bordered" id="edit-create-questionaires">
                <div class="panel-heading">
                    <h3 class="panel-title panel-icon"><i class="voyager-question"></i>Fragen</h3>
                    <div class="panel-actions">
                        <a class="panel-action voyager-angle-up" data-toggle="panel-collapse" aria-hidden="true"></a>
                    </div>
                </div>
                <div class="panel-body mt-1">
                    @foreach ($questionaires_fields as $row)
                        @include('vendor.voyager.appointments.form-component')
                    @endforeach
                </div>
            </div>

            <!-- system details panel -->
            <div class="panel panel-primary panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title panel-icon"><i class="voyager-laptop"></i>System Details</h3>
                    <div class="panel-actions">
                        <a class="panel-action voyager-angle-up" data-toggle="panel-collapse" aria-hidden="true"></a>
                    </div>
                </div>
                <div class="panel-body mt-1">
                    @foreach ($system_details_fields as $row)
                        @include('vendor.voyager.appointments.form-component')
                    @endforeach
                    {{-- upload form --}}
                    @if (false)
                        <iframe id="form_target" name="form_target" style="display:none"></iframe>
                        <form id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="post"
                                enctype="multipart/form-data" style="width:0;height:0;overflow:hidden">
                            <input name="image" id="upload_file" type="file"
                                    onchange="$('#my_form').submit();this.value='';">
                            <input type="hidden" name="type_slug" id="type_slug" value="{{ $dataType->slug }}">
                            {{ csrf_field() }}
                        </form>      @endif
                </div>
                <div class="panel-footer">
                    @section('submit-buttons')
                        <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                    @stop
                    @yield('submit-buttons')
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade modal-danger" id="confirm_delete_modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="voyager-warning"></i> {{ __('voyager::generic.are_you_sure') }}</h4>
                </div>

                <div class="modal-body">
                    <h4>{{ __('voyager::generic.are_you_sure_delete') }} '<span class="confirm_delete_name"></span>'</h4>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirm_delete">{{ __('voyager::generic.delete_confirm') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete File Modal -->
@stop

@section('javascript')

    <!-- GOOGLE MAP API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAdAloLpSTmyrWRbhD4X0txqecEr-savCg"></script>
    <script>
        window.google = google;
    </script>
    <!-- VUEJS -->
    <script src="/js/pages/appointments.js"></script>

    <script>
        var params = {};
        var $file;

        function deleteHandler(tag, isMulti) {
          return function() {
            $file = $(this).siblings(tag);

            params = {
                slug:   '{{ $dataType->slug }}',
                filename:  $file.data('file-name'),
                id:     $file.data('id'),
                field:  $file.parent().data('field-name'),
                multi: isMulti,
                _token: '{{ csrf_token() }}'
            }

            $('.confirm_delete_name').text(params.filename);
            $('#confirm_delete_modal').modal('show');
          };
        }

        $('document').ready(function () {
            
            

            $('.toggleswitch').bootstrapToggle();

            //Init datepicker for date fields if data-datepicker attribute defined
            //or if browser does not handle date inputs
            $('.form-group input[type=date]').each(function (idx, elt) {
                if (elt.type != 'date' || elt.hasAttribute('data-datepicker')) {
                    elt.type = 'text';
                    $(elt).datetimepicker($(elt).data('datepicker'));
                }
            });

            @if ($isModelTranslatable)
                $('.side-body').multilingual({"editing": true});
            @endif

            $('.side-body input[data-slug-origin]').each(function(i, el) {
                $(el).slugify();
            });

            $('.form-group').on('click', '.remove-multi-image', deleteHandler('img', true));
            $('.form-group').on('click', '.remove-single-image', deleteHandler('img', false));
            $('.form-group').on('click', '.remove-multi-file', deleteHandler('a', true));
            $('.form-group').on('click', '.remove-single-file', deleteHandler('a', false));

            $('#confirm_delete').on('click', function(){
                $.post('{{ route('voyager.media.remove') }}', params, function (response) {
                    if ( response
                        && response.data
                        && response.data.status
                        && response.data.status == 200 ) {

                        toastr.success(response.data.message);
                        $file.parent().fadeOut(300, function() { $(this).remove(); })
                    } else {
                        toastr.error("Error removing file.");
                    }
                });

                $('#confirm_delete_modal').modal('hide');
            });
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>

    @if (strtolower(auth()->user()->role->name) == 'sales_agent')
        <script>
            // to make the labels clickable on the
            // radio button, one click trggers two,
            // we only want to fire our event son 
            // click the second 

            // Sales agent can only save the appointment, 
            // when a conversion status is selected 
            // and geolocation is performed
            var count = 1;
            
            $('document').ready(function() {
                // this section and field has to be hidden
                $('#salesSection').hide();
                
                if($("input[name='comment_status']:checked").val()) {
                    if($("input[name='comment_status']:checked").val().toLowerCase() == 'open') {
                        // remove readonly from visit time and visit date fields
                        $('#visitDate').find('input').prop('readonly', false);
                        $('#visitTime').find('input').prop('readonly', false);

                    } else if ($("input[name='comment_status']:checked").val().toLowerCase() == 'positive') {   
                        $('#salesSection').show();
                    } 
                }

                

                $('#comment_status_radios').click(function() {
                    if(count == 2) {
                        if ($("input[name='comment_status']:checked").val()) {
                            count = 1;

                            $('#comment_feedback_input').show();

                            if($("input[name='comment_status']:checked").val().toLowerCase() == 'positive') {
                                $('#salesSection').show();
                                $('#visitDate').find('input').prop('readonly', true);
                                $('#visitTime').find('input').prop('readonly', true);

                            } else if($("input[name='comment_status']:checked").val().toLowerCase() == 'open') {
                                // remove readonly from visit time and visit date fields
                                
                                var startDate = new Date();
                                var startDate = startDate.setDate(startDate.getDate() - 1);
                                var oldDate = $("#visitDate .datepicker--date-only").val();
                                var test = $("#visitDate .datepicker--date-only").datetimepicker('destroy');

                                $("#visitDate  .datepicker--date-only").datetimepicker({
                                    format: "DD-MM-YYYY",
                                    minDate: startDate
                                });

                                $("#visitDate  .datepicker--date-only").val(oldDate);

                                $('#visitDate').find('input').prop('readonly', false);
                                $('#visitTime').find('input').prop('readonly', false);

                            } else {
                                $('#salesSection').hide();
                                $('#visitDate').find('input').prop('readonly', true);
                                $('#visitTime').find('input').prop('readonly', true);

                            }
                        } 
                    } else {
                        count++;
                    }
                });

                
            });
        </script>
    @endif
    
@stop
