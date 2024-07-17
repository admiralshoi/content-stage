

$(document).ready(function () {


});


function appendTableRows(table, data, columnKeys, maxRows = 10, rowClickAction = undefined) {
    if(empty(data) || empty(columnKeys)) {
        table.html("");
        return;
    }

    let html = "", rowCounter = 0;

    for(let dataRow of data) {
        rowCounter++;

        if(rowClickAction !== undefined && Object.keys(window).includes(rowClickAction))
            html += "<tr onClick='" + rowClickAction + "(this)' class='cursor-pointer'>";
        else html += "<tr>";

        for(let key of columnKeys) {
            let value = (key in dataRow) ? dataRow[key] : "";
            html += (key === "action" ? "<td>" : "<td data-key-"+key+"='" + value + "'>") + (key === "action" ? value : prepareProperNameString(value)) + "</td>";
        }

        html += "</tr>";
        if(rowCounter >= maxRows) break;
    }

    table.html(html);
}


function setDataTableNoData(dataTable) {
    dataTable.find("tbody").first().replaceWith($("<tbody></tbody>"));
    setDataTable(dataTable);
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

function numberWithCommas(x, separator = ",", max = null) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, separator);
}

function latestVal(arr) {
    if(typeof arr !== "object") return arr;
    let keys = Object.keys(arr), l = (keys.length)-1;
    return arr[keys[l]];
}

function popBoxes(element, propValue = null) {
    if(propValue === null) propValue = !element.is(":checked");

    element.prop("checked",propValue);
}


function serializeForm(formElement) {
    return formElement.serializeArray().reduce(function(obj, item) {
        obj[item.name] = item.value;
        return obj;
    }, {});
}

function multiCheckBoxes(masterBox) {
    let parent = masterBox.parents(".dataParentContainer").first(),
        children = parent.find("input[type=checkbox]"), propValue = masterBox.is(":checked");
    if(parent.length === 0 || children.length === 0) return false;

    children.each(function (){
        // if($(this).hasClass("masterBox")) return;
        popBoxes($(this),propValue);
    });
}


function pluralS(count, str) {
    if(isNormalInteger(count)) count = parseInt(count);
    if(typeof count !== "number") return str;

    return (count !== 1 && count !== -1) ? str + "s" : str;
}

function setCaretPosition(el, pos) {
    // Modern browsers
    if (el.setSelectionRange) {
        el.focus();
        el.setSelectionRange(pos, pos);

        // IE8 and below
    } else if (el.createTextRange) {
        var range = el.createTextRange();
        range.collapse(true);
        range.moveEnd('character', pos);
        range.moveStart('character', pos);
        range.select();
    }
}

function scrollToElement(element,time = 1000, yOffset = 0) {
    if(element.length === 1) scrollToY(((element.offset().top) - yOffset),time);
}
function scrollToY(y,time = 1000) {
    $("html, body").animate({
        scrollTop: y
    },time);
}


function trimObjectMaxXValues(object,max) {
    let counter = 1, response = {};
    for(let k in object) {
        if(counter > max)
            break;
        response[k] = object[k];
        counter++;
    }
    return response;
}

function timeAgo(timestamp, phpTime = false) {
    if(isNormalInteger(timestamp)) timestamp = parseInt(timestamp);
    if(phpTime) timestamp *= 1000;

    let timeNow = (new Date()).valueOf(), difference = Math.round((timeNow-timestamp) / 1000),
    hoursFloor = Math.floor(difference / 3600), response, count;

    if((24 * 365) < hoursFloor) { //Year in hours
        count = Math.floor(hoursFloor / (24 * 365));
        response = count + " " + pluralS(count,"year") + " ago";
    }
    else if((24 * 30 * 3) < hoursFloor) { // 3 months in hours (we display days if not greater than 3 months)
        count = Math.floor(hoursFloor / (24 * 30)); // Display in unit of 1 month (not 3 months)
        response = count + " " + pluralS(count,"month") + " ago";
    }
    else if(24 < hoursFloor) { // day in hours
        count = Math.floor(hoursFloor / 24);
        response = count + " " + pluralS(count,"day") + " ago";
    }
    else  {
        if(hoursFloor === 0) {
            count = Math.round(difference / 60);
            response = count + " " + pluralS(count,"minute") + " ago";
        }
        else response = hoursFloor + " " + pluralS(hoursFloor,"hour") + " ago"; // Hours
    }

    return response;
}

