

class Editor {

    editorTypes = ["image", "video", "text", "rich_text", "date"];

    constructor() {
        this.isInit = false;
    }

    init(media_type, reference) {
        this.editorType = media_type;
        this.reference = reference;

        if(this.isInit) return false;
        if(!this.editorTypes.includes(this.editorType)) return false;

        switch (this.editorType) {
            case "image":
                this.editor = new Image(this.editorType, this.reference);

                break;
            case "video":
                this.editor = new Video(this.editorType, this.reference);

                break;
            case "text":
                this.editor = new Text(this.editorType);

                break;
            case "rich_text":
                this.editor = new RichText(this.editorType);

                break;
            case "date":
                this.editor = new DateObject(this.editorType);

                break;
        }
        this.isInit = true;

        return this;
    }



    setDataBox(dataBox,fields,extra = "") {
        if(dataBox.length === 0) return true;

        for(let value of Object.values(fields)) {
            dataBox.append(
                '<p class="font-16 font-weight-bold">'+value+'</p>'
            );
        }
        dataBox.append(extra);
    }

    clearDataBox(dataBox) {
        if(dataBox.length) dataBox.html("");
    }


    collectAttributes(clickedElement){
        let params = this.params, cropSettings = {}, dataBoxFields = [];
        for(let attribute in params[this.editorType].attributes) {
            let attrItem = params[this.editorType].attributes[attribute];
            let attributeValue = clickedElement.attr(attrItem.selector);
            if(attributeValue === undefined) attributeValue = "";

            if(this.editorType === "image")
                cropSettings[attribute] = parseFloat(attributeValue);

            if(attrItem.dataBox)
                dataBoxFields.push(attrItem.text + attributeValue);
        }

        return {cropSettings,dataBoxFields}
    }


    responseHandler(response, eNoticeParent,successMsg = "") {
        response = ensureObject(response);

        if("success" in response) ePopup("Success", successMsg,0,"success", "approve");
        else ePopup("Error", JSON.stringify(response.error));
    }


    setEditorParams(params) {
        this.params = params;
    }

    prepareContainers(dataBox,eNoticeParent,titleBox,clickedElement) {
        let params = this.params, editorContainer = (params.editorContainer);

        $(document).find("."+params.containerClass).each(function (){
            $(this).removeClass(params.openClass)
        });


        for(let editorType in params.editorSelectors) {
            let el = editorContainer.find(params.editorSelectors[editorType]);
            if(editorType === this.editorType) el.removeClass(params.editorHiddenClass)
            else el.addClass(params.editorHiddenClass)
        }

        ePopup("","",1);
        this.clearDataBox(dataBox)

        editorContainer.addClass(params.openClass);
        titleBox.text(params[this.editorType].title_text);

        let collectAttributes = this.collectAttributes(clickedElement);
        if(!empty(collectAttributes.dataBoxFields)) this.setDataBox(dataBox,collectAttributes.dataBoxFields,params[this.editorType].dataBoxExtra);

        return collectAttributes;
    }



    mimicEditorToElement(editor,element) {
        let eClass = this, params = eClass.params, textArea = editor.find(params[eClass.editorType].textAreaEditor);
        element = element.parents(".creatorMode").length ? element.parents(".creatorMode").first() : element;





        if(eClass.editorType === "text") textArea.val(((element.text()).trim()));
        else textArea.html(((element.html()).trim()));




        if(params.text.strLenBox !== false) {
            if(eClass.editorType === "text") (editor.find(params[eClass.editorType].strLenBox)).text(((textArea.val()).length));
            else (editor.find(params[eClass.editorType].strLenBox)).text(((textArea.text()).length));
        }

        textArea.off("keyup").on("keyup",function (){


            let value = eClass.editorType === "text" ? textArea.val() : $(this).html(),
                trimVal = value.replaceAll('"',"'");

            if(empty(trimVal)) {
                trimVal = "CANNOT BE EMPTY";
                if(eClass.editorType === "text") $(this).val(trimVal);
                else $(this).text(trimVal);
            }

            if(eClass.editorType === "text") element.text(trimVal);
            else element.html(trimVal);
            element.attr("data-value",trimVal);

            if(element.hasClass("noEdits"))
                element.removeClass("noEdits");
            // else if (element.parents(".noEdits").length)
            //     element.parents(".noEdits").first().removeClass("noEdits");

            if(params[eClass.editorType].strLenBox !== false) {
                if(eClass.editorType === "text") (editor.find(params[eClass.editorType].strLenBox)).text(((textArea.val()).length));
                else (editor.find(params[eClass.editorType].strLenBox)).text(((textArea.text()).length));
            }


        });

    }

}


