



async function resetPwd(btn) {
    let form = btn.parents("form").first();
    let emailField = form.find("input[name=email]").first();

    if(!emailField.length) return false;
    let email = emailField.val();

    if(empty(email)) return false;
    let params = {request: "reset_pwd", email};

    let result = await requestServer(params)
    console.error(result);
    result = ensureObject(result);

    if(typeof result !== "object" || !("status" in result)) {
        ePopup("Something went wrong", "Please try again later");
        return false;
    }

    if(result.status === "error") {
        ePopup("Something went wrong", result.error);
        return false;
    }

    ePopup(result.message, "Check your email", 0, "success", "email_approve");
    window.setTimeout(function () {window.location = window.location.href;}, 2500);
}


async function createNewPwdFromReset(btn) {
    let form = btn.parents("form").first();
    let passwordField = form.find("input[name=password]").first();
    let passwordRepeatField = form.find("input[name=password_repeat]").first();
    let token = findGetParameter("token");

    if(empty(token)) return false;
    if(!passwordField.length || !passwordRepeatField.length) return false;
    let password = passwordField.val();
    let passwordRepeat = passwordRepeatField.val();

    if(empty(password) || empty(passwordRepeat)) {
        ePopup("Empty fields", "Please fill out both password fields");
        return false;
    }
    let params = {
        request: "reset_pwd_new_password",
        data: {
            password,
            password_repeat: passwordRepeat,
            token
        }
    };

    let result = ensureObject(await requestServer(params));

    if(typeof result !== "object" || !("status" in result)) {
        ePopup("Something went wrong", "Please try again later");
        return false;
    }

    if(result.status === "error") {
        ePopup("Something went wrong", result.message);
        return false;
    }

    ePopup("Password reset successful", result.message, 0, "success", "approve");
    window.setTimeout(function () {window.location = serverHost + "?page=login";}, 1500);
}


async function baseRequest(parent,request) {
    let result = ensureObject(await requestServer(request));

    if(result === true) {
        if(parent !== null) eNotice("","HIDE");
        return true;
    }

    if(typeof result === "object" && !empty(result) && "error" in result) {
        if(parent !== null) eNotice(result.error,parent);
        return false;
    }

    if(parent !== null) eNotice("","HIDE");
    return true;
}






async function switchView(btn) {
    let switchParent = btn.parents("[data-switchParent]").first(),
        switchId = switchParent.attr("data-switch-id"),
        switchObjects = switchParent.find(".switchViewObject[data-switch-id=" + switchId + "]"),
        currentTitleElement = switchParent.find("#switchCurrentTitle").first(),
        switchTarget = btn.data("toggle-switch-object"),
        activeBtnClass = switchParent.attr("data-active-btn-class"), inactiveBtnClass = switchParent.attr("data-inactive-btn-class");

    if(switchParent.length === 0 || switchObjects.length === 0) return false;
    let targetObject = switchParent.find(".switchViewObject[data-switch-object-name="+switchTarget+"]"),
        currentVisibleObject = switchParent.find(".switchViewObject[data-is-shown=true][data-switch-id=" + switchId + "]");

    if(targetObject.length === 0) return false;
    if(targetObject.attr("data-is-shown") === "true") return false;

    let newTitle = targetObject.data("switch-object-title");
    let btnActiveClass = !empty(activeBtnClass) ? activeBtnClass : "btn-secondary", btnInactiveClass = !empty(inactiveBtnClass) ? inactiveBtnClass : "btn-outline-secondary";

    if(currentTitleElement.length > 0) currentTitleElement.text(newTitle);
    currentVisibleObject.attr("data-is-shown", "false");

    // currentVisibleObject.fadeOut(function (){
    //     targetObject.fadeIn({duration: 350});
    // });
    currentVisibleObject.hide();
    targetObject.show();
    targetObject.attr("data-is-shown", "true");

    if((!empty(activeBtnClass) && !empty(inactiveBtnClass))) {
        switchParent.find(".switchViewBtn[data-switch-id=" + switchId + "]").each(function (){
            $(this).removeClass(btnActiveClass).addClass(btnInactiveClass);
        });
        btn.removeClass(btnInactiveClass).addClass(btnActiveClass);
    }

}