function numberFormatting(number) {
    if(isNormalInteger(number)) number = parseFloat(number);
    return new Intl.NumberFormat('us-US').format(number);
}

function dateToHourFormat(time, deliminator = ":") {
    if(isNormalInteger(time)) time = parseInt(time);

    let dateObj = new Date((time * 1000)), hour = (dateObj.getHours()).toString(), min = (dateObj.getMinutes()).toString();
    return (hour.length === 1 ? 0+hour:hour) + deliminator + (min.length === 1 ? 0+min:min);

}
function convertDate(time, getHours = false,monthName = false, dayAndMonth = false, includeSeconds = false) {
    if(isNormalInteger(time)) time = parseInt(time);

    let dateObj = (new Date(time*1000));
    let month = dateObj.getMonth() + 1; //months from 1-12
    let day = dateObj.getDate();
    let year = dateObj.getUTCFullYear();
    if(!monthName) month = month < 10 ? "0"+month : month;
    day = day < 10 ? "0"+day : day;
    let hours = "";

    if(getHours) {
        let hour = (dateObj.getHours()).toString(), min = (dateObj.getMinutes()).toString(), sec = (dateObj.getSeconds()).toString();
        hours += " " + (hour.length === 1 ? 0+hour:hour) + ":"
            + (min.length === 1 ? 0+min:min) + (!includeSeconds ? "" : ":" + (sec.length === 1 ? 0+sec:sec));
    }

    if(dayAndMonth) {
        return !monthName ? day+"-"+month + (getHours ? ", " + hours : "") :
            monthNames[(month - 1)] + "-" + day + (getHours ? ", " + hours : "");
    }

    return !monthName ? day+"-"+month+"-"+year + hours :
        monthNames[(month - 1)] + "-" + day + "-" + year + (getHours ? ", " + hours : "");
}
const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun","Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];


function compareDateStamps(stamp_1,stamp_2,php=false, od = false) {
    if(php) {
        stamp_1 *= 1000;
        stamp_2 *= 1000;
    }
    let greater, lesser, days;
    if(stamp_1 >= stamp_2) {
        greater = new Date(stamp_1); lesser = new Date(stamp_2);
    } else {
        greater = new Date(stamp_2); lesser = new Date(stamp_1);
    }
    greater.setHours(0,0,0,0);
    lesser.setHours(0,0,0,0);

    return getDateNumbers(greater-lesser, od);
}


function getDateNumbers(timestamp,od= true,dd= false) {
    let date = new Date(timestamp);
    let year = date.getFullYear();
    let month = date.getMonth();
    let day = date.getDate();
    if(dd) { //Double digit
        month = (month + 1) < 10 ? "0"+(month+1) : (month+1);
        day = day < 10 ? "0"+day : day;
    }
    if(od){ //Only days
        year -= 1970;
        if(month > 0)
            day += month*30;
        if(year > 0)
            day += year*30;
    }

    return {y:year.toString(),m:month.toString(),d:day.toString()};
}

function shortNumbByT(number,shortM = true, shortK = false, includeCharSeparate = false) {
    number = typeof number !== "number" ? parseInt(number) : number;
    let mil = 1000000, kilo = 1000,m="M",k="K", response = "";
    if((number >= mil || number <= -mil)  && shortM) {
        let x = (number / mil).toFixed(1);
        response = includeCharSeparate ? {number: x,char:m} : x+m;
    } else if((number >= kilo || number <= -kilo) && shortK) {
        let x = (number / kilo).toFixed(1);
        response = includeCharSeparate ? {number: x,char:k} : x+k;
    } else
        response = number;
    return response;
}

