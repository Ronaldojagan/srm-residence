@extends('backend.layout.main')
@section('content')
    @push('head_content')
        <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    @endpush
    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <div class="mt-3 mt-md-0">
                @if (isset($edit))
                <h4 class="page-title-main page">Edit Banner</h4>
                @else
                <h4 class="page-title-main page">Create Banner</h4>
                @endif
            </div>
        </div><!-- end col-->
        <div class="col-md-6 mt-3 mb-3 flex-wrap align-items-center justify-content-sm-end">
            <a href="{{ url('/admin/settings') }}" type="button" class="btn add-btn float-end waves-effect waves-light button-color text-white"><i
                        class="mdi mdi-keyboard-backspace me-1"></i> Back</a>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <div class="card save-card card-shadow form-card-color">
                <div class="card-body">
                    @if(isset($edit))
                    <form action="{{url('admin/banner/'.Crypt::encrypt($edit->id))}}" method="post" enctype="multipart/form-data" class="form-horizontal">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Banner Name</label>
                                <div class="">
                                    <input type="text" name="name" class="form-control form-field-style input-form  @error('name') is-invalid @enderror"
                                        placeholder="Enter Banner Name" value="{{ $edit->name }}">
                                    @error('name')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Image</label>
                                <div class="">
                                    <input type="file" class="form-control form-field-style input-form @error('image') is-invalid @enderror"
                                    name="image" />
                                    @error('image')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <a href="javascript:void(0);" data-url="{{url('storage/banner/'.$edit->image) }}" data-toggle="modal" data-target="#image-view" width="35px" height="35px" class="rounded-circle me-1 data-table-image edit open-image-model">{{$edit->image}}</a>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-dark">Status</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="radio" id="active" name="status" class="form-check-input"
                                                @if ($edit->status == '1') checked @endif value="1" checked>
                                            <label class="form-check-label" for="active">Active</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="radio" id="inactive" name="status" class="form-check-input"
                                                @if ($edit->status == '0') checked @endif value="0">
                                            <label class="form-check-label" for="inactive">Inactive</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="submit_btn text-end">
                            <button type="submit"
                                class="btn waves-effect waves-light button-color save-width input-form text-white account-save">Save</button>
                        </div>
                    </form>
                    @else
                    <form action="{{url('admin/banner')}}" method="post" enctype="multipart/form-data" class="form-horizontal">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Banner Name</label>
                                <div class="">
                                    <input type="text" name="name" class="form-control form-field-style input-form  @error('name') is-invalid @enderror"
                                        placeholder="Enter Banner Name" value="{{ old('name') }}">
                                    @error('name')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Image</label>
                                <div class="">
                                    <input type="file" class="form-control form-field-style input-form @error('image') is-invalid @enderror"
                                    name="image" />
                                    @error('image')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-dark">Status</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="radio" id="active" name="status" class="form-check-input"
                                                @if (old('status') == '1') checked @endif value="1" checked>
                                            <label class="form-check-label" for="active">Active</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input type="radio" id="inactive" name="status" class="form-check-input"
                                                @if (old('status') == '0') checked @endif value="0">
                                            <label class="form-check-label" for="inactive">Inactive</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <button type="submit"
                                    class="btn waves-effect waves-light button-color save-width input-form text-white account-save">Save</button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @push('footer_content')
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"
        integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <script src="{{ url('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
    @endpush
@endsection