async function setDateRangePicker() {


    if($(document).find(".DP_RANGE").length) {
        $(document).find(".DP_RANGE").each(async function (){
            let el = $(this), startDate = 0, endDate = 0, ranges = {};
            if(el.data("no-pick") === true) return; //Used for displaying period-lists mainly

            startDate = moment().startOf('week').add(1, 'weeks').add(1, "days");
            endDate = moment().endOf('week').add(1, "weeks").add(1, "days");
            ranges = {
                'Today': [moment().startOf('day'), moment().endOf('day')],
                'Tomorrow': [moment().startOf('day').add(1, 'days'), moment().endOf('day').add(1, 'days')],
                'Next week': [startDate, endDate]
            };





            el.daterangepicker({
                opens: 'left',
                timePicker: true,
                timePicker24Hour: true,
                startDate: new Date(startDate),
                endDate: new Date(endDate),
                locale: {
                    format: 'MMMM DD YYYY, HH:mm'
                },
                ranges
            }, function(start, end) {

            });

        });
    }
}

async function hasUserSession() {
    if(!userSession) return false;

    var checkUserActiveSession = setInterval(async function (){
        await requestServer({request: "hasSession"})
            // .then(res => res.json())
            .then(res => {
                res = ensureObject(res);
                let refresh = false;
                if(typeof res !== "object" || empty(res)) {
                    console.log("Failed to check session");
                    refresh = true;
                }
                else if(!("session" in res) || !res.session) {
                    console.log("Session expired");
                    refresh = true;
                }

                if(refresh) window.location = serverHost + "?logout";
            })
            .catch(res => {
                console.log(res);
                clearInterval(checkUserActiveSession);
                setTimeout(function (){ window.location = serverHost; }, (2 * 1000))
            })
    }, (1000 * 60));
}



async function trackEvent(action, name) {
    await requestServer({request: "userLogging", action_type: action, action_name: name});
}



async function bulkAction(selectElement) {
    let targetObjectsSelector = selectElement.data("target-bulk-items"), fetchParams = selectElement.data("bulk-info-fields"),
        bulkParent = selectElement.parents(".dataParentContainer").first(), action = selectElement.val().trim();

    if(targetObjectsSelector === undefined || fetchParams === undefined || bulkParent === undefined) return false;
    if(empty(action)) return false;

    let list = [], targetObjects = bulkParent.find("input[type=checkbox]" + targetObjectsSelector);
    if(targetObjects.length === 0) return false;

    if(empty(fetchParams)) return false;
    fetchParams = fetchParams.split(",");

    targetObjects.each(function () {
        let checkbox = $(this), values = {};
        if(!(checkbox.is(":checked"))) return;

        for(let param of fetchParams) {
            let paramValue = checkbox.data("bulk-" + param);
            if(paramValue === undefined) return;

            values[param] = paramValue;
        }

        list.push(values);
    });

    await window[action](list)
        .then(() => { selectElement.val("") })
        .catch(() => { selectElement.val("") })
}

async function exampleSweetAlertRequestByBulk(items){
    if(empty(items)) return false;
    let ids = items.map(function (item){ return item.id; });

    swalConfirmCancel({
        request: "some_requets",
        fields: {ids: ids},
        refreshTimeout: 1000,
        visualText: {
            preFireText: {
                title: "Resolve "+pluralS(ids.length,"violation")+"?",
                text: "Resolving the violations means they will no longer count as violations. Ids: " + ids.join(", "),
                icon: "warning",
                confirmBtnText: "Resolve"
            },
            successText: {
                title: pluralS(ids.length,"violation")+" resolved",
                text: "Successfully deleted "+pluralS(ids.length,"violation")+" with "+pluralS(ids.length,"id")+": "+ ids.join(", "),
                icon: "success",
                html: ""
            },
            errorText: {
                title: "Failed",
                text: "Failed to resolve "+pluralS(ids.length,"violation")+". Try again. Error message: <_ERROR_MSG_>",
                icon: "error",
                html: ""
            }
        }
    });
}