function pairArraysToObject(object1,object2,k=0) {
    let keyObject, valObject,response={};
    if(k === 0) {
        keyObject = object1;
        valObject = object2;
    }
    else {
        keyObject = object2;
        valObject = object1;
    }

    for (let i in keyObject) {
        let item = keyObject[i];
        response[item] = valObject[i];
    }
    return response;
}


function ucFirst(string) {
    return empty(string) ? string : string[0].toUpperCase() +  string.slice(1);
}

function prepareProperNameString(string,UCA = true, UCF = true, ucExceptions = false) {
    if(empty(string)) return string;
    if(typeof string !== "string") string = string.toString();
    string = (string.trim()).replaceAll("_"," ");
    let response = [];
    if(UCA) {
        let words = string.split(" ");
        for(let word of words) {
            let wordV = word.toLowerCase();
            if(ucExceptions && settings.ucExceptions.includes(wordV)) {
                wordV = wordV.toUpperCase();
            } else
                wordV = ucFirst(word);
            response.push(wordV);
        }
    }
    return UCA ? response.join(" ") : (UCF ? ucFirst(string) : string);
}


/**
 * Remove incorrect class active on left-menu links
 */

function setActiveNavItemLabels() {
    let link_href, current_uri, host = serverHost, full_path = window.location.href, activeItem = false //Full window name
    $("#sidebar a.sidebar-nav-link").each(function () {
        link_href = $(this).attr("href");
        current_uri = full_path.replace(host,"");
        let page = $(this).attr("data-page");

        if(page !== undefined) {
            if(page.includes("?")) page = (page.split("?"))[1];
            if(page.includes("#")) page = page.replace("#","");
            for(let field of page.split("&")) {
                let pair = field.split("="),
                    arg = pair[0], value = pair[1];
                if(arg !== "page") continue;
                page = value;
            }
        }

        if(current_uri === link_href) {
            $(this).addClass("active");
            activeItem = true;
        } else if(page !== undefined) {
            if(findGetParameter("page") === page) {
                $(this).addClass("active");
                activeItem = true;
            }
            else if(findGetParameter("page") === null && (page === "?" || page === "")) {
                $(this).addClass("active");
                activeItem = true;
            }
        }

        if(activeItem) {
            let menuHeader = $(this).parents("[data-collapser]");
            if(menuHeader.length > 0)  menuHeader.first().addClass("activeMenuHeader");
            return false;
        }

    });
}


function isFloat(n){
    return Number(n) === n && n % 1 !== 0;
}

function isNormalInteger(str) {
    let n = Math.floor(Number(str));
    return n !== Infinity && String(n) === str && n >= 0;
}

function findGetParameter(parameterName) {
    let result = null,
        tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });
    return result;
}


function getKeyByValue(obj, value, searchByValue = true) {
    if(searchByValue)
        return Object.keys(obj).filter(function(key) {return (obj[key].toLowerCase()).includes(value)});
    else
        return Object.keys(obj).filter(function(key) {return (key.toLowerCase()).includes(value)});
}

function empty(variable) {
    return variable === null || variable === "" ||
        (typeof variable === "object" && Object.keys(variable).length === 0) ||
        typeof variable === "undefined" || variable === undefined;
}

function ensureString(variable) {
    return typeof variable === "string" ? variable : JSON.stringify(variable);
}

function ensureObject(variable) {
    if(typeof variable === "string") {
        try {
            variable = JSON.parse(variable);
        }
        catch (e) {
            console.error("EnsureObject failed to parse variable (%s). ErrorMsg: %s",variable,e);
            variable = e;
        }
    }
    return variable;
}

const httpBuildQuery = (queryParams) => {
    let esc = encodeURIComponent;
    return Object.keys(queryParams).map(k => esc(k) + '=' + esc(queryParams[k])).join('&');
}