class DateObject extends Editor {
    constructor(editorType) {
        super();
        this.isInit = true;
        this.editorType = editorType;
    }

    start() {

        let params = this.params,
            editorContainer = (params.editorContainer),
            editor = editorContainer.find(params.editorSelectors[this.editorType]),
            dataBox = editor.find("."+params.dataBoxClass),
            titleBox = editorContainer.find(params.titleSelector),
            clickedElement = (params.clickedElement),
            eNoticeParent = (params.eNoticeParent);
        this.prepareContainers(dataBox,eNoticeParent,titleBox,clickedElement);

        this.handleDates(editor);

    }


    handleDates(editor) {
        let eClass = this, params = eClass.params,type = params[eClass.editorType],
            doneBtn = (params.editorContainer).find(type.doneBtn), error = {};
        if(doneBtn.length === 0) return false;

        doneBtn.off("click").on("click",function (e){
            e.preventDefault()
            ePopup("","",1);

            let collectValues = {};
            for(let when in type.inputElements) {
                let dateElement = (params.editorContainer).find(type.inputElements[when]);
                if(dateElement.length === 0) return false;

                let format = dateElement.attr("data-inputmask-inputformat"),
                    value = dateElement.val().trim();

                if(format === undefined) return false;

                collectValues[when] = value;
            }

            if(empty(collectValues.from)) {
                ePopup("Not so fast", "'Date from' is required to be filled out");
                return false;
            }

            for(let when in collectValues) {
                let value = collectValues[when];
                if(empty(value)) continue;

                if(!value.includes("/")) {
                    ePopup("Not so fast","Missing '/' from the date string");
                    return false;
                }

                let split = value.split("/");
                if(split.length !== 3) {
                    ePopup("Ooops, did you see that?","Malformed date string")
                    return false;
                }

                let month = split[1], day = split[0], year = split[2];
                if(month.length !== 2 || day.length !== 2 || year.length !== 4) {
                    ePopup("Ooops did you notice that?","Malformed date string. Date fields are off");
                    return false;
                }

                let date = new Date(([month,day,year].join("-"))),
                    timestamp, now = Date.now();
                date.setHours(0,0,0,0);
                timestamp = date.getTime();


                if(now >= timestamp){
                    ePopup("Not so fast","Bad dates. Dates cannot be before today's date");
                    return false;
                }

                collectValues[when] = timestamp;
            }


            if(!empty(collectValues.to) && (collectValues.from >= collectValues.to)){
                ePopup("Not so fast","'Date to' must be after that of 'Date from'. If you only have 1 date then simply leave 'Date to' empty",(editor));
                return false;
            }

            eClass.setDateValues(collectValues);
        });
    }



    setDateValues(dates) {
        let eClass = this, params = eClass.params, clickedElement = params.clickedElement,
            dateString = "", collectStringDates = {},thisYear = (new Date()).getFullYear();

        for(let when in dates) {
            let date = dates[when];
            if(empty(date)) {
                delete dates[when];
                continue;
            }

            let mo = new Intl.DateTimeFormat('en', { month: 'short' }).format(date);
            let da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(date);
            let ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(date);
            let dateArr = {month: mo+".", day: da,year: ye};

            if(parseInt(ye) === thisYear)
                delete dateArr.year;

            collectStringDates[when] = Object.values(dateArr).join(" ");
        }

        dateString = Object.values(collectStringDates).join(" - ");

        clickedElement.attr("data-value",JSON.stringify(dates));
        clickedElement.text(dateString)

        if(clickedElement.hasClass("noEdits")) clickedElement.removeClass("noEdits");

        (params.editorContainer).find(params.containerCloseSelector).trigger("click");
    }

}