function selectElements(el, canUnselect = true, exclusive = false) {
    if(canUnselect && !exclusive) {
        if(el.hasClass("selected")) el.removeClass("selected");
        else el.addClass("selected");
        return;
    }

    if(!canUnselect) if(el.hasClass("selected")) return;
    let parentSelector = ".dataParentContainer", elSelector = ".selectable-el";

    if(el.hasClass("selected")) {
        el.removeClass("selected");
        return;
    }

    let parent = el.parents(parentSelector).first(), elements = parent.find(elSelector);
    if(!elements.length) return;

    elements.each(function (){ if($(this).hasClass("selected")) $(this).removeClass("selected"); })

    if(el.hasClass("selected")) el.removeClass("selected");
    else el.addClass("selected");
}





async function createUser(btn) {
    let parent = btn.parents('#user_signup_form').first();
    let fields = {
        password: "input[name=password]",
        password_repeat: "input[name=password_repeat]",
        full_name: "input[name=full_name]",
        email: "input[name=email]",
        access_level: "select[name=access_level]"
    };


    for(let fieldName in fields) {
        let el = parent.find(fields[fieldName]).first();

        if(empty(el.val())) {
            ePopup(prepareProperNameString(fieldName) + " error", prepareProperNameString(fieldName) + " must not be empty");
            return false;
        }

        fields[fieldName] = el.val().trim();
    }

    let result = ensureObject(await requestServer({request: "create_user", fields}));
    console.log(result);

    if(typeof result !== "object" || (!("error" in result) && !("message" in result))) {
        ePopup("Server error","Something went wrong");
        return false;
    }
    if("error" in result) {
        ePopup("Account creation error", result.error);
        return false;
    }

    ePopup("Success", result.message, 0, "success", "approve")
    window.setTimeout(function (){
        window.location = serverHost;
    }, 2000)
}





async function loginUser(btn) {
    let parent = btn.parents("#user_login_form").first();
    let fields = {
        email: "input[name=email]",
        password: "input[name=password]",
    };


    for(let fieldName in fields) {
        let el = parent.find(fields[fieldName]).first();

        if(empty(el.val())) {
            ePopup(prepareProperNameString(fieldName) + " error", prepareProperNameString(fieldName) + " must not be empty");
            return false;
        }

        fields[fieldName] = el.val().trim();
    }

    let result = ensureObject(await requestServer({request: "login_user", fields}));
    console.log(result)

    if(typeof result !== "object" || (!("error" in result) && (!("status" in result) || result.status !== "success"))) {
        ePopup("Server error","Something went wrong");
        return false;
    }

    if("error" in result) {
        ePopup("Login error", result.error);
        return false;
    }


    ePopup("Success", "Successfully signed in", 0, "success", "approve");
    window.setTimeout(function (){ window.location = serverHost; }, 1000);
}






function getTimepickerTime(timeRangeElement) {
    return {
        start: Math.round(((new Date((timeRangeElement.data('daterangepicker').startDate))).valueOf()) / 1000),
        end: Math.round(((new Date((timeRangeElement.data('daterangepicker').endDate))).valueOf()) / 1000)
    };
}





function initChartDraw(element) {
    let id = element.attr("id");
    switch(id) {
        default: return;
        case "cities": return citiesChart(element);
        case "gender_count": return genderCountChart(element);
        case "age_range": return ageRangeChart(element);
        case "countries":
            google.charts.load("current", {packages:["geochart"]});
            google.charts.setOnLoadCallback(function () { countriesChart(element) });
    }
}
function countriesChart(element) {
    let data = ("creatorAnalytics" in window) && ("countries" in window.creatorAnalytics) ? window.creatorAnalytics.countries : [];
    if(empty(data)) return;

    // data.height = "500px";
    // data.title = "Top 8 Cities";
    setGoogleGeoChart(element, data);
}
function citiesChart(element) {
    let data = ("creatorAnalytics" in window) && ("cities" in window.creatorAnalytics) ? window.creatorAnalytics.cities : [];
    if(empty(data)) return;

    data.height = "500px";
    data.title = "Top 8 Cities";
    renderCharts(element.get(0), data, element.data("chart-type"));
}
function genderCountChart(element) {
    let data = ("creatorAnalytics" in window) && ("gender_count" in window.creatorAnalytics) ? window.creatorAnalytics.gender_count : [];
    if(empty(data)) return;

    data.height = "500px";
    data.title = "Gender distribution";
    renderCharts(element.get(0), data, element.data("chart-type"));

}
function ageRangeChart(element) {
    let data = ("creatorAnalytics" in window) && ("gender_age_range" in window.creatorAnalytics) ? window.creatorAnalytics.gender_age_range : [];
    if(empty(data)) return;


    let labels = data[ (Object.keys(data)[0]) ].labels;
    let series = [];
    for(let key in data) {
        let item = data[key];
        series.push({
            name: prepareProperNameString(key),
            data: item.series
        });
    }

    renderCharts(
        element.get(0),
        {
            labels,
            series,
            height: "750px",
            orientation: "horizontal",
            title: "Age & Gender distribution"
        },
        element.data("chart-type")
    );

}





