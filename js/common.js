// $Id: common.js,v 1.11 2006/01/26 13:59:31 max Exp $

//
// Enviroment identificator
//
var localIsDOM = document.getElementById?true:false;
var localIsJava = navigator.javaEnabled();
var localIsStrict = document.compatMode=='CSS1Compat';
var localPlatform = navigator.platform;
var localVersion = "0";
var localBrowser = "";
var localBFamily = "";
if (window.opera && localIsDOM) {
	localBFamily = localBrowser = "Opera";
	if (navigator.userAgent.search(/^.*Opera.([\d.]+).*$/) != -1)
		localVersion = navigator.userAgent.replace(/^.*Opera.([\d.]+).*$/, "$1");
	else if (window.print)
		localVersion = "6";
	else
		localVersion = "5";
} else if (document.all && document.all.item)
	localBFamily = localBrowser = 'MSIE';
if (navigator.appName=="Netscape") {
	localBFamily = "NC";
	if (!localIsDOM) {
		localBrowser = 'Netscape';
		localVersion = navigator.userAgent.replace(/^.*Mozilla.([\d.]+).*$/, "$1");
		if(localVersion != '')
			localVersion = "4";
	} else if(navigator.userAgent.indexOf("Safari") >= 0)
		localBrowser = 'Safari';
	else if (navigator.userAgent.indexOf("Netscape") >= 0)
		localBrowser = 'Netscape';
	else if (navigator.userAgent.indexOf("Firefox") >= 0)
		localBrowser = 'Firefox';
	else 
		localBrowser = 'Mozilla';
	
}
if (navigator.userAgent.indexOf("MSMSGS") >= 0)
	localBrowser = "WMessenger";
else if (navigator.userAgent.indexOf("e2dk") >= 0)
	localBrowser = "Edonkey";
else if (navigator.userAgent.indexOf("Gnutella") + navigator.userAgent.indexOf("Gnucleus") >= 0)
	localBrowser = "Gnutella";
else if (navigator.userAgent.indexOf("KazaaClient") >= 0)
	localBrowser = "Kazaa";

if (localVersion == '0' && localBrowser != '') {
	var rg = new RegExp("^.*"+localBrowser+".([\\d.]+).*$");
	localVersion = navigator.userAgent.replace(rg, "$1");
}
var localIsCookie = ((localBrowser == 'Netscape' && localVersion == '4')?(document.cookie != ''):navigator.cookieEnabled);

//
// Opener/Closer HTML block
//
function visibleBox(id,skipOpenClose) {
	elm1 = document.getElementById("open"+id);
	elm2 = document.getElementById("close"+id);
	elm3 = document.getElementById("box"+id);

	if(!elm3)
		return false;

	if (skipOpenClose) {
		elm3.style.display = (elm3.style.display == "")?"none":"";
	} else if(elm1) {
		if (elm1.style.display == "") {
			elm1.style.display = "none";
			if(elm2)
				elm2.style.display = "";
			elm3.style.display = "none";
		} else {
			elm1.style.display = "";
			if(elm2)
				elm2.style.display = "none";
			elm3.style.display = "";
		}
	}
}

