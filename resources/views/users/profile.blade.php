@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">{{ $user->name }}'s Profile</div>
                    <div class="panel-body">
                        <div class="col-md-3">
                            <div class="profile-image-container">
                                <img id="avatar-md" src="/uploads/avatars/{{ $user->avatar }}" style="width:150px; height:150px; float:left; border-radius:50%; margin-right:25px;">
                            </div>
                            <div class="uploadButton">
                                <button class="btn btn-primary" id="upload-button">Upload new photo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="crop-container" style="display:none"></div>
@endsection
@section('scripts')

    @parent
    <script src="/plugins/croppic/croppic.min.js"></script>
    <style src=""></style>
    <script>
        var eyeCandy = $('#crop-container');
        var croppedOptions = {
            rotateControls: false,
            uploadUrl: '{{ url("/upload") }}',
            uploadData: {
                'csrf_token': '{{ csrf_token() }}',
            },
            cropUrl: '{{ url("/crop") }}',
            cropData: {
                'csrf_token' : '{{ csrf_token() }}',
                'width' : eyeCandy.width(),
                'height': eyeCandy.height()
            },
            modal: true,
            customUploadButtonId : 'upload-button',
            onAfterImgCrop : function () {
                $.ajax({
                    url: '{{ url("/avatar") }}',
                    type: 'get',
                    dataType: 'json',
                    success: function (data) {
                        $("#avatar-md").attr("src",data.imgsrc);
                        $("#avatar-sm").attr("src",data.imgsrc);
                    },
                    complete: function () {
                    }
                });
            }

        };
        var cropperBox = new Croppic('crop-container', croppedOptions);
    </script>
@endsection

