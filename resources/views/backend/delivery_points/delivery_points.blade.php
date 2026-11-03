@extends('backend.layout.main')

@section('content')
    @push('head_content')
    @endpush

    <div class="row justify-content-between">
        <div class="col-md-6 mt-3">
            <h4 class="page-title-main page">
                {{ isset($edit) ? 'Edit Delivery Points' : 'Create Delivery Points' }}
            </h4>
        </div>
        <div class="col-md-6 mt-3 d-flex flex-wrap align-items-center justify-content-sm-end">
            <a href="{{ url('/admin/delivery-points') }}"
                class="btn add-btn float-end waves-effect waves-light button-color text-white">
                <i class="mdi mdi-keyboard-backspace me-1"></i> Back
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-8 col-lg-6 card save-card card-shadow input-background mt-4">
            <div class="card-body">
                <form action="{{ isset($edit) ? url('/admin/delivery-points/' . Crypt::encrypt($edit->id)) : url('/admin/delivery-points') }}"
                    method="post" class="form-horizontal" enctype="multipart/form-data">
                    @csrf
                    @if (isset($edit))
                        @method('PUT')
                    @endif
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label text-dark">Location</label>
                            <input type="text" name="location"
                                class="form-control input-form @error('name') is-invalid @enderror"
                                placeholder="Enter Loaction" value="{{ isset($edit) ? $edit->location : old('name') }}">
                            @error('name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label text-dark">Sub Location</label>
                            <input type="text" name="sub_location"
                                class="form-control input-form @error('name') is-invalid @enderror"
                                placeholder="Enter Sub Location" value="{{ isset($edit) ? $edit->sub_location : old('name') }}">
                            @error('name')
                                <span class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label text-dark d-block">Status</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input @error('status') is-invalid @enderror" type="radio"
                                    name="status" id="status_active" value="1"
                                    {{ old('status', isset($edit) ? $edit->status : '') == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="status_active">
                                    Active
                                </label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input @error('status') is-invalid @enderror" type="radio"
                                    name="status" id="status_inactive" value="0"
                                    {{ old('status', isset($edit) ? $edit->status : '') == 0 ? 'checked' : '' }}>
                                <label class="form-check-label" for="status_inactive">
                                    Inactive
                                </label>
                            </div>

                            @error('status')
                                <span class="invalid-feedback d-block">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        {{-- <div class="col-12 mb-3">
                            <label class="form-label text-dark">Image</label>

                            <div class="file-input-group">
                                <div class="input-group">
                                    <input type="file"
                                        class="form-control input-form @error('image') is-invalid @enderror" name="image"
                                        id="imageUpload" accept="image/*">


                                </div>

                                <div class="file-info mt-2" id="fileInfo">
                                    @if (isset($edit) && $edit->image)
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-dark me-2">
                                                <i class="mdi mdi-file-image me-1"></i> {{ $edit->image }}
                                            </span>
                                            <a href="javascript:void(0);" class="open-image-model text-primary"
                                                data-url="{{ url('storage/brand/' . $edit->image) }}">
                                                <i class="mdi mdi-eye me-1"></i> View
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">No file selected</span>
                                    @endif
                                </div>

                                <div class="image-preview mt-2" id="imagePreview"
                                    @if (isset($edit) && $edit->image) style="display: block;"
                                        @else
                                            style="display: none;" @endif>
                                    @if (isset($edit) && $edit->image)
                                        <img src="{{ url('storage/brand/' . $edit->image) }}" alt="Current Image"
                                            class="img-thumbnail preview-image" style="max-height: 150px;">
                                    @endif
                                </div>
                            </div>

                            @error('image')
                                <div class="invalid-feedback d-block">
                                    <strong>{{ $message }}</strong>
                                </div>
                            @enderror
                        </div> --}}
                    </div>

                    <div class="submit text-end">
                        <button type="submit"
                            class="btn waves-effect waves-light button-color save-width input-form text-white account-save">
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
        <script>
            $(document).ready(function() {
                $('#imageUpload').on('change', function() {
                    const file = this.files[0];
                    const fileInfo = $('#fileInfo');
                    const preview = $('#imagePreview');

                    if (file) {
                        fileInfo.html(`
                    <div class="d-flex align-items-center">
                        <span class="badge bg-light text-dark me-2">
                            <i class="mdi mdi-file-image me-1"></i> ${file.name}
                        </span>
                        <span class="text-muted">(${(file.size/1024).toFixed(2)} KB)</span>
                    </div>
                `);

                        if (file.type.match('image.*')) {
                            const reader = new FileReader();

                            reader.onload = function(e) {
                                preview.html(`
                            <img src="${e.target.result}"
                                 alt="Preview"
                                 class="img-thumbnail preview-image"
                                 style="max-height: 150px;">
                        `).show();
                            }

                            reader.readAsDataURL(file);
                        }
                    } else {
                        @if (isset($edit) && $edit->image)
                            fileInfo.html(`
                        <div class="d-flex align-items-center">
                            <span class="badge bg-light text-dark me-2">
                                <i class="mdi mdi-file-image me-1"></i> {{ $edit->image }}
                            </span>
                            <a href="javascript:void(0);"
                               class="open-image-model text-primary"
                               data-url="{{ url('storage/brand/' . $edit->image) }}">
                                <i class="mdi mdi-eye me-1"></i> View
                            </a>
                        </div>
                    `);
                            preview.show();
                        @else
                            fileInfo.html('<span class="text-muted">No file selected</span>');
                            preview.hide();
                        @endif
                    }
                });
            });
        </script>
    @endpush
@endsection