async function requestServer(params = {}, method = "post") {

    let response = await $.ajax({
        url: serverHost + "requests.php",
        type: "POST",
        data: params
    });

    // let response = await $.post(serverHost+"http.php",params) ;


    try {
        if(!empty(response) && typeof (JSON.parse(response)) === "object") {
            let obj = ensureObject(response);
            if(typeof obj === "object") {
                let keys = Object.keys(obj);
                if(keys.includes("status") && keys.includes("401") && keys.status === "error" && keys["401"]) {
                    console.error("You made an authorized request to the server. Please contact support for help if you need to");
                    // window.location = window.location.href;
                    return false;
                }
            }
        }
    } catch (e) {
        // console.error("Caught request: " + e); //dont really want to call this, because not all things will be an intended as an object
    }

    return response;
}

function renderTextWithDots(str,maxLength = 15, dotCount = 3) {
    let dots = "";
    for(let i = 1; i <= dotCount; i++) dots += ".";

    return str.length <= maxLength ? str :
        str.substr(0, (maxLength - dotCount)) + dots;
}

const arrayValues = {
    getLatest: (obj) => {
        if(typeof obj !== "object") return obj;
        return obj [ (Object.keys(obj)[0]) ];
    },
    getNth: (obj,n) => {
        if(typeof obj !== "object" || Object.keys(obj).length < (n-1)) return obj;
        return obj [ (Object.keys(obj)[n]) ];
    },
    getDiff: (obj,n1, n2) => {
        if(typeof obj !== "object" || Object.keys(obj).length < (n1-1) || Object.keys(obj).length < (n2-1)) return 0;
        let values = [obj [ (Object.keys(obj)[n1]) ], obj [ (Object.keys(obj)[n2]) ]];

        if(typeof values[0] !== "number") values[0] = parseInt(values[0]);
        if(typeof values[1] !== "number") values[1] = parseInt(values[1]);

        let diff =  Math.round(values[0] - values[1]);

        return { value:  diff, operator: diff < 0 ? "" : "+" };
    },
    getLast: (obj) => {
        if(typeof obj !== "object") return obj;
        return obj [ (Object.keys(obj)[ (Object.keys(obj).length - 1) ]) ];
    }

};

const intOperator = (num,onlyPositive = true) => {
    num = (typeof num !== "number" && isNormalInteger(num)) ? parseInt(num) : num;
    return num >= 0 ? "+" : (onlyPositive ? "" : "-");
}


function validateEmail(mail) {
    return (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail));
}

function firstWord(str) {
    return (typeof str !== "string" || !str.includes(" ")) ? str : ((str.split(" "))[0]);
}


function ePopup(title, msg, hide = 0, type = "error", icon = "error_triangle") {
    let body = $(document).find("body");
    body.find("#top-popup-notify").first().fadeOut().remove();
    if(hide) return;

    let image = "";
    if(icon === "error_triangle") image = serverHost + "images/icons/error_icon.png"
    else if(icon === "approve") image = serverHost + "images/icons/approve_white_icon.png"
    else if(icon === "email_approve") image = serverHost + "images/icons/email.png"

    let html = '<div id="top-popup-notify" style="display: none;">';
        html += '<div class="popup-notify-element ' + type + '">';
            html += '<div class="flex-row-start flex-align-start">';
                html += '<img src="' + image + '" class="square-60 hideOnMobileBlock" />';
                html += '<div class="flex-col-start ml-3">';
                    html += '<p class="font-20 font-weight-bold">' + title + '</p>';
                    html += '<p class="font-14">' + msg + '</p>';
                html += '</div>';
            html += '</div>';
            html += '<div class="flex-row-start flex-align-start square-60 ml-1">';
                html += '<img src="' + serverHost + '/images/icons/close-window-white.png" class="square-20 cursor-pointer hover-opacity-half close-popup"/>';
            html += '</div>';
        html += '</div>';
    html += '</div>';

    body.append(html);
    body.find("#top-popup-notify").first().fadeIn();
}

function closePopup(btn) {
    btn.parents("#top-popup-notify").first().fadeOut();
    btn.parents("#top-popup-notify").first().remove();
}
function ePopupTimeout(title, message, type = "error", icon = "error_triangle", timeout = 2500) {
    ePopup(title, message, 0, type, icon);
    setTimeout(() => { ePopup("", "",1) }, timeout);
}



