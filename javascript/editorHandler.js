const uploadSettings = {
    profile_picture:{
        dimensions: {
            size: {
                width: 350,
                height: 350
            },
            ratio: 1,
            buffer: .01
        }
    },
    cover_image:{
        dimensions: {
            size: {
                width: 500,
                height: 500
            },
            ratio: 1,
            buffer: .01
        }
    }
}

function leaveSiteWarning(){ return 'Are you sure you want to leave? Changes will not be saved'; }
// if(findGetParameter("page") === "manage_events") $(window).bind("beforeunload",leaveSiteWarning);


$(function (){


    (function($) {
        'use strict';
        if($(document).find("[data-inputmask]").length) $(":input").inputmask(); // initializing inputmask
    })(jQuery);




    if($(document).find(".creatorMode").length > 0) setEditorElementAttributes();

    $(document).on("click",".upload-img",function (){
        let dataId = $(this).attr("data-id");
        if(!(dataId in uploadSettings)) return false;
        editorHandler($(this), dataId);
    });

    $(document).on("click",".pageEditor .close",function (){
        $(this).parents(".pageEditor").removeClass("open");
    });

    $(document).on("click","button[name=finish_profile_image_setup], #upload_cover_images",async function (){

        let res = ensureObject(await requestServer({request: "pre_signup_conditions_met"}));
        if(res.completed) {
            await requestServer({request: "set_logged_in"})
                .then(() => {

                    ePopup("Account setup completed", "You are now ready to explore Simplif, welcome!",
                        0, "success", "approve");
                    window.setTimeout(function (){ window.location = serverHost; }, 5000);
                })
        }
        else {
            if(("missing_items" in res)) ePopupTimeout("Soon done", "You're still missing " + prepareProperNameString(res.missing_items.join(" and ")))
            else ePopupTimeout("Something went wrong", "An unexpected error occurred. Please try again later");
        }
    });

    // $(document).on("dblclick",".creatorMode:not(select)",function (e){
    //     let element, target = $(e.target);
    //     if(target.hasClass("creatorMode")) element = target;
    //     else if(target.parents(".creatorMode").length) element = target.parents(".creatorMode").first();
    //     else return false;
    //     editorHandler(element);
    // });

});





function editorHandler(clickedElement, dataId) {
    let editorContainer = $(document).find(".pageEditor"), mediaType = "image";

    let editor = (new Editor()).init(mediaType, dataId)
    let elementParent = clickedElement.parents(".dataParentContainer").first();
    let title = prepareProperNameString(dataId, false, false);

    if(editor === false) return false;
    editor = editor.editor;

    let params = {
        editorContainer,
        containerClass: "editorContainer",
        openClass: "open",
        dataBoxClass: "editor-data-box",
        editorSelectors: {image: "#editorImageContainer", video: "#editorVideoContainer"},
        titleSelector: "#editorTitle",
        editorHiddenClass: "hidden",
        clickedElement,
        eNoticeParent: editorContainer,
        containerCloseSelector: ".close",
        image: {
            crop: true,
            imageContainerSelector: "#cropImageContainerDiv",
            imagePlaceholder: serverHost+"includes/template/assets/images/placeholder.jpg",
            fileInfoSelector: ".file-upload-info",
            cropBtn: "button[name=cropBtn]",
            attributes: {
                minWidth: {dataBox: true, text: "Min. width: ", selector: "data-size-width"},
                minHeight: {dataBox: true, text: "Min. height: ", selector: "data-size-height"},
                ratio: {dataBox: true, text: "Recommended ratio: ", selector: "data-size-ratio"},
                ratioBuffer: {dataBox: false, text: "", selector: "data-size-buffer"},
            },
            title_text: "Upload " + title,
            dataBoxExtra: "",
            formSelector: "form[name=uploadImage]",
            hiddenFileElement: ".file-upload-default",
            hiddenFileId: "#cropperImageUpload",
            imageId: "croppingImage",
            successMessage: "Successfully uploaded the image",
            asyncRequestName: "uploadImage",
            onSuccess: {
                redirectOnUpload: false,
                redirectPath: "",
                call_method: true,
                method_name: "setAdditionalMediaElement",
                method_arguments: {src: "", container: elementParent, media_type: mediaType, reference: dataId},
                clickedElement: {
                    setSrc: false,
                    addClass: false,
                    removeClass: [],
                    setAttributes: ["data-value"]
                },
                closeEditor: true
            }
        },
        video: {
            fileInfoSelector: ".file-upload-info",
            title_text: "Upload " + title,
            dataBoxExtra: "",
            formSelector: "form[name=uploadVideo]",
            hiddenFileElement: ".file-upload-default",
            hiddenFileId: "#videoUploadHiddenFile",
            successMessage: "Successfully uploaded the video",
            attributes: {
                fileSize: {dataBox: true, text: "Max filesize: ", selector: "data-file-size"},
            },
            onSuccess: {
                redirectOnUpload: false,
                redirectPath: "",
                call_method: true,
                method_name: "setAdditionalMediaElement",
                method_arguments: {src: "", container: elementParent, media_type: "video"},
                clickedElement: {
                    setSrc: false,
                    addClass: false,
                    removeClass: [],
                    setAttributes: ["data-value"]
                },
                closeEditor: true
            }
        }
    }

    editor.setEditorParams(params);
    editor.start();
}


















async function validateEventFields() {
    let allFields = $(document).find(".creatorMode"),
        success = true, failureCount = 0;
    allFields.each(function () {
        if($(this).attr("data-value") === undefined || empty($(this).attr("data-value"))) {
            success = false;
            failureCount += 1;
        }

    });

    return {success,failureCount};
}






async function setEditorElementAttributes() {
    $(document).find("[data-id]").each(function () {
        let element = $(this), dataId = element.attr("data-id"),
            field, min, max, ratio, width,height,buffer;

        if(!Object.keys(uploadSettings).includes(dataId)) return;
        field = uploadSettings[dataId];

        if(Object.keys(field).includes("length")) {
            min = field.length.min;
            max = field.length.max;
            element.attr("data-length-min",min).attr("data-length-max",max);
            return;
        }

        if(Object.keys(field).includes("file_size")) {
            element.attr("data-file-size",field.file_size + "Mb");
            return;
        }

        if(Object.keys(field).includes("dimensions")) {
            let dimensions = field.dimensions;
            width = dimensions.size.width;
            height = dimensions.size.height;
            ratio = dimensions.ratio;
            buffer = dimensions.buffer;
            element.attr("data-size-width",width).attr("data-size-height",height)
                .attr("data-size-ratio",ratio).attr("data-size-buffer",buffer);

        }
    });
}






