

$(document).on("keyup","#username_search_field, .withSmallSuggestions",function () {
    let type = $(this).hasClass("withSmallSuggestions") ? "small" : "large";
    getSearchResults($(this), "small");
});
$(document).on("click",".search_suggestion",function () {
    searchUserBySuggestion($(this));
});

function searchUserBySuggestion(suggestion) {
    if(suggestion.attr("data-username") === undefined)
        return false;

    $(document).find("#username_search_field").val(suggestion.attr("data-username"));
    $(document).find("form.search-form").submit();
}


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


async function getSearchResults(field,type) {
    let parent = field.parents(".searchParent").first(), searchValue = field.val(), maxSuggestions = 10, container = parent.find(".suggestion_container"),
        employeeSearch = true, request;

    request = {
        request: "userSearchSuggestions",
        username: searchValue
    }


    if(empty(searchValue)) {
        container.addClass("no-vis");
        container.html("");
        return true;
    }

    container.removeClass("no-vis");
    if(type === "small") container.html(smallSearchSuggestions("Searching...", employeeSearch));
    else container.html(createSearchSuggestionObjects("Searching..."));

    let result = await requestServer(request);
    result = ensureObject(result);


    if(typeof result !== "object" || empty(result) || "error" in result) {
        window.setTimeout(function () {
            if(type === "small") container.html(smallSearchSuggestions(null,employeeSearch));
            else container.html(createSearchSuggestionObjects(null));
        },500);
        return true;
    }

    if(Object.keys(result).length > maxSuggestions) result.splice(maxSuggestions,(Object.keys(result).length));
    if(type === "small") container.html(smallSearchSuggestions(result,employeeSearch));
    else container.html(createSearchSuggestionObjects(result));

    return true;
}


function smallSearchSuggestions(data, employeeSearch = true) {
    let html = '', nameKey;

    if(data !== null && typeof data === "string") {
        html += '<div class="smallSuggestionsBox flex-row-start flex-align-center">';
            html += '<p class="">';
                html += data;
            html += '</p>';
        html += '</div>';
        return  html;
    }
    if(data === null) {
        html += '<div class="smallSuggestionsBox flex-row-start flex-align-center">';
            html += '<p class="">';
                html += 'No employees found';
            html += '</p>';
        html += '</div>';
        return  html;
    }

    if(typeof data !== "object" || empty(data)) return "";

    for(let user of data) {
        nameKey = Object.keys(data[0]).includes("nickname") ? "nickname" : "username";
        if("uid" in user && nameKey in user && "picture" in user) {

            if(employeeSearch)
                html += '<a href="'+serverHost+'?employee_id='+user.uid+'" class="smallSuggestionsBox flex-row-start flex-align-center" ' +
                    'data-id="'+user.id+'" data-name="'+user[nameKey]+'">';
            else
                html += '<div class="smallSuggestionsBox flex-row-start flex-align-center selectEmployeeFromSearch" data-id="'+user.uid+'" data-name="'+user[nameKey]+'" ' +
                    'data-user-email="'+user.email+'" data-uid="'+user.uid+'" >';

                html += '<div class="flex-row-start flex-align-center" style="height: inherit;">';

                    html += '<div class="square-30 border-radius-50">';
                    html += '<img src="'+serverHost + user.picture+'" class="w-100 h-100 border-radius-50"/>';
                    html += '</div>';
                    html += '<p class="">';
                    html += ucFirst(user[nameKey]);

                    if(Object.keys(user).includes("email") && !empty(user.email)) {
                        html += '  -  ' + user.email;
                    }

                    html += '</p>';
                html += '</div>';

            if(employeeSearch) html += '</a>';
            else html += '</div>';

        }
    }
    return html;
}



function createSearchSuggestionObjects(data) {
    let html = '', nameKey;

    if(data !== null && typeof data === "string") {
        html += '<div class="search_suggestion">';
            html += '<div class="flexColCenter">';
                html += '<p class="user_suggestion_user_text">';
                    html += data;
                html += '</p>';
            html += '</div>';
        html += '</div>';
        return  html;
    }
    if(data === null) {
        html += '<div class="search_suggestion">';
            html += '<div class="flexColCenter">';
                html += '<p class="user_suggestion_user_text">';
                    html += 'No employees found';
                html += '</p>';
            html += '</div>';
        html += '</div>';
        return  html;
    }

    if(typeof data !== "object" || empty(data)) return "";

    for(let user of data) {
        nameKey = Object.keys(data[0]).includes("nickname") ? "nickname" : "username";
        if("picture" in user && nameKey in user) {
            html += '<div class="search_suggestion" data-id="'+user.id+'">';
                html += '<div class="user_suggestion_img">';
                    html += '<img src="'+serverHost + user.picture+'" />';
                html += '</div>';
                html += '<div class="flexColCenter">';
                    html += '<p class="user_suggestion_user_text">';
                        html += user[nameKey];
                    html += '</p>';
                html += '</div>';
            html += '</div>';

        }
    }
    return html;
}

async function toggleIntegrationAnalytics(btn) {
    let id = btn.attr("data-id");
    await requestServer({request: "toggleIntegrationAnalytics", id})
    window.location = window.location.href;
}



async function accessPointList(){
    let parent = $("#dataContainer");
    console.log("i");

    eNotice("Loading access points..",parent, 0,"warning");
    let accessPoints = await requestServer({request: "getAccessPointList"});
    accessPoints = ensureObject(accessPoints);

    if(typeof accessPoints !== "object" || empty(accessPoints)) {
        eNotice("Could not find any access points",parent);
        return true;
    }

    let htmlObj = $("<tbody></tbody>");
    let dataTable = $(document).find("#accessPointTable");


    for(let accessPoint of accessPoints) {
        let dataVal = JSON.stringify({id: accessPoint.id, name: accessPoint.name, type: accessPoint.type});
        htmlObj.append(
            "<tr data-access-point-id='" + accessPoint.id + "'>" +
                "<td>" + prepareProperNameString(accessPoint.type) + "</td>" +
                "<td>" + prepareProperNameString(accessPoint.nickname) + "</td>" +
                "<td>" + (((accessPoint.action_level).toString()) === "1" ? "Read only": "Modify") + "</td>" +
                "<td>" +
                    "<div class='flex-row-start flex-align-center'>" +
                        "<input type='text' name='edit_access_point' class='form-control' placeholder='Open access' data-changeAction='access_point' " +
                            "value='"+accessPoint.access_levels+"' data-value='"+dataVal+"'>" +
                    "</div>" +
                "</td>" +
                "<td>" + accessPoint.description + "</td>" +
            "</tr>"
        );
    }

    dataTable.find("tbody").first().replaceWith(htmlObj);
    setDataTable(dataTable,[ 0, "desc" ], false,[],100,
        [
            {
                render: function (data, type, full, meta) {
                    return "<div class='text-wrap mxw-200px'>" + data + "</div>";
                },
                targets: 4
            }
        ]
    );
    parent.find(".card").first().removeClass("hidden");
    eNotice("",parent,1);

    htmlObj.on("change","input[name=edit_access_point]", async function (){
        // eNotice("Updating access point",parent, 0,"warning");
        let accessLevels = $(this).val().trim();
        if(!empty(accessLevels))
            accessLevels = ((accessLevels.split(",")).filter(function (level){
                if(isNormalInteger(level)) level = parseInt(level);
                return !(empty(level) || !(typeof level === "number") || (level < 1 || level > 9));
            }).map(function (level){ return level.trim(); })).join(",");

        let identifier = JSON.parse($(this).attr("data-value").trim());
        await baseRequest(parent,{
            request: "updateAccessPoint",
            access_levels: accessLevels,
            identifier
        });
    });

    accessPointUserRoleList();
}



