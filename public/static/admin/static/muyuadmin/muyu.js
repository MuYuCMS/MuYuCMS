    
    function isUrl(str) {
	    if (/^http[s]?:\/\/([\w\-\.]+)+[\w-]*([\w\-\.\/\?%&=]+)?$/ig.test(str)) {
		return true;
	    } else {
		return false;
	    }
        }
        
    function allcheck(ray,str){
        for(i=0;i<str.length;i++){
			if($(str[i]).is(":checked")){
				ray += $(str[i]).val()+',';
			}
		}
		return ray;
    }
    
    function isCheck(){
	    var IsCheck = document.getElementById("checkAll");
	    if(IsCheck.checked == false){
	        var inputs = document.getElementsByName("fav");
	        for(var i=0;i<inputs.length;i++){
	            inputs[i].checked = false;
	        }
	    }
	    if(IsCheck.checked == true){
	        var inputs = document.getElementsByName("fav");
	        for(var i=0;i<inputs.length;i++){
	            inputs[i].checked = true;
	        }
	    }
	}
	function checkNumRule(val) {
            var num_point_str = val.toString();
            if (num_point_str.indexOf('-')!==-1) { // 小于0
                return false;
            }
            var num_point = num_point_str.split('.');
 
            if (num_point_str.indexOf('.') !== -1) {
                if (num_point[1].toString().length === 0 || num_point[1].toString().length > 2) {
                    return false;
                } else {
                    return true;
                }
            } else if (parseFloat(val).toString() === 'NaN' || Number(val) < 0) {
                return false;
            } else {
                return true;
            }
        }