function setLineChartNoData(chartElement) {
    let data = {
        series: {
            name: "",
            data: []
        },
        labels: [],
        title: "No data available"
    };

    renderCharts(chartElement.get(0), data, "line");
}
async function multiChart(element, data, title) {
    let chartType = element.attr("data-chart-type");
    if(chartType === undefined) {
        setLineChartNoData(element);
        return false;
    }
    renderCharts(element.get(0), {...data, title}, chartType);
}





var newCreatorData = null;
var currentCreatorUsernameSearch = null;
async function newCreatorLoadUsername() {
    let field = $("[name=new_creator_username]").first(), previewArea = $("#profile_preview").first(),
        picturePreview = $("#profile_image_preview").first(), previewUsername = $("#username_preview").first();
    if(!field.length || !previewArea.length || !picturePreview.length || !previewUsername.length) return;

    ePopup("Loading creator...", "Hold on a moment", 0, "warning")

    let username = field.val();
    if(username !== currentCreatorUsernameSearch) {
        currentCreatorUsernameSearch = username;
        newCreatorData = null;
    }
    if(!(username === currentCreatorUsernameSearch && !empty(newCreatorData))) {
        newCreatorData = ensureObject(await requestServer( {
            request: "load_new_creator",
            username: field.val()
        } ));
    }


    console.log(newCreatorData)

    if("error" in newCreatorData) {
        ePopupTimeout("Failed", newCreatorData.error.message, "error", "error_triangle", 5000 )
        return;
    }


    if(!("data" in newCreatorData)) {
        ePopupTimeout("Failed", "Malformed response", "error", "error_triangle", 5000 )
        return;
    }

    let data = newCreatorData.data;
    console.log(data);

    if(("bulk" in data) && data.bulk) {
        let title = empty(data.errors) ? "All done!" : "Partially completed";
        let message = empty(data.errors) ?  [data.message] : [data.message, "The remaining usernames have been copied to clip-holder"];
        ePopupTimeout(title, message.join(".   "), "success", "approve", empty(data.errors) ? 2000 : 5000);

        if(!empty(data.errors)) {
            let html = '<div class="copyContainer hidden">';
                html += '<p class="mt-2 copyElement copyBtn" id="usernamesRemaining">';
                    html += (data.errors).join(",");
                html += '</div>';
            html += '</div>';

            previewArea.append(html);
            previewArea.find("#usernamesRemaining").first().trigger("click");
        }
    }
    else {
        picturePreview.attr("src", resolveAssetPath(data.profile_picture));
        previewUsername.text(data.full_name);
        previewArea.slideDown();
        ePopup("", "", 1);
    }
    field.val("");

}
async function storeLoadedCreator() {
    if(empty(newCreatorData) && ("data" in newCreatorData) && !empty(newCreatorData.data)) {
        ePopup("No creator data", "No creator data found. Try researching the creator");
        return;
    }

    let field = $("[name=new_creator_username]").first(), previewArea = $("#profile_preview").first(),
        picturePreview = $("#profile_image_preview").first(), previewUsername = $("#username_preview").first();
    if(!field.length || !previewArea.length || !picturePreview.length || !previewUsername.length) return;

    ePopup("storing creator...", "Hold on a moment", 0, "warning")

    console.log(newCreatorData)

    let result = ensureObject(await requestServer( {
        request: "store_new_creator",
        data: newCreatorData.data
    } ));

    if("error" in result) {
        ePopupTimeout("Failed", result.error.message)
        return;
    }


    ePopupTimeout("Done!", "Successfully stored the new creator", "success", "approve")
    picturePreview.attr("src", "");
    previewUsername.text("");
    field.val("");
    previewArea.slideUp();
    window.setTimeout(function (){ window.location = window.location.href; })
}
async function toggleCreator(btn) {
    let id = btn.attr("data-toggle-creator");
    if(typeof id === "undefined") return;

    await requestServer({
        request: "toggle_creator",
        creator_id: id
    })
    .then(() => { window.location = window.location.href; })
}