//
// URL encode
//
function urlEncode(url) {
	return url.replace(/\s/g, "+").replace(/&/, "&amp;").replace(/"/, "&quot;")
}

/*
	Price format
*/
function price_format(price) {
var x, cnt, top, botom;
	precision = number_format_point;
	precision = Math.pow(10, precision);
	price = Math.round(price*precision)/precision;
	top = Math.floor(price);
	bottom = Math.round((price-top)*precision)+precision;
	top = top+"";
	bottom = bottom+"";
	cnt = 0;
	for(x = top.length; x >= 0; x--) {
		if(cnt%3 == 0 && cnt > 0 && x > 0)
			top = top.substr(0,x)+number_format_th+top.substr(x,top.length);
		cnt++;
	}
	price = top+number_format_dec+bottom.substr(1,bottom.length);
	return price;
}

/*
	Substitute
*/
function substitute(lbl) {
var x, rg;
	for(x = 1; x < arguments.length; x+=2) {
		if(arguments[x] && arguments[x+1]) {
			rg = new RegExp("\\{\\{"+arguments[x]+"\\}\\}", "gi");
			lbl = lbl.replace(rg,  arguments[x+1]);
			rg = new RegExp('~~'+arguments[x]+'~~', "gi");
			lbl = lbl.replace(rg,  arguments[x+1]);
		}
	}
	return lbl;
}

function getWindowWidth(w) {
	if (!w)
		w = window;
    if (localBFamily == "MSIE")
		return w.document.body.clientWidth;
    else 
		return w.innerWidth;
}

function getWindowHeight(w) {
	if (!w)
		w = window;
    if (localBFamily == "MSIE")
		return w.document.body.clientHeight;
	else
		return w.innerHeight;
}

function getDocumentHeight(w){
	if (!w)
		 w = window;
	if (localBFamily == "MSIE" || (localBFamily == "Opera" && localVersion >= 7 && localVersion < 8))
		return isStrict ? w.document.documentElement.scrollHeight : w.document.body.scrollHeight;
	if (localBFamily == "NC")
		return w.document.height
	if (localBFamily == "Opera")
		return w.document.body.style.pixelHeight
}

/*
	Check list of checkboxes
*/
function checkMarks(form, reg, lbl) {
var is_exist = false;

	if (form.elements.length == 0)
		return true;

	for (var x = 0; x < form.elements.length; x++) {
		if (form.elements[x].name.search(reg) == 0 && form.elements[x].type == 'checkbox' && !form.elements[x].disabled) {
			is_exist = true;
			if (form.elements[x].checked)
				return true;
		}
	}

	if (!is_exist)
		return true;
	else if (lbl)
		alert(lbl);
	else if (lbl_no_items_have_been_selected)
		alert(lbl_no_items_have_been_selected);
	return false;
}

/*
	Submit form with specified value of 'mode' parmaeters
*/
function submitForm(formObj, formMode) {
	if (!formObj)
		return false;
	if (formObj.tagName != "FORM") {
		if (!formObj.form)
			return false;
		formObj = formObj.form;
	}
	if (formObj.mode)
		formObj.mode.value = formMode;
	formObj.submit();
}

/*
	Analogue of PHP function sleep()
*/
function sleep(msec) {
	var then = new Date().getTime()+msec;
	while (then >= new Date().getTime()){
	}
}

/*
	Convert number from current format
	(according to 'Input and display format for floating comma numbers' option)
	to float number
*/
function convert_number(num) {
	var regDec = new RegExp(reg_quote(number_format_dec), "gi");
	var regTh = new RegExp(reg_quote(number_format_th), "gi");
	var pow = Math.pow(10, parseInt(number_format_point));
	num = parseFloat(num.replace(" ", "").replace(regTh, "").replace(regDec, "."));
	return Math.round(num*pow)/pow;
}

/*
	Check string as number
	(according to 'Input and display format for floating comma numbers' option)
*/
function check_is_number(num) {
	var regDec = new RegExp(reg_quote(number_format_dec), "gi");
	var regTh = new RegExp(reg_quote(number_format_th), "gi");
	num = num.replace(" ", "").replace(regTh, "").replace(regDec, ".");
	return (num.search(/^[0-9]+(\.[0-9]+)?$/) != -1);
}

/*
	Qutation for RegExp class
*/
function reg_quote(s) {
	return s.replace(/\./, "\\.").replace(/\//, "\\/").replace(/\*/, "\\*").replace(/\+/, "\\+");
}

function setCookie(name, value) {
	var date = new Date();
	date.setFullYear(date.getYear()+1);
	document.cookie = name+"="+escape(value)+"; expires="+date.toGMTString();
}

function deleteCookie(name) {
	document.cookie = name+"=0; expires=Fri, 31 Dec 1999 23:59:59 GMT;";
}
