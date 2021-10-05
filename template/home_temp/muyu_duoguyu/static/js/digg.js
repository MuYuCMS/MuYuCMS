
 
function digg(url, mid, cid, d){
	var saveid = GetCookie('diggid');
	if (saveid == cid) {
	    layer.msg('亲，过多的赞美容易让人骄傲哟～');
	} else{
			  
		$.ajax({
			type: 'POST',
			url: url, 
			data: 'modelid='+mid+'&id='+cid+'&digg='+d,
			dataType: "json", 
			success: function (msg) {
				if(msg.status == 1){
					var id = d ? 'up' : 'down';
					$('#'+id).html(msg.message);
				}else{
					layer.msg(msg.message);
				}
			}
		})
		
		SetCookie('diggid', cid, 1);
		return true;				  
	}
}
 
function GetCookie(c_name){
    if (document.cookie.length > 0){
        c_start = document.cookie.indexOf(c_name + "=")
        if (c_start != -1){
            c_start = c_start + c_name.length + 1;
            c_end   = document.cookie.indexOf(";",c_start);
            if (c_end == -1){
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start,c_end));
        }
    }
    return null
}
 
function SetCookie(c_name, value, expiredays){
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = c_name + "=" +escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString()); 
}

// SITE: www.yzmcms.com