async function createCampaign(btn) {
    let parent = btn.parents("#campaign_creation_container").first(), nameField = parent.find("input[name=campaign_name]").first(),
        dateField = parent.find("input[name=campaign_dates]").first(),ppcField = parent.find("input[name=ppc]").first(),
        contentTypeField = parent.find("select[name=post_types]").first(), creatorsField = parent.find("select[name=campaign_creators]").first(),
        assignedToField = parent.find("select[name=campaign_owner]").first(), trackingField = parent.find("select[name=tracking]").first(),
        trackingTagField = parent.find("input[name=tracking_hashtag]").first();
        if(!nameField.length || !dateField.length || !ppcField.length || !contentTypeField.length || !creatorsField.length ||
            !trackingField.length || !trackingTagField.length) return;

    ePopup("Creating campaign...", "Hold on a moment", 0, "warning")



    let result = ensureObject(await requestServer( {
        request: "create_campaign",
        data: {
            name: nameField.val(),
            date_range: getTimepickerTime(dateField),
            ppc: ppcField.val(),
            content_type: contentTypeField.val(),
            creators: creatorsField.val(),
            tracking: trackingField.val(),
            tracking_hashtag: trackingTagField.val(),
            owner: empty(assignedToField.val()) ? null : assignedToField.val()
        }
    } ));

    if("error" in result) {
        ePopupTimeout("Failed to create campaign", result.error.message, "error", "error_triangle", 5000)
        return;
    }


    ePopupTimeout("Done!", "Successfully created campaign: " + nameField.val(), "success", "approve")
    window.setTimeout(function (){ window.location = window.location.href; }, 2000)
}
function toggleCampaignCreationContainer() {
    let container = $(document).find("#campaign_creation_container").first();
    if(!container.length) return;

    if(container.hasClass("container-open")) {
        container.removeClass("container-open");
        container.slideUp("slow");
    }
    else {
        container.addClass("container-open");
        container.slideDown("slow");
    }
}
async function campaignUpdate(btn) {
    let parent = $(document).find("#campaign_edit_container").first(), nameField = parent.find("input[name=campaign_name_edit]").first(),
        dateField = parent.find("input[name=campaign_dates_edit]").first(),ppcField = parent.find("input[name=ppc_edit]").first(),
        contentTypeField = parent.find("select[name=post_types_edit]").first(), creatorsField = parent.find("select[name=campaign_creators_edit]").first(),
        creatorsBulkField = parent.find("input[name=creators_bulk_edit]").first(), assignedToField = parent.find("select[name=campaign_owner_edit]").first(),
        trackingField = parent.find("select[name=tracking_edit]").first(), trackingTagField = parent.find("input[name=tracking_hashtag_edit]").first();
    let campaignId = findGetParameter("campaign")

    if(
        !nameField.length || !dateField.length || !ppcField.length || !trackingField.length ||
        !contentTypeField.length || !creatorsField.length || !creatorsBulkField.length|| !trackingTagField.length ||
        empty(campaignId)
    ) return;


    if(btn.attr("name") !== "update_campaign_btn") {
        if(parent.hasClass("container-open")) {
            parent.removeClass("container-open");
            parent.slideUp("slow");
        }
        else {
            let result = ensureObject(await requestServer({
                request: "campaign_details",
                campaign_id: findGetParameter("campaign")
            }))

            console.log(result);

            if(empty(result)) {
                ePopupTimeout("Campaign not found", "Could not find the campaign details. Try again later")
                return;
            }


            nameField.val(result.name);
            ppcField.val(result.ppc);
            contentTypeField.val(result.content_type);
            dateField.data('daterangepicker').setStartDate(new Date((parseInt(result.start) * 1000)));
            dateField.data('daterangepicker').setEndDate(new Date((parseInt(result.end) * 1000)));



            parent.addClass("container-open");
            parent.slideDown("slow");
        }
    }

    else {

        ePopup("Updating campaign...", "Hold on a moment", 0, "warning")

        let result = ensureObject(await requestServer( {
            request: "create_campaign",
            data: {
                campaign_id: campaignId,
                name: nameField.val(),
                date_range: getTimepickerTime(dateField),
                ppc: ppcField.val(),
                content_type: contentTypeField.val(),
                creators: creatorsField.val(),
                tracking: trackingField.val(),
                tracking_hashtag: trackingTagField.val(),
                creators_bulk: creatorsBulkField.val(),
                owner: empty(assignedToField.val()) ? null : assignedToField.val()
            }
        } ));

        if("error" in result) {
            ePopupTimeout("Failed to update campaign", result.error.message, "error", "error_triangle", 5000)
            return;
        }


        ePopupTimeout("Done!", "Successfully updated campaign: " + nameField.val(), "success", "approve")
        window.setTimeout(function (){ window.location = window.location.href; }, 2000)
    }
}
async function removeCreatorFromCampaign(btn) {
    let campaignId = btn.attr("data-campaign-id"), creatorId = btn.attr("data-creator-id"),
        tableRow = btn.parents("tr").first(), tableBody = tableRow.parents("tbody").first();
    if(empty(campaignId) || empty(creatorId) || !tableRow.length) return;

    let result = ensureObject(await requestServer( {
        request: "campaign_remove_creator",
        data: {
            campaign_id: campaignId,
            creator_id: creatorId,
        }
    }));

    if("error" in result) {
        ePopupTimeout("Failed to remove creator", result.error.message, "error", "error_triangle", 4000)
        return;
    }


    ePopupTimeout("Creator removed", "Campaign stats will update once the page is refreshed", "success", "approve", 4000)
    tableRow.remove();

    let select2Element = $("select[name=campaign_creators_edit]").first();
    select2MultiUnselectItem(select2Element, creatorId);

    let i = 0;
    let tableRows = tableBody.find("tr");
    if(tableRows.length) {
        tableRows.each(function () {
            i++;
            if(i % 2 === 0) $(this).addClass(("filter-row-fields"))
            else $(this).removeClass("filter-row-fields");
        })
    }

    window.setTimeout(function (){ window.location = window.location.href; })
}
async function exportCampaignToCsv() {
    let campaignId = findGetParameter("campaign");
    if(empty(campaignId)) return;

    let result = ensureObject(await requestServer({request: "export_campaign_csv", campaign_id: campaignId}));

    if(result.status === "error") {
        ePopupTimeout("Failed to create export: ", result.error.message);
        return false;
    }
    ePopupTimeout("Default toggled", result.message, "success", "approve", 2500)
    window.open(result.data.link);
    return true;
}



