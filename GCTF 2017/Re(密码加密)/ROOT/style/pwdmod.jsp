<%@ page import="java.util.*" %>

<%@ include file = "../common/commonHeader.jsp"%>
<%@ include file = "../common/commonStyleParam.jsp" %>
<jsp:useBean id="pwdrule" class="com.zte.purviewext.PwdRule" scope="page"/>
<jsp:useBean id="i18n" class="com.zte.zxywpub.I18n" scope="page" />
<%
    HashMap pmap = pwdrule.getPolicy(true);
    String minlen = (String)pmap.get(i18n.getMessage("purview.minlen"));
	  System.out.println("minlen:" + minlen);
      String maxlen = (String)pmap.get(i18n.getMessage("purview.maxlen"));
      String chrtype = (String)pmap.get(i18n.getMessage("purview.chrtype"));
      String createtype = (String)pmap.get(i18n.getMessage("purview.createtype"));
      String defaultpwd = pwdrule.getInitPassword(pmap);
      String updateflag = (String)pmap.get(i18n.getMessage("purview.updateflag"));
      String maxwrglog = (String)pmap.get(i18n.getMessage("purview.maxwrglog"));
      String pwdmaxday = (String)pmap.get(i18n.getMessage("purview.pwdmaxday"));
      String usednum = (String)pmap.get(i18n.getMessage("purview.usednum"));
      String checkflag = (String)pmap.get(i18n.getMessage("purview.checkflag"));
      String hintflag = (String)pmap.get(i18n.getMessage("purview.hintflag"));
      String prerequisite = (String)pmap.get(i18n.getMessage("purview.prerequisite"));
%>
<%@ page import="com.zte.LocaleBean" %>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>修改密码</title>
<fmt:setLocale value="${LocaleBean.locale}" scope="session"/>
        <title><fmt:message key = "index.updatepass"/></title>
        <link rel = "stylesheet" href = "<%= path %>/common/commonstyle.css" type = "text/css"/>
        <script type = "text/javascript" src = "<%= path %>/common/js/support.js"></script>

        <script language = "javascript">
function userLogin() {

  if(checkpwd()==false){
    return false ;
  }
    document.inputForm.submit();
}
function OnKeyPress(evn,Next_ActiveControl,SenderType){
    var charCode = (navigator.appName == "Netscape") ? evn.which : evn.keyCode;
    status = charCode;
    if (charCode == 13){
                Next_ActiveControl.select();
                Next_ActiveControl.focus();
                return false;
        }
    switch (SenderType.toUpperCase()) {
        case 'int'.toUpperCase():
        if ((charCode < 48) || (charCode > 57)){
            return false;
        }
        break;

                case 'float'.toUpperCase():
        var i = 0;
        if ((charCode != 46) && (charCode < 48) || (charCode > 57)){
            evn.KeyCode = 0;
            return false;
        } else if (charCode == 46){
            if (Sender.value == "")
            return false;
            else{
                for(var i = 0; i < Sender.value.length; i++){
                    var sChar = Sender.value.charAt(i);
                    if (sChar == '.') return false;
                }
            }
        }
        break;

        case 'date'.toUpperCase():
        if (((charCode < 48) || (charCode > 57)) && (charCode!=45)){
            return false;
        }
        break;

        default:
        break;
        }
    return true;
}
function onPassword(evn) {
    var charCode = (navigator.appName == "Netscape") ? evn.which : evn.keyCode;
    if (charCode == 13){
        document.forms[0].password.blur();
        userLogin();
    }
}
function checkPwd() {
    var div = document.getElementById("error").style;
    var pwd1 = document.inputForm.newPassword.value;
    var pwd2 = document.inputForm.newPassword1.value;

    if (pwd1 == pwd2) {
        div.visibility = "hidden";
        if (pwd1 == "") {
            document.inputForm.Submit.disabled = true;
        }
        else {
            document.inputForm.Submit.disabled = false;
        }
    }
    else {
        document.inputForm.Submit.disabled = true;
        div.visibility = "visible";
    }
}
function checkPwdNoErrm() {
    var div = document.getElementById("error").style;
    var pwd1 = document.inputForm.newPassword.value;
    var pwd2 = document.inputForm.newPassword1.value;

    if (pwd1 == pwd2) {
        div.visibility = "hidden";
        if (pwd1 == "") {
            document.inputForm.Submit.disabled = true;
        }
        else {
            document.inputForm.Submit.disabled = false;
        }
    }
    else {
        document.inputForm.Submit.disabled = true;
     //   div.visibility = "visible";
    }
}