async function accessPointUserRoleList(){
    let parent = $("#dataContainer");

    eNotice("Loading user roles..",parent, 0,"warning");
    let userRoles = await requestServer({request: "user_roles"});
    userRoles = ensureObject(userRoles);

    if(typeof userRoles !== "object" || empty(userRoles)) {
        eNotice("Could not find any user roles",parent);
        return true;
    }

    let htmlObj = $("<tbody></tbody>");
    let dataTable = $(document).find("#userRoleTable");


    for(let userRole of userRoles) {
        htmlObj.append(
            "<tr>" +
                "<td>" + userRole.access_level + "</td>" +
                "<td>" + prepareProperNameString(userRole.name) + "</td>" +
                "<td>" + ((userRole.defined.toString()) === "1" ? "Yes" : "No") + "</td>" +
                "<td>" + ucFirst(userRole.description) + "</td>" +
            "</tr>"
        );
    }



    dataTable.find("tbody").first().replaceWith(htmlObj);
    setDataTable(dataTable,[ 0, "asc" ], false,[],100);
    parent.find(".card").each(function () { $(this).removeClass("hidden"); });
    eNotice("",parent,1);
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



async function getErrors(dataTable, currentTableData = [], additionalTableData = []){
    let parent = $("#dataContainer"), filters = {request: "getErrors"}, errors, partialLoad = false;

    if(empty(currentTableData) || empty(additionalTableData)) {
        eNotice("Loading errors..",parent, 0,"warning");

        if(dataTable.data("use-pagination") === true) errors = await loadByPagination(dataTable, filters);
        else errors = ensureObject(await requestServer(filters));

        if(typeof errors !== "object" || empty(errors)) {
            eNotice("No errors could be found",parent);
            return true;
        }
    }
    else {
        errors = additionalTableData;
        partialLoad = true;
    }

    let htmlObj = $("<tbody></tbody>");
    if(partialLoad) {
        for(let row of currentTableData) {
            let html = "<tr>";
            for(let column of row) {
                html += "<td>" + column +"</td>"
            }
            html += "</tr>";
            htmlObj.append(html);
        }
    }

    for(let item of errors) {
        htmlObj.append(
            "<tr>" +
                "<td>" + item.id + "</td>" +
                "<td>" + convertDate(item.created_at,true, true) + "</td>" +
                "<td>" + item.error_code + "</td>" +
                "<td>" + item.error_message + "</td>" +
            "</tr>"
        );
    }

    if(!partialLoad) dataTable.attr("data-starting-id", errors[0].id);
    dataTable.find("tbody").first().replaceWith(htmlObj);
    setDataTable(dataTable,[ 0, "desc" ], false,[],50, [
        {
            render: function (data, type, full, meta) {
                return "<div class='text-wrap mxw-200px'>" + data + "</div>";
            },
            targets: "_all"
        }
    ]);

    parent.find(".card").first().removeClass("hidden");
    eNotice("",parent,1);
}


async function logContents(){
    let parent = $(".page-content").first(), filters = {request: "user_log", type: "connections"};

    eNotice("Loading logs..",parent, 0,"warning");
    let employeeLogs = await requestServer(filters);
    employeeLogs = ensureObject(employeeLogs);

    if(typeof employeeLogs !== "object" || empty(employeeLogs)) {
        eNotice("No employee-logs were found",parent);
        return true;
    }
    let logObjects = [];

    let htmlObj = $("<tbody></tbody>");
    let dataTable = $(document).find("#TableDataContent"), rowCount = 0;

    for(let employee of employeeLogs) {
        if(!("content" in employee && !empty(employee.content))) continue;

        logObjects = logObjects.concat((employee.content.map(function (item){
            return {name: employee.name, date: item.date, timestamp: item.timestamp};
        })));
    }

    if(Object.keys(logObjects).length === 0) {
        eNotice("No employee-logs were found",parent);
        return true;
    }

    logObjects = sortByKey(logObjects,"timestamp");

    for(let i in logObjects) {
        let logObj = logObjects[i];
        htmlObj.append(
            "<tr>" +
                "<td>" + ((parseInt(i))+1) + "</td>" +
                "<td>" + logObj.name + "</td>" +
                "<td>" + logObj.date + "</td>" +
            "</tr>"
        );
    }


    dataTable.find("tbody").first().replaceWith(htmlObj);
    setDataTable(dataTable,[ 0, "asc" ], false,[],50);
    parent.find(".card").first().removeClass("hidden");
    eNotice("",parent,1);
}


async function accountsAndBaseContent(name = true) {
    return await requestServer({request: "getAccountsAndBaseContent", name});
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


async function activityList(dataTable, currentTableData = [], additionalTableData = []){
    let parent = dataTable.parents(".page-content").first(), request = {request: "activityDataList"}, activity, partialLoad = false;
    if(empty(currentTableData) || empty(additionalTableData)) {
        eNotice("Loading activities..",parent, 0,"warning");

        if(dataTable.data("use-pagination") === true) activity = await loadByPagination(dataTable, request);
        else activity = ensureObject(await requestServer(request));

        if(typeof activity !== "object" || empty(activity)) {
            eNotice("",parent, 1);
            return true;
        }
    }
    else {
        activity = additionalTableData;
        partialLoad = true;
    }

    let htmlObj = $("<tbody></tbody>");
    if(partialLoad) {
        for(let row of currentTableData) {
            let html = "<tr>";
            for(let column of row) {
                html += "<td>" + column +"</td>"
            }
            html += "</tr>";
            htmlObj.append(html);
        }
    }


    for(let item of activity) {
        htmlObj.append(
            "<tr>" +
                "<td>" + item.id + "</td>" +
                "<td>" +
                    "<div class='flex-col-start>' " +
                        "<p class=''>"+(timeAgo(item.created_at, true))+"</p>" +
                        "<p class=''>"+convertDate(item.created_at,false, true)+"</p>" +
                    "</div>" +
                "</td>" +
                "<td>" + prepareProperNameString(item.action_type) + "</td>" +
                "<td>" + prepareProperNameString(item.action_name) + "</td>" +
                "<td>" +
                    "<a href='"+serverHost+"?employee_id="+item.employee_id+"' >" +
                        item.employee_name +
                    "</a>" +
                "</td>" +
            "</tr>"
        );
    }

    if(!partialLoad) dataTable.attr("data-starting-id", activity[0].id);
    dataTable.find("tbody").first().replaceWith(htmlObj);

    setDataTable(dataTable,[], false,[],100);
    eNotice("",parent,1);
}



async function trackEvent(action, name) {
    await requestServer({request: "userLogging", action_type: action, action_name: name});
}


async function loadByPaginationNext(btn) {
    let tableId = btn.data("target-table"), targetRequest = btn.data("target-request");
    if(!(targetRequest !== undefined && !empty(targetRequest) && tableId !== undefined && !empty(tableId))) return false;

    let dataTable = $(document).find("#" + tableId).first(), request = {request: targetRequest};
    if(dataTable.length === 0) return false;

    btn.unbind("click");
    btn.html('<div class="spinner-border smallSpinner" role="status"><span class="sr-only"></span></div>')
        .addClass("color-red").addClass("hover-color-white")

    let result = await loadByPagination(dataTable, request);
    if(typeof result !== "object" ||empty(result)) {
        btn.parent().remove();
        return false;
    }

    let currentTableData = [];
    if(!$.fn.dataTable.isDataTable(dataTable)) return false;

    let table = dataTable.DataTable();
    table.rows().every( function ( rowIdx, tableLoop, rowLoop ) {
        currentTableData.push(this.data());
    });


    if(tableId === "activityLogTable") await activityList(dataTable, currentTableData, result);
    else if(tableId === "violationsTable") await violationsList(dataTable, currentTableData, result);
    else if(tableId === "errorDataTable") await getErrors(dataTable, currentTableData, result);
    else if(tableId === "rougesTable") await rougesList(dataTable, currentTableData, result);


    btn.bind("click", function (){ loadByPaginationNext(btn) })
        .removeClass("color-red").removeClass("hover-color-white").html("").text("Load more");
    return true;
}

async function loadByPagination(dataTable, request) {
    let pageSize = dataTable.data("page-size"),
        offset = dataTable.attr("data-page-offset"),
        orderBy = dataTable.data("page-order"),
        startingId = dataTable.data("starting-id"),
        result;
    if(startingId === undefined ||empty(startingId)) startingId = offset;

    if(!(pageSize === undefined || empty(pageSize) || offset === undefined || empty(offset)
        || orderBy === undefined || empty(orderBy) || startingId === undefined || empty(startingId))) {

        if(isNormalInteger(pageSize)) pageSize = parseInt(pageSize);
        request = {
            ...request,
            ...{
                page_size: pageSize,
                offset,
                order: orderBy,
                column: "id",
                starting_id: startingId
            }
        }
        result = ensureObject(await requestServer(request));
        if(typeof offset !== "number") offset = parseInt(offset);

        let resultLength = (Object.keys(result).length);
        dataTable.attr("data-page-offset", (offset + resultLength));

        if(resultLength < pageSize) dataTable.parent().find(".dataNextPage").parent().remove();
    }
    else result = ensureObject(await requestServer(request));

    return result;
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



async function resetPwdToDef(btn){
    let employeeId = btn.attr("data-user-id");
    if(employeeId === undefined || empty(employeeId)) return false;

    swalConfirmCancel({
        request: "reset_pwd_to_default",
        fields: {employee_id: employeeId},
        refreshTimeout: 1000,
        visualText: {
            preFireText: {
                title: "Reset password to default?",
                text: "The old password cannot be recovered afterwards",
                icon: "warning",
                confirmBtnText: "Reset"
            },
            successText: {
                title: "Done",
                text: "Successfully reset the password back to default",
                icon: "success",
                html: ""
            },
            errorText: {
                title: "Failed",
                text: "Failed to reset password. Error message: <_ERROR_MSG_>",
                icon: "error",
                html: ""
            }
        }
    });
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



async function approveDelivery(){
    swalConfirmCancel({
        request: "approve_delivery",
        data: {oid: findGetParameter("oid")},
        refreshTimeout: 1000,
        visualText: {
            preFireText: {
                title: "Approve delivery?",
                text: "Be sure that you have checked all necessary files, post-urls etc. before accepting delivery",
                icon: "warning",
                confirmBtnText: "Yes, approve!"
            },
            successText: {
                title: "Delivery approved!",
                text: "The delivery has been approved thus successfully completed your order",
                icon: "success",
                html: ""
            },
            errorText: {
                title: "Failed to approve delivery!",
                text: "<_ERROR_MSG_>",
                icon: "error",
                html: ""
            }
        }
    });
}

async function disputeApproveOrder(){
    swalConfirmCancel({
        request: "approve_delivery",
        data: {oid: findGetParameter("oid")},
        refreshTimeout: 1000,
        visualText: {
            preFireText: {
                title: "Approve and complete order?",
                text: "Write a quick note of why you are approving this order",
                icon: "warning",
                confirmBtnText: "Yes, approve!",
                inputPlaceholder: "Admin note...",
                input: "text"
            },
            successText: {
                title: "Order approved!",
                text: "The order has been marked as completed",
                icon: "success",
                html: ""
            },
            errorText: {
                title: "Failed to approve and complete order!",
                text: "<_ERROR_MSG_>",
                icon: "error",
                html: ""
            }
        }
    });
}

async function reopenOrder(){
    swalConfirmCancel({
        request: "reopen_order",
        data: {oid: findGetParameter("oid")},
        refreshTimeout: 1000,
        visualText: {
            preFireText: {
                title: "Reopen this order?",
                text: "Write a quick note of why you are approving this order",
                icon: "warning",
                confirmBtnText: "Reopen!",
                inputPlaceholder: "Admin note...",
                input: "text"
            },
            successText: {
                title: "Success!",
                text: "The order has been reopened and all pending payment clearance has been cancelled",
                icon: "success",
                html: ""
            },
            errorText: {
                title: "Failed to reopened order!",
                text: "<_ERROR_MSG_>",
                icon: "error",
                html: ""
            }
        }
    });
}

async function requestRevision(){
    let oid = findGetParameter("oid");
    swalConfirmCancel({
        request: "request_revision",
        data: {oid},
        refreshTimeout: 1000,
        visualText: {
            preFireText: {
                title: "Request revision?",
                text: "While in revision, you'll be unable to complete the order",
                icon: "warning",
                confirmBtnText: "Yes, request revision"
            },
            successText: {
                title: "Success!",
                text: "Your order #" +oid + " is now in revision",
                icon: "success",
                html: ""
            },
            errorText: {
                title: "Failed to submit for revision!",
                text: "<_ERROR_MSG_>",
                icon: "error",
                html: ""
            }
        }
    });
}

async function submitForRevision(){
    let oid = findGetParameter("oid");
    swalConfirmCancel({
        request: "request_revision",
        data: {oid},
        refreshTimeout: 1000,
        visualText: {
            preFireText: {
                title: "Submit for revision?",
                text: "While in revision, the buyer will be unable to complete the order",
                icon: "warning",
                confirmBtnText: "Yes, submit"
            },
            successText: {
                title: "Success!",
                text: "Order #" +oid + " is now in revision",
                icon: "success",
                html: ""
            },
            errorText: {
                title: "Failed to submit for revision!",
                text: "<_ERROR_MSG_>",
                icon: "error",
                html: ""
            }
        }
    });
}

async function cancelOrder(){
    let oid = findGetParameter("oid");
    swalConfirmCancel({
        request: "cancel_order",
        data: {oid},
        refreshTimeout: 1000,
        visualText: {
            preFireText: {
                title: "Cancel order?",
                text: "Cancelling the order will nullify it and refund any amount spent",
                icon: "warning",
                confirmBtnText: "Yes, cancel and refund",
                inputPlaceholder: "Please note your reasoning.",
                input: "text"
            },
            successText: {
                title: "Order cancelled!",
                text: "Order #" +oid + " has been cancelled and payment refunded",
                icon: "success",
                html: ""
            },
            errorText: {
                title: "Failed to cancel the order!",
                text: "<_ERROR_MSG_>",
                icon: "error",
                html: ""
            }
        }
    });
}


async function requestPayout(){
    swalConfirmCancel({
        request: "request_payout",
        data: {},
        refreshTimeout: 1000,
        visualText: {
            preFireText: {
                title: "Request payout?",
                text: "Enter amount you'd like to pay out",
                icon: "warning",
                inputPlaceholder: "5 - 2500",
                input: "number",
                inputAttributes: {
                    min: 5,
                    max: 2500,
                    step: 0.01
                },
            },
            successText: {
                title: "Payout approved!",
                text: "You money is now on the way",
                icon: "success",
                html: ""
            },
            errorText: {
                title: "Failed to request payout!",
                text: "<_ERROR_MSG_>",
                icon: "error",
                html: ""
            }
        }
    });
}




//Purpose of these 2 is to avoid making http to fetch libraries on every "keyup"
var arbitraryLibray = null;
var arbitraryLibraryCurrentKey = null;

async function arbitraryTableSearch(searchInputField) {
    let requestType = searchInputField.data("request-type"), tableBodySelector = searchInputField.data("table-body-selector"),
        columnKeys = searchInputField.data("column-keys"), searchKey = searchInputField.data("search-key"),
        clickAction = searchInputField.data("click-action");

    if(requestType === undefined || tableBodySelector === undefined || columnKeys === undefined || searchKey === undefined) return;
    if(arbitraryLibraryCurrentKey !== requestType) {
        arbitraryLibraryCurrentKey = requestType;
        arbitraryLibray = null;
    }

    let query = searchInputField.val().trim(), library = empty(query) ? [] :
            (arbitraryLibray !== null ? arbitraryLibray : ensureObject(await requestServer({request: requestType})));
    if(!empty(library) && arbitraryLibray === null) arbitraryLibray = library;

    let data = empty(query) ? [] : library.filter(item => {
        if(!(searchKey in item)) return false;
        return (item[searchKey].toString()).toLowerCase().includes(query);
    });



    if(!empty(data) && columnKeys.split(",").includes("action")) {
        data = data.map(function (item) {
            return {
                ...item,
                ...{
                    action: '<i class="mdi mdi-delete-forever arbitrary-action cursor-pointer hover-color-red font-22" data-call-method="updateLocationList"' +
                        ' data-action-type="remove" data-item="' + (JSON.stringify(item)).replaceAll('"', "'") + '"></i>'
                }
            };
        });
    }


    appendTableRows($(tableBodySelector).first(), data, columnKeys.split(","), 10, clickAction);
}

async function arbitraryAction(btn) {
    let method = btn.data("call-method");
    if(method === undefined || !Object.keys(window).includes(method)) return false;

    await window[method](btn);
}




const callMethodSelectChange = (selectElement) => {
    if(!selectElement.length) return;
    let val = selectElement.val(), selectedOption = selectElement.find("[value='" + val + "']");

    if(!selectedOption.length) return;
    let method = selectedOption.attr("data-call-method"), methodArguments = selectedOption.attr("data-method-arguments");

    if(empty(method) || !Object.keys(window).includes(method)) return;
    if(!empty(methodArguments)) methodArguments = JSON.parse(methodArguments);

    if(methodArguments.length === 3) {
        methodArguments[2] = selectElement.parents(methodArguments[2]).first();
        if(!methodArguments[2].length) return;
    }

    window[method](methodArguments);
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




function formSlider(form) {
    let slideElements = form.find("[data-slide-id]");
    if(!slideElements.length) return false;
    let creationFlow = signupCreationFlow();

    form.find(".continue_slide_form").on("click", function (){
        let currentSlideId = form.attr("data-current-slide");
        let currentFlow = creationFlow[currentSlideId];
        let nextValue = "";

        if(currentSlideId === "start") {
            let selectedItem = form.find(".selectable-el.selected");
            if(!selectedItem.length) return false;

            nextValue = selectedItem.attr("data-value-type");
            if(!(nextValue in currentFlow.next)) return false;

            form.attr("data-current-slide", currentFlow.next[nextValue]);
            form.find("[data-slide-id=" + currentSlideId + "]").first().fadeOut(function (){
                form.find("[data-slide-id=" + currentFlow.next[nextValue] + "]").first().fadeIn({duration: 350});
            });
        }

    });
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









async function integrateTiktok(btn) {
    let parent = btn.parents("#user_integrate_tiktok").first();
    let fields = {
        tiktok_username: "input[name=tiktok_username]",
    };

    for(let fieldName in fields) {
        let el = parent.find(fields[fieldName]).first();

        if(empty(el.val())) {
            ePopup(prepareProperNameString(fieldName) + " error", prepareProperNameString(fieldName) + " must not be empty");
            return false;
        }

        fields[fieldName] = el.val().trim();
    }

    let result = ensureObject(await requestServer({request: "integrate_tiktok", fields}));

    if(typeof result !== "object" || (!("error" in result) && !("message" in result))) {
        ePopup("Server error","Something went wrong");
        return false;
    }
    if("error" in result) {
        ePopup("Account creation error", result.error);
        return false;
    }

    ePopup("Tiktok integrated", result.message, 0, "success", "approve");
    window.setTimeout(function (){ window.location = window.location.href; }, 1500);
}





const signupCreationFlow = () => {
    return {
        start: {
            previous: null,
            next: {
                influencer: "influencer",
                brand: "brand",
            }
        },
        influencer: {
            previous: "start",
            next: "social_media"
        },
        brand: {
            previous: "start",
            next: null
        }
    };
}






async function preSignupAddPackage(btn) {
    let menuContainer = btn.parents(".dataParentContainer").first().find("#package_container").first(),
        currentPackages = menuContainer.find("[ data-value-type=package]"), maxPackages = 3,
        formContainer = $(document).find("#package_form_field_container").first();

    let nextPackageId = currentPackages.length + 1;
    if(!formContainer.length || !menuContainer.length) return false;
    if(nextPackageId > maxPackages) return false;

    let margin = nextPackageId === 1 ? "" : "mt-3";
    let packageId = generateRandomNumber();

    let html = $(
        '<div class="p-3 border border-light-orange lighter selectable-el border-radius-10px switchViewBtn ' + margin + '" ' +
            'data-value-type="package" data-package-id="' + packageId + '" data-toggle-switch-object="package_content_' + packageId + '"  data-switch-id="packaging">' +
            '<div class="flex-row-start flex-align-center">' +
                '<div class="img-placeholder square-60 ml-1"></div>' +
                '<div class="flex-col-start ml-1 ml-md-3">' +
                    '<p class="font-20 font-weight-bold packageTitle">PACKAGE ' + nextPackageId + '</p>' +
                    '<p class="font-13 text-gray">Can add up to ' + maxPackages + ' packages</p>' +
                '</div>' +
            '</div>' +
        '</div>'
    );




    let formContent = $(await requestServer({request: "pre_signup_package_container", package_id: packageId, package_title: nextPackageId}));
    menuContainer.append(html);
    formContainer.append(formContent);

    let select2Elements = formContainer.find(".select2Multi");
    if(select2Elements.length) select2MultiInit(select2Elements);

    let filePondElements = formContent.find(".filePondFileUpload");
    if(filePondElements.length) filePondElements.each(function (){ filePondInit($(this)) });

    let maxLengthElements = formContainer.find("[maxlength]");
    if(maxLengthElements.length) setMaxLengthItems(maxLengthElements);

    html.trigger("click");
    if(nextPackageId === maxPackages) btn.hide();
}





async function createPackagePreSignup(btn) {
    let parent = btn.parents("[data-switchParent]").first().find("#package_form_field_container").first(), packageElements = parent.find(".packageItem"), maxPackages = 3;
    let fields = {
        provider: "input[name=package_provider]",
        content_type: "select[name=package_content_type]",
        title: "input[name=package_title]",
        description: "textarea[name=package_description]",
        delivery_time: "select[name=package_delivery_time]",
        price: "input[name=package_price]",
        package_tags: "select[name=package_tags]",
        package_image: "input[type=hidden][name=package_image]"
    }, collector = [];


    if(!packageElements.length || packageElements.length > maxPackages) {
        ePopup("Creation error", "You may only create between 1 and 3 packages");
        return false;
    }

    let error = false;
    packageElements.each(function (){
        let hold = {};
        for(let fieldName in fields) {
            let el = $(this).find(fields[fieldName]).first(), value = el.val();


            if(empty(value)) {
                ePopup(prepareProperNameString(fieldName, false) + " error", prepareProperNameString(fieldName, false) + " must not be empty");
                error = true;
                return false;
            }
            if(fieldName === "price" && (parseFloat(value) < 5 || parseFloat(value) > 10000)) {
                ePopup("Bad input!", "The price must be between 5 and 10.000");
                error = true;
                return false;
            }

            if(fieldName === "title" && (value.length < 20 || value.length > 60)) {
                ePopup("Bad input!", "The title must be between 20 and 65 characters");
                error = true;
                return false;
            }
            if(fieldName === "description" && (value.length < 40 || value.length > 900)) {
                ePopup("Bad input!", "The description must be between 80 and 900 characters");
                error = true;
                return false;
            }

            hold[fieldName] = typeof value === "string" ? value.trim() : value;
        }

        hold.row_id = $(this).attr("data-row-id");
        collector.push(hold);
    });
    if(error) return false;

    let result = ensureObject(await requestServer({request: "create_package", fields: collector}));

    if(typeof result !== "object" || (!("error" in result) && !("message" in result))) {
        ePopup("Server error","Something went wrong");
        return false;
    }
    if("error" in result) {
        ePopup("Package creation error", result.error);
        return false;
    }

    ePopup("Packages created!", result.message, 0, "success", "approve");
    window.setTimeout(function (){ window.location = window.location.href; }, 1500);
}


async function removePackage(btn) {
    let parent = btn.parents(".packageItem[data-row-id]").first();
    if(!parent.length) return false;

    let rowId = parent.attr("data-row-id");
    if(empty(rowId)) return false;

    if(parent.attr("data-is-dummy") === "true") {

        let packageContainer = parent.parents("[data-switchParent]").first().find("#package_container").first();
        let pckBtn = packageContainer.find("[data-package-id=" + rowId + "]");
        if(!pckBtn.length) return false;

        let addMoreBtn = packageContainer.parents(".dataParentContainer").first().find("#add_additional_package").first();
        if(!addMoreBtn.length) return false;

        pckBtn.first().remove();
        parent.remove();
        addMoreBtn.show();




        let packageContentElements = $("#package_form_field_container").find(".packageItem");
        if(!packageContentElements.length) return true;

        let packageButtonElements = packageContainer.find("[data-value-type=\"package\"]");
        if(!packageButtonElements.length) return true;



        let i = 0;
        packageContentElements.each(function () {
            i++;
            let el = $(this);
            if(el.attr("data-is-dummy") === "false") return;

            el.find(".packageTitle").first().text("Package " + i);
        })

        i = 0;
        packageButtonElements.each(function () {
            i++;
            let el = $(this);
            if(el.attr("data-is-dummy") === "false") return;
            el.find(".packageTitle").first().text("PACKAGE " + i);
        })










        return true;
    }


    swalConfirmCancel({
        request: "remove_package",
        fields: {row_id: rowId},
        refreshTimeout: 1000,
        visualText: {
            preFireText: {
                title: "Remove package?",
                text: "Removing the package cannot be undone",
                icon: "warning",
                confirmBtnText: "Remove"
            },
            successText: {
                title: "Package removed",
                text: "Successfully removed the package",
                icon: "success",
                html: ""
            },
            errorText: {
                title: "Failed",
                text: "Failed to remove package: <_ERROR_MSG_>",
                icon: "error",
                html: ""
            }
        }
    });

}








async function setAdditionalMediaElement(args) {
    console.log(args);
    if(!(("src" in args) && ("container" in args) && "media_type" in args && "reference" in args)) return false;
    let container = args.container, src = args.src, mediaType = args.media_type, reference = args.reference;

    console.log(container.length);
    if(!container.length) return false;
    let targetElement;


    if(reference === "profile_picture") targetElement = container.find("#profile_picture_placeholder").first();
    else if (reference === "cover_image") targetElement = container.find("#cover_image_container").first();
    else return false;

    console.log("target el length:  " + targetElement.length);

    if(!targetElement.length) return false;
    if(reference === "profile_picture") {
        console.log(src);
        targetElement.attr("src", src);
        return true;
    }

    console.log("not profile picture?");

    let currentCoverImages = targetElement.find("img.isCoverImage");
    if(currentCoverImages.length >= 2) targetElement.find("#cover_image_upload_container").first().hide();

    let html = '<div class="col-12 col-md-4 pr-1 mt-2 position-relative dataParentContainer">';
        html += '<div class="absolute-tr-0-0">';
            html += '<i class="cursor-pointer removeCoverImage color-white hover-color-red bg-orange border-radius-50 mdi mdi-close"';
                html += 'data-src="' + src.replace(serverHost, "") + '"></i>';
        html += '</div>';

    if(mediaType === "image") html += '<img src="' + src + '" class="w-100 isCoverImage"/>';
    else {
        html += '<video class="w-100" controls>';
        html += '<source src="' + src + '" type="video/mp4">';
        html += '<source src="' + src + '" type="video/webm">';
        html += '<source src="' + src + '" type="video/ogg">';
        html += '</video>';
    }
    html += '</div>';

    targetElement.append(html);
}


async function removeCoverImage(btn) {
    let src = btn.attr("data-src");
    if(empty(src)) return false;
    ensureObject(await requestServer({request: "removeCoverImage", src}));

    let parentContainer = btn.parents("#cover_image_container").first(), coverElementsRemaining = parentContainer.find(".removeCoverImage");
    btn.parents(".dataParentContainer").first().remove();
    if(coverElementsRemaining.length <= 3) parentContainer.find("#cover_image_upload_container").first().show();
}




async function runMe() {
    ePopup("Error", "There's currently no way to make a purchase on the page. Please try again later")
    window.setTimeout(function (){
        ePopup("", "", 1);
    }, 5000);
}


function showSpecialTextField() {
    let rowParent = $(document).find(".editRowParent").first(), textFields = rowParent.find(".specialTextFieldEditable"),
        saveBtn = rowParent.parents(".dataParentContainer").first().find("button[name=saveEditUserBtn]").first().parent();
    if(!textFields.length || !saveBtn.length) return false

    textFields.each(function (){ $(this).addClass("edit"); });
    rowParent.removeClass("col-12").addClass("col-11");
    saveBtn.show();
}

function hideSpecialTextField() {
    let rowParent = $(document).find(".editRowParent").first(), textFields = rowParent.find(".specialTextFieldEditable"),
        saveBtn = rowParent.parents(".dataParentContainer").first().find("button[name=saveEditUserBtn]").first().parent();
    if(!textFields.length || !saveBtn.length) return false

    textFields.each(function (){ $(this).removeClass("edit"); });
    rowParent.removeClass("col-11").addClass("col-12");
    saveBtn.hide();
}





async function toggleStripeTest(btn) {
    let parent = btn.parents(".dataParentContainer").first(), checkbox = parent.find("input[type=checkbox][name=test_mode_enabled]").first();
    if(!checkbox.length) return false;

    let testMode = checkbox.is(":checked");
    swalConfirmCancel({
        request: "toggle_stripe_test",
        data: {
            test_mode: testMode,
        },
        refreshTimeout: 1000,
        visualText: {
            preFireText: {
                title: "Toggle Stripe?",
                text: testMode ? "Set Stripe payments to TEST mode?" : "Enable Stripe REAL payments?",
                icon: "warning",
                confirmBtnText: "Toggle"
            },
            successText: {
                title: "Test-mode " + (testMode ? "Enabled" : "Disabled"),
                text: "Stripe is now currently set to receive " + (testMode ? "TEST mode" : "REAL payments"),
                icon: "success",
                html: ""
            },
            errorText: {
                title: "Failed",
                text: "<_ERROR_MSG_>",
                icon: "error",
                html: ""
            }
        }
    });
}



async function placeOrderProceedToCheckout(btn) {
    let parent = btn.parents(".dataParentContainer").first(),
        elementSelectors = {
            pid: "input[name=pid]",
            description: "textarea[name=requirements]",
            handle: "input[name=tiktok_handle]",
            hashtags: "select[name=hashtag_inputs]",
            caption: "textarea[name=post_captions]",
        }, collector = {};

    for(let key in elementSelectors) {
        let selector = elementSelectors[key];
        if(!parent.find(selector).length) {
            ePopupTimeout("DOM error", "Failed to find " + key + " element");
            return false;
        }

        let value = parent.find(selector).first().val();
        if(empty(value)) {
            ePopupTimeout("Invalid input", ucFirst(key) + " cannot be empty");
            return false;
        }
        collector[key] = typeof value === "string" ? value.trim() : value;
    }

    console.log(collector);


    let result = ensureObject(await requestServer({
        request: "place_order_proceed_checkout",
        data: collector
    }))

    if(typeof result !== "object") {
        ePopup("Something went wrong", "Something unexpected happened. Please try again later")
        console.error(result);
        return false;
    }

    if("error" in result) {
        ePopup("Failed to create order", result.error);
        console.error(result);
        return false;
    }

    if(!"order_details" in result) {
        ePopup("Bad response", "Received a bad response from the server. Try again later");
        console.error(result);
        return false;
    }

    window.setTimeout(function (){
        window.location = serverHost + "?page=checkout&oid=" + result.order_details.order_id;
    }, 500);
}



function messaging(btn) {
    let parent = btn.parents(".dataParentContainer").first(), messageContainer = parent.find("#messaging_field").first(),
        messageField = parent.find("input[name=message_content]").first();
    if(!messageContainer.length || !messageField.length) return false;

    let oid = findGetParameter("oid"), offset = messageContainer.attr("data-offset");
    if(empty(oid)) return false;

    let message = messageField.val();
    if(empty(message)) return;
    message = message.trim();

    requestServer({
        request: "set_message", oid, message
    })
        .then(result => {
            result = ensureObject(result);
            if(typeof result !== "object" || !("status" in result) || result.status !== "success") return;

            onloadNewMessages(messageContainer, {...result, ...{message}}, offset, true);

            messageField.val("");
            messageContainer.get(0).scrollTo(0,0);
        })
        .catch(result => {
            console.error(result);
        })
}

function onloadNewMessages(messageContainer, result, offset, newMessage = true) {
    if(!(0 in result)) result = [result];
    let newMessageHtml = '', messageCount = Object.keys(result).length;

    if(isNormalInteger(offset)) offset = parseInt(offset);
    let newOffset = offset + messageCount;

    for(let i in result) {
        let item = result[i];

        newMessageHtml += '<div class="mt-3 flex-col-start flex-align-'+item.float+' pr-3">';
            newMessageHtml += '<div class="flex-row-'+item.float+' flex-align-start flex-wrap">';
                if(item.float === "end") {
                    newMessageHtml += '<p class="font-14 text-gray">' + item.date + '</p>';
                    newMessageHtml += '<p class="ml-2 font-weight-bold font-16">' + item.name + '</p>';
                }
                else {
                    newMessageHtml += '<p class="font-weight-bold font-16">' + item.name + '</p>';
                    newMessageHtml += '<p class="ml-2 font-14 text-gray">' + item.date + '</p>';
                }
            newMessageHtml += '</div>';
            newMessageHtml += '<div class="mt-2 message-box">' + item.message + '</div>';
        newMessageHtml += '</div>';
    }

    newMessage ? messageContainer.prepend(newMessageHtml) : messageContainer.append(newMessageHtml);
    messageContainer.attr("data-offset", newOffset);
}

var messageScrollFetch = true;
function detectMessageFieldScroll(messageContainer) {
    messageContainer.on("scroll", function (){
        let el = $(document).find("#messaging_field").first();
        let elHeight = el.height();
        let scrollHeight = el.get(0).scrollHeight;
        let scrollAbsPos = Math.abs(el.scrollTop())
        let scrollCurrentPosition = scrollAbsPos + elHeight;
        let distanceFromTop = scrollHeight - scrollCurrentPosition;


        if ( (distanceFromTop < 50) ) {
            if(messageScrollFetch) {
                messageScrollFetch = false;
                loadNewMessages(messageContainer);
            }
        }

    })
}



async function loadNewMessages(messageContainer) {
    if(!messageContainer.length) return false;

    let oid = findGetParameter("oid"), offset = messageContainer.attr("data-offset"), loadSize = messageContainer.attr("data-load-size");
    if(empty(oid)) return false;

    if(isNormalInteger(offset)) offset = parseInt(offset);
    if(isNormalInteger(loadSize)) loadSize = parseInt(loadSize);

    let result = ensureObject(await requestServer({
        request: "load_messages",
        offset,
        loadSize,
        oid
    }))

    if(!empty(result)) {
        onloadNewMessages(messageContainer, result, offset, false);
        window.setTimeout(function (){messageScrollFetch = true;}, 500)
    }
}



function filePondInit(inputElement) {
    let dataAccept = inputElement.attr("accept"), imageLabeling = dataAccept === "image";
    let cfr = generateRandomNumber();
    cfr = (cfr.toString()).replace(".", "-");

    let pond = FilePond.create(inputElement.get(0));
    let pondOptions = {
        server: {
            url: "requests.php?request=tmp_file_upload",
            headers: {
                cfr
            },
        }
    };
    if(imageLabeling)
        pondOptions.labelIdle = '<div class="flex-col-around flex-align-center">' +
            '<p class="font-16 font-weight-bold">Upload package image</p>' +
            '<p class="font-14">Click or Drag & Drop</p>' +
            '<p class="font-14 color-orange-dark font-italic">Ratio 3:2 recommended</p>' +
            '</div>';

    pond.setOptions(pondOptions);
}



async function deliveryOrder() {
    let form = $(document).find("form#delivery_form").first(),
        contentURLField = form.find("input[name=delivery_content_url]"),
        filesFields = form.find("input[type=hidden][name=work_files]");

    if(!contentURLField.length) return false;
    let files = [], contentURL = contentURLField.val();

    if(empty(contentURL)) {
        ePopupTimeout("Required field is missing!", "Please put in the URL to the content that you have created", "error", "error_triangle", 5000)
        return false;
    }

    if(filesFields.length) {
        filesFields.each(function () {
            let value = $(this).val();
            if(empty(value)) return;
            files.push(value.trim());
        })
    }

    let data = {files: JSON.stringify(files), content_url: contentURL, oid: findGetParameter("oid")};
    let result = ensureObject(await requestServer({
        request: "order_delivery",
        data
    }))

    result = basicResponseHandling(result, "object", ["status", "title", "message"]);
    if(!result.status) {
        ePopupTimeout("Oooops!", result.error, "error", "error_triangle", 5000);
        return false;
    }


    ePopupTimeout(result.response.title, result.response.message, "success", "approve", 5000);
    window.setTimeout(function (){window.location = window.location.href;}, 3500)
}


async function notificationAlertBox(alertBox) {

    const notify = (alertBox) => {
        requestServer({request: "notification_unread_count"})
            .then(res => {
                let result = ensureObject(res);
                if(typeof result === "object" && ("status" in result) && ("unread_count" in result) && result.status === "success") {
                    alertBox.text((result.unread_count > 99 ? "99+" : result.unread_count));
                    if(result.unread_count === 0) alertBox.addClass("no-vis");
                    else alertBox.removeClass("no-vis");
                }
            });
    }
    notify(alertBox)
    window.setInterval( function (){ notify(alertBox) }, 5000 );
}

function insertFooter() {
    let targetDiv = $(document).find(".page-content").first();
    if(!targetDiv.length) return;
    requestServer({request: "user_footer"})
        .then(result => {
            targetDiv.append(result);
        })
        .catch(result => {console.log("error in fetching user footer: " + result);})
}



const apiIntegration = async () => {
    if(!("apiState" in window) || empty(window.apiState)) return false;
    let keys = ["code", "scopes", "state", "error"], params = {};

    for(let key of keys) {
        let value = findGetParameter(key);
        if(key !== "error" && empty(value)) return false;
        params[key] = value;
    }
    ePopup("Integrating....", "Hold on a moment while we process the integration", 0, "warning");

    let result = ensureObject(await requestServer({request: "set_api_integration", data: params}));

    if(typeof result !== "object" || !("status" in result)) {
        ePopup("Something went wrong", "Please try again later");
        return false;
    }

    if(result.status === "error") {
        ePopup("Integration unsuccessful", result.message);
        return false;
    }

    ePopup("Account confirmed", "Your account has been confirmed and the integration is now complete", 0, "success", "approve");
    window.setTimeout(function () {window.location = serverHost;}, 2000);
}



var currentSelectedAccount = null;
var analyticsData = {}
function setSelectedAccount(selectElement) {
    let val = $("select[name=selected_account]").first().val();
    currentSelectedAccount = empty(val) ? null : val;
    getAnalytics(selectElement);
}

async function getAnalytics(selectElement) {
    let rangePicker = $(".DP_RANGE").first(), categoryNameEl = $("select[name=category_filter]"),
        categoryValueEl = $("select[name=category_value_filter]"), timeSeriesEl = $("input[name=time_series]");
    if(selectElement.attr("name") === "category_filter") categoryValueEl.val("");
    if(!empty(currentSelectedAccount)) analyticsData = ensureObject(await requestServer({
        request: "ig_analytics",
        integration_id: currentSelectedAccount,
        category_name: categoryNameEl.val(),
        category_value: categoryValueEl.val(),
        time_series: timeSeriesEl.is(":checked"),
        time_range: getTimepickerTime(rangePicker)
    }))


    if(empty(analyticsData) || empty(analyticsData.account_analytics) || empty(analyticsData.media_analytics) || empty(analyticsData.medias)) {
        //Display no data
        $("#analytics_content").hide();
        $("#analytics_no_content").show();
        return;
    }



    if(selectElement.attr("name") === "category_filter") {
        let categoryValues = ensureObject(await requestServer({request: "category_values_by_category", category_name: categoryNameEl.val()}));
        let optHtml = '<option value="" selected>Category values</option>';
        if(!empty(categoryValues)) {
            for(let value of categoryValues) {
                optHtml += '<option value="' + value + '">';
                optHtml += ucFirst(value);
                optHtml += '</option>';
            }
        }
        categoryValueEl.html(optHtml);
    }



    $("#analytics_content").show();
    $("#analytics_no_content").hide();

    setAccountKpiCards(analyticsData.account_analytics);
    setAccountTableStats(analyticsData.account_analytics);

    let accountKeyChartSelect = $(document).find("select[name=account_stats_chart_key_select]").first();
    accountKeyChartSelect.on("change",function () { accountDataChart(analyticsData.account_analytics); });
    accountKeyChartSelect.trigger("change");

    setMediaKpiCards(analyticsData.media_analytics);
    setMediaTableStats(analyticsData.media_analytics);

    setMediaDisplayContainer(analyticsData.medias);
    setMediaMultiChartSelect(analyticsData.medias);
    $(document).find("select[name=media_multi_chart_media_select]").first().trigger("change");


}


function getTimepickerTime(timeRangeElement) {
    return {
        start: Math.round(((new Date((timeRangeElement.data('daterangepicker').startDate))).valueOf()) / 1000),
        end: Math.round(((new Date((timeRangeElement.data('daterangepicker').endDate))).valueOf()) / 1000)
    };
}


async function setMediaCategory(selectElement) {
    let currentValue = selectElement.val(), mediaId = selectElement.attr("data-media-id"), categoryName = selectElement.attr("data-category-name");
    if(empty(mediaId)) return;
    if(empty(categoryName)) categoryName = "";

    await requestServer({request: "media_set_category", current_value: currentValue, category_name: categoryName, media_id: mediaId})
        .then(() => {
            selectElement.val("");
            loadMediaCategories(mediaId);
        })
}

async function removeMediaCategory(closeBtnElement) {
    let name = closeBtnElement.attr("data-category-name"), mediaId = closeBtnElement.attr("data-media-id");
    if(empty(name) || empty(mediaId)) return;
    await requestServer({request: "media_remove_category", name, media_id: mediaId})
        .then(() => {
            loadMediaCategories(mediaId);
        })
}



async function loadMediaCategories(mediaId) {
    let categories = ensureObject(await requestServer({request: "media_categories", media_id: mediaId}));
    let categoryContainer = $(document).find("#media_categories").first();
    if(!categoryContainer.length) return;

    if(empty(categories)) {
        categoryContainer.html("");
        return;
    }

    let collection = {};
    for (let item of categories) {
        if(!(item.name in collection)) collection[item.name] = []
        collection[item.name].push(item);
    }

    let html = $('<div class="font-16 row" id="media_categories"></div>');
    for (let name in  collection) {
        let item = collection[name];
        let opt = {
            tags: true,
            height: "20px",
            placeholder: "Search and add values",
        };

        let optionHtml = '';
        for(let listItem of item) {
            optionHtml += '<option value="' + listItem.id + '" selected>';
                optionHtml += listItem.value;
            optionHtml += '</option>';
        }

        let obj = $(
            '<div class="col dataParentContainer bg-purple-gray color-dark border-radius-10px p-3 m-xl-1">' +
                '<p class="font-weight-bold font-18 mb-2">' + prepareProperNameString(name) + '</p>' +
                '<select class="form-control select2Multi categoryValueAssign" data-media-id="' + mediaId + '" data-category-name="' + name + '" ' +
                    'data-select2-attr=\'' + JSON.stringify(opt) + '\' multiple="multiple" >' +
                        optionHtml +
                '</select>' +
            '</div>'
        );

        html.append(obj);
    }

    categoryContainer.replaceWith(html);
    select2MultiInit($(document).find("#media_categories").first().find(".select2Multi"))
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







function accountDataChart(accountAnalytics) {
    let chartElement = $(document).find("#account_stats_chart").first(), chartSelect = $(document).find("[name=account_stats_chart_key_select]").first();
    if(!chartElement.length || !chartSelect.length) return;
    let dataItems = accountAnalytics.data;
    if(empty(dataItems)) {
        setLineChartNoData(chartElement)
        return;
    }

    dataItems = sortByKey(dataItems, "meta", false, "priority");
    let selectedKpi = chartSelect.val();

    let labels = dataItems.map(function (item) {
        return (item.meta.label_value.toString()).length > 4 ?
            convertDate(item.meta.label_value, false, true, true) : item.meta.label_value;
    });

    let data = {}, series = []
    for(let items of dataItems) {

        for(let kpi in items.totals) {
            if(![selectedKpi].includes(kpi)) continue;
            let value = items.totals[kpi];
            if(!(kpi in data)) data[kpi] = [];

            data[kpi].push(value);
        }
    }

    for(let kpi in data) {
        series.push({
            data: data[kpi],
            name: prepareProperNameString(kpi)
        });
    }

    multiChart(chartElement, {labels, series}, prepareProperNameString(selectedKpi));
}


function setAccountKpiCards(accountAnalytics) {
    let cards = {
        followers_count: {
            flat_el: "#followers_count_kpi_el",
            change_el: "#followers_count_change_el"
        },
        profile_views: {
            flat_el: "#profile_views_kpi_el",
            change_el: "#profile_views_change_el"
        },
        website_clicks: {
            flat_el: "#website_clicks_kpi_el",
            change_el: "#website_clicks_change_el"
        },
        conversion_rate: {
            flat_el: "#conversion_rate_kpi_el",
            change_el: "#conversion_rate_change_el"
        }
    }

    let endTotals = accountAnalytics.end, changesTotals = accountAnalytics.changes,
        accumulatedTotals = accountAnalytics.accumulated_total, accumulatedChanges = accountAnalytics.accumulated_changes;
    if(empty(endTotals) || empty(changesTotals) || empty(accumulatedTotals) || empty(accumulatedChanges)) return;

    endTotals = endTotals.totals;
    changesTotals = changesTotals.total;
    if(empty(endTotals) || empty(changesTotals)) return;

    for(let kpi in cards) {
        let item = cards[kpi],
        dataElement = $(document).find(item.flat_el).first(), changeElement = $(document).find(item.change_el).first();
        if(!dataElement.length || !changeElement.length) continue;

        let dataValue = kpi === "followers_count" ? parseFloat(endTotals[kpi]) : parseFloat(accumulatedTotals[kpi]);
        let changeTotalValue = kpi === "followers_count" ? parseFloat(changesTotals[kpi]) : parseFloat(accumulatedChanges[kpi]);

        let percentageChange = !(dataValue > 0 || dataValue < 0) ? 0 : Math.round(changeTotalValue / dataValue * 10000) / 100; //2 decimals rounded
        let colorClass = percentageChange > 0 ? "color-green" : (percentageChange < 0 ? "color-red" : "");

        dataElement.text((kpi === "conversion_rate" ? dataValue : numberFormatting(dataValue)))
        if(kpi === "followers_count") changeElement.addClass(colorClass).text(percentageChange + "%");
    }
}



function setAccountTableStats(accountAnalytics) {
    let table = $(document).find("table#account_stats_table").first();
    if(!table.length || empty(accountAnalytics)) return;

    let rows = [];

    let accumulatedTotals = accountAnalytics.accumulated_total, accumulatedChanges = accountAnalytics.accumulated_changes;
    if(empty(accumulatedTotals) || empty(accumulatedChanges)) return;

    let startTotals = accountAnalytics.start, endTotals = accountAnalytics.end;
    if(empty(startTotals) || empty(endTotals)) return;
    startTotals = startTotals.totals;
    endTotals = endTotals.totals;

    let changes = accountAnalytics.changes;
    if(empty(changes)) return;
    let changesAverage = changes.average, changesTotals = changes.total;
    if(empty(changesAverage) || empty(changesTotals)) return;

    for(let kpi of ["followers_count", "profile_views", "website_clicks", "conversion_rate"]) {
        rows.push([
            prepareProperNameString(kpi),
            kpi === "followers_count" ? startTotals[kpi] : 0,
            kpi === "followers_count" ? endTotals[kpi] : accumulatedTotals[kpi],
            kpi === "followers_count" ? changesAverage[kpi] : accumulatedChanges[kpi],
            kpi === "followers_count" ? changesTotals[kpi] : accumulatedTotals[kpi],
        ]);
    }


    let htmlObj = $("<tbody></tbody>");

    for(let row of rows) {
        let html = '';
        html += '<tr>'
            for(let col of row) html += '<td>' + col + '</td>';
        html += '</tr>'
        htmlObj.append(html);
    }

    table.find("tbody").first().replaceWith(htmlObj);
}


function setMediaDisplayContainer(mediaObject) {
    let container = $(document).find("#image_container").first();
    if(!container.length) return;

    let html = "";
    if(!empty(mediaObject)) {
        for(let media of mediaObject) {

            html += '<div class="col-12 col-xl-6 mt-1">';
                html += '<img src="' + media.thumbnail_url + '" class="w-100 cursor-pointer hover-opacity-half noSelect" data-toggle-media="' + media.id + '"/>';
            html += '</div>';
        }
    }

    container.html(html);
}

function setMediaMultiChartSelect(medias) {
    let container = $(document).find("select[name=media_multi_chart_media_select]").first();
    if(!container.length) return;

    let opt = {
        tags: false,
        height: "20px",
        placeholder: "Add media",
        allowClear: true,
    };
    let html = $(
        '<select name="media_multi_chart_media_select" class="form-control select2Multi" ' +
            'data-select2-attr=\'' + JSON.stringify(opt) + '\' multiple="multiple" >' +
        '</select>'
    );

    if(!empty(medias)) {
        let optionHtml = '';
        for(let media of medias) {
            optionHtml += '<option value="' + media.id + '">';
                optionHtml += convertDate(media.timestamp, true, true, true);
            optionHtml += '</option>';
        }

        html.append(optionHtml);
    }

    if(container.data('select2')) container.parents(".dataParentContainer").first().find(".select2.select2-container").remove();
    $(document).find("select[name=media_multi_chart_media_select]").first().replaceWith(html);
    select2MultiInit(html)
}


function mediaToggleStats(imageElement) {
    let mediaId = imageElement.attr("data-toggle-media");
    if(typeof mediaId === undefined) return;
    if(typeof mediaId !== "number") mediaId = parseInt(mediaId);

    let mediaAnalyticsData = analyticsData.media_analytics.data;
    let mediaObject = analyticsData.medias.filter(function (item) { return item.id === mediaId; })

    if(empty(mediaAnalyticsData) || !(mediaId in mediaAnalyticsData) || empty(mediaObject)) {
        $("#media_data_content").hide();
        $("#media_data_no_content").show();
        return;
    }

    $("#media_data_content").show();
    $("#media_data_no_content").hide();
    mediaObject = mediaObject[ (Object.keys(mediaObject)[0])];
    mediaAnalyticsData = mediaAnalyticsData[mediaId];

    $("#media_created_at").text(convertDate(mediaObject.timestamp, true, true))
    $("#media_permalink").attr("href", mediaObject.permalink).text("Open")
    $("#media_caption").text(mediaObject.caption)
    $("#media_like_kpi").text(mediaAnalyticsData.end.totals.likes)
    $("#media_comment_kpi").text(mediaAnalyticsData.end.totals.comments)
    $("#media_play_kpi").text(mediaAnalyticsData.end.totals.plays)
    $("#media_share_kpi").text(mediaAnalyticsData.end.totals.shares)
    loadMediaCategories(mediaId)
    $("select[name=assign_category]").attr("data-media-id", mediaId);

    selectedMediaTableStats(mediaAnalyticsData);

    let mediaKeyChartSelect = $(document).find("select[name=media_stats_chart_key_select]").first();
    mediaKeyChartSelect.on("change",function () { selectedMediaChart(mediaAnalyticsData); });
    mediaKeyChartSelect.trigger("change");
}


function mediaMultiChart() {
    let chartElement = $(document).find("#media_multi_chart").first(), chartSelect = $(document).find("[name=media_multi_chart_kpi_select]").first(),
        mediaSelectEl = $(document).find("[name=media_multi_chart_media_select]").first();
    if(!chartElement.length || !chartSelect.length || !mediaSelectEl.length) return;


    let mediaIds = mediaSelectEl.val();
    if(empty(mediaIds)) {
        setLineChartNoData(chartElement)
        return;
    }


    let selectedKpi = chartSelect.val();



    let series = [], labels = [];
    for(let mediaId of mediaIds) {
        let dataItems = analyticsData.media_analytics.data[mediaId].data;
        let media = (analyticsData.medias).filter(function (media) { return mediaId.includes(((media.id).toString())); });
        media = media[0];


        if(empty(dataItems)) {
            setLineChartNoData(chartElement)
            return;
        }

        dataItems = sortByKey(dataItems, "meta", ((dataItems[0].meta.label_value.toString()).length <= 4), "priority");
        let data = {};

        for(let n in dataItems) {
            let items = dataItems[n];
            if(empty(items) || empty(items.totals)) {
                labels.push(n);
            }
            else {
                labels.push(
                    (items.meta.label_value.toString()).length > 4 ?
                        convertDate(items.meta.label_value, false, true, true) : items.meta.label_value
                );

                for(let kpi in items.totals) {
                    if(![selectedKpi].includes(kpi)) continue;
                    let value = ("graph_value" in items) ? items.graph_value : items.totals[kpi];
                    if(!(kpi in data)) data[kpi] = [];

                    data[kpi].push(value);
                }
            }
        }


        for(let kpi in data) {
            if(empty(data[kpi])) series.push(null);
            else {
                series.push({
                    data: data[kpi],
                    name:  prepareProperNameString(kpi) + " - (" + convertDate(media.timestamp, true, true, true) +  ")"
                });
            }
        }
    }


    multiChart(chartElement, {labels, series}, prepareProperNameString(selectedKpi));
}




function selectedMediaChart(mediaAnalytics) {
    let chartElement = $(document).find("#selected_media_chart").first(), chartSelect = $(document).find("[name=media_stats_chart_key_select]").first();
    if(!chartElement.length) return;
    let dataItems = mediaAnalytics.data;
    if(empty(dataItems)) {
        setLineChartNoData(chartElement)
        return;
    }

    dataItems = sortByKey(dataItems, "meta", ((dataItems[0].meta.label_value.toString()).length <= 4), "priority");
    let selectedKpi = chartSelect.val();


    let data = {}, series = [], labels = [];
    for(let n in dataItems) {
        let items = dataItems[n];
        if(empty(items) || empty(items.totals)) {
            labels.push(n);
        }
        else {
            labels.push(
                (items.meta.label_value.toString()).length > 4 ?
                    convertDate(items.meta.label_value, false, true, true) : items.meta.label_value
            );

            for(let kpi in items.totals) {
                if(![selectedKpi].includes(kpi)) continue;
                let value = ("graph_value" in items) ? items.graph_value : items.totals[kpi];
                if(!(kpi in data)) data[kpi] = [];

                data[kpi].push(value);
            }
        }
    }


    for(let kpi in data) {
        if(empty(data[kpi])) series.push(null);
        else {
            series.push({
                data: data[kpi],
                name: prepareProperNameString(kpi)
            });
        }
    }

    multiChart(chartElement, {labels, series}, prepareProperNameString(selectedKpi));
}




function selectedMediaTableStats(mediaAnalytics) {
    let table = $(document).find("table#selected_media_stats").first();
    if(!table.length || empty(mediaAnalytics)) return;

    let rows = [];

    let startTotals = mediaAnalytics.start, endTotals = mediaAnalytics.end;
    if(empty(startTotals) || empty(endTotals)) return;
    startTotals = startTotals.totals;
    endTotals = endTotals.totals;

    let kpis = Object.keys(endTotals);
    let changes = mediaAnalytics.changes;
    if(empty(changes)) return;
    let changesAverage = changes.average, changesTotals = changes.total;
    if(empty(changesAverage) || empty(changesTotals)) return;

    for(let kpi of kpis) {
        rows.push([
            prepareProperNameString(kpi),
            startTotals[kpi],
            endTotals[kpi],
            changesAverage[kpi],
            changesTotals[kpi],
        ]);
    }


    let htmlObj = $("<tbody></tbody>");

    for(let row of rows) {
        let html = '';
        html += '<tr>'
        for(let col of row) html += '<td>' + (isFloat(col) ? (Math.round(col * 100) / 100) : col) + '</td>';
        html += '</tr>'
        htmlObj.append(html);
    }

    table.find("tbody").first().replaceWith(htmlObj);
}




function setMediaTableStats(mediaAnalytics) {
    let table = $(document).find("table#media_stats_table").first();
    if(!table.length || empty(mediaAnalytics)) return;

    let rows = [];

    let startTotals = mediaAnalytics.start, endTotals = mediaAnalytics.end;
    if(empty(startTotals) || empty(endTotals)) return;

    let changes = mediaAnalytics.changes;
    if(empty(changes)) return;
    let changesAverage = changes.average, changesTotals = changes.total;
    if(empty(changesAverage) || empty(changesTotals)) return;

    let keys = Object.keys(startTotals);

    let tableHeadObj = $("<thead></thead>");
    tableHeadObj.append('<tr>');
    tableHeadObj.append('<th></th>');
    for(let key of keys) tableHeadObj.append('<th>' + prepareProperNameString(key) + '</th>');
    tableHeadObj.append('</tr>');
    table.find("thead").first().replaceWith(tableHeadObj);


    rows.push(["start"].concat(Object.values(startTotals)));
    rows.push(["end"].concat(Object.values(endTotals)));
    rows.push(["avg"].concat(Object.values(changesAverage)));
    rows.push(["total"].concat(Object.values(changesTotals)));




    let htmlObj = $("<tbody></tbody>");

    for(let row of rows) {
        let html = '';
        html += '<tr>'
        for(let col of row) html += '<td>' + (isFloat(col) ? (Math.round(col * 100) / 100) : col) + '</td>';
        html += '</tr>'
        htmlObj.append(html);
    }

    table.find("tbody").first().replaceWith(htmlObj);
}




function setMediaKpiCards(mediaAnalytics) {
    let cards = {
        likes: {
            flat_el: "#likes_kpi_el",
            change_el: "#likes_change_el"
        },
        reach: {
            flat_el: "#reach_kpi_el",
            change_el: "#reach_change_el"
        },
        total_interactions: {
            flat_el: "#interaction_kpi_el",
            change_el: "#interaction_change_el"
        },
        interaction_rate: {
            flat_el: "#interaction_rate_kpi_el",
            change_el: "#interaction_rate_change_el"
        }
    }

    let endTotals = mediaAnalytics.end, changesTotals = mediaAnalytics.changes;
    if(empty(endTotals) || empty(changesTotals)) return;
    changesTotals = changesTotals.total;
    if(empty(changesTotals)) return;

    for(let kpi in cards) {
        let item = cards[kpi],
            dataElement = $(document).find(item.flat_el).first(), changeElement = $(document).find(item.change_el).first();
        if(!dataElement.length || !changeElement.length) continue;

        let dataValue = parseFloat(endTotals[kpi]);
        let changeTotalValue = parseFloat(changesTotals[kpi]);

        let percentageChange = !(dataValue > 0 || dataValue < 0) ? 0 : Math.round(changeTotalValue / dataValue * 10000) / 100; //2 decimals rounded
        let colorClass = percentageChange > 0 ? "color-green" : (percentageChange < 0 ? "color-red" : "");

        dataElement.text((kpi === "interaction_rate" ? dataValue : numberFormatting(dataValue)))
        changeElement.addClass(colorClass).text(percentageChange + "%");
    }
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




const runTemp = async () => {
    let modal = new ModalHandler('integrationSelection')

    let obj = [
        {
            provider: "instagram",
            items: [
                {
                    id: 1234,
                    name: "danskerfesten_malaga"
                }
            ]
        },
        {
            provider: "facebook",
            items: [
                {
                    id: 3214,
                    name: "Dansker Festen"
                }
            ]
        }
    ]
    let finalObj = {}
    for(let i in obj) {
        let item = obj[i];
        finalObj[item.provider] = "";

        for (let j of item.items) {
            finalObj[item.provider] += `<option value="${j.id}">${j.name} (Id: ${j.id})</option>`;
        }
    }


    modal.construct(finalObj)
    await modal.build()
    const integrate  = (btn, modalHandler) => {
        let modalBody = null;
        btn.parents().each(function () {
            if($(this).find(".modal-body").length) {
                modalBody = $(this).find(".modal-body").first();
                return true;
            }
        })
        if (empty(modalBody)) return;

        let facebookElement = modalBody.find("select[name=facebook]"), instagramElement = modalBody.find("select[name=instagram]");
        if(empty(facebookElement, instagramElement)) return;

        let data = {
            facebook: facebookElement.val(),
            instagram: instagramElement.val(),
        }
        console.log(data);

        modalHandler.dispose()
    }
    modal.bindEvents({integrate: integrate})


    modal.open()
}
// runTemp()

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