var liveMentionTable = null;
async function mentionLiveTableTracking(table) {
    let offset = table.attr("data-row-offset"), pageLength = 100, blink = true;
    if(typeof offset === "undefined") {
        offset = 0;
        blink = false;
    }
    else offset = parseInt(offset);

    let result = ensureObject(await requestServer({
        request: "mention_live_tracking",
        offset,
        page_size: pageLength
    }));

    if(empty(result)) return;

    result = sortByKey(result, "id");
    let newOffset = result[0].id;
    if(!blink) liveMentionTable = table.DataTable({order: [0, "desc"]});


    for(let item of result) {
        let rowNode = liveMentionTable
            .row.add( [
                item.id,
                !('creator_link' in item) ? item.username : `<a href="${item.creator_link}">${item.username}</a>`,
                !('permalink' in item) || empty(item.permalink) ? prepareProperNameString(item.type) :
                    `<a href="${item.permalink}" target="_blank">${prepareProperNameString(item.type)}<i class="mdi mdi-open-in-new ml-1"></i></a>`,
                item.campaign_id === 0 ? "No" : '<a href="' + item.campaign_link + '">Yes</a>',
                item.display_date
            ] )
            .draw()
            .node();


        if(blink) {
            $(rowNode).addClass("blink-bg")
            setTimeout(function () {
                $(rowNode).removeClass("blink-bg")
            }, 4000);
        }
    }

    table.attr("data-row-offset", newOffset);
}