//_____________________________________________________________________________________________________________________________________________________________________

class Text extends Editor {
    constructor(editorType) {
        super();
        this.isInit = true;
        this.editorType = editorType;
    }

    start() {

        let params = this.params,
            editorContainer = (params.editorContainer),
            editor = editorContainer.find(params.editorSelectors[this.editorType]),
            dataBox = editor.find("."+params.dataBoxClass),
            titleBox = editorContainer.find(params.titleSelector),
            clickedElement = (params.clickedElement),
            eNoticeParent = (params.eNoticeParent);

        this.prepareContainers(dataBox,eNoticeParent,titleBox,clickedElement);

        if(params[this.editorType].mimic)
            this.mimicEditorToElement(editor,clickedElement);

    }



}

//_____________________________________________________________________________________________________________________________________________________________________

class RichText extends Editor {
    constructor(editorType) {
        super();
        this.isInit = true;
        this.editorType = editorType;
    }

    start() {
        let params = this.params,
            editorContainer = (params.editorContainer),
            editor = editorContainer.find(params.editorSelectors[this.editorType]),
            dataBox = editor.find("."+params.dataBoxClass),
            titleBox = editorContainer.find(params.titleSelector),
            clickedElement = (params.clickedElement),
            eNoticeParent = (params.eNoticeParent);

        this.prepareContainers(dataBox,eNoticeParent,titleBox,clickedElement);


        if(params[this.editorType].mimic) this.mimicEditorToElement(editor,clickedElement);

    }

}





class Image extends Editor{

    constructor(editorType, reference) {
        super();
        this.isInit = true;
        this.editorType = editorType;
        this.reference = reference;
    }



    start() {

        let params = this.params,
            editorContainer = (params.editorContainer),
            editor = editorContainer.find(params.editorSelectors.image),
            dataBox = editor.find("."+params.dataBoxClass),
            titleBox = editorContainer.find(params.titleSelector),
            clickedElement = (params.clickedElement),
            eNoticeParent = (params.eNoticeParent);


        let collectAttributes = this.prepareContainers(dataBox,eNoticeParent,titleBox,clickedElement);
        editor.find(params.image.imageContainerSelector).html(
            '<img src="'+params.image.imagePlaceholder+'" class="w-100" id="'+params.image.imageId+'" alt="cropper"/>'
        );
        editor.find(params.image.fileInfoSelector).val("");


        this.imageHandler(collectAttributes.cropSettings);
    }





