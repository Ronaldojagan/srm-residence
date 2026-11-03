@extends('backend.layout.main')

@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    @endpush

    <div class="row mb-3">
        <div class="col-md-7 mt-3">
            <div class="mt-3 mt-md-0">
                <h4 class="page-title-main page">Customer Edit</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="profile-card">
                <div class="row">
                    <div class="col-md-4 text-center">
                        @if($edit->image == 'avatar.jpg')
                            <img src="{{ url('img/avatar.jpg') }}" alt="{{$edit->name}}" class="profile-image mb-2" style="width:50%">
                        @else
                            <img src="{{ url('storage/'.$edit->image) }}" alt="{{$edit->name}}" class="profile-image mb-2">
                        @endif
                        @if($edit->status == true)
                            <p class="active-status text-success mb-0">Active</p>
                        @else
                            <p class="active-status text-danger mb-0">Inactive</p>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h3 class="text-primary mb-3">{{$edit->name}}</h3>
                        <p class="mb-1">{{$edit->phone}}</p>
                        <button class="btn waves-effect waves-light button-color save-width input-form text-white account-save" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                            Update Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">Update Customer Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('admin/users/' . Crypt::encrypt($edit->id)) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ $edit->status == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $edit->status == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn waves-effect waves-light button-color save-width input-form text-white account-save">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('footer_content')
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"
            integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
        <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
        <script src="{{ url('assets/js/pages/sweet-alerts.init.js') }}"></script>
    @endpush
@endsection
