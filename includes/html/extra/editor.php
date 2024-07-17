<div class="pageEditor editorContainer">
    <div class="row border-bottom border-light pb-1">
        <div class="col-sm-12 flex-row-around">
            <span class="font-20 font-weight-bold" id="editorTitle">Upload image</span>
        </div>
    </div>


    <div class="row flex-row-around mt-2">
        <div class="col-11 col-lg-10 col-xl-9 ">
            <form name="uploadImage" method="post" class="row hidden" id="editorImageContainer">
                <div class="col-xs-12 col-lg-8 form-group">
                    <input type="file" name="img[]" class="file-upload-default" accept="image/*" id="cropperImageUpload">
                    <div class="flex-row-around w-100">
                        <input type="hidden" name="request" value="uploadImage" />
                        <div class="flex-col-around w-100">
                            <div class="form-element input-group">
                                <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Image">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-orange-white" type="button">Select</button>
                                </span>
                            </div>
                            <div id="cropImageContainerDiv" class="">
                                <img src="<?=HOST?>includes/template/assets/images/placeholder.jpg" class="w-100" id="croppingImage" alt="cropper"/>
                            </div>

                            <div class="mt-2 pb-2">
                                <div class="alert alert-danger eNotice hidden" role="alert"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-lg-4">
                    <div class="flex-col-between h-100">
                        <div class="flex-col-start flex-align-center ml-2 mt-2 editor-data-box"></div>
                        <div class="form-element flex-row-end">
                            <button class="btn btn-primary-muted hidden" name="cropBtn">
                                Crop & upload
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <form name="uploadVideo" method="post" class="row hidden" id="editorVideoContainer">
                <div class="col-12 col-lg-8 form-group">
                    <input type="file" name="video[]" class="file-upload-default" accept="video/*" id="videoUploadHiddenFile">
                    <div class="flex-row-around w-100">
                        <input type="hidden" name="request" value="uploadVideo" />
                        <div class="flex-col-around w-100">
                            <div class="form-element input-group">
                                <input type="text" class="form-control file-upload-info" disabled="" placeholder="Upload Video">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-red" type="button">Select</button>
                                </span>
                            </div>

                            <div class="mt-2 pb-2">
                                <div class="alert alert-danger eNotice hidden" role="alert"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="flex-col-start h-100">
                        <div class="flex-col-start flex-align-center ml-2 mt-2 editor-data-box"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>



    <div class="close">
        <i class="mdi mdi-close"></i>
    </div>
</div>