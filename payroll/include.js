function GetXmlHttpObject(handler)
{ 
    var objXmlHttp=null

    if (navigator.userAgent.indexOf("Opera")>=0) {
        alert("This example doesn't work in Opera") 
        return 
    }
    if (navigator.userAgent.indexOf("MSIE")>=0) { 
        var strName="Msxml2.XMLHTTP"
        if (navigator.appVersion.indexOf("MSIE 5.5")>=0) {
            strName="Microsoft.XMLHTTP"
        } 
        try { 
            objXmlHttp=new ActiveXObject(strName)
            objXmlHttp.onreadystatechange=handler 
            return objXmlHttp
        } catch(e) { 
            alert("Error. Scripting for ActiveX might be disabled") 
            return 
        } 
    } 
    if (navigator.userAgent.indexOf("Mozilla")>=0) {
        objXmlHttp=new XMLHttpRequest()
        objXmlHttp.onload=handler
        objXmlHttp.onerror=handler 
        return objXmlHttp
    }
}

var responseText;

function remoteCall(url)
{
    xmlHttp=GetXmlHttpObject(stateChanged);
    xmlHttp.open("GET", url , false);
    xmlHttp.send(null);
    return xmlHttp.responseText;

}

function stateChanged() 
{ 
    if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") {
        responseText = xmlHttp.responseText;
    }
} 
