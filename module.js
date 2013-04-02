
function showmoreoptions(selectobj){
	
	for(i = 0 ; i < selectobj.options.length ; i++){
		var divid = selectobj.options[i].value + 'params';
		divobj = document.getElementById(divid);
		if (divobj){
			divobj.style.visibility = 'hidden';
			divobj.style.display = 'none';
		}
	}
	
	if (selectobj.selectedIndex){
		var divid = selectobj.options[selectobj.selectedIndex].value + 'params';
		divobj = document.getElementById(divid);
		if (divobj){
			divobj.style.visibility = 'visible';
			divobj.style.display = 'block';
		}
	}
	
}