    imageHandler(settings) {
        let eClass = this, params = this.params, reference = this.reference;

        $('.file-upload-browse').off('click').on('click', function(e) {
            $(this).parents(params.editorSelectors.image).first().find(params.image.hiddenFileElement)
                .trigger('click');
        });

        let croppingImage = document.querySelector('#' + params.image.imageId),
            form = $(croppingImage).parents(params.image.formSelector).first(),
            cropBtn = (params.editorContainer).find(params.image.cropBtn),
            upload = $(params.image.hiddenFileId),
            cropper = '', fileExtension = '';


        //Sets cropper on the placeholder image
        cropper = new Cropper(croppingImage);
        // on change show image with crop options
        upload.off('change').on('change', async function (e) {
            ePopup("","",1);
            eClass.showCropper(false);

            //Inputs the filename to the bar
            let fileName = $(this).val().replace(/C:\\fakepath\\/i, '');
            fileExtension = fileExt(fileName);

            form.find(params.image.fileInfoSelector).val(fileName);
            if (e.target.files.length) {
                cropper.destroy(); //Destroys the cropper on the placeholder
                // start file reader
                const reader = new FileReader();
                reader.onload = function (e) {
                    if(e.target.result){
                        croppingImage.src = e.target.result; //Image source
                        cropper = new Cropper(croppingImage,{
                            autoCropArea: 1
                        }); //Set new cropper for new image

                    }
                };
                reader.readAsDataURL(e.target.files[0]); //Something



                if(!params.image.crop) {
                    window.setTimeout(async function () {
                        if (params.image.crop) {
                            await eClass.

                            upload(cropper,params.eNoticeParent,fileExtension, reference);
                        }

                    }, 300);
                    return true;
                }





                window.setTimeout(function (){

                    let imageData = cropper.getImageData(),
                        validateCrop = eClass.imageCropperValidateData(imageData,settings);


                    if("error" in validateCrop) {
                        ePopup("Ops!", validateCrop.error);
                        eClass.showCropper();
                        cropper.destroy();
                        return false;
                    }
                    if(!("success" in validateCrop) || !("success" in validateCrop && "crop" in validateCrop)) {
                        ePopup("Unexpected error","Something went wrong");
                        eClass.showCropper();
                        cropper.destroy();
                        return false;
                    }
                    if(validateCrop.success && validateCrop.crop && !("cropData" in validateCrop)) {
                        ePopup("Unexpected error","Something went wrong, no crop-data returned");
                        eClass.showCropper();
                        cropper.destroy();
                        return false;
                    }


                    if(validateCrop.success && !validateCrop.crop) {
                        eClass.upload(cropper,params.eNoticeParent,fileExtension, reference);
                        eClass.showCropper();
                        return true;
                    }

                    cropper.destroy();

                    let cropData = validateCrop.cropData,
                        scaledMinWidth = (Math.ceil(cropData.targetMinWidth / cropData.widthRatio)),
                        scaledMinHeight = (Math.ceil(cropData.targetMinHeight / cropData.heightRatio));

                    cropper = new Cropper(croppingImage,{
                        aspectRatio: cropData.targetRatio,
                        minCropBoxWidth: scaledMinWidth,
                        minCropBoxHeight: scaledMinHeight
                    });

                    eClass.showCropper(true,true);


                    // crop on click
                    cropBtn.off("click").on('click',function(e) {
                        $(this).off("click");
                        e.preventDefault();
                        eClass.upload(cropper,params.eNoticeParent,fileExtension, reference);
                    });
                }, 500);
            }
        });
    }

    showCropper(show = true,showBtn = false) {
        if(!show)
            (this.params.editorContainer).find(this.params.image.imageContainerSelector).addClass("no-vis");
        else
            (this.params.editorContainer).find(this.params.image.imageContainerSelector).removeClass("no-vis");


        if(showBtn)
            (this.params.editorContainer).find(this.params.image.cropBtn).removeClass("hidden");
        else
            (this.params.editorContainer).find(this.params.image.cropBtn).addClass("hidden");
    }

