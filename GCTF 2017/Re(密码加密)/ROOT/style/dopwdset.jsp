<%@ include file = "../common/commonHeader.jsp"%>
<jsp:useBean id = "Purview" class = "com.zte.zxywpub.Purview" scope = "page"/>

<%
    String operId = (String) session.getAttribute("OPERID");
    String newPassword = (String) request.getParameter("newPassword");
    String url = request.getContextPath() + "/index.jsp";
    if (newPassword != null) {
        url = request.getContextPath() + "/index_0.jsp";
		try{
			Purview.changePwd(operId, newPassword);
		}catch(Exception e){
			e.printStackTrace();
            url = request.getContextPath() + "/style/pwdmod.jsp";
			%>
			<script>
				alert("<fmt:message key="index.pwdmod.impacterror"/>");
				top.document.location.href = '<%= url %>';
			</script>
			<%
		}
       
    }
%>
<html>
<script>top.document.location.href = '<%= url %>';</script>
</html>