async function removeIntegration(el) {
    let rowId = el.attr("data-id"), parentRow = el.parents("tr").first();
    let result = ensureObject(await requestServer({request: "remove_integration", id: rowId}));

    if(result.status === "error") {
        ePopupTimeout("Failed to remove integration", result.error.message);
        return false;
    }
    ePopupTimeout("Integration removed", result.message, "success", "approve")
    parentRow.slideUp("fast", function() { $(this).remove(); } );
    return true;
}
async function toggleIntegrationDefault(el) {
    let rowId = el.attr("data-id");
    let result = ensureObject(await requestServer({request: "toggle_integration_default", id: rowId}));

    if(result.status === "error") {
        ePopupTimeout("Failed...", result.error.message);
        return false;
    }
    ePopup("Enabled", result.message, 0, "success", "approve")
    window.setTimeout(function () { window.location = window.location.href; }, 2000)
    return true;
}





const connectFB = async () => {
    if(!findGetParameter("code") || findGetParameter("state") !== "some-state-192484") return false;
    ePopup("Connecting accounts...", "Retrieving accounts, this may take a moment", 0, "warning", "approve" )
    let result = ensureObject(await requestServer({
        request: "setNewUserIntegration", code: findGetParameter("code")
    }))



    if(!(typeof result === "object" && "status" in result && result.status === "success" && ('data' in result) && !empty(result.data))) {
        let errorMsg = "Something sadly went wrong";
        if("message" in result) errorMsg = result.message;
        ePopupTimeout("Error",errorMsg)
        return false;
    }

    ePopup("","",1)

    let finalObj = {}
    for(let i in result.data) {
        let item = result.data[i];
        if(!Object.keys(finalObj).includes(item.provider)) finalObj[item.provider] = "";
        finalObj[item.provider] += `<option value="${item.item_id}">${item.item_name} (Id: ${item.item_id})</option>`;
    }

    let modal = new ModalHandler('integrationSelection')
    modal.construct(finalObj)
    await modal.build()
    modal.bindEvents({
        integrate: async (btn, modalHandler) => {
            const end = () => {
                btn.removeAttr('disabled')
            }
            const start = () => {
                btn.attr('disabled', 'disabled')
            }
            start();

            let modalBody = null;
            btn.parents().each(function () {
                if($(this).find(".modal-body").length) {
                    modalBody = $(this).find(".modal-body").first();
                    return true;
                }
            })
            if (empty(modalBody)) return;

            let facebookElement = modalBody.find("select[name=facebook]"), instagramElement = modalBody.find("select[name=instagram]"),
                errorBox = modalBody.find(".modalErrorBox").first();
            if(empty(facebookElement, instagramElement, errorBox)) return;

            const setError = (txt) => {
                errorBox.text(txt)
                errorBox.removeClass('d-none')
                end()
            }
            const clearError = () => {
                errorBox.text('')
                errorBox.addClass('d-none')
            }
            start();

            let fbValue = facebookElement.val(), igValue = instagramElement.val(),selectedData = {};
            if(!empty(fbValue)) selectedData.facebook = fbValue;
            if(!empty(igValue)) selectedData.instagram = igValue;


            if(empty(selectedData)) {
                setError('You should select at least one integration to use')
                return false;
            }
            clearError();

            let response = ensureObject(await requestServer({
                request: 'store_integrations',
                data: {
                    raw: result,
                    selection: selectedData
                }
            }))

            if(typeof response !== "object") {
                setError("Something went wrong. Try again later.")
                return false;
            }
            if(response.status === "error") {
                setError(response.error.message)
                return false;
            }

            ePopupTimeout("Completed", response.message, "success", "approve", 1500)
            window.setTimeout(function (){window.location = serverHost + "?page=integrations"}, 1500)
            modalHandler.dispose()
        }
    })
    modal.open()





}