    async upload(cropper, eNoticeParent,fileExtension, reference) {

        let croppedCanvas = cropper.getCroppedCanvas({
            maxWidth: 4096,
            maxHeight: 4096
        }), response = {}, eClass = this, params = this.params;

        croppedCanvas.toBlob(async function (blob){
            /* Add blob and file name */
            let formData = new FormData();
            formData.append('file', blob);
            formData.append('request', "uploadMediaBlob");
            formData.append('reference', reference);


            $.ajax({
                url: serverHost+"requests.php",
                type: "POST",
                data:  formData,
                contentType: false,
                cache: false,
                processData:false,
                beforeSend : function() {
                    ePopup("Uploading....", "Hold on a moment while we process the media",0,"warning");
                },
                success: function(result) {
                    response = ensureObject(result);

                    if(typeof response !== "object" || (!("error" in response) && !("content" in response))) {
                        ePopup("Something went wrong", "We got an unexpected error. Please try again later");
                        return false;
                    }
                    if("error" in response) {
                        ePopup("Failed to upload media", response.error);
                        return false;
                    }
                    if(("successMessage" in params.image)) ePopup("Media successfully uploaded", params.image.successMessage, 0, "success", "approve");


                    let success = eClass.params.image.onSuccess;
                    if(success.redirectOnUpload) {
                        window.setTimeout(function (){
                            window.location = empty(success.redirectPath) ? serverHost : success.redirectPath;
                        }, 600);
                        return true;
                    }

                    if(success.call_method) {
                        let methodName = success.method_name;
                        if(methodName in window) {
                            if(!empty(success.method_arguments) && ("src" in success.method_arguments))
                                success.method_arguments.src = serverHost+response.content;

                            window[methodName](success.method_arguments)
                        }
                    }

                    let actions = success.clickedElement;

                    if(actions.setSrc) eClass.params.clickedElement.attr("src",serverHost+result.content);

                    if(actions.addClass !== false) {
                        for(let newClass of actions.addClass) {
                            (eClass.params.clickedElement).addClass(newClass);
                        }
                    }

                    if(actions.removeClass !== false) {
                        for(let classToRemove of actions.removeClass) {
                            (eClass.params.clickedElement).removeClass(classToRemove);
                        }
                    }

                    if(actions.setAttributes !== false) {
                        for(let newAttribute of actions.setAttributes) {
                            (eClass.params.clickedElement).attr(newAttribute,result.content);
                        }
                    }

                    if(success.closeEditor) {
                        window.setTimeout(function (){
                            (eClass.params.editorContainer).find(eClass.params.containerCloseSelector).trigger("click");
                        },200);
                    }


                    return true;
                },
                error: function(e) {
                    return {error: "Something went wrong"};
                }
            });
        }, ('image/' + fileExtension),1);
    }





    imageCropperValidateData(imageData, settings) {
        let response = {};
        if(imageData.naturalWidth < settings.minWidth) {
            response.error = `The image is too small. Minimum width required is ${settings.minWidth}px `+
                `but the image is only ${imageData.naturalWidth}px wide`;
            return response;
        }

        if(imageData.naturalHeight < settings.minHeight) {
            response.error = `The image is too small. Minimum height required is ${settings.minHeight}px `+
                `but the image is only ${imageData.naturalHeight}px tall`;
            return response;
        }

        let ratioDifference = imageData.aspectRatio - settings.ratio;

        if(ratioDifference < settings.ratioBuffer && ratioDifference > (-1 * settings.ratioBuffer))
            return {success: true, crop: false};

        let calculatedDimensions = newImgDimension(settings.ratio,imageData.aspectRatio, imageData.naturalWidth, imageData.naturalHeight);
        if (!(settings.minWidth <= calculatedDimensions.width && settings.minHeight <= calculatedDimensions.height)) {
            response.error = `The image's ratio requires it to be cropped from an aspect ratio of ${imageData.aspectRatio} to `+
                `${settings.ratio}. However, due to the image's dimensions of ${imageData.naturalWidth}x${imageData.naturalHeight} `+
                `would render the image below the minimum dimensions of ${settings.minWidth}x${settings.minHeight}`;
            return response;
        }


        return {
            success: true,
            crop: true,
            cropData: {
                naturalWidth: imageData.naturalWidth,
                naturalHeight: imageData.naturalHeight,
                canvasWidth: parseFloat((imageData.width.toFixed(2))),
                canvasHeight: parseFloat((imageData.height.toFixed(2))),
                targetMinWidth: settings.minWidth,
                targetMinHeight: settings.minHeight,
                targetRatio: settings.ratio,
                heightRatio: (imageData.naturalHeight / imageData.height),
                widthRatio: (imageData.naturalWidth / imageData.width)
            }
        }

    }



}














class Video extends Editor{

    constructor(editorType, reference) {
        super();
        this.isInit = true;
        this.editorType = editorType;
        this.reference = reference;
    }