function eNotice(msg,parent = null,hide = 0,type = "danger"){
    let parentSelector,
        hideElement = (element) => {
            element.html("");
            element.addClass("hidden")
        },
        showElement = (element,msg) => {
            element.removeClass("hidden")
            element.html(msg);
        };

    if(parent === "HIDE") {
        $(document).find(".eNotice").each(function () {
            if($(this).hasClass("hidden") === false) {
                hideElement($(this));
            }
        });
        return 1;
    }
    if(parent === null) parentSelector = document;
    else parentSelector = parent;
    let E_element = $(parentSelector).find(".eNotice").first();
    let types = {warning:"alert-warning",danger:"alert-danger",success:"alert-success"};
    if(hide === 1) {
        hideElement(E_element);
    } else {
        let errorType = Object.keys(types).includes(type) ? types[type] : type.danger;
        if(E_element.hasClass(errorType) === false){
            for(let a in types){
                if(errorType !== types[a]) {
                    E_element.removeClass(types[a]);
                }
            }
            E_element.addClass(errorType);
        }
        showElement(E_element,msg);
    }
}


function eNoticeTimeout(message, parent, type = "danger", timeout = 5000) {
    eNotice(message, parent,0, type);
    setTimeout(() => { eNotice("",parent,1) }, timeout);
}


function sortByKey(arr,key = "id", ascending = false, key2 = "") {
    arr.sort(function (a, b) {
        if(!(key in a && key in b)) return 0;

        let val1, val2;
        val1 = a[key];
        val2 = b[key];

        if(!empty(key2)) {
            val1 = val1[key2];
            val2 = val2[key2];
        }

        val1 = typeof val1 !== "number" ? parseFloat(val1) : val1;
        val2 = typeof val2 !== "number" ? parseFloat(val2) : val2;
        if (val1 === val2) return 0;
        return (val1 > val2) ? (ascending ? 1 : -1) : (ascending ? -1 : 1) ;
    });

    return arr;
}




/**
 * Pagination generator.
 * Currently supports a grid version only in SET, but simple adjustments to fit others can be made
 * Max pagination: 7
 * @type {{set: paginationGenerator.set, htmlRow: (function(*, *, *): string), gridPaginationClick: paginationGenerator.gridPaginationClick, list: (function(*, *): [])}}
 */
