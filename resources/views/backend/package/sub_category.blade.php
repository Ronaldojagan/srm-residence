@extends('backend.layout.main')

@section('content')
    @push('head_content')
    @endpush

    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <h4 class="page-title-main page">
                {{ isset($edit) ? 'Edit Sub Category' : 'Create Sub Category' }}
            </h4>
        </div>
        <div class="col-md-6 mt-3 d-flex flex-wrap align-items-center justify-content-sm-end">
            <a href="{{ url('/admin/category/'. Crypt::encrypt($category->id)) }}" class="btn add-btn float-end waves-effect waves-light button-color text-white">
                <i class="mdi mdi-keyboard-backspace me-1"></i> Back
            </a>
        </div>
    </div>
        <div class="row">
            <div class="col-12 col-md-8 col-lg-6 card save-card card-shadow input-background mt-4">
                <div class="card-body">
                    <form action="{{ isset($edit) ? url('/admin/sub-category/' . Crypt::encrypt($edit->id)) : url('/admin/sub-category') }}" method="post" class="form-horizontal" enctype="multipart/form-data">
                        @csrf
                        @if (isset($edit))
                            @method('PUT')
                        @endif
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Category Name</label>
                                <input type="text" class="form-control input-form" value="{{ $category->name }}" readonly>
                                <input type="hidden" name="category_id" value="{{ $category->id }}">
                                @error('category_id')
                                    <span class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Sub Category Name</label>
                                <input type="text" name="name" class="form-control input-form @error('name') is-invalid @enderror"
                                       placeholder="Enter Sub Category Name" value="{{ isset($edit) ? $edit->name : old('name') }}">
                                @error('name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label text-dark">Image</label>
                                <input type="file" class="form-control input-form @error('image') is-invalid @enderror" name="image"
                                       @if(isset($edit)) data-default-file="{{ url('storage/category/' . $edit->image) }}" @endif />
                                @if(isset($edit))
                                    <a href="javascript:void(0);" class="open-image-model" data-url="{{ url('storage/sub-category/' . $edit->image) }}">
                                        {{ $edit->image }}
                                    </a>
                                @endif
                                @error('image')
                                <span class="invalid-feedback d-block">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>

                        </div>

                        <div class="submit text-end">
                            <button type="submit" class="btn waves-effect waves-light button-color save-width input-form text-white account-save">
                                @if (isset($edit))
                                    Update
                                @else
                                    Create
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @push('footer_content')
        <script src="{{ url('assets/libs/dropzone/min/dropzone.min.js') }}"></script>
        <script src="{{ url('assets/libs/dropify/js/dropify.min.js') }}"></script>
        <script src="{{ url('assets/js/pages/form-fileuploads.init.js') }}"></script>
    @endpush
@endsection