function resetxp() {
    document.getElementById("error").style.visibility = "hidden";
    document.inputForm.Submit.disabled = true;
    document.inputForm.reset();
}
         </script>

         <script language = "JavaScript">
             if (parent.frames.length > 0) parent.document.all.main.style.height = "400";
         </script>
<style type="text/css">
<!--
.STYLE1 {color: #ECECEC}
-->
</style>
</head>
<%
String flag001=com.zte.ZteParam.get("ssys_logo");
%>
<body onload = "checkPwdNoErrm()">
    <%
        String operid = (String)request.getAttribute("operid");
        String operpass = (String)request.getAttribute("operpwd");

        if (operid != null && operpass != null) {
    %>
 <form name = "inputForm" method = "post" action = "<%= path %>/style/dopwdset.jsp">
<div id="div01">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td align="center">
  <table width="1001" border="0" cellspacing="0" cellpadding="0">
    <tr>
       <td><img src="../images/lt/top<%=flag001%>.gif" width="1001" height="107" /></td>
    </tr>
    <tr>
      <td valign="top"><table width="1001" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td align="center"><img src="../images/lt/vasp_02<%=flag001%>.jpg" width="1002" height="94" /></td>
          </tr>
        <tr>
            <td align="center"><img src="../images/lt/vasp<%=flag001%>.jpg" width="1002" height="141" /></td>
        </tr>
        <tr>
          <td height="190" align="center" background="../images/lt/vasp_04.jpg"><table width="230" border="0" cellspacing="10" cellpadding="0">
            <tr>
              <td colspan="2" align="left" valign="bottom" class="font1 STYLE1"><img src="../images/lt/uni<%=flag001%>.gif" width="180" height="28" /></td>
              </tr>
            <tr>
                                <td align = "right"  nowrap="nowrap">
                                   <fmt:message key = "index.password1"/>
								 </td>
                                 <td>
								<input type = "hidden" name = "oldPassword" value = "<%= operpass %>"/>
                                <input type = "hidden" name = "userId" value = "<%= operid %>"/>
                                <input type = "password" name = "newPassword" class = "input-style1" onKeyPress = "return OnKeyPress(event,document.inputForm.newPassword1,'')" onkeyup = "checkPwdNoErrm()"/>
							     </td>
            </tr>
            <tr>
                          <td align = "right"  nowrap="nowrap"><fmt:message key = "index.password2"/>:</td>
                                 <td>
  <input type = "password" name = "newPassword1" class = "input-style1" onKeyPress = "return onPassword(event)" onkeyup = "checkPwd()"/>
     <input type = "hidden" name = "servicekey" value = "<%= "all" %>">
                                      <div id = "error" >
                                       <font color = "red">
                                           <fmt:message key = "index.passworderror"/></font>
                                    </div>
                                 </td>

            </tr>
            <tr>
              <td>&nbsp;</td>
              <td><label>
       <input name = "Submit" type = "button" class = "button" value = '<fmt:message key="button.sendIn"/>' onclick = "userLogin()" disabled = "treu"/>
                    &nbsp;&nbsp;&nbsp;
       <input name = "resetx" type = "button" class = "button" value = '<fmt:message key="button.reset"/>' onclick = "resetxp()"/>
              </label></td>
            </tr>
          </table></td>
        </tr>
      </table>
      </td>
    </tr>
    <tr>
      <td height="60" align="center"><img src="../images/lt/f<%=flag001%>.gif" width="168" height="27" /></td>
    </tr>
  </table>
</td></tr>
</table>
</div>
</form>
            <%
                } else {
                    response.sendRedirect(request.getContextPath() + "/index.jsp");
                }
            %>
</body>
</html>
<script language = "javascript">
	function checkpwd0(pwd){
		  var allValid = true;
		  for (i = 0;i<pwd.length;i++)
		  {

		    ch = pwd.charAt(i);
		    if((ch<='9' && ch >= '0') || (ch <='z' && ch >= 'a') ||(ch <='Z' && ch >='A')){ 
		    }
		    else{
		     allValid = false;
		    }
		  }
		  if (!allValid) return (false);
		  else return (true);
		}
		function checkpwd1(pwd){
		  var allValid = true;
		  for (i = 0;i<pwd.length;i++)
		  {

		    ch = pwd.charAt(i);
		    if((ch<='9' && ch >= '0')){ 
		    }
		    else{
		     allValid = false;
		    }
		  }
		  if (!allValid) return (false);
		  else return (true);
		}
		function checkpwd2(pwd){
		  var allValid = true;
		  for (i = 0;i<pwd.length;i++)
		  {
		    ch = pwd.charAt(i);
		    if((ch <='z' && ch >= 'a') ||(ch <='Z' && ch >='A')){ 
		    }
		    else{
		     allValid = false;
		    }
		  }
		  if (!allValid) return (false);
		  else return (true);
		}
		function checkpwd3(pwd){
		  var allValid = true;
		  for (i = 0;i<pwd.length;i++)
		  {
		    ch = pwd.charAt(i);
		    if(((ch<='9' && ch >= '0')||(ch <='z' && ch >= 'a') ||(ch <='Z' && ch >='A'))&&!checkpwd2(pwd) &&!checkpwd1(pwd) ){
		    }
		    else{
		     allValid = false;
		    }
		  }
		  if (!allValid) return (false);
		  else return (true);
		}				
	function checkpwd(){
		var fm = document.inputForm;
		//-
		 if(fm.newPassword.value.length > <%=maxlen%> || fm.newPassword.value.length < <%=minlen%> ){
			alert(
			'<fmt:message key="operinfo.pwdalert"><fmt:param><%=maxlen%></fmt:param><fmt:param><%=minlen%></fmt:param></fmt:message>');
			return false;
		 }
		//-
		if("0" == "<%=chrtype%>") {

			if(!checkpwd0(fm.newPassword.value)){
				alert('<fmt:message key="operInfo.pwdcharacterillegal"/>');
				fm.newPassword.value = '';
				fm.newPassword1.value = '';         
				fm.newPassword.focus();
				return false;
			}else{
				return true;
			}
			
			
		}else if("1" == "<%=chrtype%>"){
			if(!checkpwd1(fm.newPassword.value)){
				alert('<fmt:message key="operInfo.pwdcharacterillegal"/>');
				fm.newPassword.value = '';
				fm.newPassword1.value = '';         
				fm.newPassword.focus();
				return false;
			}else{
				return true;
			}
		}else if("2" == "<%=chrtype%>"){
			if(!checkpwd2(fm.newPassword.value)){
				alert('<fmt:message key="operInfo.pwdcharacterillegal"/>');
				fm.newPassword.value = '';
				fm.newPassword1.value = '';         
				fm.newPassword.focus();
				return false;
			}else{
				return true;
			}
		}else if("3" == "<%=chrtype%>"){
			if(!checkpwd3(fm.newPassword.value)){
				alert('<fmt:message key="operInfo.pwdcharacterillegal"/>');
				fm.newPassword.value = '';
				fm.newPassword1.value = '';         
				fm.newPassword.focus();
				return false;
			}else{
				return true;
			}
		}
		
	}    
</script>