const paginationGenerator = {
    set: (gridContainer,area = 16) => {
        let gridItemClass = "gridItem",
            gridItems = gridContainer.find(`.${gridItemClass}`);
        if(gridItems.length === 0 || gridItems.length <= area) return;

        let pages = Math.ceil(gridItems.length / area), counter = 1, page = 1;

        gridItems.each(function () {
            if(counter > area) {counter = 1; page += 1;}

            $(this).attr("data-page",page);
            counter += 1;
        });

        let paginationRow = $(
            '<div class="w-100 flex-row-end pt-2 pb-2 pr-4 pl-4">'+
            '<div class="flex-row-around" id="paginationRow"><span data-paginator="1"></span></div>'+
            '</div>'
        );

        paginationRow.insertAfter(gridContainer);

        $(paginationRow).off("click").on("click","[data-paginator]",function (){
            let next = $(this).data("paginator");
            if(next === undefined || $(this).hasClass("active")) return;
            paginationGenerator.gridPaginationClick(next,gridItems,paginationRow,pages);
        });
        paginationGenerator.gridPaginationClick(1,gridItems,paginationRow,pages);
    },
    list: (currentPage,lastPage) => {
        let pageList = [], max = 7;
        if(lastPage > max) {
            let equilibrium = (((currentPage - 1) > 3) && ((lastPage - currentPage) > 3)),
                pagStart = ((currentPage - 1) <= 3), pagEnd = ((lastPage - currentPage) <= 3);
            pageList = [
                1,
                (!pagStart) ? "..." : 2,
                (equilibrium) ? (currentPage - 1) : (pagStart ? 3 : (lastPage - 4)),
                (equilibrium) ? currentPage : (pagStart ? 4 : (lastPage - 3)),
                (equilibrium) ? (currentPage + 1) : (pagEnd ? (lastPage - 2) : (1 + 4)),
                (!pagEnd) ? "..." : (lastPage - 1),
                lastPage
            ];
        } else {
            for(let i = 1; i <= max; i++) pageList.push(i);
        }

        return pageList;
    },
    htmlRow: (list,currentPage, lastPage) => {
        let pagination = '<span class="x30-btn cursor-pointer" id="pag_prev" data-paginator="' + (currentPage === 1 ? 1 : (currentPage - 1)) +'">Previous</span>';
        for(let i in list) {
            let p = list[i];

            if(typeof p !== "number") pagination += '<span class="x30-btn">...</span>';
            else pagination += '<span class="x30-btn cursor-pointer ' + (p === currentPage ? "active" : "") + '" data-paginator="' + p +'">' + p +'</span>';
        }
        pagination += '<span class="x30-btn cursor-pointer" id="pag_next" data-paginator="' + (currentPage === lastPage ? lastPage : (currentPage + 1)) +'">Next</span>';

        return pagination
    },
    gridPaginationClick: (next,gridItems,paginationRow,lastPage = 1) => {
        if(paginationRow.find("[data-paginator]").length === 0) return;
        if(paginationRow.find("[data-paginator="+next+"]").hasClass("active")) return;

        let list = paginationGenerator.list(next,lastPage), pagination = paginationGenerator.htmlRow(list,next,lastPage);
        paginationRow.find("#paginationRow").first().html(pagination);

        gridItems.each(function (){
            if($(this).data("page") !== next) $(this).hide();
            else  $(this).show();
        });
    }
};


const copyToClipboard = str => {
    const el = document.createElement('textarea');
    el.value = str;
    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.left = '-9999px';
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);


    const confirmCopyElement = $('<div class="pt-2 pb-2 pl-3 pr-3 alert-success alert position-fixed tr-5rem zindex99">Copied!</div>');
    confirmCopyElement.hide().prependTo(".page-content").fadeIn(200);
    setTimeout(function (){
        confirmCopyElement.fadeOut(1000).remove();
    }, 3000);
};