    start() {

        let params = this.params,
            editorContainer = (params.editorContainer),
            editor = editorContainer.find(params.editorSelectors.video),
            dataBox = editor.find("."+params.dataBoxClass),
            titleBox = editorContainer.find(params.titleSelector),
            clickedElement = (params.clickedElement),
            eNoticeParent = (params.eNoticeParent);

        let collectAttributes = this.prepareContainers(dataBox,eNoticeParent,titleBox,clickedElement);
        editor.find(params.video.fileInfoSelector).val("");

        this.videoHandler(collectAttributes.cropSettings);
    }





    videoHandler() {
        let eClass = this, params = this.params, reference = this.reference;
        $('.file-upload-browse').off('click').on('click', function(e) {
            $(this).parents(params.editorSelectors.video).first().find(params.video.hiddenFileElement)
                .trigger('click');
        });

        let form = $(params.video.formSelector).first(), upload = $(params.video.hiddenFileId), fileExtension = '';
        upload.off('change').on('change', async function (e) {
            ePopup("","",1);

            //Inputs the filename to the bar
            let fileName = $(this).val().replace(/C:\\fakepath\\/i, '');
            fileExtension = fileExt(fileName);

            form.find(params.video.fileInfoSelector).val(fileName);
            if (e.target.files.length) {
                window.setTimeout(async function () {
                    await eClass.upload(e.target.files[0],params.eNoticeParent, reference);
                }, 300);
            }
        });
    }

    async upload(blob, eNoticeParent, reference) {
        let response = {}, eClass = this, params = this.params;

        /* Add blob and file name */
        let formData = new FormData();
        formData.append('file', blob);
        formData.append('request', "uploadMediaBlob");
        formData.append('reference', reference);


        $.ajax({
            url: serverHost+"requests.php",
            type: "POST",
            data:  formData,
            contentType: false,
            cache: false,
            processData:false,
            beforeSend : function() {
                ePopup("Uploading....", "Hold on a moment while we process the media",0,"warning");
            },
            success: function(result) {
                response = ensureObject(result);

                if(typeof response !== "object" || (!("error" in response) && !("content" in response))) {
                    ePopup("Something went wrong", "We received an unexpected error. Please try again later");
                    return false;
                }
                if("error" in response) {
                    ePopup("Failed to upload media", response.error);
                    return false;
                }
                if(("successMessage" in params.video)) ePopup("Media successfully uploaded", params.video.successMessage, 0, "success", "approve");


                let success = eClass.params.video.onSuccess;
                if(success.redirectOnUpload) {
                    window.setTimeout(function (){
                        window.location = empty(success.redirectPath) ? serverHost : success.redirectPath;
                    }, 600);
                    return true;
                }

                if(success.call_method) {
                    let methodName = success.method_name;
                    if(methodName in window) {
                        if(!empty(success.method_arguments) && ("src" in success.method_arguments))
                            success.method_arguments.src = serverHost+response.content;

                        window[methodName](success.method_arguments)
                    }
                }

                let actions = success.clickedElement;
                if(actions.setSrc) eClass.params.clickedElement.attr("src",serverHost+response.content);

                if(actions.addClass !== false) {
                    for(let newClass of actions.addClass) {
                        (eClass.params.clickedElement).addClass(newClass);
                    }
                }

                if(actions.removeClass !== false) {
                    for(let classToRemove of actions.removeClass) {
                        (eClass.params.clickedElement).removeClass(classToRemove);
                    }
                }

                if(actions.setAttributes !== false) {
                    for(let newAttribute of actions.setAttributes) {
                        (eClass.params.clickedElement).attr(newAttribute,response.content);
                    }
                }

                if(success.closeEditor) {
                    window.setTimeout(function (){
                        (eClass.params.editorContainer).find(eClass.params.containerCloseSelector).trigger("click");
                    },200);
                }


                return true;
            },
            error: function(e) {
                return {error: "Something went wrong"};
            }
        });
    }
}
















