function docReady(fn) {
    if (document.readyState === "complete" || document.readyState === "interactive") {
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}



/**
 * Max length
 */
function setMaxLengthItems(elements) {
    elements.each(function (){
        let placement = $(this).data("maxlength-placement"),
            options = {
                alwaysShow: true,
                warningClass: "badge mt-1 badge-success",
                limitReachedClass: "badge mt-1 badge-danger"
            };

        if(!empty(placement)) options.placement = placement;
        $(this).maxlength(options)
    })
}
if($(document).find("[maxlength]").length) { setMaxLengthItems($(document).find("[maxlength]")); }


/**
 *
 * @param elementList[0 => ["elements to show"], 1 => ["elements to hide"], 2 => jQueryParentElement#Optional]
 *
 */
function toggleHiddenItems(elementList) {
    if(elementList.length < 2 || elementList.length > 3) return;
    let parent = elementList.length === 3 ? elementList[2] : $(document);

    for(let i = 0; i < elementList.length; i++) {
        let toggleList = elementList[i];
        if(empty(toggleList)) continue;

        for(let identifier of toggleList) {
            let element = parent.find(identifier).first();
            if(!element.length) continue;

            if(i === 0) element.show();
            else element.hide();
        }
    }
}


function generateTableDropdown(innerContent, id, linkText) {
    let expandedColumn = "";

    expandedColumn += "<div class='dropdown' style='max-width: inherit; white-space: inherit'>";
        expandedColumn += "<span class='no-after underline-it cursor-pointer hover-color-red dropdown-toggle' id='" + id + "' data-toggle='dropdown'";
            expandedColumn += " aria-haspopup='true' aria-expanded='false' ";
            expandedColumn += " data-is-loaded='false' style='max-width: inherit; white-space: inherit'>";
                expandedColumn += linkText;
            expandedColumn += "</span>";
            expandedColumn += "<div class='dropdown-menu mnw-400px' aria-labelledby='" + id + "' >";
                expandedColumn += innerContent;
            expandedColumn +=  "</div>";
        expandedColumn +=  "</div>";
    expandedColumn +=  "</div>";

    return expandedColumn;
}



function newImgDimension(targetRatio,currentRatio,imgW=null,imgH=null) {
    // Only a single dimension can be null.
    // If dimension is null, we assume that the other dimensions is already correctly calculated
    if(currentRatio < 1)
        return imgW === null ? {width:imgH*targetRatio,height:imgH} : {width:imgW,height:(imgW/targetRatio)};
    else
        return imgH === null ? {width:imgW,height:imgW/targetRatio} : {width:(imgH*targetRatio),height:imgH};
}

function fileExt (str) {
    return str.substring(str.lastIndexOf('.')+1);
}



/*
    Must by default contain status = success | error
    if error, error  =>  "some err message" is required
 */
function basicResponseHandling(response, objType = "object", requiredFields = ["status"]) {
    if(typeof response !== objType) return {status: false, error: "#930 Something went wrong. Please try again later"};
    if("status" in response && (response.status === "error" && !("error" in response))) return {status: false, error: "#932 Something went wrong. Please try again later"};

    if(response.status === "error") return {status: false, error: response.error};
    for(let key of requiredFields) if(!(key in response)) return {status: false, error: "#931 Something went wrong. Please try again later"};

    return {status: true, response}
}

function generateRandomNumber() {
    return parseInt((Math.random(Math.random(7,9999), (new Date()).getTime())) * (100000000));
}


function select2MultiInit(elements = []) {
    if(empty(elements)) elements = $(document).find(".select2Multi");
    elements.each(function () {
        let attributeSettings = $(this).data("select2-attr");
        if(empty(attributeSettings)) attributeSettings = {};
        let values = [];

        if(typeof attributeSettings !== "object") attributeSettings = JSON.parse(attributeSettings);
        $(this).select2(attributeSettings);
    });
}

function select2MultiRemove(elements = []) {
    if(empty(elements)) elements = $(document).find(".select2Multi");
    elements.each(function () {
        $(this).select2("remove");
    });
}

function select2MultiUnselectItem(selectElement, idToRemove) {
    var values = selectElement.val();
    if (values) {
        var i = values.indexOf(idToRemove);
        if (i >= 0) {
            values.splice(i, 1);
            selectElement.val(values).change();
        }
    }
}


function togglePasswordVisibility() {
    let passwordFields = $(document).find(".togglePwdVisibilityField");
    if(!passwordFields.length) return false;

    passwordFields.each(function () {
        let el = $(this);

        let clickIcon = $('<i class="mdi mdi-eye absolute-tr-10-10 font-16 cursor-pointer hover-opacity-half togglePwdVisibility" data-current-show="password"></i>');
        clickIcon.insertAfter(el);

        clickIcon.on("click", function () {
            let currentType = el.attr("type");
            if(currentType === "password") el.attr("type", "text");
            else el.attr("type", "password");
            $(this).attr("data-current-show", (currentType === "password" ? "text" : "password"))
        })
    })
}






function resolveAssetPath(path) {
    if (empty(path) || typeof path !== "string") return path;
    if(!path.includes("https://") && !path.includes("http://")) return serverHost + path;
    return path;
}




function IsImageOk(img) {
    // During the onload event, IE correctly identifies any images that
    // weren't downloaded as not complete. Others should too. Gecko-based
    // browsers act like NS4 in that they report this incorrectly.
    if (!img.complete) {
        return false;
    }

    // However, they do have two very useful properties: naturalWidth and
    // naturalHeight. These give the true size of the image. If it failed
    // to load, either of these should be zero.
    if (typeof img.naturalWidth != "undefined" && img.naturalWidth == 0) {
        return false;
    }

    // No other way of checking: assume it's ok.
    return true;
}